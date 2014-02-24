<?
$MESS["MAIN_DUMP_FILE_CNT"] = "Files compressed:";
$MESS["MAIN_DUMP_FILE_SIZE"] = "Files size:";
$MESS["MAIN_DUMP_FILE_FINISH"] = "Backup completed";
$MESS["MAIN_DUMP_FILE_MAX_SIZE"] = "Do not include files which size exceeds (0 - no limit): ";
$MESS["MAIN_DUMP_FILE_STEP_SLEEP"] = "interval:";
$MESS["MAIN_DUMP_FILE_STEP_sec"] = "sec";
$MESS["MAIN_DUMP_FILE_MAX_SIZE_b"] = "B";
$MESS["MAIN_DUMP_FILE_MAX_SIZE_kb"] = "kB";
$MESS["MAIN_DUMP_FILE_MAX_SIZE_mb"] = "MB ";
$MESS["MAIN_DUMP_FILE_MAX_SIZE_gb"] = "GB ";
$MESS["MAIN_DUMP_FILE_DUMP_BUTTON"] = "Back up";
$MESS["MAIN_DUMP_FILE_STOP_BUTTON"] = "Stop";
$MESS["MAIN_DUMP_FILE_KERNEL"] = "Back up kernel files:";
$MESS["MAIN_DUMP_FILE_NAME"] = "Filename";
$MESS["FILE_SIZE"] = "File Size";
$MESS["MAIN_DUMP_FILE_TIMESTAMP"] = "Modified";
$MESS["MAIN_DUMP_FILE_PUBLIC"] = "Back up public files:";
$MESS["MAIN_DUMP_BASE_STAT"] = "statistics";
$MESS["MAIN_DUMP_BASE_SINDEX"] = "search index";
$MESS["MAIN_DUMP_BASE_SIZE"] = "Mb";
$MESS["MAIN_DUMP_PAGE_TITLE"] = "Backup";
$MESS["MAIN_DUMP_LIST_PAGE_TITLE"] = "Back-ups";
$MESS["MAIN_DUMP_AUTO_PAGE_TITLE"] = "Create Auto Backup";
$MESS["MAIN_DUMP_AUTO_BUTTON"] = "Auto Backup";
$MESS["MAIN_DUMP_SITE_PROC"] = "Compressing...";
$MESS["MAIN_DUMP_ARC_SIZE"] = "Archive size:";
$MESS["MAIN_DUMP_TABLE_FINISH"] = "Tables processed:";
$MESS["MAIN_DUMP_ACTION_DOWNLOAD"] = "Download";
$MESS["MAIN_DUMP_DELETE"] = "Delete";
$MESS["MAIN_DUMP_ALERT_DELETE"] = "Are you sure you want to delete file?";
$MESS["MAIN_DUMP_FILE_PAGES"] = "Backup copies";
$MESS["MAIN_RIGHT_CONFIRM_EXECUTE"] = "Attention! Unpacking the backup copy on the working site can corrupt the site! Continue?";
$MESS["MAIN_DUMP_RESTORE"] = "Unpack";
$MESS["MAIN_DUMP_MYSQL_ONLY"] = "The backup feature supports MySQL databases only.<br>Please use external tools to create the database copy.";
$MESS["MAIN_DUMP_HEADER_MSG1"] = "To move the site back-up archive to another server, copy the restore script <a href='#EXPORT#'>restore.php</a> and the archive file to the document root of the new server. Then, type in your browser: <b>&lt;site name&gt;/restore.php</b>.";
$MESS["MAIN_DUMP_SKIP_SYMLINKS"] = "Skip Symbolic Links to Directories:";
$MESS["MAIN_DUMP_MASK"] = "Exclude Files and Folders (mask):";
$MESS["MAIN_DUMP_MORE"] = "More...";
$MESS["MAIN_DUMP_FOOTER_MASK"] = "The following rules apply to exclusion masks:
 <p>
 <li>the mask can contain asterisks &quot;*&quot; that match any or none characters in the file or folder name;</li>
 <li>if a path starts with a slash or a backslash (&quot;/&quot; or &quot;\\&quot;), the path is relative to the site root;</li>
 <li>otherwise, the mask applies to each file and folder;</li>
 <p>Examples of templates:</p>
 <li>/content/photo - excludes the folder/content/photo;</li>
 <li>*.zip - excludes ZIP files (the ones with the &quot;zip&quot; extension);</li>
 <li>.access.php - excludes all files &quot;.access.php&quot;;</li>
 <li>/files/download/*.zip - excludes ZIP files in /files/download;</li>
 <li>/files/d*/*.ht* - excludes files with extensions starting with &quot;ht&quot; in directories starting with &quot;/files/d&quot;.</li>";
