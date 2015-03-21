<?php
include_once('./uidefault/menu.php');
include('./uidefault/messages.php');

$H->addStyleFile($UI.'/planet.css');

$T=new Table();

$T(1,1)
  ->_($planet->name)
  ->span(1,4)
  ->setClass('title');

$H[]=$T;
?>
