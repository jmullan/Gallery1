<?
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000 Bharat Mediratta
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */
?>
<?
session_register_and_set("cols");
session_register_and_set("rows");
session_register_and_set("page");
session_register_and_set("edit");
session_register_and_set("albumName");
session_register_and_set("albumListPage");

function session_register_and_set($name) {
	session_register($name);
	$setname = "set_$name";
	global $$name;
	global $$setname;
	if (!empty($$setname)) {
		$$name = $$setname;
	} if (!$$name) {
		global $app;
		if (strcmp($app->default["$name"], "")) {
			$$name = $app->default["$name"];
		}
	}
}
?>
