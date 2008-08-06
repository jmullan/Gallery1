<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title> Gallery 1: Localization Status Report (<?php print strftime("%x") ?>)</title>
    <link rel="stylesheet" type="text/css" href="g1-report.css">
  </head>
    <body>
	<h1> Localization Status Report for Gallery 1 </h1>
	<h3> Generated: <?php print strftime("%x %X") ?></h3>
	<h3> Build: <?php echo $gallery->version ?></h3>

      <center>
	<table>
<?php
	$index = 0;
	$nr = 1;
	$old_percentDone = 0;
	foreach ($reportData as $locale => $localeData) {
		if (!($index % 5)) {
			include(dirname(__FILE__) . '/row_header.inc');
		}
		$index++;
		$class = $index % 2 ? 'dark' : 'light';
		if (($localeData['plugins']['core']['percentDone'] < 50 || $localeData['plugins']['common']['percentDone'] <40) && $pluginData['percentDone'] <50) {
			$wclass = 'kicked';
		} else {
			$wclass = $class;
		}
?>
	<tr>
		<td rowspan="<?php print sizeof($localeData['plugins'])+1 ?>" class="<?php echo $wclass ?>"><?php echo ($localeData['percentDone'] != $old_percentDone) ? $nr : "&nbsp;" ?></td>
		<td rowspan="<?php print sizeof($localeData['plugins'])+1 ?>" class="<?php echo $class ?>"><?php print $reportData[$locale]['langname'] ?></td>
		<td rowspan="<?php print sizeof($localeData['plugins'])+1 ?>" class="<?php echo $class ?>"><?php print $locale ?></td>
		<td bgcolor="#<?php print percentColor($localeData['percentDone']) ?>" rowspan="<?php print sizeof($localeData['plugins']) +1 ?>" class="locale_percent"><?php print $localeData['percentDone'] ?>%</td>
		<td colspan="5" height="1"></td>
	<tr>
<?php 
		$keys = array_keys($localeData['plugins']);
		$plugin = $keys[0];
		$pluginData = $localeData['plugins'][$plugin];
		$modifier = 'light';
		include(dirname(__FILE__) . '/plugin_rowdata.inc');
?>
	</tr>

<?php 
		for ($i = 1; $i < sizeof($localeData['plugins']); $i++) {
			echo "\n\t<tr>";
			$keys = array_keys($localeData['plugins']);
			$plugin = $keys[$i];
			$pluginData = $localeData['plugins'][$plugin];
			$modifier = $i % 2 ? 'dark' : 'light';
			include(dirname(__FILE__) . '/plugin_rowdata.inc');
			echo "\n\t</tr>";
		}
		if ($localeData['percentDone'] != $old_percentDone) $nr++;
		$old_percentDone=$localeData['percentDone'];
	} 
?>
	<tr>
		<th colspan="11">Totals</th>
	</tr>
	<tr>
		<td rowspan="<?php print sizeof($localeData['plugins'])+1 ?>"><?php echo $index ?></td>
		<td rowspan="<?php print sizeof($localeData['plugins'])+1 ?>" colspan="2" align="center">All</td>
		<td rowspan="<?php print sizeof($localeData['plugins'])+1 ?>" bgcolor="<?php echo percentColor(round($total_percentDone['all']/$index,2)) ?>"><?php echo round($total_percentDone['all']/$index,2) ?>%</td>
	</tr>
<?php

	foreach ($total_percentDone as $key => $percentDone) {
		if ($key == 'all') continue;
		echo '<tr>';
		echo '<td>' . $key . '</td>';
		echo '<td bgcolor="' . percentColor(round($percentDone/$index,2)) . '">' . round($percentDone/$index,2) . '%</td>';
		echo '<td colspan="5"></td>';
		echo '</tr>';
	}
?>
	</table>

      </center>
    </body>
</html>
