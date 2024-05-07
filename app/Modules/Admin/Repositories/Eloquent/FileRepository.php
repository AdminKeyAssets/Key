<?php

namespace App\Modules\Admin\Repositories\Eloquent;

use App\Modules\Admin\Models\Statics\File;
use App\Modules\Admin\Repositories\Contracts\IFileRepository;
use App\Repositories\Eloquent\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Image;
use Storage;

/**
 * @property UploadedFile uploadedFile
 */
class FileRepository extends BaseRepository implements IFileRepository
{

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var
     */
    protected $file;

    /**
     * @var
     */
    protected $uploadedFile;

    /**
     * @var string
     */
    protected $src = '';

    /**
     * @var string
     */
    protected $disk = 'public';

    /**
     * FileRepository constructor.
     * @param File $model
     */
    public function __construct(File $model)
    {
        parent::__construct($model);
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @param UploadedFile $file
     * @return $this
     */
    public function setFile(UploadedFile $file)
    {
        $this->uploadedFile = $file;
        return $this;
    }

    /**
     * @param string $disk
     * @return $this|string
     */
    public function setFileSystemMode($disk = 'public')
    {
        $this->disk = $disk;
        return $this;
    }

        /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Upload image.
     */
    public function uploadFile()
    {
        /** @var $type string */
        $type = $this->request->get('type');

        // Save Original.
        $this->src = Storage::disk($this->disk)->put($type . '/'.File::ORIGINAL_FILE_FOLDER_NAME.'/' . $type,$this->uploadedFile);

        // If file type is image, upload also resize some px.
        if ($this->request->get('file_type') == 'image') {
            $this->uploadFileSomeResolution();
        }

        // Save file src and type in db..
        $this->saveInDb();

        return $this;
    }

    /**
     * Resize image resolutions.
     */
    private function uploadFileSomeResolution()
    {
        if(!config('admin.image.upload_resolutions')) {
            return;
        }

        foreach(config('admin.image.resolutions') as $resolution) {
            /** @var $img \Intervention\Image\Image */
            $file = Image::make($this->uploadedFile)->resize($resolution, null, function ($constraint) {
                $constraint->aspectRatio();
            });

            Storage::disk($this->disk)->put(str_replace(File::ORIGINAL_FILE_FOLDER_NAME, $resolution,$this->src),(string)$file->encode());
        }
    }

    /**
     * @return Model
     */
    public function saveInDb()
    {
        return $this->file = $this->create([
            'src'           => $this->src,
            'type'          => $this->request->get('type'),
            'file_type'     => $this->request->get('file_type')
        ]);
    }

}
