<?php

/**
 *
 * Prints a box with a nested table. Left side is some text, right side the (admin) commands.
 *
 * @package Gallery
 * @todo 	Move to templates/
 *
 * @version $Id$
 */

global $gallery, $adminbox;

$checklist = array('header', 'commands', 'text');
foreach ($checklist as $check) {
	if (!isset($adminbox[$check])) {
		$adminbox[$check] = '';
	}
}

/* Note: We use a table here, as its automatically scales the height. */
?>

<div class="g-adminbar">
  <table width="100%">
  <tr>
      <td colspan="2" class="g-adminbarheader right"><?php echo $adminbox['header'] ?></td>
    </tr>
    <tr>
      <td class="g-adminbartext left"><?php echo $adminbox['text'] ?></td>
      <td class="g-adminbarcommands right">
      		<?php echo $adminbox['commands']; ?>
      </td>
    </tr>
  </table>
</div>
