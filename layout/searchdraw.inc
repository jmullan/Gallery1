<?

// If the border color is not passed in, we do a black/white combo
if ($searchdraw["bordercolor"]) {
	$borderIn = $searchdraw["bordercolor"];
} else {
	$borderIn = "#FFFFFF";
}

$imageDir = $app->photoAlbumURL."/images";
$pixelImage = "<img src=\"$imageDir/pixel_trans.gif\" width=\"1\" height=\"1\">";

if ($searchdraw["Title"]) {
?>
  <span class="title">
    <?=$searchdraw["Title"] ?>
  </span>
  <br>
  <br>
  <table width="<?=$navigator["fullWidth"] . $navigator["widthUnits"]?>" border="0" cellspacing="0" cellpadding="0">
<?
} 
?>
  <tr>
    <td width=150></td>
    <td colspan="2" bgcolor="<?=$borderIn?>"><?=$pixelImage?></td>
  </tr>
<?
?>
  <tr>
    <td rowspan=2 width=152>
      <a href="<?=$searchdraw["photoURL"]?>"><?=$searchdraw["photolink"]?></a>
    </td>
    <td rowspan=2 width=30>
      &nbsp;
    </td>

<?
?>
    <td>
      <?=$searchdraw["Text1"]?>
    </td>
<?
?>
    <tr>
      <td>
        <?=$searchdraw["Text2"]?>
      </td>
    </tr>
  </tr>
<?
?>
