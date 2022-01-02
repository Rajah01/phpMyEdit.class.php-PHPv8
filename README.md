Update phpMyEdit.class.php for PHP version 8 (compatible with PHP v7)

Based upon Patrick Goupell's Update for PHP version 7 (October 2018) (http://www.yooperlinux.com/sites/default/files/downloads/phpMyEdit-5.8.0.zip) with additional edits and fixes derived from Kimball Rexford and other contributors at https://kimballrexford.com/phpmyedit-for-php7-and-mysqli/

Tested under PHP v8.1.1 (Android/Termux, Windows), v7.4.1 (Linux/Centos7), and v7.3.3 (Windows), using a basic calling program generated by "phpMyEditSetup.php" (Goupell's PHP7 version, included in above ZIPfile), as well as more complex, legacy calling programs of my own.

A (perhaps overly ambitious) goal of this release was to produce a Development version that generates no Errors, Warnings, or Notices (a goal realized with the tested calling programs). Accordingly, "error_reporting = E_ALL", to reveal additional undetected errors, or errors introduced by updates. Two versions of phpMyEdit.class.php for PHP v7 are known to me; both produce about 2.5Mb of Notices & Warnings with E_ALL after a single run of my calling programs, although both do conclude successfully in PHP v7 (both throw fatal errors in PHP v8). Note that earlier versions of phpMyEdit had similar error rates. Many of these errors are glaring and seem to occur by design, perhaps as an odd method of steering the program.

For Production use, change "E_ALL" to "E_ALL & ~E_NOTICE" (line 3270).

YMMV: Your mileage may vary! Compare this code to an earlier version of phpMyEdit if you encounter errors.

Not specifically related to phpMyEdit, but important nonetheless: For successful UTF-8 character encoding, changes may be required to MySQL configuration. See README.MYSQLI_and_UTF8.md

--------------------------

PHP's "htmlspecialchars()" function is modified in this version to become "htmlspecialchars($myString,ENT_SUBSTITUTE,'UTF-8',true);" via the added global function "fhtmlspecialchars()" (at Top-File), to which all htmlspecialchars calls are redirected.

--------------------------

This version of phpMyEdit implements one optional tweak, which ensures that after Viewing or Changing any record, the calling program returns to and focuses upon that record instead of the first record.

It sets a $_SESSION['lastrec'] variable that contains the record number being Viewed or Changed. If you do NOT implement this option in your calling programs, it is entirely benign, the only cost being a $_SESSION variable (alternatively, delete the two lines that reference $_SESSION['lastrec']).

If you do implement this option, it requires three statements in your client calling programs:

[In PHP, at TopFile:]

	<?php if(!isset($_SESSION['lastrec'])){$_SESSION['lastrec']="";} ?>

[In HTML head section:]

	echo '<script> function jumpto(lastrec){ var el=document.getElementById(lastrec); el.scrollIntoView({block:"center"}); } </script>';

[HTML body statement:]

	echo '<body'; if($_SESSION['lastrec']!=""){echo ' onload="jumpto(\''.$_SESSION['lastrec'].'\')";';} echo '>';

--------------------------

Feedback, corrections, suggestions, improvement all welcomed.
