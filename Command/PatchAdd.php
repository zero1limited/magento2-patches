<?php
namespace Zero1\Patches\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Composer\Semver\Semver;

class PatchAdd extends AbstractPatch
{
    const BASE_URL = 'https://raw.githubusercontent.com/zero1limited/magento2-patches/master';   
    
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

        $patch = $this->resolvePatch($patchName, $output);
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

    protected function resolvePatch($patchName, OutputInterface $output)
    {
        $version = $this->getVersion();
        $patchConfig = $this->getPatchConfig();

        $patch = false;
        foreach($patchConfig as $patchId => $patchInfo){
            if($patchInfo['name'] == $patchName){
                $output->writeln('Found patch, evaluating version');
                foreach($patchInfo['versions'] as $constraint){
                    $satisfied = Semver::satisfies($version, $constraint);
                    $output->writeln($constraint.' satisfied by '.$version.' '.json_encode($satisfied));
                    if($satisfied){
                        return $patchInfo;
                    }
                }
                $output->writeln('<error>Unable to find a compatible version'.PHP_EOL.print_r($patchInfo['versions'], true).'</error>');
                break;
            }
        }
        $output->writeln('<error>Unable to find patch by name</error>');
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

        foreach($patchInfo['files'] as $module => $filename){
            $relativePathToPatch = implode(
                DIRECTORY_SEPARATOR,
                [
                    self::BASE_URL,
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
