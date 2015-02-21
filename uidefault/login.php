<?php
$H[]="Free Online Somewhat-Massive Multiplayer Game";

$T=new Table();
$T->setClass('nullspace');
$T(0,0)[] = new Image("IMG/NCTitle.png","Northern Cross");
$T(1,0)[] = new Image("IMG/Cygnus.jpg","Northern Cross constellation");
$T(2,0)[] = "Login:";
$T(2,0)[] = new TextInput(field("login"),$_COOKIE['pname']);
$T(3,0)[] = "Password:";
$T(3,0)[] = new PasswordInput(field("password"));

if (isset($_COOKIE['pname']))
  $H->onLoad("document.getElementsByName('password')[0].focus()");
else
  $H->onLoad("document.getElementsByName('login')[0].focus()");

$T(4,0)[] = new Submit(submit('login'),'Enter');

$F=new Form('login.php');
$F[]=$T;

$H[]=$F;
?>
