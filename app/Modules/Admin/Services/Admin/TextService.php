<?php


namespace App\Modules\Admin\Services\Admin;


use App;
use App\Modules\Admin\Models\Statics\Text;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * @property array config
 * @property Filesystem files
 */
class TextService
{
    const JSON_GROUP = '_json';

    /**
     * @var
     */
    protected $config;

    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * TextService constructor.
     */
    public function __construct()
    {
        $this->files = new Filesystem();
        $this->config = config('language_manager');
    }

    /**
     * @param bool $replace
     * @param null $base
     * @param bool $import_group
     */
    public function importTranslations($replace = false, $base = null, $import_group = false)
    {
        $basePath = App::langPath();
        $counter = 0;
        //allows for vendor lang files to be properly recorded through recursion.
        $vendor = true;
        if ($base == null) {
            $base = $basePath;
            $vendor = false;
        }

        foreach ($this->files->directories($base) as $langPath) {
            $locale = basename($langPath);

            //import langfiles for each vendor
            if ($locale == 'vendor') {
                foreach ($this->files->directories($langPath) as $vendor) {
                    $counter += $this->importTranslations($replace, $vendor);
                }

                continue;
            }
            $vendorName = $this->files->name($this->files->dirname($langPath));
            foreach ($this->files->allfiles($langPath) as $file) {
                $info = pathinfo($file);
                $group = $info['filename'];
                if ($import_group) {
                    if ($import_group !== $group) {
                        continue;
                    }
                }

                if (in_array($group, $this->config['exclude_groups'])) {
                    continue;
                }
                $subLangPath = str_replace($langPath . DIRECTORY_SEPARATOR, '', $info['dirname']);
                $subLangPath = str_replace(DIRECTORY_SEPARATOR, '/', $subLangPath);
                $langPath = str_replace(DIRECTORY_SEPARATOR, '/', $langPath);

                if ($subLangPath != $langPath) {
                    $group = $subLangPath . '/' . $group;
                }

                if (!$vendor) {
                    $translations = \Lang::getLoader()->load($locale, $group);
                } else {
                    $translations = include $file;
                    $group = 'vendor/' . $vendorName;
                }

                if ($translations && is_array($translations)) {
                    foreach (Arr::dot($translations) as $key => $value) {
                        $importedTranslation = $this->importTranslation($key, $value, $locale, $group, $replace);
                        $counter += $importedTranslation ? 1 : 0;
                    }
                }
            }
        }
    }

    /**
     * @param $key
     * @param $value
     * @param $locale
     * @param $group
     * @param bool $replace
     * @return bool
     */
    public function importTranslation($key, $value, $locale, $group, $replace = false)
    {
        // process only string values
        if (is_array($value)) {
            return false;
        }
        $value = (string) $value;
        $translation = Text::firstOrNew([
            'locale' => $locale,
            'group'  => $group,
            'key'    => $key,
        ]);

        // Check if the database is different then the files
        $newStatus = $translation->value === $value ? Text::STATUS_SAVED : Text::STATUS_CHANGED;
        if ($newStatus !== (int) $translation->status) {
            $translation->status = $newStatus;
        }

        // Only replace when empty, or explicitly told so
        if ($replace || ! $translation->value) {
            $translation->value = $value;
        }

        $translation->save();

        return true;
    }

    /**
     * @param $langsData
     * @param $fileName
     * @param false $createEvent
     *
     * @return $this
     * @throws \Exception
     */
    public function postEdit($langsData, $fileName,$createEvent = false) : self
    {
        foreach($langsData as $key => $langData) {
            if(!in_array($fileName, $this->config['exclude_groups'])) {

                foreach(config('language_manager.locales') as $locale) {

                    if (Text::where('group', $fileName)->where('key', $key)->where('locale', $locale)->exists() && $createEvent ) {
                        throw new \Exception('THIS_KEY_ALREADY_TAKEN');
                    }

                    $translation = Text::firstOrNew([
                        'locale'    => $locale,
                        'group'     => $fileName,
                        'key'       => $key
                    ]);
                    $translation->value = (string) $langData[$locale] ?: null;
                    $translation->status = Text::STATUS_CHANGED;
                    $translation->save();

                }
            }
        }

        return $this;
    }

