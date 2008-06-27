##############################################################
## MOD Title: mod_fix_gallery_popups_7.x
## MOD Author: Jens Tkotz
## MOD Description: ---
## MOD Version: 7.x.x
##
## Installation Level: Easy
## Installation Time: ~5 Minutes
## Files To Edit:
##               modules.php
##
## Included Files: n/a
## $Id$
############################################################## 

#
#-----[ OPEN ]------------------------------------------
#
	modules.php

#
#-----[ FIND ]------------------------------------------
#
if ($view == 3 AND paid()) {
                                $modpath .= "modules/$name/$file.php";
                        if (file_exists($modpath)) {
                                include($modpath);
                        } else {
                                die ("Sorry, such file doesn't exist...");
                                }
                    } else { 
#
#-----[ AFTER, ADD ]------------------------------------------
#
                        if ($view != 0) { 



#
#-----[ FIND ]------------------------------------------
#

                                include("footer.php");
                                die();
#
#-----[ AFTER, ADD ]------------------------------------------
#
                        } 


#
#-----[ SAVE/CLOSE ALL FILES ]------------------------------------------
#
# EoM