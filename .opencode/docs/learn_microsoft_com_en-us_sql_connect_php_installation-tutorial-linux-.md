# Linux and macOS Installation for the Drivers for PHP - PHP drivers for SQL Server | Microsoft Learn

> Source: https://learn.microsoft.com/en-us/sql/connect/php/installation-tutorial-linux-mac
> Cached: 2026-04-16T20:57:40.294Z

---

Table of contents 
			
			
				
				Exit editor mode
			
		
	
			
				
					
		
			
				
		
			
				
					
				
			
			
		

		
	 
		
			
		
			
				
			
		
		
			
				
			
			Ask Learn
		
		
			
				
			
			Ask Learn
		
	 
		
			
				
			
			Focus mode
		
	 

			
				
					
						
					
				
				
					
		
			
			Table of contents
		
	 
		
			
				
			
			Read in English
		
	 
		
			
				
			
			Add
		
	
					
		
			
				
			
			Add to plan
		
	  
		
			
				
			
			Edit
		
	
					
		
		#### Share via

		
					
						
							
						
						Facebook
					

					
						
							
						
						x.com
					

					
						
							
						
						LinkedIn
					
					
						
							
						
						Email
					
			  
	 
		
		
				
					
						
						
						
					
					Copy Markdown
				
		   
				
					
						
					
					Print
				
		  
	
				
			
		
	
			
		
	  
		
		
			
			
				
					
						
						Note
					
					
						Access to this page requires authorization. You can try [signing in](#) or changing directories.
					
					
						Access to this page requires authorization. You can try changing directories.
					
				
			
		
	
					# Linux and macOS Installation Tutorial for the Microsoft Drivers for PHP for SQL Server

					
		
			 
				
					
		
			
				
			
			Feedback
		
	
				
		  
		
	 
		
			
				
					
				
				
					
						Summarize this article for me
					
				
			
			
			
		
	 
		
			
				In this article
			
		
	
					The following instructions assume a clean environment and show how to install PHP 8.3, the Microsoft ODBC driver, the Apache web server, and the Microsoft Drivers for PHP for SQL Server on Ubuntu, Red Hat, Debian, SUSE, Alpine, and macOS. These instructions advise installing the drivers using PECL. You can also download the prebuilt binaries from the [Microsoft Drivers for PHP for SQL Server](https://github.com/Microsoft/msphpsql/releases) GitHub project page and install them following the instructions in [Loading the Microsoft Drivers for PHP for SQL Server](loading-the-php-sql-driver?view=sql-server-ver17). For an explanation of extension loading and why we don't add the extensions to php.ini, see the section on [loading the drivers](loading-the-php-sql-driver?view=sql-server-ver17#loading-the-driver-at-php-startup).

The following instructions install PHP 8.3 by default using `pecl install`, if the PHP 8.3 packages are available. You might need to run `pecl channel-update pecl.php.net` first. Some supported Linux distros default to old versions of PHP, which aren't supported for the latest version of the PHP drivers for SQL Server. To install PHP 8.4 or 8.5 instead, see the notes at the beginning of each section.

Also included are instructions for installing the PHP FastCGI Process Manager, PHP-FPM, on Ubuntu. PHP-FPM is needed if you're using the nginx web server instead of Apache.

While these instructions contain commands to install both SQLSRV and PDO_SQLSRV drivers, the drivers can be installed and function independently. Users comfortable with customizing their configuration can adjust these instructions to be specific to SQLSRV or PDO_SQLSRV. Both drivers have the same dependencies except where noted.

For the latest supported operating systems versions, see [Support Matrix](microsoft-php-drivers-for-sql-server-support-matrix?view=sql-server-ver17).

Note

Make sure to install the latest ODBC driver version to ensure optimal performance and security. For installation instructions, see [Install the Microsoft ODBC driver for SQL Server (Linux)](../odbc/linux-mac/installing-the-microsoft-odbc-driver-for-sql-server?view=sql-server-ver17) or [Install the Microsoft ODBC driver for SQL Server (macOS)](../odbc/linux-mac/install-microsoft-odbc-driver-sql-server-macos?view=sql-server-ver17).

## Installing on Ubuntu

Note

To install PHP 8.4 or 8.5, replace 8.3 with 8.4 or 8.5 in the following commands.

### Step 1. Install PHP (Ubuntu)

```
sudo su
add-apt-repository ppa:ondrej/php -y
apt-get update
apt-get install php8.3 php8.3-dev php8.3-xml -y --allow-unauthenticated

```

### Step 2. Install prerequisites (Ubuntu)

Install the ODBC driver for Ubuntu by following the instructions on the [Install the Microsoft ODBC driver for SQL Server (Linux)](../odbc/linux-mac/installing-the-microsoft-odbc-driver-for-sql-server?view=sql-server-ver17). Make sure to also install the `unixodbc-dev` package. It's used by the `pecl` command to install the PHP drivers.

```
sudo apt-get install unixodbc-dev

```

### Step 3. Install the PHP drivers for Microsoft SQL Server (Ubuntu)

```
sudo pecl install sqlsrv
sudo pecl install pdo_sqlsrv
sudo su
printf "; priority=20\nextension=sqlsrv.so\n" > /etc/php/8.3/mods-available/sqlsrv.ini
printf "; priority=30\nextension=pdo_sqlsrv.so\n" > /etc/php/8.3/mods-available/pdo_sqlsrv.ini
exit
sudo phpenmod -v 8.3 sqlsrv pdo_sqlsrv

```

If there's only one PHP version in the system, then the last step can be simplified to `phpenmod sqlsrv pdo_sqlsrv`.

### Step 4. Install Apache and configure driver loading (Ubuntu)

```
sudo su
apt-get install libapache2-mod-php8.3 apache2
a2dismod mpm_event
a2enmod mpm_prefork
a2enmod php8.3
exit

```

### Step 5. Restart Apache and test the sample script (Ubuntu)

```
sudo service apache2 restart

```

To test your installation, see [Testing your installation](#testing-your-installation) at the end of this document.

## Installing on Ubuntu with PHP-FPM

Note

To install PHP 8.4 or 8.5, replace 8.3 with 8.4 or 8.5 in the following commands.

### Step 1. Install PHP (Ubuntu with PHP-FPM)

```
sudo su
add-apt-repository ppa:ondrej/php -y
apt-get update
apt-get install php8.3 php8.3-dev php8.3-fpm php8.3-xml -y --allow-unauthenticated

```

Verify the status of the PHP-FPM service by running:

```
systemctl status php8.3-fpm

```

### Step 2. Install prerequisites (Ubuntu with PHP-FPM)

Install the ODBC driver for Ubuntu by following the instructions on the [Install the Microsoft ODBC driver for SQL Server (Linux)](../odbc/linux-mac/installing-the-microsoft-odbc-driver-for-sql-server?view=sql-server-ver17). Make sure to also install the `unixodbc-dev` package. It's used by the `pecl` command to install the PHP drivers.

### Step 3. Install the PHP drivers for Microsoft SQL Server (Ubuntu with PHP-FPM)

```
sudo pecl config-set php_ini /etc/php/8.3/fpm/php.ini
sudo pecl install sqlsrv
sudo pecl install pdo_sqlsrv
sudo su
printf "; priority=20\nextension=sqlsrv.so\n" > /etc/php/8.3/mods-available/sqlsrv.ini
printf "; priority=30\nextension=pdo_sqlsrv.so\n" > /etc/php/8.3/mods-available/pdo_sqlsrv.ini
exit
sudo phpenmod -v 8.3 sqlsrv pdo_sqlsrv

```

If there's only one PHP version in the system, then the last step can be simplified to `phpenmod sqlsrv pdo_sqlsrv`.

Verify that `sqlsrv.ini` and `pdo_sqlsrv.ini` are located in `/etc/php/8.3/fpm/conf.d/`:

```
ls /etc/php/8.3/fpm/conf.d/*sqlsrv.ini

```

Restart the PHP-FPM service:

```
sudo systemctl restart php8.3-fpm

```

### Step 4. Install and configure nginx (Ubuntu with PHP-FPM)

```
sudo apt-get update
sudo apt-get install nginx
sudo systemctl status nginx

```

To configure nginx, you must edit the `/etc/nginx/sites-available/default` file. Add `index.php` to the list below the section that says `# Add index.php to the list if you are using PHP`:

```
# Add index.php to the list if you are using PHP
index index.html index.htm index.nginx-debian.html index.php;

```

Uncomment and modify the section following `# pass PHP scripts to FastCGI server` as follows:

```
# pass PHP scripts to FastCGI server
#
location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
}

```

### Step 5. Restart nginx and test the sample script (Ubuntu with PHP-FPM)

```
sudo systemctl restart nginx.service

```

To test your installation, see [Testing your installation](#testing-your-installation) at the end of this document.

## Installing on Red Hat

### Step 1. Install PHP (Red Hat)

To install PHP on Red Hat 8, run the following commands:

Note

To install PHP 8.4 or 8.5, replace remi-8.3 with remi-8.4 or remi-8.5 respectively in the following commands.

```
sudo su
dnf install https://dl.fedoraproject.org/pub/epel/epel-release-latest-8.noarch.rpm
dnf install https://rpms.remirepo.net/enterprise/remi-release-8.rpm
dnf install yum-utils
dnf module reset php
dnf module install php:remi-8.3
subscription-manager repos --enable codeready-builder-for-rhel-8-x86_64-rpms
dnf update
# Note: The php-pdo package is required only for the PDO_SQLSRV driver
dnf install php-pdo php-pear php-devel

```

### Step 2. Install prerequisites (Red Hat)

Install the ODBC driver for Red Hat 8 by following the instructions on the [Install the Microsoft ODBC driver for SQL Server (Linux)](../odbc/linux-mac/installing-the-microsoft-odbc-driver-for-sql-server?view=sql-server-ver17). Make sure to also install the `unixodbc-dev` package. It's used by the `pecl` command to install the PHP drivers.

### Step 3. Install the PHP drivers for Microsoft SQL Server (Red Hat)

```
sudo pecl install sqlsrv
sudo pecl install pdo_sqlsrv
sudo su
echo extension=pdo_sqlsrv.so >> `php --ini | grep "Scan for additional .ini files" | sed -e "s|.*:\s*||"`/30-pdo_sqlsrv.ini
echo extension=sqlsrv.so >> `php --ini | grep "Scan for additional .ini files" | sed -e "s|.*:\s*||"`/20-sqlsrv.ini
exit

```

You can alternatively install from the Remi repo:

```
sudo yum install php-sqlsrv

```

### Step 4. Install Apache (Red Hat)

```
sudo yum install httpd

```

SELinux is installed by default and runs in Enforcing mode. To allow Apache to connect to databases through SELinux, run the following command:

```
sudo setsebool -P httpd_can_network_connect_db 1

```

### Step 5. Restart Apache and test the sample script (Red Hat)

```
sudo apachectl restart

```

To test your installation, see [Testing your installation](#testing-your-installation) at the end of this document.

## Installing on Debian

Note

To install PHP 8.4 or 8.5, replace 8.3 in the following commands with 8.4 or 8.5.

### Step 1. Install PHP (Debian)

```
sudo su
apt-get install curl apt-transport-https
wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list
apt-get update
apt-get install -y php8.3 php8.3-dev php8.3-xml php8.3-intl

```

### Step 2. Install prerequisites (Debian)

Install the ODBC driver for Debian by following the instructions on the [Install the Microsoft ODBC driver for SQL Server (Linux)](../odbc/linux-mac/installing-the-microsoft-odbc-driver-for-sql-server?view=sql-server-ver17). Make sure to also install the `unixodbc-dev` package. It's used by the `pecl` command to install the PHP drivers.

You might also need to generate the correct locale to get PHP output to display correctly in a browser. For example, for the en_US UTF-8 locale, run the following commands:

```
sudo su
sed -i 's/# en_US.UTF-8 UTF-8/en_US.UTF-8 UTF-8/g' /etc/locale.gen
locale-gen

```

You might need to add `/usr/sbin` to your `$PATH`, as the `locale-gen` executable is located there.

### Step 3. Install the PHP drivers for Microsoft SQL Server (Debian)

```
sudo pecl install sqlsrv
sudo pecl install pdo_sqlsrv
sudo su
printf "; priority=20\nextension=sqlsrv.so\n" > /etc/php/8.3/mods-available/sqlsrv.ini
printf "; priority=30\nextension=pdo_sqlsrv.so\n" > /etc/php/8.3/mods-available/pdo_sqlsrv.ini
exit
sudo phpenmod -v 8.3 sqlsrv pdo_sqlsrv

```

If there's only one PHP version in the system, then the last step can be simplified to `phpenmod sqlsrv pdo_sqlsrv`. As with `locale-gen`, `phpenmod` is located in `/usr/sbin` so you might need to add this directory to your `$PATH`.

### Step 4. Install Apache and configure driver loading (Debian)

```
sudo su
apt-get install libapache2-mod-php8.3 apache2
a2dismod mpm_event
a2enmod mpm_prefork
a2enmod php8.3

```

### Step 5. Restart Apache and test the sample script (Debian)

```
sudo service apache2 restart

```

To test your installation, see [Testing your installation](#testing-your-installation) at the end of this document.

## Installing on SUSE

Note

In the following instructions, replace `<SuseVersion>` with your version of SUSE - if you're using SUSE Linux Enterprise Server 15, it's SLE_15_SP3 or SLE_15_SP4 (or higher). Not all versions of PHP are available for all versions of SUSE Linux - refer to `http://download.opensuse.org/repositories/devel:/languages:/php` to see which versions of SUSE have the default version PHP available, or check `http://download.opensuse.org/repositories/devel:/languages:/php:/` to see which other versions of PHP are available for which versions of SUSE.

### Step 1. Install PHP (SUSE)

```
sudo su
zypper -n ar -f https://download.opensuse.org/repositories/devel:languages:php/<SuseVersion>/devel:languages:php.repo
zypper --gpg-auto-import-keys refresh
zypper -n install php8 php8-pdo php8-devel php8-openssl

```

### Step 2. Install prerequisites (SUSE)

Install the ODBC driver for SUSE by following the instructions on the [Install the Microsoft ODBC driver for SQL Server (Linux)](../odbc/linux-mac/installing-the-microsoft-odbc-driver-for-sql-server?view=sql-server-ver17). Make sure to also install the `unixodbc-dev` package. It's used by the `pecl` command to install the PHP drivers.

### Step 3. Install the PHP drivers for Microsoft SQL Server (SUSE)

```
sudo pecl install sqlsrv
sudo pecl install pdo_sqlsrv
sudo su
echo extension=pdo_sqlsrv.so >> `php --ini | grep "Scan for additional .ini files" | sed -e "s|.*:\s*||"`/pdo_sqlsrv.ini
echo extension=sqlsrv.so >> `php --ini | grep "Scan for additional .ini files" | sed -e "s|.*:\s*||"`/sqlsrv.ini
exit

```

### Step 4. Install Apache and configure driver loading (SUSE)

```
sudo su
zypper install apache2 apache2-mod_php8
a2enmod php8
echo "extension=sqlsrv.so" >> /etc/php8/apache2/php.ini
echo "extension=pdo_sqlsrv.so" >> /etc/php8/apache2/php.ini
exit

```

### Step 5. Restart Apache and test the sample script (SUSE)

```
sudo systemctl restart apache2

```

To test your installation, see [Testing your installation](#testing-your-installation) at the end of this document.

## Installing on Alpine

Note

PHP 8.3 or above might be available from testing or edge repositories for Alpine. You can instead compile PHP from source.

### Step 1. Install PHP (Alpine)

PHP packages for Alpine can be found in the `edge/community` repository. Check [Enable Community Repository](https://wiki.alpinelinux.org/wiki/Enable_Community_Repository) on their WIKI page. Add the following line to `/etc/apk/repositories`, replacing `<mirror>` with the URL of an Alpine repository mirror:

```
http://<mirror>/alpine/edge/community

```

Then run:

```
sudo su
apk update
# Note: The php*-pdo package is required only for the PDO_SQLSRV driver
# For PHP 8.*
apk add php8 php8-dev php8-pear php8-pdo php8-openssl autoconf make g++


... [Content truncated]