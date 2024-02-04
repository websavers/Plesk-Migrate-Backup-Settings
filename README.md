# How to run this script:

Ensure you have Plesk PHP 8.2 installed

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
```

# Data Plesk Migrator copies for us with param examples for Dropbox:
table: cl_param

cl_id: XX
param: ext-dropbox-backup-*
val: tokens/hashes

table: dom_param

dom_id: YY
param: ext-dropbox-backup-*
val: tokens/hashes