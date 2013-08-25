<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");
include_once("internal/news.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");

$H->sTitle="Northern Cross - Broadcast";

$sql=&OpenSQL($H);

ForceActivePlayer($sql, $H, "broadcast.php");

$menuselected="Broadcast";
include("part/mainmenu.php");

if (!$_SESSION['IsAdmin'])
    {
	$H->Insert(new Error("Must be an admin in order to see this page"));
	$H->Draw();
	die;
    }


post("send","string");
if ($POST['send']=="Send")
{
    post("messy","string");
    news_broadcast($sql,$POST['messy']);
    $H->Insert(new Info("Message sent"));
}


$T=new Table();
$T->sClass='block';
$T->Insert(1,1,"Broadcast");
$T->Insert(1,2,"<textarea class=\"text forumtext\" name=\"messy\" rows=\"5\" cols=\"40\">$Text</textarea>");
$T->Insert(1,3,new Input("submit","send","Send","smbutton"));

$F=new Form("broadcast.php",true);
$F->Insert($T);
$H->Insert($F);

$H->Draw();
CloseSQL($sql);
?>
