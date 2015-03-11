<?php

namespace Wb\BackendBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BackupCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('website:backup')
            ->setDescription('Create a backup and send a mail with .zip link')
            ->addArgument("websitepath", InputArgument::REQUIRED, "Website root path")
            ->addArgument("websitedbname", InputArgument::REQUIRED, "Website db name")
            ->addArgument("websitedbhost", InputArgument::REQUIRED, "Website db host")
            ->addArgument("websitedblogin", InputArgument::REQUIRED, "Website db login")
            ->addArgument("websitedbpassword", InputArgument::REQUIRED, "Website db name");
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Starting backup...");

        $websitepath = $input->getArgument("websitepath");
        $websitedbname = $input->getArgument("websitedbname");
        $websitedbhost = $input->getArgument("websitedbhost");
        $websitedblogin = $input->getArgument("websitedblogin");
        $websitedbpassword = $input->getArgument("websitedbpassword");

        $webfolder = $this->getContainer()->get('kernel')->getRootDir()."/../web";

        $outputPath = $webfolder . "/backup";

        $sqlFilename = "backup" . time() . ".sql";
        $zipFilename = "backup" . time() . ".zip";

        $sqlFullPath = $outputPath . "/" . $sqlFilename;
        $zipFullPath = $outputPath . "/" . $zipFilename;

        $sqlCommand = "mysqldump -h " . $websitedbhost . " -u" . $websitedblogin . " -p" . $websitedbpassword . " " . $websitedbname . " > " . $sqlFullPath;
        $zipCommand = "zip -r " . $zipFullPath . " " . $websitepath . " " . $sqlFullPath;

        $output->writeln("Backup database " . $websitedbname . " on host " . $websitedbhost . "...");
        $output->writeln($sqlCommand);

        exec($sqlCommand);

        $output->writeln("Backup database " . $websitedbname . " successfully completed");
        $output->writeln("Creating zip...");

        exec($zipCommand);

        $output->writeln($zipCommand);
        $output->writeln("Backup generated at " . $zipFullPath);

    }
}