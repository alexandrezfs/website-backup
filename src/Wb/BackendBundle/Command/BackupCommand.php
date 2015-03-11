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
            ->addArgument("websitedbpassword", InputArgument::REQUIRED, "Website db name");;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Starting backup...");

        $websitepath = $input->getArgument("websitepath");
        $websitedbname = $input->getArgument("websitedbname");
        $websitedbhost = $input->getArgument("websitedbhost");
        $websitedblogin = $input->getArgument("websitedblogin");
        $websitedbpassword = $input->getArgument("websitedbpassword");

        $webfolder = $this->getContainer()->get('kernel')->getRootDir() . "/../web";

        $outputPath = $webfolder . "/backup";

        $sqlFilename = "backup" . time() . ".sql";
        $zipFilename = "backup" . time() . ".zip";

        $sqlFullPath = $outputPath . "/" . $sqlFilename;
        $zipFullPath = $outputPath . "/" . $zipFilename;

        $sqlCommand = "mysqldump -h " . $websitedbhost . " -u" . $websitedblogin . " -p" . $websitedbpassword . " " . $websitedbname . " > " . $sqlFullPath;
        $zipCommand = "zip -r " . $zipFullPath . " " . $websitepath . " " . $sqlFullPath;

        //Removing all previous backup
        exec('rm -rf ' . $webfolder . '/backup/*');

        $output->writeln("Backup database " . $websitedbname . " on host " . $websitedbhost . "...");
        $output->writeln($sqlCommand);

        exec($sqlCommand);

        $output->writeln("Backup database " . $websitedbname . " successfully completed");
        $output->writeln("Creating zip...");

        exec($zipCommand);

        $output->writeln($zipCommand);
        $output->writeln("Backup generated at " . $zipFullPath);


        //Send a mail alert...
        require_once($this->getContainer()->get('kernel')->getRootDir() . "/../vendor/phpmailer/phpmailer/PHPMailerAutoload.php");

        $mail = new \PHPMailer;

        $mail->isSMTP();
        $mail->Host = 'smtp.mandrillapp.com';
        $mail->SMTPAuth = true;
        $mail->Username = $this->getContainer()->getParameter('mandrill_login');
        $mail->Password = $this->getContainer()->getParameter('mandrill_api_key');
        $mail->SMTPSecure = 'tls';
        $mail->From = 'noreply@mandrillapp.com';
        $mail->Port = 587;
        $mail->FromName = $this->getContainer()->getParameter('sitename') . ' Backup';
        $mail->AddAddress($this->getContainer()->getParameter('alert_email_addr'));
        $mail->IsHTML(true);
        $mail->Subject = $this->getContainer()->getParameter('sitename') . ' backup link';
        $mail->Body = 'This is your backup download link : <a href="' . $this->getContainer()->getParameter('frontend_url') . '/backup/' . $zipFilename . '">DOWNLOAD</a><br>If you only need SQL file: <a href="' . $this->getContainer()->getParameter('frontend_url') . '/backup/' . $sqlFilename . '">DOWNLOAD</a><br>FOR SECURITY REASONS, THESES LINK WILL EXPIRE IN 24 HOURS.<br><br>----------------------<br>By Website Backup - Alexandre Nguyen <a href="http://alexandrenguyen.fr">http://alexandrenguyen.fr</a>';

        if (!$mail->Send()) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
            exit;
        }
    }
}