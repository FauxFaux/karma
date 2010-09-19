<?='<?xml version="1.0" encoding="UTF-8"?>'?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-gb">
<head>
	<link rel="stylesheet" href="style.css" type="text/css" />
<?
	$reasonspage=isset($_GET{'reasons'});

	if ($reasonspage)
		echo '	<title>Compsoc Karma Awards - Reasons for ' . $_GET{'reasons'} . '</title>';
	else
	{
?>
	<title>Compsoc Karma Awards</title>
	<script type="text/javascript"><!--
		function showit(item)
		{
			document.getElementById("l" + item).style.display='none';
			document.getElementById("r" + item).innerHTML='<iframe src="?reasons=' + item + '" />';
		}
		// -->
	</script>
<?
	}
?>
</head>
<body>
<?
	mysql_connect(null, 'choob_scripts');
	mysql_select_db('choob');
	ini_set('error_reporting', E_ALL);

	if ($reasonspage)
	{
		function dumpreasons($mes, $in)
		{
			$query='SELECT `reason` FROM `_objectdb_plugins_karma_karmareasonobject` WHERE `string` = "' . mysql_real_escape_string($_GET{'reasons'}) . '" AND `direction` = ';
			$res = mysql_query($query . $in);
			if (mysql_num_rows($res))
			{
				echo "<h2>..has $mes karma...</h2><ul>";
				while ($row = mysql_fetch_array($res))
					echo '<li>' . htmlentities($row[0]) . '</li>';
				echo '</ul>';
			}
		}

		dumpreasons('gained', 1);
		dumpreasons('lost', -1);
	}
	else
	{
		switch (@$_GET{'sort'})
		{
			case 'up': $sort='up'; break;
			case 'down': $sort='down'; break;

			default: $sort='mevalue';
		}

		$res = mysql_query('SELECT COUNT( `string` ) AS cnt, `string` FROM `_objectdb_plugins_karma_karmareasonobject` GROUP BY `string`');
		$hasreasons = array();
		while ($row = mysql_fetch_assoc($res))
			$hasreasons[strtolower($row['string'])]=$row['cnt'];

		mysql_free_result($res);
		
		if (isset($_GET{'real'}))
			$value='(`up` - `down`)';
		else if (isset($_GET{'delta'}))
			$value='(`up` + `down`)';
		else
			$value='value';
			
		$res = mysql_query('SELECT `string`,`up`,`down`,' . $value . ' AS `mevalue` FROM `_objectdb_plugins_karma_karmaobject` HAVING abs(`mevalue`) > 3 ORDER BY `' . $sort . '` DESC,`string` ASC');
?>
	<h1>Compsoc Karma Awards</h1>
	<p>Karma rankings generated live from BadgerBOT&#39;s database on the <a href="http://www.warwickcompsoc.co.uk/">UWCS</a> IRC server.</p>

	<p><a href="index.phps">The source</a> is avaliable - please suggest improvements.</p>
	<table>
		<tr><th>Rank</th><th>Picture</th><th>Object</th><th><?=(
			$sort!='up' ? '<a href="?sort=up">Up</a>' : '<span class="sorted">Up</span>'
		)?></th><th><?=(
						$sort!='down' ? '<a href="?sort=down">Down</a>' : '<span class="sorted">Down</span>'
				)?></th><th><?=(
			$sort!='mevalue' ? '<a href="?">Score</a>' : '<span class="sorted">Score</span>'
		)?></th></tr>
<?
		$imglist = array();
		$handler = opendir('images');
		while ($file = readdir($handler))
			if ($file != '.' && $file != '..')
				$imglist[] = $file;
		closedir($handler);

		$images=array();
		foreach ($imglist as $image)
			$images[strtolower(substr($image, 0, strpos($image, '.')))]=$image;

		$rank = 0;
		$prevval = 0;
		$even=false;
		while ($row = mysql_fetch_assoc($res))
		{
			if ($prevval != $row[$sort])
				$rank++;
			$prevval = $row[$sort];
			echo '		<tr' . ($even ? ' class="even"' : '') . "><td>$rank</td><td>" . (isset($images[$row['string']]) ? '<img src="images/' . $images[$row['string']] . '" alt="' . $row['string'] . '" />' : '' ) . '</td><td>';
			echo str_replace('_', ' ', htmlentities($row['string']));
			if (isset($hasreasons[$row['string']]))
				echo '<p id="l' . $row['string'] . '"> ..with <a href="?reasons=' . $row['string'] . '" onclick="javascript:showit(\'' . $row['string'] . '\'); return false;">' . $hasreasons[$row['string']] . ' reason' . ($hasreasons[$row['string']] == 1 ? '' : 's') . ' &raquo;</a></p><div id="r' . $row['string'] . '" />';
			echo "</td><td>{$row['up']}</td><td>{$row['down']}</td><td>{$row['mevalue']}</td></tr>\n";
			$even=!$even;
		}
		echo '	</table><p><a href="/">Back to Faux\'s Stuff</a>.</p>';
	}
?>
</body>
</html>
