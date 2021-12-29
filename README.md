Update phpMyEdit.class.php for PHP version 8

Based upon Patrick Goupell's Update for PHP version 7 (October 2018)
  (http://www.yooperlinux.com/sites/default/files/downloads/phpMyEdit-5.8.0.zip)
  with additional edits and fixes derived from Kimball Rexford and other
  contributors at https://kimballrexford.com/phpmyedit-for-php7-and-mysqli/ 

Apart from fixes to the code (some enumerated by posters including myself at kimballrexford.com), the most consequential changes were to MySQLI configuration, ensuring that UTF-8 uses the new "utf8mb4" default in all tables of the "information_schema" database, in client databases and calling programs, and in my.ini|my.cnf.  I largely followed extensive guidance at https://www.toptal.com/php/a-utf-8-primer-for-php-and-mysql

When done, the relevant output of

  mysql> SHOW VARIABLES LIKE 'char%';

is similar to:

	| character_set_client		| utf8mb4

	| character_set_connection	| utf8mb4

	| character_set_database	| utf8mb4

	| character_set_results		| utf8mb4

	| character_set_server		| utf8mb4

	| character_set_system		| utf8mb4

(The "character_set_system" value may vary, but setting "character_set_client" as above should overcome a discrepancy.)

If still "latin1" (or "utf8mb3", or whatever), reexamine your config.

In my.ini|cnf:

	[client]

	default-character-set=utf8mb4

	[mysql]

	default-character-set=utf8mb4

	[mysqld]

	character-set-client-handshake=false

	character-set-server=utf8mb4

	collation-server=utf8mb4_general_ci

PHP's "htmlspecialchars()" function is modified in this version to become "htmlspecialchars($myString,ENT_SUBSTITUTE,'UTF-8',true);" via an added global function "fhtmlspecialchars()" (at Top-Of-File), to which all htmlspecialchars calls are redirected.

There is one optional tweak, which ensures that after Viewing or Changing any record, the calling program returns to and focuses upon that record rather than TopOfFile.

It sets a $_SESSION['lastrec'] variable that contains the record number being displayed. If you don't implement this option in your calling PHP programs, it is entirely benign, the only cost being a $_SESSION variable (alternatively, delete the two lines that reference $_SESSION['lastrec']).

If you do wish to implement this option, it requires three statements in your client PHP calling programs:

	<?php

	...

	if(!isset($_SESSION['lastrec'])){$_SESSION['lastrec']="";}

	...

[In HTML:]

	echo '<script>

function jumpto(lastrec){

	var el=document.getElementById(lastrec);

	el.scrollIntoView({block:"center"});

}

</script>

...

</head>

<body';

	if($_SESSION['lastrec']!=""){echo ' onload="jumpto(\''.$_SESSION['lastrec'].'\')";';}

	echo '>';

	...

That's it.

Give it a try -- feedback and improvement welcomed.
