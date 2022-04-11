<?php

/*
 * phpMyEdit - instant MySQL table editor and code generator
 *
 * phpMyEditSetup.php - interactive table configuration utility (setup)
 * ____________________________________________________________
 *
 * Copyright (c) 1999-2002 John McCreesh <jpmcc@users.sourceforge.net>
 * Copyright (c) 2001-2002 Jim Kraai <jkraai@users.sourceforge.net>
 * Versions 5.0 and higher developed by Ondrej Jombik <nepto@php.net>
 * Copyright (c) 2002-2006 Platon Group, http://platon.sk/
 * All rights reserved.
 *
 * See README file for more information about this software.
 * See COPYING file for license information.
 *
/* $Platon: phpMyEdit/phpMyEditSetup.php,v 1.50 2007-09-16 12:57:07 nepto Exp $ */

/*
 ***********************
 updated October 2018, Patrick Goupell, patrick@yoopermail.us
 updated for php version 7
 replace mysql_ functions with mysqli_ functions

 ***********************

	updated 5 February 2022, Robert Holmgren, rjh@holmgren.org
	updated for PHP version 8
	changes indicated by "//" comments and/or string "PHP v8"

 ***********************
*/

/* Update to current version: https://github.com/Rajah01/phpMyEdit.class.php-PHPv8 */

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=us-ascii">
	<title>phpMyEdit Setup</title>
	<style type="text/css">
	<!--
		body  { font-family: "Verdana", "Arial", "Sans-Serif"; text-align: left }
		h1    { color: #004d9c; font-size: 13pt; font-weight: bold }
		h2    { color: #004d9c; font-size: 11pt; font-weight: bold }
		h3    { color: #004d9c; font-size: 11pt; }
		p     { color: #004d9c; font-size: 9pt; }
		table { border: 1px solid #004d9c; border-collapse: collapse; border-spacing: 0px; }
		td    { border: 1px solid; padding: 3px; color: #004d9c; font-size: 9pt; }
		hr
		{
		height: 1px;
		background-color: #000000;
		color: #000000;
		border: solid #000000 0;
		padding: 0;
		margin: 0;
		border-top-width: 1px;
		}
	-->
	</style>
</head>
<body bgcolor="white">

<?php
// **************************
//
//  setup environment
//
// **************************

	if (! defined('PHP_EOL'))
	{
		define('PHP_EOL', strtoupper(substr(PHP_OS, 0, 3) == 'WIN') ? "\r\n" : (strtoupper(substr(PHP_OS, 0, 3) == 'MAC') ? "\r" : "\n")); // PHP v8
	}

	$hn = @$_POST['hn'];            // host (default localhost)
	$pt = @$_POST['pt'];            // port (default 3306)
	$un = @$_POST['un'];            // user name
	$pw = @$_POST['pw'];            // password
	if(isset($_POST['db'])){if(strlen($_POST['db'])>0) //PHP v8
		{$db = @$_POST['db'];}}     // database (optional)	//PHP v8
	if(isset($_POST['tb'])){if(strlen($_POST['tb'])>0) //PHP v8
	{$tb = @$_POST['tb'];}}         // table name (optional)		//PHP v8
	$id = @$_POST['id'];            // table unique key
	$submit = @$_POST['submit'];    // submit button

	$options       = @$_POST['options'];
	$baseFilename  = @$_POST['baseFilename'];
	$pageTitle     = @$_POST['pageTitle'];
	$pageHeader    = @$_POST['pageHeader'];
	$HTMLissues    = @$_POST['HTMLissues'];
	$CSSstylesheet = @$_POST['CSSstylesheet'];


// ***************************
//
//  Support functions start here
//
// ***************************

function echo_html($x)
{
	echo htmlspecialchars($x),PHP_EOL;
}

function build_buffer($x)
{
	global $buffer;
	$buffer .= $x . PHP_EOL;
}

#:#####################################:#
#:#  Function:   check_constraints    #:#
#:#  Parameters: tb=table name        #:#
#:#              fd=field name        #:#
#:#  return:     lookup default for   #:#
#:#              said constraint      #:#
#:#              or null if no        #:#
#:#              constraint is found. #:#
#:#  Contributed by Wade Ryan,        #:#
#:#                 20060906          #:#
#:#####################################:#
function check_constraints($db1, $tb, $field)
{
	$constraint_arg="";

	$query = "show create table $tb";
	$result = mysqli_query ($db1, $query);
//	$tableDef = preg_split('/\n/',$result);
	$rows = mysqli_fetch_row($result); //PHP v8
//	while (list($key,$val) = each($tableDef))
	foreach ($rows as $val)
	{
		$words=preg_split("/[\s'`()]+/", $val);
//		if ($words[1] == "CONSTRAINT" && $words[6]=="REFERENCES")
		if(isset($words[1])){if ($words[1] == "CONSTRAINT" && $words[6]=="REFERENCES")
		{
			if ($words[5]==$field)
			{
				$constraint_arg="  'values' => array(\n".
				"    'table'  => '$words[7]',\n".
				"    'column' => '$words[8]'\n".
				"  ),\n";
			}
		}}
	}
	mysqli_free_result ($result);
	return $constraint_arg;
}

function get_versions()
{
	$ret_ar  = array();
	$dirname = dirname(__FILE__);
	foreach (array(
		'current' => __FILE__,
		'setup'   => "$dirname/phpMyEditSetup.php",
		'core'    => "$dirname/phpMyEdit.class.php",
		'version' => "$dirname/doc/VERSION")
		as $type => $file)
	{
		if (@file_exists($file) && @is_readable($file))
		{
			if (($f = fopen($file, 'r')) == false)
			{
				continue;
			}
			$str = trim(fread($f, 4096));
			if (strpos($str, ' ') === false && strlen($str) < 10)
			{
				$ret_ar[$type] = $str;
			}
			else if (preg_match('|\$'.'Platon:\s+\S+,v\s+(\d+.\d+)\s+|', $str, $matches))
			{
				$ret_ar[$type] = $matches[1];
			}
			fclose($f);
		}
	}
	return $ret_ar;
}

function EchoFormHiddenInput ($self, $hn, $pt, $un, $pw, $db, $tb, $id, $opt)
{
	echo '<form action="'.htmlspecialchars($self).'" method="POST">
	<input type="hidden" name="hn" value = "' . htmlspecialchars($hn) . '">
	<input type="hidden" name="pt" value = "' . htmlspecialchars($pt) . '">
	<input type="hidden" name="un" value = "' . htmlspecialchars($un) . '">
	<input type="hidden" name="pw" value= "' . htmlspecialchars($pw) . '">' . PHP_EOL;

	if ($db != "")
		echo '<input type="hidden" name="db" value = "' . htmlspecialchars($db) . '">' . PHP_EOL;

	if ($tb != "")
		echo '<input type="hidden" name="tb" value = "' . htmlspecialchars($tb) . '">' . PHP_EOL;

	if ($id != "")
		echo '<input type="hidden" name="id" value = "' . htmlspecialchars($id) . '">' . PHP_EOL;

	if ($opt != "")
		echo '<input type="hidden" name="options" value="1">' . PHP_EOL;

}

function ShowLogin ($self, $hn, $pt, $un, $pw, $db, $tb)
{
	echo '
		<form action="' . htmlspecialchars($self) . '" method="POST">
		<table border="1" cellpadding="1" cellspacing="0" summary="Login form">
		<tr>
		<td>Hostname:</td>
		<td><input type="text" name="hn" value="' . htmlspecialchars($hn) . '"></td>
		</tr><tr>
		<tr>
		<td>Port:</td>
		<td><input type="text" name="pt" value="' . htmlspecialchars($pt) . '"></td>
		</tr><tr>
		<td>Username:</td>
		<td><input type="text" name="un" value="' . htmlspecialchars($un) . '"></td>
		</tr><tr>
		<td>Password:</td>
		<td><input type="password" name="pw" value="'.htmlspecialchars($pw).'"></td>
		</tr><tr>
		<td>Database:</td>
		<td><input type="text" name="db" value="' . htmlspecialchars($db) . '"></td>
		</tr><tr>
		<td>Table:</td>
		<td><input type="text" name="tb" value="' . htmlspecialchars($tb) . '"></td>
		</tr>
		</table><br>
		<input type="submit" name="submit" value="Submit">
		</form>'.PHP_EOL;
}

function ShowSelectDatabase ($self, $hn, $pt, $un, $pw, $db1)
{
	$error = false;

	$sql = "show databases";
	$dbs = mysqli_query($db1, $sql);

	if ($error == false)
	{
		echo '<h1>Please select a database</h1>' . PHP_EOL;

		$result = EchoFormHiddenInput ($self, $hn, $pt, $un, $pw, "", "", "", "");

		echo '
			<table border="1" cellpadding="1" cellspacing="1" summary="Database selection">' . PHP_EOL;

		while ($row = mysqli_fetch_assoc($dbs))
		{
			$db = $row['Database'];
			echo '<tr><td><input'.$checked.' type="radio" name="db" value="'.$db.'"></td><td>'.$db.'</td></tr>'.PHP_EOL;
		}

		echo '</table><br>
		<input type="submit" name="submit" value="Submit">
		<input type="submit" name="cancel" value="Cancel">
		</form>' . PHP_EOL;

	mysqli_free_result ($dbs);
	}
}

function CheckForDatabase ($self, $hn, $pt, $un, $pw, $db, $tb, $db1)
{
	$havedb = false;

	$stmt = "show databases";
	$result = mysqli_query ($db1, $stmt);

	while ($row = mysqli_fetch_array($result))
	{
		$thisdb = $row [0];
		if ($thisdb == $db)
		{
			$havedb = true;
			break;
		}
	}

	mysqli_free_result ($result);

	return $havedb;
}

function CheckForDatabaseTable ($self, $hn, $pt, $un, $pw, $db, $tb, $db1)
{
	$havetb = false;

	$stmt = "show columns from $tb in $db";
	$result = mysqli_query ($db1, $stmt);

	if ($row = mysqli_fetch_assoc($result))
	{
		$havetb = true;
	}

	mysqli_free_result ($result);

	return $havetb;
}

function ShowSelectTable ($self, $hn, $pt, $un, $pw, $db, $db1)
{
	echo '<h1>Please select a table from database: '.htmlspecialchars($db).'</h1>' . PHP_EOL;

	$result = EchoFormHiddenInput ($self, $hn, $pt, $un, $pw, $db, "", "", "");

	echo '
		<table border="1" cellpadding="1" cellspacing="1" summary="Table selection">' . PHP_EOL;

	$stmt = "show tables from $db";
	$result = mysqli_query ($db1, $stmt);

	while ($row = mysqli_fetch_array($result))
	{
		$tb = $row[0];
		echo '<tr><td><input type="radio" name="tb" value="' . $tb . '"></td><td>' . $tb . '</td></tr>' . PHP_EOL;
	}
	echo '</table><br>
		<input type="submit" name="submit" value="Submit">
		<input type="submit" name="cancel" value="Cancel">
		</form>'.PHP_EOL;

	mysqli_free_result ($result);
}

function ShowSelectId ($self, $hn, $pt, $un, $pw, $db, $tb, $db1)
{
	echo '  <h1>Please select an identifier from table: ' . htmlspecialchars($tb) . '</h1>
		<p>
		This field will be used in change, view, copy and delete operations.<br>
		The field should be numeric and must uniquely identify a record.
		</p>
		<p>
		Please note, that there were problems reported by phpMyEdit users 		regarding using MySQL reserved word as unique key name (the example for this is "key" name). Thus we recommend you to use another name of unique key. Usage of "id" or "ID" should be safe and good idea.
		</p>' . PHP_EOL;

	$result = EchoFormHiddenInput ($self, $hn, $pt, $un, $pw, $db, $tb, "", "");

	echo '		<table border="1" cellpadding="1" cellspacing="1" summary="Key selection">' . PHP_EOL;

	$stmt = "show columns from $tb in $db";
	$result = mysqli_query ($db1, $stmt);
	while ($row = mysqli_fetch_array($result))
	{
		$field = $row [0];
		$type = $row [1];
		$key = $row [3];

		if ($key == "PRI")
		{
			$checked = "checked";
			// get primary key type
		}
		else
			$checked = "";

		echo '<tr><td><input ' . $checked . ' type="radio" name="id" value="',htmlspecialchars($field),'"></td>';
		echo '<td>',htmlspecialchars($field),'</td>';
		// should I get ff (flags) from getcolumnmeta ???
		// strlen($ff) <= 0 && $ff = '---';  // what is this ???
//		echo '<td>',htmlspecialchars($ff),'</td></tr>' . PHP_EOL;; //commented in PHP v8 (the above two lines are NOT my comments!)
	}

//	mysqli_free_result ($stmt);
	mysqli_free_result ($result);

	echo '</table><br>
		<input type="submit" name="submit" value="Submit">
		<input type="submit" name="cancel" value="Cancel">
		</form>' . PHP_EOL;
}

function GetPrimaryKeyType ($self, $hn, $pt, $un, $pw, $db, $tb, $db1)
{
	$primarykeytype = "";

	$stmt = "show columns from $tb in $db";
	$result = mysqli_query ($db1, $stmt);
	while ($row = mysqli_fetch_array($result))
	{
		$field = $row [0];
		$type = $row [1];
		$key = $row [3];

		if ($key == "PRI")      // what if UNI(que) and no PRI
		{
			// now decode the type
			//$result => $result2 //PHP v8
			$result2 = DecodeType ($type);
			$primarykeytype = $result2[0];  // array: type, length
		}
	}

//    mysqli_free_result ($stmt);
	mysqli_free_result ($result);

	return $primarykeytype;
}

function DecodeType ($type)
{
	$result = $type;
	$length = 0;
	$i = strpos ($type, "(");
	if ($i > 0)
	{
		$result = substr ($type, 0, $i);
	}
	$length = GetLength ($type);

	return array ("$result", "$length");
}

function GetLongestValue ($type)
{
	$len = 0;
	$i = strpos ($type, "(");
	$j = strlen ($type);
	$mytype = substr ($type, $i + 1, ($j - $i) - 2);

	// split $mytype at ","
	$values = explode(",", $mytype);
	$i = count ($values);
	for ($j = 0; $j < $i; $j++)
	{
		$k = strlen ($values [$j]);
		if ($k > $len)
			$len = $k;
	}

	if ($len > 0)
		$len -= 2;
	return $len;
}

function ShowSelectOptions ($self, $hn, $pt, $un, $pw, $db, $tb, $db1, $id)
{
	echo "select options: $hn, $pt, $un, $pw, $db, $tb, $id<br>";
	echo '<h1>Please select additional options</h1>' . PHP_EOL;

	$result = EchoFormHiddenInput ($self, $hn, $pt, $un, $pw, $db, $tb, $id, "");

	echo '		<table border="1" cellpadding="1" cellspacing="1" summary="Additional options">
		<tr><td>Base filename</td><td><input type="text" name=baseFilename value ="'.htmlspecialchars($tb).'"></td></tr>
		<tr><td>Page title</td><td><input type="text" name=pageTitle value ="'.htmlspecialchars($tb).'"></td></tr>
		<tr><td>Page header</td><td><input type="checkbox" name=pageHeader></td></tr>
		<tr><td>HTML header &amp; footer</td><td><input type="checkbox" name=HTMLissues></td></tr>
		<tr><td>CSS basic stylesheet</td><td><input checked type="checkbox" name=CSSstylesheet></td></tr>
		</table><br>
		<input type="submit" name="submit" value="Submit">
		<input type="submit" name="cancel" value="Cancel">
		<input type="hidden" name="options" value="1">
		</form>' . PHP_EOL;
}

function GetLength($type)
{
	$len = 0;
	$mytype = $type;

	$i = strpos ($mytype, "(");
	if ($i > 0)
	{
		$mytype = substr ($mytype, 0, $i);
	}

	switch ($mytype)
	{
		case "blob":
			$len = 60;
			break;
		case "date":
			$len = 10;
			break;
		case "datetime":
			$len = 22;
			break;
		case "enum":
			$len = 30;
			// now get the longest enum value
			$len = GetLongestValue ($type);
			break;
		case "set":
			$len = 30;
			// now get the longest set value
			$len = GetLongestValue ($type);
			break;
		case "text":
			$len = 60;
			break;
		case "time":
			$len = 11;
			break;
		case "timestamp":
			$len = 22;
			break;
		default:
			$i = strpos ($type, "(");
			if ($i > 0)
			{
				$j = strpos ($type, ",");
				if ($j == 0)
					$j = strpos ($type, ")" );
					$len = substr ($type, $i + 1, $j - $i - 1);
			}
	}

	return $len;
}

function GenerateCode ($self, $hn, $pt, $un, $pw, $db, $tb, $db1, $id)
{
	global $buffer;
	global $pageHeader;
	global $pageTitle;
	global $contentFile;
	global $CSSstylesheet;
	global $HTMLissues;

	echo '<h1>Here is your phpMyEdit calling program</h1>'.PHP_EOL;
	echo '<h2>You may now copy and paste it into your PHP editor</h2>'.PHP_EOL;

	$css_directive = <<<END
<style type="text/css">
	hr.pme-hr            { border: 0px solid; padding: 0px; margin: 0px; border-top-width: 1px; height: 1px; }
	table.pme-main       { border: #004d9c 1px solid; border-collapse: collapse; border-spacing: 0px; width: 100%; }
	table.pme-navigation { border: #004d9c 0px solid; border-collapse: collapse; border-spacing: 0px; width: 100%; }
	td.pme-navigation-0, td.pme-navigation-1 { white-space: nowrap; }
	th.pme-header        { border: #004d9c 1px solid; padding: 4px; background: #add8e6; }
	td.pme-key-0, td.pme-value-0, td.pme-help-0, td.pme-navigation-0, td.pme-cell-0,
	td.pme-key-1, td.pme-value-1, td.pme-help-0, td.pme-navigation-1, td.pme-cell-1,
	td.pme-sortinfo, td.pme-filter { border: #004d9c 1px solid; padding: 3px; }
	td.pme-buttons { text-align: left;   }
	td.pme-message { text-align: center; }
	td.pme-stats   { text-align: right;  }
</style>
END;

	$css_directive .= PHP_EOL;

	if (! $CSSstylesheet)
	{
		$css_directive = '';
	}

	if ($HTMLissues)
	{
		$buffer = <<<END
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
		"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>$pageTitle</title>
$css_directive
</head>
<body>
$buffer
</body>
</html>
END;
	}
	else if ($CSSstylesheet)
	{
		$buffer = $css_directive;
	}

	if ($pageHeader)
	{
		build_buffer('<h3>'.$pageTitle.'</h3>');
	}

	$versions    = '';
	$versions_ar = get_versions();
	foreach (array(
				'version' => 'phpMyEdit version:',
				'core'    => 'phpMyEdit.class.php core class:',
				'setup'   => 'phpMyEditSetup.php script:',
				'current' => 'generating setup script:')
			as $type => $desc) {
		$version = isset($versions_ar[$type]) ? $versions_ar[$type] : 'unknown';
		$versions .= sprintf("\n *  %36s %s", $desc, $version);
	}

	$text = <<<END
<?php

/*
 * IMPORTANT NOTE: This generated file contains only a subset of huge amount
 * of options that can be used with phpMyEdit. To get information about all
 * features offered by phpMyEdit, check official documentation. It is available
 * online and also for download on phpMyEdit project management page:
 *
 * http://platon.sk/projects/main_page.php?project_id=5
 *
 * This file was generated by:
 *$versions
 */

// MySQL host name, port, user name, password, database, and table
\$opts['hn'] = '$hn';
\$opts['pt'] = '$pt';
\$opts['un'] = '$un';
\$opts['pw'] = '$pw';
\$opts['db'] = '$db';
\$opts['tb'] = '$tb';

// Name of field which is the unique key
\$opts['key'] = '$id';

// Type of key field (int/real/string/date etc.)
END;

	build_buffer ($text);

	if ($id == '')
	{
		build_buffer("\$opts['key_type'] = '';");
	}
	else
	{
		$result = GetPrimaryKeyType ($self, $hn, $pt, $un, $pw, $db, $tb, $db1);
		if ($result != "")
		{
			build_buffer("\$opts['key_type'] = '".$result."';");
		}
	}

	$text = <<<END
// Sorting field(s)
\$opts['sort_field'] = array('$id');

// Number of records to display on the screen
// Value of -1 lists all records in a table
\$opts['inc'] = 15;

// Options you wish to give the users
// A - add,  C - change, P - copy, V - view, D - delete,
// F - filter, I - initial sort suppressed
\$opts['options'] = 'ACPVDF';

// Number of lines to display on multiple selection filters
\$opts['multiple'] = '4';

// Navigation style: B - buttons (default), T - text links (default - PHP v8), G - graphic links
// Buttons position: U - up, D - down (default)
\$opts['navigation'] = 'DBT';

// Display special page elements
\$opts['display'] = array(
	'form'  => true,
	'query' => true,
	'sort'  => true,
	'time'  => true,
	'tabs'  => true
);

// Set default prefixes for variables
\$opts['js']['prefix']               = 'PME_js_';
\$opts['dhtml']['prefix']            = 'PME_dhtml_';
\$opts['cgi']['prefix']['operation'] = 'PME_op_';
\$opts['cgi']['prefix']['sys']       = 'PME_sys_';
\$opts['cgi']['prefix']['data']      = 'PME_data_';

/* Get the user's default language and use it if possible or you can
   specify particular one you want to use. Refer to official documentation
   for list of available languages. */
\$opts['language'] = \$_SERVER['HTTP_ACCEPT_LANGUAGE'] . '-UTF8';

/* Table-level filter capability. If set, it is included in the WHERE clause
   of any generated SELECT statement in SQL query. This gives you ability to
   work only with subset of data from table.

\$opts['filters'] = \"column1 like '%11%' AND column2<17\";
\$opts['filters'] = \"section_id = 9\";
\$opts['filters'] = \"PMEtable0.sessions_count > 200\";
*/

/* Field definitions

Fields will be displayed left to right on the screen in the order in which they
appear in generated list. Here are some most used field options documented.

['name'] is the title used for column headings, etc.;
['maxlen'] maximum length to display add/edit/search input boxes
['trimlen'] maximum length of string content to display in row listing
['width'] is an optional display width specification for the column
		e.g.  ['width'] = '100px';
['mask'] a string that is used by sprintf() to format field output
['sort'] true or false; means the users may sort the display on this column
['strip_tags'] true or false; whether to strip tags from content
['nowrap'] true or false; whether this field should get a NOWRAP
['select'] T - text, N - numeric, D - drop-down, M - multiple selection
['options'] optional parameter to control whether a field is displayed
  L - list, F - filter, A - add, C - change, P - copy, D - delete, V - view
			Another flags are:
			R - indicates that a field is read only
			W - indicates that a field is a password field
			H - indicates that a field is to be hidden and marked as hidden
['URL'] is used to make a field 'clickable' in the display
		e.g.: 'mailto:\$value', 'http://\$value' or '\$page?stuff';
['URLtarget']  HTML target link specification (for example: _blank)
['textarea']['rows'] and/or ['textarea']['cols']
  specifies a textarea is to be used to give multi-line input
  e.g. ['textarea']['rows'] = 5; ['textarea']['cols'] = 10
['values'] restricts user input to the specified constants,
			e.g. ['values'] = array('A','B','C') or ['values'] = range(1,99)
['values']['table'] and ['values']['column'] restricts user input
  to the values found in the specified column of another table
['values']['description'] = 'desc_column'
  The optional ['values']['description'] field allows the value(s) displayed
  to the user to be different to those in the ['values']['column'] field.
  This is useful for giving more meaning to column values. Multiple
  descriptions fields are also possible. Check documentation for this.
*/
END;

	build_buffer ($text);

	$ts_cnt  = 0;
	$stmt = "show columns from $tb in $db";
	$result = mysqli_query ($db1, $stmt);

	while ($row = mysqli_fetch_array ($result))
	{
		$field = $row[0];
		$type = $row[1];
		$null = $row[2];
		$key = $row[3];
		$default = $row[4];
		$extra = $row[5];

		$len = 0;
		$fm = $field;
		$fn = strtr($field, '_-.', '   ');
		$fn = preg_replace('/(^| +)id( +|$)/', '\\1ID\\2', $fn); // uppercase IDs
		$fn = ucfirst($fn);

		build_buffer( '$opts[\'fdd\'][\'' . $field . '\'] = array(');
		build_buffer("  'name'     => '" . str_replace('\'','\\\'',$fn) . "',");

		if ((substr($type,0,3) == 'set') or (substr($type,0,4) == 'enum'))
		{
			build_buffer("  'select'   => 'M',");
		}
		else
		{
			build_buffer("  'select'   => 'T',");
		}

		if ($extra == 'auto_increment')
		{
			build_buffer("  'options'  => 'AVCPDR', // auto increment");
		}
		else if ($type == 'timestamp')
		{
			if ($ts_cnt > 0)
			{
				build_buffer("  'options'  => 'AVCPD',");
			}
			else
			{ // first timestamp
				build_buffer("  'options'  => 'AVCPDR', // updated automatically (MySQL feature)");
			}
			$ts_cnt++;
		}

		$length = DecodeType ($type);
		$len = $length [1];
		if (($type == 'blob') or ($type == 'text'))
		{
			$DoNothing = true;
		}
		else
		{
			build_buffer("  'maxlen'   => $len,");
		}

		// blobs -> textarea
		if (($type == 'blob') or ($type == 'text'))
		{
			build_buffer("  'textarea' => array(");
			build_buffer("    'rows' => 5,");
			build_buffer("    'cols' => 80),");
		}

		// SETs and ENUMs get special treatment
		if ((substr($type,0,3) == 'set' || substr($type,0,4) == 'enum') && ! (($pos = strpos($type, '(')) === false))
		{
			$indent = str_repeat(' ', 18);
			$outstr = substr($type, $pos + 2, -2);
			$outstr = explode("','", $outstr);
			$outstr = str_replace("''", "'",  $outstr);
			$outstr = str_replace('"', '\\"', $outstr);
			$outstr = implode('",' . PHP_EOL . $indent . '"', $outstr);
			build_buffer("  'values'   => array(".PHP_EOL.$indent.'"'.$outstr.'"),');
		}

		// automatic support for Default values
		if ($default != '' && $default != 'NULL')
		{
			build_buffer("  'default'  => '".$default."',");
		}
		else if ($key == 'PRI')       // ok if auto increment
		{                             // but not if a text type key
			build_buffer("  'default'  => '0',");
		}

		// check for table constraints
		$outstr = check_constraints($db1, $tb, $field);
		if ($outstr != '')
		{
			build_buffer($outstr);
		}

		build_buffer("  'sort'     => true");
		//build_buffer("  'nowrap'   => false,");
		build_buffer(');');
	}

	mysqli_free_result ($result);

	build_buffer("
// Now important call to phpMyEdit
require_once 'phpMyEdit.class.php';
new phpMyEdit(\$opts);

?>
");

	// write the content include file
	echo 'Trying to write content file to: <b>'.'./'.$contentFile.'</b><br>'.PHP_EOL;
	$filehandle = @fopen('./'.$contentFile, 'w+');
	if ($filehandle) {
		fwrite($filehandle, $buffer);
//		flush($filehandle);
		flush();
		fclose($filehandle);
		echo 'phpMyEdit content file written successfully<br>';
	}
	else
	{
		echo 'phpMyEdit content file was NOT written due to inssufficient privileges.<br>';
		echo 'Please copy and paste content listed below to <i>'.'./'.$contentFile.'</i> file.';
	}
	echo '<br><hr>';
	echo '<pre>';
	echo_html($buffer);

	echo '</pre><hr>'.PHP_EOL;

}

// *********************
//
//  Mainline starts here
//
//**********************

	$phpExtension = '.php';
	$arr=array("hn","pt","un","pw","db","tb","db1");foreach($arr as $arry){if(!isset($$arry)){$$arry='';}}	//PHP v8
	if (isset($baseFilename) && $baseFilename != '')
	{
		$phpFile = $baseFilename.$phpExtension;
		$contentFile = $baseFilename.'.php';
	}
	else if (isset($tb))
	{
		$phpFile = $tb.$phpExtension;
		$contentFile = $tb.'.php';
	}
	else
	{
		$phpFile = 'index'.$phpExtension;
		$contentFile = 'phpMyEdit-content.php';
	}

	$self = basename($_SERVER['PHP_SELF']);
	$buffer = '';

	//    now do the main lifting
	if (empty($submit))         // first time or cancel button
	{
		echo "<h1>Please log in to your MySQL database</h1>";
		if (! isset($hn))
			$hn = 'localhost';

		if (! isset($pt))
			$pt = '3306';

		ShowLogin ($self, $hn, $pt, $un, $pw, $db, $tb);
	}
	else                        // process the user input
	{
		$havehost = true;           // assume we have host info
		$havedb = false;            // no database info
		$havetb = false;            // no table info

		if ($hn == "")              // host present?
			$haveuost = false;

		if ($un == "")              // user present?
			$havehost = false;

		if ($pw == "")              // password present?

			$havehost = false;


		if ($havehost == true)
		{
			/* Connect to a MySQL database  */
			$host = $hn;
			if ($pt != "")
				$host .= ":$pt";

//			$db1 = @mysqli_connect($host, $un, $pw);
			$db1 = mysqli_connect($host, $un, $pw, $db);	//PHP v8

			if ($db1)
			{
				// check for database
				if ($db != "")
					$havedb = CheckForDatabase ($self, $hn, $pt, $un, $pw, $db, $tb, $db1);

				if ($tb != "")
					$havetb = CheckForDatabaseTable ($self, $hn, $pt, $un, $pw, $db, $tb, $db1);
			}
		}

		if ($havehost == false)
		{
			$result = ShowLogin ($self, $hn, $pt, $un, $pw, $db, $tb);
		}
		else if (!$db1)
		{
			echo '<h2>Sorry - mysql login failed - please try again</h2>' . PHP_EOL;
			$result = ShowLogin ($self, $hn, $pt, $un, $pw, $db, $tb);
		}
		else if ((! isset($db)) or ($havedb == false))
		{
			if (isset($db))
				echo "<h2>Sorry - unknown database $db - please try again</h2>" . PHP_EOL;

			$result = ShowSelectDatabase ($self, $hn, $pt, $un, $pw, $db1);
		}
		else if (!isset($tb))
		{
			$result = ShowSelectTable ($self, $hn, $pt, $un, $pw, $db, $db1);
		}
		else if ($havetb == false)
		{
			echo "<h2>Sorry - unknown table $tb - please try again</h2><br>" . PHP_EOL;
			$result = ShowSelectTable ($self, $hn, $pt, $un, $pw, $db, $db1);
		}
		else if (!isset($id))
	{
			$result = ShowSelectId ($self, $hn, $pt, $un, $pw, $db, $tb, $db1);
		}
	else if (!isset($options))
	{
			$result = ShowSelectOptions ($self, $hn, $pt, $un, $pw, $db, $tb, $db1, $id);
		}
	else
	{
			$result = GenerateCode ($self, $hn, $pt, $un, $pw, $db, $tb, $db1, $id);
		}
	}

	if ($db1)
		mysqli_close ($db1);        // close any open connection
?>

</body>
</html>

