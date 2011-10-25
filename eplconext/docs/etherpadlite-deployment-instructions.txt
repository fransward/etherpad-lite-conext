EtherpadLite for SURFconext - Deployment instructions
------------------------------------------------------------
2011/10/24; mdobrinic

Etherpad Lite is installed on an Ubuntu 11.04 virtual machine.
Etherpad Lite's backend store is SQLite. When performance would require so, it is also possible to use a MySQL backend.
The following installation instructions are the guideline of the Etherpad Lite installation procedure: http://mclear.co.uk/2011/08/01/install-etherpad-lite-on-ubuntu/
Etherpad Lite will be installed in /usr/local/etherpadlite


1. Infrastructure install
Apache must be available as http-server. Apache must be include php support.

Package requirements for running the application, are:
# apt-get python libssl-dev git-core git libsqlite3-dev gzip curl
# apt-get install sqlite
# apt-get install php5-sqlite

NodeJS installation:
# cd ~user-or-any-other-workdir
# wget http://nodejs.org/dist/node-v0.4.11.tar.gz
# tar zxvf node-v0.4.11.tar.gz
# cd node-v0.4.11
# ./configure ; make
# make install

Next, install NPM (NodeJS Package Manager):
# git clone https://github.com/isaacs/npm.git
# cd npm
# make install

Next, install the Etherpad Lite application from the repository:
# cd /usr/local
# git clone 'git://github.com/Pita/etherpad-lite.git'
# cd etherpad-lite

When npm has problems to install packages, the following error will show up:
...
npm ERR! System Linux 2.6.38-10-server
npm ERR! command "node" "/usr/local/bin/npm" "install"
npm ERR! cwd /usr/local/etherpad-lite
npm ERR! node -v v0.4.11
npm ERR! npm -v 1.0.27
npm ERR! Error: First argument needs to be a number, array or string.
npm ERR!     at new Buffer (buffer.js:156:15)
npm ERR!     at regRequest (/usr/local/lib/node_modules/npm/lib/utils/npm-registry-client/request.js:82:17)
npm ERR!     at GET (/usr/local/lib/node_modules/npm/lib/utils/npm-registry-client/request.js:211:3)
npm ERR!     at get_ (/usr/local/lib/node_modules/npm/lib/utils/npm-registry-client/get.js:121:3)
npm ERR!     at /usr/local/lib/node_modules/npm/lib/utils/npm-registry-client/get.js:46:10
npm ERR!     at cb (/usr/local/lib/node_modules/npm/node_modules/graceful-fs/graceful-fs.js:37:9)
npm ERR! Report this *entire* log at:
npm ERR!     <http://github.com/isaacs/npm/issues>
npm ERR! or email it to:
npm ERR!     <npm-@googlegroups.com>
npm ERR! 
npm ERR! System Linux 2.6.38-10-server
npm ERR! command "node" "/usr/local/bin/npm" "install"
npm ERR! cwd /usr/local/etherpad-lite
npm ERR! node -v v0.4.11
npm ERR! npm -v 1.0.27
npm ERR! 
npm ERR! Additional logging details can be found in:
npm ERR!     /usr/local/etherpad-lite/npm-debug.log
...

This requires a manual bugfix in the npm registry:
# vi /usr/local/lib/node_modules/npm/lib/utils/npm-registry-client/request.js

Line 82-83, comment out the remote.auth portion, to make it look like this:
 82:  // remote.auth = new Buffer( npm.config.get("_auth")
 83:  //                         , "base64" ).toString("utf8")

This problem should be fixed in npm by now (october/2011) though.



----------
2. Infrastructure configuration

2.1. Apache configuration
Copy the file etherpad.conext.surfnetlabs.nl to /etc/apache2/sites-available.
Enable the site with
# a2ensite etherpad.conext.surfnetlabs.nl

The site configuration makes use of key- and certificate files, stored as:

  SSLCertificateFile    /usr/local/etc/ssl/star.conext.surfnetlabs.nl.CHAINED.pem
  SSLCertificateKeyFile /usr/local/etc/ssl/star.conext.surfnetlabs.nl.key
  SSLCertificateChainFile /usr/local/etc/ssl/star.conext.surfnetlabs.nl.CHAINED.pem

