<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/validator.php");
include_once("internal/forumfunc.php");

session_start();


$H = new HTML();
$H->AddStyle("default.css");

$H->sTitle="Northern Cross - old help";

$sql=&OpenSQL($H);

$H->AddStyle("forum.css");

$H->Insert(new Error("Attention: almost all topics are not ready yet.<br>Thank you for your patience"));
get("page","string");
def($GET['page'],'main');
$page=makequotedstring($GET['page']);
$A=$sql->query("SELECT Description, Text FROM NC_Help WHERE Page=$page");
$Text=decode($A[0]['Text']);
$Description=htmlentities($A[0]['Description']);

$T=new Table();
$T->sClass="block forumtable";
$T->Insert(1,1,"Northern Cross Help");
$T->SetClass(1,1,"forummaintitle");
$T->Insert(1,2,"$Description");
$T->SetClass(1,2,"forumtitle");
$T->Insert(1,3,"$Text");
$H->Insert($T);

include("part/mainsubmenu.php");
$H->Draw();
CloseSQL($sql);
?>
