<?php
$H[]="Free Online Somewhat-Massive Multiplayer Game";
include('messages.php');

$T=new Table();
$T->setClass('nullspace');
$T(1,1)->_(Image("IMG/NCTitle.png","Northern Cross"))->span(1,2);
$T(2,1)->_(Image("IMG/Cygnus.jpg","Northern Cross constellation"))->span(1,2);
$T(3,1)->_("Login:");
$T(3,2)->_(TextInput(field("login"),$savedPlayerName));
$T(4,1)->_("Password:");
$T(4,2)->_(PasswordInput(field("password")));

if ($savedPlayerName!='')
  $H->onLoad("document.getElementsByName('password')[0].focus()");
else
  $H->onLoad("document.getElementsByName('login')[0].focus()");

$T(5,1)->_(Submit(field('submit_login'),'Enter'))->span(1,2)->setClass('buttons');

$H[]=Form('login.php')->_($T);

?>
