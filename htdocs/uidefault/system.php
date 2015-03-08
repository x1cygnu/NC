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

foreach ($planets as $planet) {
  $row = $planet['Orbit']+2;
  $T($row,1)->_($planet['Orbit']);
  $T($row,2)->_($planet['TypeName']);
  $T($row,3)->_($planet['Population']);
  $T($row,4)->_($planet['Name']);
}

$H[]=$T;
?>
