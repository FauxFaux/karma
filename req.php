<?
mysql_connect(null, 'choob_scripts');
mysql_select_db('choob');
$res = mysql_query('SELECT `up`,`down`,value FROM `_objectdb_plugins_karma_karmaobject` where `string`="' . mysql_real_escape_string($_SERVER['QUERY_STRING']) . '"');
echo mysql_error();
while ($a = mysql_fetch_row($res))
	foreach ($a as $b)
		echo "$b ";

