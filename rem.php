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

$H->sTitle="Northern Cross - Account Removal";

$sql=&OpenSQL($H);

post("login","string");
post("pass","string");
post("reason","string");
if ($POST['login']!="" and $POST['pass']!="") {
    $S=account_remove($sql, $POST['login'], $POST['pass']);
    if ($S===true)
	$H->Insert(new Info("Account has been succesfully removed"));
    else
	$H->Insert(new Error($S));
}
$e=$GET['e'];


$F=new Form("rem.php",true);
$T=new Table();
$T->sClass="regform";
$T->sDefaultRowClass="block";
$T->Insert(1,1,"Account removal form");
$T->aRowClass[1]="title";
$T->Insert(1,2,"Login");
$T->SetClass(1,2,"legend");
$T->Insert(2,2,new Input("text","login",$GET['login'],"text",1));
$T->Insert(1,3,"Password");
$T->SetClass(1,3,"legend");
$T->Insert(2,3,new Input("password","pass","","text",2));
$T->Insert(1,4,"Reason");
$T->SetClass(1,4,"legend");
$T->Insert(2,4,new Input("text","reason",$GET['email'],"text",4));
$T->SetClass(1,5,"legend");
$T->Join(1,1,2,1);
$T->Insert(1,5,new Input("submit","","Remove","smbutton",5));
$T->Join(1,5,2,1);

$F->Insert($T);

$H->Insert(new Image("./IMG/NCTitle.png","Northern Cross"));
$H->Insert($F);

$H->Insert("If you have problems with account removal, please contact CygnusX1 at nc @ smp.if.uj.edu.pl");
$H->Br();
$H->Insert("You can also contact CygnusX1 via in-game PM");


include("part/mainsubmenu.php");
CloseSQL($sql);
$H->Draw();
?>