Make sure they exist.
(CHAINED-file contains: cat server_cert chain_certs >> CHAINED.pem)

* Redirect to https (force SSL)
Accomplished by including the Redirect-line in the default-site virtual host
specification, like this:

<VirtualHost *:80>
   ...
   Redirect permanent / https://etherpad.conext.surfnetlabs.nl/
   ...
</VirtualHost>


* Enable proxy modules
Enable proxy modules as follows:
# a2enmod proxy
# a2enmod proxy_http



----------
3. Application install
3.1. EtherpadLite
EtherpadLite is the document editor.
It was already installed from the repository in the first chapter.


3.2. eplconext
Unpack the source package as follows:
# cd /var/www
# tar zxvf etherpad.conext.surfnetlabs.nl.version-x.y.z.tgz



4. Application configuration

4.1. System related
* Auto-start EtherpadLite on system startup

Create an Upstart-script in /etc/init, named 'etherpadlite.conf' with the following contents:
----
#
# Start Etherpad as Service on Ubuntu
#
description "Etherpad Lite collaborative document editting"
# Start when system starts
start on runlevel [3]
stop on shutdown

exec /usr/local/etherpad-lite/bin/run.sh
----
Now it is possible to start and stop etherpad using service commands:
Maintenance commando's:
$ sudo start etherpadlite
$ sudo status etherpadlite



* Possible upgrades
MySQL instead of SQLite
Etherpad Lite uses a datastore based on key->value elements. This is a storage strategy that is 
offered by the minimalistic SQLite database backend. Maybe in bigger deployment, the use of 
MySQL is preferred to SQLite.


4.2. Application related

* EtherpadLite
Settings are defined in
/usr/local/etherpadlite/settings.json

The default settings are unchanged though.


* EPLconext application
The eplconext application is configured in
eplconext/include/config.ini

Review the configuration, and make sure the values for 
OAUTH_CONFIG_consumerKey, 
OAUTH_CONFIG_consumerSecret and
ETHERPADLITE_APIKEY
are set appropriately.

The ETHERPADLITE_APIKEY-value can be found in
/usr/local/etherpad-lite/APIKEY.txt


* SimpleSAMLphp:
SimpleSAMLphp is installed in eplconext/lib/simplesamlphp-1.8.0
SimpleSAMLphp serves two functions:
1. As SAML2-SP to consume identities from SURFfederatie
2. As OAuth-provider, to offer OAuth-protected services

- SimpleSAML SAML2-SP
SimpleSAML configuration is done by:
1. Update config/authsources.php :
   In 'default-sp' configure 
   - the SP's 'privatekey' and 'certificate' files (read from cert/ directory)
   - the 'idp', to match the entityId of the SURFfederatie IDP (makes it default ==> no WAYF)
   
2. Updating config.php :
   - secretsalt
   - technical contact info
   - Updating authproc: 
Enable NameID-to-Attribute filter in authproc.sp:
.....
        'authproc.sp' => array(
                ...
                /* append NameID to available attributes */
                20  => array(
                  'class' => 'saml:NameIDAttribute',
                  'attribute' => 'NameID',
                  'format' => '%V',
                ),
.....

4. Add SURFfederatie IDP-metadata in metadata/saml20-idp-remote.php
Note: must match in authsources.php:default-sp['idp']


All SimpleSAMLphp configuration is stored in
config/*
metadata/*


- SimpleSAML OAuth Provider
Enable mod_oauth:
$ cd /var/www/etherpad.conext.surfnetlabs.nl/eplconext/lib/simplesamlphp-1.8.0/modules/oauth
$ touch enable

The file config/module_oauth.php configures oauth-settings of SimpleSAMLphp.

OAuth is configured (default) to store its tokens using SQLite in $simplesamlphp/data directory
Make sure this directory is writable for the www-data user; set www-data as owner:
$ sudo chown -R www-data:www-data /var/www/etherpad.conext.surfnetlabs.nl/eplconext/lib/simplesamlphp-1.8.0/data





