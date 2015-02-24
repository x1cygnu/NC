<?php
if (isset($registerAID)) {
  info('Account created');
  $go="index";
  include('./gopart.php');
} else {
include("./$UI/messages.php");

$T=new Table();
$T(1,1)->_("Registration")->setClass("title")->span(1,2);
$T(2,1)->_("Login name:")->setClass('legend');
$T(2,2)->_(TextInput(field('login'),str_or_empty($loginName)));
$T(3,1)->_("Shown name:")->setClass('legend');
$T(3,2)->_(TextInput(field('showname'),str_or_empty($showName)));
$T(4,1)->_('Password:')->setClass('legend');
$T(4,2)->_(PasswordInput(field('password')));
$T(5,1)->_('Password:')->setClass('legend');
$T(5,2)->_(PasswordInput(field('password2')));
$T(6,1)->_("e-mail:")->setClass('legend');
$T(6,2)->_(TextInput(field('email'),str_or_empty($email)));
$T(7,1)->_(Submit(field('submit_register'),'Register'))->span(1,2);

$H[]=Form('register.php')->_($T);
}

?>
