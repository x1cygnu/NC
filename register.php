<?php

include_once("internal/html.php");
include_once("constant.php");
include_once("internal/security/validator.php");
include_once("internal/account.php");
include_once("internal/common.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");
$H->AddStyle("register.css");

$H->sTitle="Northern Cross - Registration";


get("login","string");
get("email","string");
get("e","integer");

$e=$GET['e'];

if ($e & 1) $H->Insert(new Error("Login required in order to register."));
if ($e & 2) $H->Insert(new Error("Login length must be between 2 and 32."));
if ($e & 256) $H->Insert(new Error("Only spaces are present in your login"));
if ($e & 512) $H->Insert(new Error("Login may only consist of alphanumeric characters and space"));
if ($e & 4) $H->Insert(new Error("Please, provide your password."));
if ($e & 8) $H->Insert(new Error("Please, repeat your password in \"confirm password\" field."));
if ($e & 16) $H->Insert(new Error("Passwords do not match."));
if ($e & 32) $H->Insert(new Error("Provide your e-mail. <br/> You will not be able to finalise registration without valid email address."));
if ($e & 64) $H->Insert(new Error("Provided e-mail does not seem to be valid. <br/> You will not be able to finalise registration without valid email addres."));
if ($e & 128) $H->Insert(new Error("Login or e-mail is already in use"));


$F=new Form("regconf.php",true);
$T=new Table();
$T->sClass="regform";
$T->sDefaultRowClass="block";
$T->Insert(1,1,"Registration form");
$T->aRowClass[1]="title";
$T->Insert(1,2,"Login");
$T->SetClass(1,2,"legend");
$T->Insert(2,2,new Input("text","pass1",$GET['login'],"text",1));
$T->Insert(1,3,"Password");
$T->SetClass(1,3,"legend");
$T->Insert(2,3,new Input("password","login","","text",2));
$T->Insert(1,4,"Confirm password");
$T->SetClass(1,4,"legend");
$T->Insert(2,4,new Input("password","pass2","","text",3));
$T->Insert(1,5,"e-mail");
$T->SetClass(1,5,"legend");
$T->Insert(2,5,new Input("text","email",$GET['email'],"text",4));
//$T->Insert(1,6,"Activation link will be send to provided e-mail.");
$T->Insert(1,7,"You are not allowed to have more than one account!");
//$T->SetClass(1,6,"regwarning");
$T->SetClass(1,7,"regwarning");
$T->SetClass(1,8,"legend");
$T->Join(1,6,2,1);
$T->Join(1,1,2,1);
$T->Insert(1,8,new Input("submit","","Register","smbutton",5));
$T->Join(1,7,2,1);
$T->Join(1,8,2,1);

$F->Insert($T);

$H->Insert(new Image("./IMG/NCTitle.png","Northern Cross"));
$H->Insert($F);

$H->Script("
function Check()
{
alert('dupa');
alert(login);
return false;
}
");
//$H->Insert("Warning: For some reason @aol reject activation mails. Please use different address");
//$H->Insert(new Br());
$H->Insert("If you have problems with registering, please contact CygnusX1 at nc @ smp.if.uj.edu.pl");


include("part/mainsubmenu.php");
$H->Draw();
?>