$MESS["MAIN_DUMP_ERROR"] = "Error";
$MESS["ERR_EMPTY_RESPONSE"] = "Server returned an empty response. Please contact your hosting company for log review for date: #DATE#";
$MESS["DUMP_NO_PERMS"] = "Insufficient server permission to create backup files.";
$MESS["DUMP_NO_PERMS_READ"] = "Error opening the backup file for reading.";
$MESS["DUMP_DB_CREATE"] = "Creating database dump";
$MESS["DUMP_CUR_PATH"] = "Current path:";
$MESS["INTEGRITY_CHECK"] = "Integrity Check";
$MESS["CURRENT_POS"] = "Progress:";
$MESS["STEP_LIMIT"] = "Step Duration:";
$MESS["DISABLE_GZIP"] = "Disable compression (reduces CPU load)";
$MESS["INTEGRITY_CHECK_OPTION"] = "Check backup integrity when completed";
$MESS["MAIN_DUMP_DB_PROC"] = "Compressing database dump";
$MESS["TIME_SPENT"] = "Start at:";
$MESS["TIME_H"] = "h";
$MESS["TIME_M"] = "m";
$MESS["TIME_S"] = "s";
$MESS["MAIN_DUMP_FOLDER_ERR"] = "The folder #FOLDER# has no write permissions.";
$MESS["MAIN_DUMP_NO_CLOUDS_MODULE"] = "The Cloud Storage module is not installed.";
$MESS["MAIN_DUMP_INT_CLOUD_ERR"] = "Error initializing the cloud storage. Please try again later.";
$MESS["MAIN_DUMP_ERR_FILE_SEND"] = "Cannot upload file to cloud storage:";
$MESS["MAIN_DUMP_ERR_OPEN_FILE"] = "Cannot open file for reading:";
$MESS["MAIN_DUMP_SUCCESS_SENT"] = "Archive has been uploaded to cloud storage successfully.";
$MESS["MAIN_DUMP_SUCCESS_SAVED"] = "Changes have been saved.";
$MESS["MAIN_DUMP_SUCCESS_SAVED_DETAILS"] = "Auto backup will become active after you have configured cron.";
$MESS["MAIN_DUMP_AUTO_NOTE"] = "Use your hosting control panel to add the following new job to cron: <b>#SCRIPT#</b>. Recommended schedule: weekly.";
$MESS["MAIN_DUMP_CLOUDS_DOWNLOAD"] = "Download files from cloud storage";
$MESS["MAIN_DUMP_FILES_DOWNLOADED"] = "Files uploaded";
$MESS["MAIN_DUMP_FILES_SIZE"] = "Total uploaded";
$MESS["MAIN_DUMP_DOWN_ERR_CNT"] = "Files skipped";
$MESS["MAIN_DUMP_FILE_SENDING"] = "Sending archive to cloud storage";
$MESS["MAIN_DUMP_USE_THIS_LINK"] = "Use this link when moving the archive to another server using";
$MESS["MAIN_DUMP_ERR_COPY_FILE"] = "Cannot copy file: ";
$MESS["MAIN_DUMP_ERR_INIT_CLOUD"] = "Cannot connect to cloud storage";
$MESS["MAIN_DUMP_ERR_FILE_RENAME"] = "File rename error: ";
$MESS["MAIN_DUMP_ERR_NAME"] = "The archive name can include only Latin characters, digits, hyphens and periods.";
$MESS["MAIN_DUMP_FILE_SIZE1"] = "Archive size";
$MESS["MAIN_DUMP_LOCATION"] = "Location";
$MESS["MAIN_DUMP_PARTS"] = "parts: ";
$MESS["MAIN_DUMP_LOCAL"] = "local storage";
$MESS["MAIN_DUMP_GET_LINK"] = "Get link";
$MESS["MAIN_DUMP_SEND_CLOUD"] = "Upload to cloud storage ";
$MESS["MAIN_DUMP_SEND_FILE_CLOUD"] = "Upload archive to cloud storage ";
$MESS["MAIN_DUMP_RENAME"] = "Rename";
$MESS["MAIN_DUMP_ARC_NAME_W_O_EXT"] = "Archive name without extension";
$MESS["MAIN_DUMP_ARC_NAME"] = "Archive name";
$MESS["MAIN_DUMP_ARC_LOCATION"] = "Save backup to: ";
$MESS["MAIN_DUMP_LOCAL_DISK"] = "local disk";
$MESS["MAIN_DUMP_EVENT_LOG"] = "event log";
$MESS["MAIN_DUMP_ENC_PASS_DESC"] = "Archive password must include at least 6 characters.";
$MESS["MAIN_DUMP_EMPTY_PASS"] = "Archive password is not specified.";
$MESS["MAIN_DUMP_NOT_INSTALLED"] = "Mcrypt for PHP is not installed.";
$MESS["MAIN_DUMP_NO_ENC_FUNCTIONS"] = "Encryption is unavailable. Please contact your system administrator.";
$MESS["MAIN_DUMP_ENABLE_ENC"] = "Encrypt archive data";
$MESS["MAIN_DUMP_ENC_PASS"] = "Archive password (at least 6 characters):";
$MESS["MAIN_DUMP_SAVE_PASS"] = "Please keep your password safe. You won't be able to extract files from the archive if your password is lost.";
$MESS["MAIN_DUMP_SAVE_PASS_AUTO"] = "The password you provide will be encrypted and saved locally. Your license key will be used as the encryption parameter. You are strongly advised to change the password at least once a month.";
$MESS["MAIN_DUMP_MAX_ARCHIVE_SIZE"] = "Maximum uncompressed volume size (MB):";
$MESS["DUMP_MAIN_SESISON_ERROR"] = "Your session has expired. Please reload the page.";
$MESS["DUMP_MAIN_ERROR"] = "Error! ";
$MESS["DUMP_MAIN_REGISTERED"] = "Registered";
$MESS["DUMP_MAIN_EDITION"] = "Edition";
$MESS["DUMP_MAIN_ACTIVE_FROM"] = "Active from";
$MESS["DUMP_MAIN_ACTIVE_TO"] = "Active until";
$MESS["DUMP_MAIN_ERR_GET_INFO"] = "Cannot obtain key information from update server.";
$MESS["DUMP_MAIN_BITRIX_CLOUD"] = "Bitrix Clouds";
$MESS["DUMP_MAIN_BITRIX_CLOUD_DESC"] = "Bitrix Cloud Storage";
$MESS["DUMP_MAIN_ERR_PASS_CONFIRM"] = "The passwords you typed don't match.";
$MESS["DUMP_MAIN_PASSWORD_CONFIRM"] = "Repeat password:";
$MESS["DUMP_MAIN_MAKE_ARC"] = "Backup";
$MESS["MAKE_DUMP_FULL"] = "Create full backup copy";
$MESS["DUMP_MAIN_PARAMETERS"] = "Parameters";
$MESS["DUMP_MAIN_EXPERT_SETTINGS"] = "Advanced settings";
$MESS["DUMP_MAIN_ENC_ARC"] = "Encrypt archive";
$MESS["DUMP_MAIN_SITE"] = "Website:";
$MESS["DUMP_MAIN_IN_THE_CLOUD"] = "cloud:";
$MESS["DUMP_MAIN_IN_THE_BXCLOUD"] = "Bitrix cloud";
$MESS["DUMP_MAIN_ENABLE_EXPERT"] = "Enable advanced backup settings";
$MESS["DUMP_MAIN_CHANGE_SETTINGS"] = "Modifying the advanced parameters may produce an incomplete or damaged archive thus preventing further recovery. You must have a complete understanding of the effect each of the parameters will have on the result.";
$MESS["DUMP_MAIN_ARC_CONTENTS"] = "Backup contents";
$MESS["DUMP_MAIN_DOWNLOAD_CLOUDS"] = "Download data from clouds and add it to backup:";
$MESS["DUMP_MAIN_ARC_DATABASE"] = "Add database to backup";
$MESS["DUMP_MAIN_DB_EXCLUDE"] = "Exclude from database:";
$MESS["DUMP_MAIN_ARC_MODE"] = "Archive mode";
$MESS["DUMP_MAIN_MULTISITE"] = "If your system has multiple websites with different paths to web server root directory, such websites will be backed up and restored individually. This is a special case: a full archive is created only once, for one of the websites; when backing up other websites, you will have to exclude the kernel and database using the <b>Advanced Settings</b>. If the backup copies will then be used to restore websites at another web server, you'll have to create the symbolic links to folders <b>bitrix</b> and <b>upload</b> manually.";
$MESS["BCL_BACKUP_USAGE"] = "Space used: #USAGE# of #QUOTA#.";
$MESS["DUMP_BXCLOUD_NA"] = "Bitrix Cloud Storage is unavailable";
$MESS["DUMP_ERR_NON_ASCII"] = "National characters are not allowed in the password to avoid restoration problems.";
$MESS["DUMP_MAIN_BXCLOUD_INFO"] = "Bitrix Inc. provides cloud space for three backup copies free of charge for an active license. You will access your backups by supplying a valid license key and a password. You won't be able to restore a website from a backup copy if you lose your password.";
$MESS["MAIN_DUMP_BXCLOUD_ENC"] = "Encryption cannot be disabled for backups saved to Bitrix Cloud Storage.";
$MESS["MAIN_DUMP_FROM"] = "from";
$MESS["DUMP_ERR_BIG_BACKUP"] = "Backup size exceeds your Bitrix Cloud quota. The archive has been saved on the local machine.";
$MESS["DUMP_BACK"] = "back";
$MESS["DUMP_RETRY"] = "Try again";
$MESS["MAIN_DUMP_ERR_DELETE"] = "You cannot manually delete files stored in Bitrix Cloud. The outdated archives are replaced with the new one as soon as you create and upload a new backup.";
$MESS["ERR_NO_BX_CLOUD"] = "The cloud service support module is not installed";
$MESS["ERR_NO_CLOUDS"] = "The cloud storage module is not installed.";
$MESS["DUMP_DELETE_ERROR"] = "Cannot delete the file #FILE#";
$MESS["DUMP_MAIN_AUTO_PARAMETERS"] = "Autorun script parameters";
$MESS["DUMP_MAIN_SAVE"] = "Save";
$MESS["DUMP_ADDITIONAL"] = "Additional parameters";
$MESS["DUMP_DELETE"] = "Delete local backups";
$MESS["DUMP_NOT_DELETE"] = "never";
$MESS["DUMP_CLOUD_DELETE"] = "only if successfully copied to cloud";
$MESS["DUMP_RM_BY_TIME"] = "in #TIME# days since creation";
$MESS["DUMP_RM_BY_CNT"] = "if there are more than #CNT# backups";
$MESS["DUMP_RM_BY_SIZE"] = "if total size exceeds #SIZE# GB";
$MESS["MAIN_DUMP_SHED_CLOSEST_TIME"] = "Next run is scheduled for: ";
$MESS["MAIN_DUMP_SHED_CLOSEST_TIME_TODAY"] = "The next run is scheduled for today: ";
$MESS["MAIN_DUMP_SHED_CLOSEST_TIME_TOMORROW"] = "The next run is scheduled for tomorrow:";
$MESS["MAIN_DUMP_SHED"] = "Schedule";
$MESS["MAIN_DUMP_PERIODITY"] = "Run:";
$MESS["MAIN_DUMP_PER_1"] = "daily";
$MESS["MAIN_DUMP_PER_2"] = "every other day";
$MESS["MAIN_DUMP_PER_3"] = "every 3 days";
$MESS["MAIN_DUMP_PER_5"] = "every 5 days";
$MESS["MAIN_DUMP_PER_7"] = "weekly";
$MESS["MAIN_DUMP_PER_14"] = "every other week";
$MESS["MAIN_DUMP_PER_21"] = "every three weeks";
$MESS["MAIN_DUMP_PER_30"] = "monthly";
$MESS["MAIN_DUMP_DELETE_OLD"] = "Outdated archives";
$MESS["MAIN_DUMP_SHED_TIME_SET"] = "This option is available only if the system agents use cron. Otherwise, you will have to use your hosting provider's control panel to have the script <b>/bitrix/modules/main/tools/backup.php</b> run when required.";
$MESS["MAIN_DUMP_AUTO_LOCK"] = "Automatic backup started";
$MESS["MAIN_DUMP_AUTO_LOCK_TIME"] = "Time elapsed since start: #TIME#";
$MESS["AUTO_LOCK_EXISTS_ERR"] = "Automatic backup started on #DATETIME# failed with an unrecoverable error. Please review server logs to find the reason.";
$MESS["AUTO_EXEC_METHOD"] = "Run:";
$MESS["AUTO_EXEC_FROM_BITRIX"] = "using Bitrix cloud service";
$MESS["AUTO_EXEC_FROM_CRON"] = "as agent using cron";
$MESS["AUTO_EXEC_FROM_MAN"] = "by calling #SCRIPT# directly";
$MESS["AUTO_URL"] = "website URL";
$MESS["DUMP_AUTO_TAB"] = "Autorun";
$MESS["MAIN_DUMP_AUTO_WARN"] = "Enable <a href=\"#LINK#\">auto backup</a> to have the most recent copy of your data for recovery.";
$MESS["DUMP_LOCAL_TIME"] = "(local server time)";
$MESS["DUMP_CHECK_BITRIXCLOUD"] = "Check the current task status at <a href=\"#LINK#\">Bitrix Cloud service</a>";
$MESS["DUMP_WARN_NO_BITRIXCLOUD"] = "Cannot enable auto backup. Please install the cloud service support module or use cron to run agents.";
$MESS["DUMP_SAVED_DISABLED"] = "Auto backup is disabled.<br>Backups can only be created by running /bitrix/modules/main/tools/backup.php manually.";
$MESS["DUMP_AUTO_INFO_ON"] = "Auto backup is enabled";
$MESS["DUMP_AUTO_INFO_OFF"] = "Auto backup is disabled";
$MESS["DUMP_BTN_AUTO_DISABLE"] = "Disable auto backup";
$MESS["DUMP_BTN_AUTO_ENABLE"] = "Enable auto backup";
$MESS["DUMP_AUTO_INFO_TEXT"] = "<b>Auto backup</b>

Have the auto backup feature create the most recent copy of your data for you for recovery in case of hardware or software failure. 

Bitrix Cloud Monitor will create backup copies by navigating to a special URL at your website at scheduled time. The URL includes a secret ID that allows a caller to create the backup copy but not access it. No access to your website's Conrol Panel is needed and may be blocked by IP.

By default, the backup copy is saved to Bitrix Cloud in encrypted form in multiple locations. This is the most secure way to preserve your data.

If Bitrix Cloud services are inaccessible but the agents are scheduled using cron, the backup copy will be created locally.";
?>