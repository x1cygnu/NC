<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/validator.php");
include_once("internal/account.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");

$H->sTitle="Northern Cross - SU";

$sql=&OpenSQL($H);

ForcePlayer($sql, $H, "su.php");

$menuselected="SU";
include("part/mainmenu.php");

if (!$_SESSION['IsAdmin'])
    {
    $H->Insert(new Error("Only admin may enter here"));
    $H->Draw();
    die;
    }

post("name","string");
post("loose","integer");
if (exists($POST['name']))
{
    $R=account_su($sql, $POST['name']);
    if ($R!=="")
	$H->Insert(new Error("$R"));
    else {
      $H->Insert(new Info("Login successful"));
      if ($POST['loose']==1)
	$_SESSION['IsAdmin']=0;
    }
}
$T=new Table();
$T->sClass='block';
$T->Insert(1,1,"Login as:");
$T->Insert(2,1,new Input("text","name","","text"));
$T->Insert(1,2,"Loose admin priviledges:");
$T->Insert(2,2,new Input("checkbox","loose","1",""));
$T->Insert(1,3,new Input("submit","login","login","smbutton"));
$T->Join(1,3,2,1);

$F=new Form("su.php",true);
$F->Insert($T);
$H->Insert($F);
$H->Draw();
CloseSQL($sql);
?>
