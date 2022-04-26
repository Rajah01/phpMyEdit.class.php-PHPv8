If necessary, change MySQLI configuration, to ensure that UTF-8 uses the new "utf8mb4" default in all tables (especially "SCHEMATA") of the "information_schema" database, in client databases and calling programs, and in "my.ini|my.cnf".

The output of:

  mysql> SHOW VARIABLES WHERE Variable_name LIKE 'character\_set\_%' OR Variable_name LIKE 'collation%';

should be similar to:

	| character_set_client		| utf8mb4

	| character_set_connection	| utf8mb4

	| character_set_database	| utf8mb4

	| character_set_results		| utf8mb4

	| character_set_server		| utf8mb4

	| character_set_system		| utf8 or utf8mb{#} (<b>cannot be changed!</b>)

	| collation_connection		| utf8mb4_general_ci (or utf8mb4_unicode_ci)

	| collation_database		| utf8mb4_general_ci	(")

	| collation_server		| utf8mb4_general_ci	(")

(The read-only "character_set_system" value may vary, but setting "character_set_client" as above should overcome any discrepancy.)

If still "latin1" (or "utf8mb3", or whatever), reexamine your config.

I largely follow extensive guidance at https://www.toptal.com/php/a-utf-8-primer-for-php-and-mysql <i>and</i> https://mathiasbynens.be/notes/mysql-utf8mb4 (extremely complicated), but appropriate settings in "my.ini" (or "my.cnf") may be sufficient to obtain the desired result (reissue SHOW VARIABLES ... as above, after restarting mysqld|mysqld_safe).

In "my.ini|my.cnf":

	[client]

	default-character-set=utf8mb4

	[mysql]

	default-character-set=utf8mb4

	[mysqld]

	character-set-client-handshake=false

	character-set-server=utf8mb4

	collation-server=utf8mb4_general_ci

Also append DEFAULT CHARSET=utf8mb4 to your MySQL CREATE TABLE definitions!

--------------------------
