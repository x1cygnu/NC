<?php
include_once('./uidefault/menu.php');
include('./uidefault/messages.php');

$H->addStyleFile($UI.'/planets.css');

$T=new Table();

$T(1,1)
  ->_("Owned planets")
  ->span(1,4)
  ->setClass('title');

$T->row(2)->setClass('legend');
$T(2,1);
$T(2,2)->_('name');
$T(2,3)->_('pop');

$row=2;
foreach ($planets as $planet) {
  ++$row;
  $T($row,2)->_($planet['Name'].' '.$planet['Orbit'])->setClass('pname');
  $T($row,3)->_($planet['Population'])->setClass('ppop');
}

$H[]=$T;
?>
