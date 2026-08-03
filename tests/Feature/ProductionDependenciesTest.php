<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class ProductionDependenciesTest extends TestCase
{
    private function devPackageDirectories(): array
    {
        $composer = json_decode(file_get_contents(base_path_of('composer.json')), true);
        $devPackages = array_keys($composer['require-dev'] ?? []);

        $dirs = [];
        foreach ($devPackages as $package) {
            $path = base_path_of('vendor/'.$package);
            if (is_dir($path)) {
                $dirs[$package] = realpath($path);
            }
        }

        return $dirs;
    }

    private function productionSourceFiles(): array
    {
        $files = [];
        foreach (['app', 'config', 'routes', 'database'] as $dir) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(base_path_of($dir), \FilesystemIterator::SKIP_DOTS)
            );
            foreach ($iterator as $file) {
                if ($file->getExtension() === 'php') {
                    $files[] = $file->getPathname();
                }
            }
        }

        return $files;
    }

    public function test_no_production_code_depends_on_a_dev_only_package(): void
    {
        $devDirs = $this->devPackageDirectories();

        if ($devDirs === []) {
            $this->markTestSkipped('Dev dependencies are not installed.');
        }

        $loader = require base_path_of('vendor/autoload.php');
        $offenders = [];

        foreach ($this->productionSourceFiles() as $file) {
            preg_match_all('/^use\s+(\\\\?[A-Za-z0-9_\\\\]+)\s*(?:as\s+\w+)?;/m', file_get_contents($file), $m);

            foreach ($m[1] as $class) {
                $class = ltrim($class, '\\');

                $classFile = $loader->findFile($class);
                if ($classFile === false) {
                    continue;
                }

                $classFile = realpath($classFile);
                foreach ($devDirs as $package => $dir) {
                    if ($classFile !== false && str_starts_with($classFile, $dir.DIRECTORY_SEPARATOR)) {
                        $offenders[] = sprintf(
                            '%s uses %s, provided by %s (require-dev)',
                            str_replace(base_path_of('').'/', '', $file),
                            $class,
                            $package
                        );
                    }
                }
            }
        }

        $this->assertSame(
            [],
            array_values(array_unique($offenders)),
            "Production code references classes that only exist when dev dependencies are installed.\n"
            ."A `composer install --no-dev` deploy will fatal on these:\n  ".
            implode("\n  ", array_unique($offenders))."\n"
        );
    }
}

if (! function_exists('Tests\Feature\base_path_of')) {
    function base_path_of(string $path): string
    {
        return rtrim(dirname(__DIR__, 2).'/'.$path, '/');
    }
}
