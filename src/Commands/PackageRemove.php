<?php

namespace Cherrypulp\LaravelPackageGenerator\Commands;

use Exception;
use Illuminate\Console\Command;
use Cherrypulp\LaravelPackageGenerator\Commands\Traits\InteractsWithUser;
use Cherrypulp\LaravelPackageGenerator\Commands\Traits\ChangesComposerJson;
use Cherrypulp\LaravelPackageGenerator\Commands\Traits\InteractsWithComposer;
use Cherrypulp\LaravelPackageGenerator\Commands\Traits\ManipulatesPackageFolder;

class PackageRemove extends Command
{
    use ChangesComposerJson;
    use ManipulatesPackageFolder;
    use InteractsWithUser;
    use InteractsWithComposer;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package:remove
                            {vendor : The vendor part of the namespace}
                            {package : The name of package for the namespace}
                            {--i|interactive : Interactive mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove the existing package.';

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

        $relPackagePath = "packages/$vendorFolderName/$packageFolderName";
        $packagePath = base_path($relPackagePath);

        try {
            $this->composerRemovePackage($vendorFolderName, $packageFolderName);
            $this->removePackageFolder($packagePath);
            $this->unregisterPackage($vendor, $package, "packages/$vendorFolderName/$packageFolderName");
            $this->composerDumpAutoload();

            return 0;
        } catch (Exception $e) {
            $this->error($e->getMessage());

            return -1;
        }
    }
}
