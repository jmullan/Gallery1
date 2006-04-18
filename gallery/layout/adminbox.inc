<?php /* $Id$ */ ?>
<?php

global $gallery, $adminbox;

$checklist = array('commands', 'text');
foreach ($checklist as $check) {
	if (!isset($adminbox[$check])) {
		$adminbox[$check] = '';
	}
}

?>

<div class="g-adminbar">
  <table width="100%">
    <tr>
      <td class="g-adminbartext left"><?php echo $adminbox["text"] ?></td>
      <td class="g-adminbarcommands right">
	<form name="admin_options_form" action="view_album.php" class="right">
	<?php echo $adminbox["commands"] ?>
	</form>
      </td>
    </tr>
  </table>
</div>
