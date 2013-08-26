<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/validator.php");

include_once("internal/help.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->AddStyle("default.css");

$menuselected="Help";
include("part/mainmenu.php");

$sql=&OpenSQL($H);

post("post","string");
if ($_SESSION['IsAdmin'] and $POST['post']=='Help')
{
    post("entry","string");
    post("descr","string");
    post("messy","string");
    if (!exists($POST['entry']))
	$H->Insert(new Error("No page name specified"));
    elseif (!exists($POST["descr"]))
	$H->Insert(new Error("No description"));
    elseif (!exists($POST["messy"]))
	$H->Insert(new Error("No message"));
    else
	help_put($sql,$POST['entry'],$POST['descr'],$POST['messy']);
}

$A=$sql->query("SELECT * FROM NC_Help ORDER BY HID");

$T=new Table();
$T->Insert(1,1,"Northern Cross help pages");
$T->Insert(1,2,"#");
$T->Insert(2,2,"ID");
$T->Insert(3,2,"Page name");
$T->Insert(4,2,"Page description");
$T->Insert(5,2,"Edit");

$T->sClass='block';
$T->aRowClass[1]='title';
$T->aRowClass[2]='legend';

$i=2;
$row=0;
foreach ($A as $Help)
{
    ++$i;
    ++$row;
    $T->Insert(1,$i,"{$row}");
    $T->Insert(2,$i,$Help['HID']);
    $T->Insert(3,$i,$Help['Page']);
    $T->Insert(4,$i,$Help['Description']);
    if ($_SESSION['IsAdmin'])
    {
	$T->Insert(5,$i,new Link("post.php?help={$Help['Page']}","Edit"));
    }
    $T->SetRowLink($i,"help.php?page={$Help['Page']}");
    $T->onRowMouseOver($i,"this.className='menu'");
    $T->onRowMouseOut($i,"this.className='block'");

} 
++$i;
if ($_SESSION['IsAdmin'])
{
    $T->Insert(1,$i,new Link("post.php?help=_new","New page"));
    $T->aRowClass[$i]='title';
    $T->Join(1,$i,5,1);
}

$T->Join(1,1,5,1);
$H->Insert($T);

include("part/mainsubmenu.php");

$H->Draw();
CloseSQL($sql);
?>
