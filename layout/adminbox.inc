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
      <td class="g-adminbartext left"><?php echo $adminbox['text'] ?></td>
      <td class="g-adminbarcommands right"  >
      		<?php echo $adminbox['commands'] ?>
      </td>
    </tr>
  </table>
</div>
