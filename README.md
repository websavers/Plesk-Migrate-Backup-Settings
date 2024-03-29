# Requirements
- Plesk PHP 8.2 on source and destination servers, or edit shebang in PHP script
- Root or sudo access to both servers

# Instructions
1. Run on the source server:
```
mkdir pleskMigrateBackupSettings && cd pleskMigrateBackupSettings
plesk db -Xe "SELECT id,name FROM domains;" > old_domain_id_map.xml
plesk db -Xe "SELECT id,login FROM clients;" > old_client_id_map.xml
plesk db -Xe "SELECT * FROM BackupExcludeFiles;" > table_BackupExcludeFiles.xml
plesk db -Xe "SELECT * FROM BackupsScheduled;" > table_BackupsScheduled.xml
plesk db -Xe "SELECT * FROM BackupsSettings;" > table_BackupsSettings.xml
cd ..
tar -cvzf pleskMigrateBackupSettings.tgz pleskMigrateBackupSettings
```
2. Copy pleskMigrateBackupSettings.tgz to destination server and extract it: `tar -xvzf pleskMigrateBackupSettings.tgz`
3. Upload pleskMigrateBackupSettings.php to that directory, change to that dir via shell and run:
```
plesk db -Xe "SELECT id,name FROM domains;" > new_domain_id_map.xml
plesk db -Xe "SELECT id,login FROM clients;" > new_client_id_map.xml
chmod u+x pleskMigrateBackupSettings.php && ./pleskMigrateBackupSettings.php

# If all is well from that output, run these to import the data into Plesk DB:

sed -i '/^local-infile/s/0/1/' /etc/my.cnf && systemctl restart mariadb

# For some reason Plesk thinks this id should be unique, but it's not... so fix that
plesk db -e "ALTER TABLE BackupsSettings DROP INDEX id, ADD INDEX id (id, type, param) USING BTREE;"

cp table_BackupsSettings_fixed.xml /tmp/BackupsSettings.xml
plesk db -e "LOAD XML LOCAL INFILE '/tmp/BackupsSettings.xml' INTO TABLE BackupsSettings;"

cp table_BackupsScheduled_fixed.xml /tmp/BackupsScheduled.xml
plesk db -e "LOAD XML LOCAL INFILE '/tmp/BackupsScheduled.xml' INTO TABLE BackupsScheduled;"

cp table_BackupExcludeFiles.xml /tmp/BackupExcludeFiles.xml
plesk db -e "LOAD XML LOCAL INFILE '/tmp/BackupExcludeFiles.xml' INTO TABLE BackupExcludeFiles;"

# Cleanup
sed -i '/^local-infile/s/1/0/' /etc/my.cnf && systemctl restart mariadb
rm -f /tmp/BackupExcludeFiles.xml /tmp/BackupsScheduled.xml /tmp/BackupsSettings.xml
```
4. If you have users with *local* Plesk backups, you'll need to copy the data to the new server. Run this on the source server to use rsync to do that:
```
rsync -av /var/lib/psa/dumps/ root@DESTINATION_SERVER_IP:/var/lib/psa/dumps
```

## Note about cloud backups

The tokens used to connect Plesk to cloud services may be invalidated by the move, particularly if the IP changes. Clients backups will fail in such cases and they'll have to login to reconnect to their cloud service.

## Data Plesk Migration Manager copies for us with param examples for Dropbox:
table: cl_param

cl_id: XX
param: ext-dropbox-backup-*
val: tokens/hashes

table: dom_param

dom_id: YY
param: ext-dropbox-backup-*
val: tokens/hashes