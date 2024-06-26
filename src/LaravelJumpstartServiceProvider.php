<?php

namespace Infinity\Jumpstart;

use Illuminate\Support\Facades\File;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelJumpstartServiceProvider extends PackageServiceProvider
{
    const PACKAGE_JSON = 'package.json';

    const NPM_COMMANDS = [
        'dev' => 'vite',
        'build' => 'run-p type-check "build-only {@}" --',
        'preview' => 'vite preview',
        'test:unit' => 'vitest --dir ./resources/ts/',
        'build-only' => 'vite build',
        'type-check' => 'vue-tsc --build --force',
        'lint' => 'eslint . --ext .vue,.js,.jsx,.cjs,.mjs,.ts,.tsx,.cts,.mts --fix --ignore-path .gitignore',
        'format' => 'prettier --write ./resources/ts/',
    ];

    const NPM_DEPENDENCIES = [
        '@rushstack/eslint-patch' => '^1.8.0',
        '@tailwindcss/forms' => '^0.5.3',
        '@tsconfig/node20' => '^20.1.4',
        '@types/jsdom' => '^21.1.6',
        '@types/node' => '^20.12.5',
        '@vitejs/plugin-vue' => '^5.0.4',
        '@vue/eslint-config-prettier' => '^9.0.0',
        '@vue/eslint-config-typescript' => '^13.0.0',
        '@vue/test-utils' => '^2.4.5',
        '@vue/tsconfig' => '^0.5.1',
        '@vueuse/core' => '^10.9.0',
        'autoprefixer' => '^10.4.2',
        'axios' => '^1.6.8',
        'eslint' => '^8.57.0',
        'eslint-plugin-vue' => '^9.23.0',
        'jsdom' => '^24.0.0',
        'laravel-vite-plugin' => '^1.0.2',
        'npm-run-all2' => '^6.1.2',
        'pinia' => '^2.1.7',
        'postcss' => '^8.4.38',
        'prettier' => '^3.2.5',
        'tailwindcss' => '^3.4.3',
        'typescript' => '~5.4.0',
        'vite' => '^5.2.11',
        'vitest' => '^1.2.2',
        'vue' => '^3.4.21',
        'vue-router' => '^4.3.0',
        'vue-tsc' => '^2.0.11',
    ];

    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-jumpstart')
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->startWith(function (InstallCommand $command) {
                        $command->comment('Removing old files...');
                        $this->deleteOldFiles();

                        $command->comment('Copying new files...');
                        File::copyDirectory(dirname(__DIR__).'/stubs', base_path());

                        $command->comment('Adding dependencies');
                        $this->modifyNodePackage();
                    });
            });
    }

    /**
     * Remove the existing files that are no longer needed.
     */
    protected function deleteOldFiles(): void
    {
        File::delete(base_path('vite.config.js'));
        File::delete(resource_path('views/welcome.blade.php'));
        File::deleteDirectory(resource_path('js'));
    }

    /**
     * Adjust the node dependencies.
     */
    protected function modifyNodePackage(): void
    {
        $packageFileName = base_path(self::PACKAGE_JSON);
        $configuration = File::json($packageFileName);

        unset($configuration['dependencies'], $configuration['devDependencies']);

        $configuration['scripts'] = self::NPM_COMMANDS;
        $configuration['devDependencies'] = self::NPM_DEPENDENCIES;

        ksort($configuration['devDependencies']);

        $data = json_encode($configuration, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        File::put(self::PACKAGE_JSON, $data);
    }
}
