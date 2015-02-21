<?php
$H[]="Free Online Somewhat-Massive Multiplayer Game";
include('messages.php');

$T=new Table();
$T->setClass('nullspace');
$T(0,0)->_(Image("IMG/NCTitle.png","Northern Cross"));
$T(1,0)->_(Image("IMG/Cygnus.jpg","Northern Cross constellation"));
$T(2,0)->_("Login:")->_(TextInput(field("login"),$savedPlayerName));
$T(3,0)->_("Password:")->_(PasswordInput(field("password")));

if ($savedPlayerName!='')
  $H->onLoad("document.getElementsByName('password')[0].focus()");
else
  $H->onLoad("document.getElementsByName('login')[0].focus()");

$T(4,0)->_(Submit(field('login'),'Enter'));

$H[]=Form('login.php')->_($T);

?>
