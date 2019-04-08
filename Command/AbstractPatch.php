<?php
namespace Zero1\Patches\Command;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Composer\ComposerJsonFinder;
use Magento\Framework\App\ProductMetadata;

abstract class AbstractPatch extends Command
{
    protected $version;

    protected $componentRegistrar;

    protected $directoryList;

    protected $patchConfig;

    public function __construct(
        ComponentRegistrarInterface $componentRegistrar,
        DirectoryList $directoryList,
        $name = null
    ){
        parent::__construct($name);
        $this->componentRegistrar = $componentRegistrar;
        $this->directoryList = $directoryList;
    }

    protected function getVersion()
    {
        if(!$this->version){
            $directoryList = new DirectoryList(BP);
            $composerJsonFinder = new ComposerJsonFinder($directoryList);
            $productMetadata = new ProductMetadata($composerJsonFinder);
            $this->version = $productMetadata->getVersion();
        }
        return $this->version;
    }

    protected function getModuleDir()
    {
        return $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, 'Zero1_Patches');
    }

    protected function getPatchConfig()
    {
        if(!$this->patchConfig){
            $this->patchConfig = json_decode(
                file_get_contents($this->getModuleDir().DIRECTORY_SEPARATOR.'patches.json'),
                true
            );
        }
        return $this->patchConfig;
    }
} 