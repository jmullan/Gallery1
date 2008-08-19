<?php
/**
 * Gallery SVN ID:
 * $Id: multiInput.js.php 13850 2006-06-19 12:37:37Z jenst $
*/

	require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
?>
// full day names
Calendar._DN = new Array(
<?php
$i=0; echo strftime('"%A"', mktime(0,0,0,5,$i,0));
do { $i++; echo strftime(',%n"%A"', mktime(0,0,0,5,$i,0)); } while ($i < 7);
?>);

// Please note that the following array of short day names (and the same goes
// for short month names, _SMN) isn't absolutely necessary.  We give it here
// for exemplification on how one can customize the short day names, but if
// they are simply the first N letters of the full name you can simply say:
//
//   Calendar._SDN_len = N; // short day name length
//   Calendar._SMN_len = N; // short month name length
//
// If N = 3 then this is not needed either since we assume a value of 3 if not
// present, to be compatible with translation files that were written before
// this feature.

// short day names
Calendar._SDN = new Array(
<?php
$i=0; echo strftime('"%a"', mktime(0,0,0,5,$i,0));
do { $i++; echo strftime(',%n"%a"', mktime(0,0,0,5,$i,0)); } while ($i < 7);
?>);

// First day of the week. "0" means display Sunday first, "1" means display
// Monday first, etc.
Calendar._FD = 1;

// full month names
Calendar._MN = new Array(
<?php
$i=2; echo strftime('"%B"', mktime(0,0,0,$i,0,0));
do { $i++; echo strftime(',%n"%B"', mktime(0,0,0,$i,0,0)); } while ($i < 13);
?>);

// short month names
Calendar._SMN = new Array(
<?php
$i=2; echo strftime('"%b"', mktime(0,0,0,$i,0,0));
do { $i++; echo strftime(',%n"%b"', mktime(0,0,0,$i,0,0)); } while ($i < 13);
?>);

// tooltips
Calendar._TT = {};
Calendar._TT["INFO"]	= "<?php echo gTranslate('core', "About the calendar"); ?>";

Calendar._TT["ABOUT"] =
"DHTML Date/Time Selector\n" +
"(c) dynarch.com 2002-2005 / Author: Mihai Bazon\n" + // don't translate this this ;-)
"For latest version visit: http://www.dynarch.com/projects/calendar/\n" +
"Distributed under GNU LGPL.  See http://gnu.org/licenses/lgpl.html for details." +
"\n\n" +
"Date selection:\n" +
"- Use the \xab, \xbb buttons to select year\n" +
"- Use the " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + " buttons to select month\n" +
"- Hold mouse button on any of the above buttons for faster selection.";
Calendar._TT["ABOUT_TIME"] = "\n\n" +
"Time selection:\n" +
"- Click on any of the time parts to increase it\n" +
"- or Shift-click to decrease it\n" +
"- or click and drag for faster selection.";

Calendar._TT["PREV_YEAR"]	= "<?php echo gTranslate('core', "Prev. year (hold for menu)") ?>";
Calendar._TT["PREV_MONTH"]	= "<?php echo gTranslate('core', "Prev. month (hold for menu)") ?>";
Calendar._TT["GO_TODAY"]	= "<?php echo gTranslate('core', "Go Today"); ?>"
Calendar._TT["NEXT_MONTH"]	= "<?php echo gTranslate('core', "Next month (hold for menu)") ?>";
Calendar._TT["NEXT_YEAR"]	= "<?php echo gTranslate('core', "Next year (hold for menu)") ?>";
Calendar._TT["SEL_DATE"]	= "<?php echo gTranslate('core', "Select date") ?>";
Calendar._TT["DRAG_TO_MOVE"]= "<?php echo gTranslate('core', "Drag to move") ?>";
Calendar._TT["PART_TODAY"]	= "<?php echo gTranslate('core', " (today)") ?>";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"]	= "<?php echo gTranslate('core', "Display %s first") ?>";

// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] = "0,6";

Calendar._TT["CLOSE"]		= "<?php echo gTranslate('core', "Close") ?>";
Calendar._TT["TODAY"]		= "<?php echo gTranslate('core', "Today") ?>";
Calendar._TT["TIME_PART"]	= "<?php echo gTranslate('core', "(Shift-)Click or drag to change value") ?>";

// date formats
Calendar._TT["DEF_DATE_FORMAT"]	= "<?php echo gTranslate('core', "%Y-%m-%d") ?>";
Calendar._TT["TT_DATE_FORMAT"]	= "<?php echo gTranslate('core', "%a, %b %e") ?>";

Calendar._TT["WK"]		= "<?php echo gTranslate('core', "wk") ?>";
Calendar._TT["TIME"]	= "<?php echo gTranslate('core', "Time:") ?>";