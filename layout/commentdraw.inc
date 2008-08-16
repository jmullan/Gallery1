<?php
	/* $Id$ */

global $commentdraw, $i, $index, $id;

$commentTable = new galleryTable();
$commentTable->setAttrs(array(
	'width' => '75%',
	'align' => 'center',
        'border' => 0,
        'cellspacing' => 0,
        'cellpadding' => 0,
        'class' => 'g-comment-box'));

$commentTable->setColumnCount(3);

$commenterName = '<b>'. wordwrap($commentdraw["name"], 50, " ", 1) .'</b>';
if ($gallery->user->isAdmin()) {
	$commenterName .= '@ &nbsp;'. $commentdraw["IPNumber"];
}

$commentTable->addElement(array(
        'content' => gTranslate('core', "From:"),
        'cellArgs' => array('class' => 'commentboxhead', 'width' => 50)));

$commentTable->addElement(array(
        'content' => $commenterName,
        'cellArgs' => array('class' => 'commentboxhead', 'width' => '55%')));


$commentTable->addElement(array(
        'content' => '('. $commentdraw["datePosted"] .')',
        'cellArgs' => array('class' => 'commentboxhead')));


$commentTable->addElement(array(
        'content' => wordwrap($commentdraw["comment"], 100, " ", 1),
        'cellArgs' => array('colspan' => '3')));

if ($gallery->user->canWriteToAlbum($gallery->album)) {
    $url = doCommand('delete-comment',
	array('index'=> $commentdraw['index'],
		'comment_index' => $i,
		'albumName' => $commentdraw['albumName'])
	);

   $commentTable->addElement(array(
        'content' => '<a href="#" onclick="javascript:' . popup($url,1) . '">['. gTranslate('core', "delete comment") . ']</a>',
        'cellArgs' => array('colspan' => '3', 'class' => 'commentboxfooter', 'align' => 'center')));
}

global $commentBox;
$commentBox = $commentTable->render();

?>