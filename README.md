# website-backup
This small app written in SF2 generates a zip file with sql file from a mysql database inside + the whole website folder.
Perfect for a WordPress website or any other website that use mysql or mariaDB.

##Installation

Clone this repo and setup a vhost as `web/` for DocumentRoot.

    cd /path/to/website-backup
    composer install
    mkdir -p web/backup

##Configuration

Create a file named `wbconfig.yml` inside `app/config` folder.

    parameters:
      sitename: YOUR WEBSITE NAME
      frontend_url: YOUR BACKUP HOST
      mandrill_api_key: YOUR API KEY
      mandrill_login: YOUR MANDRILL LOGIN
      alert_email_addr: EMAIL ADDRESS WHEN A BACKUP ALERT IS GENERATED
      
##Usage

With a symfony command, generate a backup:

    cd /path/to/website-backup
    php app/console website:backup websitepath websitedbname websitedbhost websitedblogin websitedbpassword
    
Set this command in the crontab to automate it !
