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

--------------------------
