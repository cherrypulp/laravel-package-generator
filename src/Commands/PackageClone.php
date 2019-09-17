<?php

namespace Cherrypulp\LaravelPackageGenerator\Commands;

use Cherrypulp\LaravelPackageGenerator\Commands\Traits\ChangesComposerJson;
use Cherrypulp\LaravelPackageGenerator\Commands\Traits\InteractsWithComposer;
use Cherrypulp\LaravelPackageGenerator\Commands\Traits\InteractsWithGit;
use Cherrypulp\LaravelPackageGenerator\Commands\Traits\InteractsWithUser;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class PackageClone extends Command
{
    use InteractsWithComposer;
    use InteractsWithGit;
    use InteractsWithUser;
    use ChangesComposerJson;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package:clone
                            {url : The url of the Github repository}
                            {vendor? : The vendor part of the namespace}
                            {package? : The name of package for the namespace}
                            {--b|branch=master : The branch to clone}
                            {--i|interactive : Interactive mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clone an existing package.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $url = $this->argument('url');

        try {
            list($vendorFolderName, $packageFolderName) = $this->getVendorAndFolderName($url);
        } catch (Exception $e) {
            $this->error($e->getMessage());

            return -1;
        }

        $vendor = $this->getVendor() ?: Str::title($vendorFolderName);
        $package = $this->getPackage() ?: Str::studly($packageFolderName);

        $relPackagePath = "workbench/$vendorFolderName/$packageFolderName";
        $packagePath = base_path($relPackagePath);

        try {
            $this->cloneRepo($url, $packagePath, $this->option('branch'));
            $this->registerPackage($vendor, $package, $relPackagePath);
            $this->composerUpdatePackage($vendorFolderName, $packageFolderName);
            $this->composerDumpAutoload();

            $this->info('Finished.');
        } catch (Exception $e) {
            $this->error($e->getMessage());

            return -1;
        }

        return 0;
    }

    /**
     * Get vendor and package folder name.
     *
     * @param $url
     *
     * @return array
     */
    protected function getVendorAndFolderName($url)
    {
        if (Str::contains($url, '@')) {
            $vendorAndPackage = explode(':', $url);

            $vendorAndPackage = explode('/', $vendorAndPackage[1]);

            return [
                $vendorAndPackage[0],
                Str::replaceLast('.git', '', $vendorAndPackage[1]),
            ];
        }

        $urlParts = explode('/', $url);

        return [$urlParts[3], $urlParts[4]];
    }
}
