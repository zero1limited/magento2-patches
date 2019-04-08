<?php
namespace Zero1\Patches\Command;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Composer\ComposerJsonFinder;
use Magento\Framework\App\ProductMetadata;

class PatchAdd extends AbstractPatch
{
    protected function configure()
    {
        $this->setName("patch:add");
        $this->setDescription("Updates the composer.json to enable a specified patch");
        $this->setDefinition([
            new InputOption(
                'patch',
                null,
                InputOption::VALUE_REQUIRED,
                'The patch you wish to add.'
            ),
            new InputOption(
                'message',
                null,
                InputOption::VALUE_OPTIONAL,
                'The message you wish to add the patch with'
            )
        ]);
        parent::configure();

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $patchName = $input->getOption('patch');
        $message = $input->getOption('message');
        if(!$message){
            $message = $patchName.' - Added via Zero1_Patches';
        }

        $output->writeln('Patch: '.$patchName);
        $output->writeln('Message: '.$message);
        $output->writeln("Magento Version: ".$this->getVersion());

        $patch = $this->resolvePatch($patchName);
        if(!$patch){
            $output->writeln('<error>Unable to resolve "'.$patchName.'", to a patch.</error>');
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        try{
            $this->updateComposerJson($patch, $message);
        }catch(\Exception $e){
            $output->writeln('<error>There was an error adding the patch: "'.$e->getMessage().'"</error>');
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        $output->writeln('SUCCESS!');
        $output->writeln('The patch has been added to you composer.json');
        $output->writeln('You can now run: "composer install" to apply the patch.');
    }

    protected function resolvePatch($patchName)
    {
        $version = $this->getVersion();
        $patchConfig = $this->getPatchConfig();

        $patch = false;
        foreach($patchConfig as $patchId => $patchInfo){
            if($patchInfo['name'] == $patchName && in_array($version, $patchInfo['versions'])){
                $patch = $patchInfo;
                break;
            }
        }
        return $patch;
    }

    protected function updateComposerJson($patchInfo, $message)
    {
        $composerJson = json_decode(
            file_get_contents(
                $this->directoryList->getRoot().DIRECTORY_SEPARATOR.'composer.json'
            ),
            true
        );
        if(!isset($composerJson['extra'])){
            $composerJson['extra'] = [
                'patches' => []
            ];
        }elseif(!isset($composerJson['extra']['patches'])){
            $composerJson['extra']['patches'] = [];
        }

        $relativePathToModule = str_replace(
            $this->directoryList->getRoot().DIRECTORY_SEPARATOR,
            '',
            $this->getModuleDir()
        );

        foreach($patchInfo['files'] as $module => $filename){
            $relativePathToPatch = implode(
                DIRECTORY_SEPARATOR,
                [
                    $relativePathToModule,
                    $patchInfo['base_dir'],
                    $filename
                ]
            );
            if(!isset($composerJson['extra']['patches'][$module])){
                $composerJson['extra']['patches'][$module] = [];
            }
            $composerJson['extra']['patches'][$module][$message] = $relativePathToPatch;

        }

        file_put_contents(
            $this->directoryList->getRoot().DIRECTORY_SEPARATOR.'composer.json',
            json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }
} 