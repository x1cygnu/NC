<?php
chdir('..');
include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");
include_once("internal/armageddon.php");
include_once("internal/account.php");
include_once("internal/alliance.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->AddStyle("default.css");
$H->AddStyle("title.css");

$T=new Table();
$T->Insert(1,1,"Login:");
$T->Insert(2,1,new Input("text","login","","text name"));
$T->Insert(1,2,"Password:");
$T->Insert(2,2,new Input("password","password","","text name"));
$T->Insert(1,3,new Input("submit","subtmit","Enter","smbutton"));

$sql=&OpenSQL();
if (isset($sql))
{

if (CheckFrozen($sql))
    $H->Insert(new Error("Game is frozen at the moment"));

$U=round_get_frozen($sql);
$Now=EncodeNow();
if ($Now<$U['FrozenTo'])
{
    $T->Insert(1,4,"Frozen (GMT)");
    $T->Insert(2,4,"" . DecodeTime($U['FrozenFrom'],0));
    $T->Insert(2,4," - ");
    $T->Insert(2,4,"" . DecodeTime($U['FrozenTo'],0));
}

}
else
{
    $H->Insert("Database locked out");
}

$F=new Form("login.php");
$F->Insert(&$T);
$H->Insert(&$F);
$H->Draw();
?>
