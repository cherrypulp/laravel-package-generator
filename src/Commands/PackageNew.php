<?php

namespace Cherrypulp\LaravelPackageGenerator\Commands;

use Cherrypulp\LaravelPackageGenerator\Commands\Traits\ChangesComposerJson;
use Cherrypulp\LaravelPackageGenerator\Commands\Traits\CopiesSkeleton;
use Cherrypulp\LaravelPackageGenerator\Commands\Traits\InteractsWithComposer;
use Cherrypulp\LaravelPackageGenerator\Commands\Traits\InteractsWithGit;
use Cherrypulp\LaravelPackageGenerator\Commands\Traits\ManipulatesPackageFolder;
use Exception;
use Illuminate\Console\Command;

class PackageNew extends Command
{
    use ChangesComposerJson;
    use ManipulatesPackageFolder;
    use InteractsWithComposer;
    use CopiesSkeleton;
    use InteractsWithGit;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package:new
                            {vendor : The vendor part of the namespace}
                            {package : The name of package for the namespace}
                            {--i|interactive : Interactive mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new package.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $vendor = $this->getVendor();
        $package = $this->getPackage();

        $vendorFolderName = $this->getVendorFolderName($vendor);
        $packageFolderName = $this->getPackageFolderName($package);

        $relPackagePath = "workbench/$vendorFolderName/$packageFolderName";
        $packagePath = base_path($relPackagePath);

        try {
            $this->createPackageFolder($packagePath);
            $this->registerPackage($vendorFolderName, $packageFolderName, $relPackagePath);
            $this->copySkeleton($packagePath, $vendor, $package, $vendorFolderName, $packageFolderName);
            $this->initRepo($packagePath);
            $this->composerUpdatePackage($vendorFolderName, $packageFolderName);
            $this->composerDumpAutoload();

            $this->info('Finished. Are you ready to write an awesome package?');
        } catch (Exception $e) {
            $this->error($e->getMessage());

            return -1;
        }

        return 0;
    }
}