    /**
     * @param null $group
     * @param bool $json
     * @return mixed
     */
    public function exportTranslations($group = null, $json = false)
    {
        $basePath = App::langPath();

        if (! is_null($group) && ! $json) {
            if (! in_array($group, $this->config['exclude_groups'])) {
                $vendor = false;
                if ($group == '*') {
                    return $this->exportAllTranslations();
                } else {
                    if (Str::startsWith($group, 'vendor')) {
                        $vendor = true;
                    }
                }

                $tree = $this->makeTree(Text::ofTranslatedGroup($group)
                    ->orderByGroupKeys(Arr::get($this->config, 'sort_keys', false))
                    ->get());

                foreach ($tree as $locale => $groups) {
                    if (isset($groups[$group])) {
                        $translations = $groups[$group];
                        $path = $basePath;

                        $localePath = $locale.DIRECTORY_SEPARATOR.$group;
                        if ($vendor) {
                            $path = $basePath.'/'.$group.'/'.$locale;
                            $localePath = Str::after($group, '/');
                        }
                        $subFolders = explode(DIRECTORY_SEPARATOR, $localePath);
                        array_pop($subFolders);

                        $subFolderLevel = '';
                        foreach ($subFolders as $subFolder) {
                            $subFolderLevel = $subFolderLevel.$subFolder.DIRECTORY_SEPARATOR;

                            $temp_path = rtrim($path.DIRECTORY_SEPARATOR.$subFolderLevel, DIRECTORY_SEPARATOR);
                            if (! is_dir($temp_path)) {
                                mkdir($temp_path, 0777, true);
                            }
                        }

                        $path = $path.DIRECTORY_SEPARATOR.$locale.DIRECTORY_SEPARATOR.$group.'.php';

                        $output = "<?php\n\nreturn ".var_export($translations, true).';'.\PHP_EOL;
                        $this->files->put($path, $output);
                    }
                }
                Text::ofTranslatedGroup($group)->update(['status' => Text::STATUS_SAVED]);
            }
        }

        if ($json) {
            $tree = $this->makeTree(Text::ofTranslatedGroup(self::JSON_GROUP)
                ->orderByGroupKeys(Arr::get($this->config, 'sort_keys', false))
                ->get(), true);

            foreach ($tree as $locale => $groups) {
                if (isset($groups[self::JSON_GROUP])) {
                    $translations = $groups[self::JSON_GROUP];
                    $path = $basePath.'/'.$locale.'.json';
                    $output = json_encode($translations, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE);
                    $this->files->put($path, $output);
                }
            }

            Text::ofTranslatedGroup(self::JSON_GROUP)->update(['status' => Text::STATUS_SAVED]);
        }

    }

    /**
     * @param $translations
     * @param bool $json
     * @return array
     */
    protected function makeTree($translations, $json = false)
    {
        $array = [];
        foreach ($translations as $translation) {
            if ($json) {
                $this->jsonSet($array[$translation->locale][$translation->group], $translation->key,
                    $translation->value);
            } else {
                Arr::set($array[$translation->locale][$translation->group], $translation->key,
                    $translation->value);
            }
        }

        return $array;
    }

    /**
     * @param $array
     * @param $key
     * @param $value
     * @return mixed
     */
    public function jsonSet(&$array, $key, $value)
    {
        if (is_null($key)) {
            return $array = $value;
        }
        $array[$key] = $value;

        return $array;
    }

    /**
     * @param $namespace
     * @param $group
     * @param $key
     */
    public function missingKey($namespace, $group, $key)
    {
        if (! in_array($group, $this->config['exclude_groups'])) {
            Text::firstOrCreate([
                'locale' => config('app.locale'),
                'group'  => $group,
                'key'    => $key,
            ]);
        }
    }

    /**
     * @param Request $request
     *
     * @return $this
     * @throws \Exception
     */
    public function deleteText(Request $request) : self
    {
        Text::where('group', $request->get('file'))
                ->where('key', $request->get('key'))->delete();

        return $this;
    }

}
