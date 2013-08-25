<?php
//ob_start("ob_gzhandler");

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");
include_once("internal/background.php");

session_start();



$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");

$H->sTitle="Northern Cross - Background images";

$sql=&OpenSQL($H);

ForcePlayer($sql, $H, "bgimg.php");

if ($_SESSION['IsAdmin']!=1)
    {
	$H->Draw();
	die;
    }

$menuselected="Bgmap";
include("part/mainmenu.php");


get("e","integer");
if ($_GET['e']!="")
{
    $sql->query("DELETE FROM NC_NewBackgroundList WHERE NBgX={$GET['e']}");
}


post("file","string");
if (exists($POST['file']))
    $sql->query("INSERT INTO NC_NewBackgroundList VALUES(NULL," . makequotedstring($POST['file']) . ")");

$F=new Form("bgimg.php","true");
$F->Insert("File name: ");
$F->Insert(new Input("text","file","","text"));
$F->Insert(new Br());
$F->Insert(new Input("submit","submit","submit","smbutton"));
$F->Insert(new Br());
$H->Insert($F);

$T=new Table();
$T->sClass='block';
$T->Insert(1,1,"Background image manager");
$T->Insert(1,2,"Id");
$T->Insert(2,2,"Picture");
$T->Insert(3,2,"Delete");
$T->Join(1,1,3,1);
$A=$sql->query("SELECT * FROM NC_NewBackgroundList ORDER BY NBgX");

$row=2;
foreach ($A as $Pic)
{
    ++$row;
    if (!isset($L[$X]))
    {
        $T->Insert(1,$row,$Pic['NBgX']);
	$T->SetClass(1,$row,'sublegend');
	$T->Insert(3,$row,new Link("bgimg.php?e=" . $Pic['NBgX'],"X"));
	}
    $T->Insert(2,$row,new Image("IMG/bg/" . $Pic['FileName']));
}
$T->aRowClass[1]='title';
$T->aRowClass[2]='legend';
$H->Insert($T);
$H->Draw();
CloseSQL($sql);
?>
