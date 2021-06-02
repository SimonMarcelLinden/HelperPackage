<?php

namespace SimonMarcelLinden\Presets;

use Illuminate\Filesystem\Filesystem;

abstract class Preset {

    /**
     * Ensure the component directories we need exist.
     *
     * @param mixed $directories
     * @return void
     */
    protected static function ensureComponentDirectoryExists($directories): void{
        if( is_array($directories)) {
            foreach ($directories as $directory) {
                static::createDirectoryIfNotExists($directory);
            }
        } else {
            static::createDirectoryIfNotExists($directories);
        }
    }

    /**
     * Make sure the directory still exists.
     *
     * @param String $path
     * @return void
     */
    private static function createDirectoryIfNotExists(String $path): void {
        $filesystem = new Filesystem;
        if (! $filesystem->isDirectory($directory = $path )) {
            $filesystem->makeDirectory($directory, 0755, true);
        }
    }

    /**
     * Update the "package.json" file.
     *
     * @param  bool  $dev
     * @return void
     */
    protected static function updatePackages($dev = true) {
        if (! file_exists(base_path('package.json'))) {
            return;
        }

        $configurationKey = $dev ? 'devDependencies' : 'dependencies';

        $packages = json_decode(file_get_contents(base_path('package.json')), true);

        $params = array_key_exists($configurationKey, $packages) ? $packages[$configurationKey] : [];

        // $packages[$configurationKey] = static::updatePackageArray($params, $configurationKey);
        $packages[$configurationKey] = static::updatePackageArray($params);

        ksort($packages[$configurationKey]);

        file_put_contents( base_path('package.json'), json_encode($packages, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT).PHP_EOL );
    }

    /**
     * Remove the installed Node modules.
     *
     * @return void
     */
    protected static function removeNodeModules(): void {
        tap(new Filesystem, function ($files) {
            $files->deleteDirectory(base_path('node_modules'));

            $files->delete(base_path('yarn.lock'));
        });
    }

    /**
     * Update the given package array.
     *
     * @param  array  $packages | return ['vue' => '^4.0.0',] + Arr::except($packages, ['react-dom', ]);
     * @return array
     */
    abstract static function updatePackageArray(array $packages);
}
