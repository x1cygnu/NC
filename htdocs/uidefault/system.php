<?php
include_once('./uidefault/menu.php');
include('./uidefault/messages.php');

$H->addStyleFile($UI.'/system.css');

$T=new Table();

$T(1,1)
  ->_($star['Name']." ")
  ->_("(${star['X']}/${star['Y']})")
  ->span(1,4)
  ->setClass('title');

$T->row(2)->setClass('legend');
$T(2,1);
$T(2,2)->_('class');
$T(2,3)->_('pop');
$T(2,4)->_('owner');

foreach ($planets as $planet) {
  $row = $planet['Orbit']+3;
  $T($row,1)->_($planet['Orbit'])->setClass('porbit');
  $T($row,2)->_($planet['TypeName'])->setClass('ptype');
  $T($row,3)->_($planet['Population'])->setClass('ppop');
  $T($row,4)->_($planet['Name'])->setClass('powner');
}

$H[]=$T;
?>
