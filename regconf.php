<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("constant.php");
include_once("internal/security/validator.php");
include_once("internal/account.php");

session_start();

$delay=3000;

global $GET;
$GET=array();

$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");

$H->sTitle="Northern Cross - Registration confirmation";

global $POST;

post("login","string");
post("pass1","string");
post("pass2","string");
post("email","string");

$login=$POST['pass1'];
$pass1=$POST['login'];
$pass2=$POST['pass2'];
$email=$POST['email'];

$e=0;

if (!exists($login))
    $e+=1;
else
{
    if (strlen($login)<2 or strlen($login)>32)
	$e+=2;
    $onlyspaces=true;
    $illegal=false;
    for ($i=0; $i<strlen($login); ++$i)
    {
	if ($login{$i}!=' ')
	    $onlyspaces=false;
	if (!(($login{$i}>='A' and $login{$i}<='Z') or ($login{$i}>='a' and $login{$i}<='z') or ($login{$i}>='0' and $login{$i}<='9') or $login{$i}==' '))
	    $illegal=true;
    }
    if ($onlyspaces)
	$e+=256;
    if ($illegal)
	$e+=512;
}
if (!exists($pass1))
    $e+=4;
elseif (!exists($pass2))
    $e+=8;
elseif ($pass1!=$pass2)
    $e+=16;
if (!exists($email))
    $e+=32;
else
{    
$demail=explode("@",$email);
$c=count($demail);
$sbef=strlen($demail[0]);
$saft=strlen($demail[1]);
$cdot=substr_count($demail[1],".");

if ($c!=2 or $sbef<3 or $saft<5 or $cdot==0)
    $e+=64;
}

if ($e==0)
{
    $sql=&OpenSQL($H);
    $regcode=random();
    settype($regcode,"string");
    if (!account_create($sql, $login, $pass1, $email, $regcode))
	$e=128;
    else
    {
/*    
    mail($email,"Northern Cross registration",
"
Welcome to Northern Cross constellation!

You have just registered as '$login' with password '$pass1'. 
If you don't know what it is all about, please just ignore this mail.
If that is the case, the Northern Cross team apologises for spamming your
email account. To finalize registration process, please follow that link:
" . $NCURL . "/regme.php?regcode=" . $regcode . "

For your safety, we advice you not to use deprececated web browsers,
like Internet ExploDer. Our pages are written according to
W3C HTML 4.01 Transitional standard which may be misunderstood
by such browsers.

Greetings,
NC Management

","From: nc@smp.if.uj.edu.pl");*/
//    $H->Insert(new Info("You have registered in Northern Cross<br/>Please follow instructions in e-mail you shall receive soon, to activate your account."));
    $H->Insert(new Info("You have succesfully registered in Northern Cross<br/>You may start playing now!"));
    }
}

if ($e>0)
{
$H->Script("parent.location='register.php?login={$_POST['pass1']}&email={$_POST['email']}&e=$e';");
}

include("part/mainsubmenu.php");
$H->Draw();
?>
