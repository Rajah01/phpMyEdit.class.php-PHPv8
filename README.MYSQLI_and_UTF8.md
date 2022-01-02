Change MySQLI configuration, to ensure that UTF-8 uses the new "utf8mb4" default in all tables (especially "SCHEMATA") of the "information_schema" database, in client databases and calling programs, and in "my.ini|my.cnf".

I largely follow extensive guidance at https://www.toptal.com/php/a-utf-8-primer-for-php-and-mysql

When done, the relevant output of

  mysql> SHOW VARIABLES LIKE 'char%';

is similar to:

	| character_set_client		| utf8mb4

	| character_set_connection	| utf8mb4

	| character_set_database	| utf8mb4

	| character_set_results		| utf8mb4

	| character_set_server		| utf8mb4

	| character_set_system		| utf8mb4

(The read-only "character_set_system" value may vary, but setting "character_set_client" as above should overcome any discrepancy.)

If still "latin1" (or "utf8mb3", or whatever), reexamine your config.

In "my.ini|cnf":

	[client]

	default-character-set=utf8mb4

	[mysql]

	default-character-set=utf8mb4

	[mysqld]

	character-set-client-handshake=false

	character-set-server=utf8mb4

	collation-server=utf8mb4_general_ci

PHP's "htmlspecialchars()" function is modified in this version to become "htmlspecialchars($myString,ENT_SUBSTITUTE,'UTF-8',true);" via the added global function "fhtmlspecialchars()" (at Top-Of-File), to which all htmlspecialchars calls are redirected.

--------------------------

This version of phpMyEdit implements one optional tweak, which ensures that after Viewing or Changing any record, the calling program returns to and focuses upon that record rather than TopOfFile.

It sets a $_SESSION['lastrec'] variable that contains the record number being displayed. If you do NOT implement this option in your calling PHP programs, it is entirely benign, the only cost being a $_SESSION variable (alternatively, delete the two lines that reference $_SESSION['lastrec']).

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

</head>

<body';

	if($_SESSION['lastrec']!=""){echo ' onload="jumpto(\''.$_SESSION['lastrec'].'\')";';}

	echo '>';

	...

