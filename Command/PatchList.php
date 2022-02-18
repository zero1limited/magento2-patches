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

class PatchList extends AbstractPatch
{
    protected function configure()
    {
        $this->setName("patch:list");
        $this->setDescription("Show all patches available for this version of magento");
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Magento Version: ".$this->getVersion());
        foreach($this->getPatchConfig() as $patchId => $patchInfo){
            if(in_array($this->getVersion(), $patchInfo['versions'])){
                $output->writeln($patchInfo['name'].' - '.$patchInfo['description'].' - More info: '.$patchInfo['link']);
                $output->writeln('To apply this patch, run "bin/magento patch:add --patch='.$patchInfo['name'].'" ');
                $output->writeln('');
            }
        }
    }
} 
