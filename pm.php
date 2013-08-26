<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/validator.php");
include_once("internal/pm.php");
include_once("internal/account.php");
session_start();


$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");

$H->sTitle="Northern Cross - Messages";

$sql=&OpenSQL($H);

ForcePlayer($sql, $H, "pm.php");

$H->AddStyle("forum.css");
$H->AddStyle("pm.css");


get("v","string");


get("ee","integer");
if ($GET['ee']>0)
    pm_user_remove($sql, $GET['ee'], $_SESSION['AID']);

get("dd","integer");
if ($GET['dd']>0)
{
    pm_user_remove_several($sql, $GET['dd'], $_SESSION['AID'], $GET['v']);
    }

if (PostControl(true))
    post("post","string");
if ($POST['post']=="Send")
{
    post("to","string");
    post("topic","string");
    post("messy","string");
    if (!double_post($POST['to'] . $POST['topic'] . $POST['messy']))
    {
    $id=account_get_id($sql,$POST['to']);
    if (!exists($id))
	$H->Insert(new Error("Unknown addressat"));
    elseif (strlen($POST['messy'])<1)
	$H->Insert(new Error("Message body missing"));
    else
    {
	pm_send($sql, $_SESSION['AID'], $id, $POST['topic'], $POST['messy']);
	$H->Insert(new Info("Message has been sent"));
	}
    }
    else
    $H->Insert(new Error("Double post"));
    $GET['v']="out";
}

$menuselected="PM";
include("part/mainmenu.php");

$T=new Table();
$T->sClass='legend';
$T->Insert(1,1,"Northern Cross Mail");
$T->SetClass(1,1,"forummaintitle");
$T->Insert(1,2,new Link("pm.php?v=all","All"));
$T->Insert(2,2,new Link("pm.php?v=in","Inbox"));
$T->Insert(3,2,new Link("pm.php?v=out","Outbox"));
$T->Insert(4,2,new Link("post.php?pm=0","Compose"));
$T->Join(1,1,4,1);

$H->Insert($T);
get("e","integer");
if ($GET['e']>0)
    $H->Insert(new Link("pm.php?v={$GET['v']}&ee={$GET['e']}","Click here to confirm deletion"));

get("d","integer");
if ($GET['d']>0)
    $H->Insert(new Link("pm.php?v={$GET['v']}&dd={$GET['d']}","Click here to confirm deletion"));

$PMs=array();

if ($GET['v']=='all') $PMs=pm_get_all($sql,$_SESSION['AID']);
elseif ($GET['v']=='in' or $GET['v']=='') $PMs=pm_get_in($sql,$_SESSION['AID']);
elseif ($GET['v']=='out') $PMs=pm_get_out($sql,$_SESSION['AID']);


$i=1;
$T=new Table();
$T->sClass='block forumtable';

foreach ($PMs as $PM)
{
    $T->Insert(1,$i,"From: ");
    if ($PM['FromTag']!="")
      $T->Insert(1,$i,'[' . $PM['FromTag'] . '] ');
    $T->Insert(1,$i,$PM['FromNick'] . "<br/>");

    $T->Insert(1,$i,"To: ");
    if ($PM['ToTag']!="")
      $T->Insert(1,$i,'[' . $PM['ToTag'] . '] ');
    $T->Insert(1,$i,$PM['ToNick'] . '<br/>');

    $Time=DecodeTime($PM['Time']);
    $T->Insert(1,$i,"Time:<br/>$Time");
    

    $T->Insert(2,$i,"{$PM['Topic']}");
    $T->Insert(2,$i+1,decode($PM['Text']));
//    $T->SetClass(2,$i+1,"q");
    
    if ($PM['To']==$_SESSION['AID'])
	{
        $T->Insert(3,$i,new Link("post.php?pm={$PM['From']}","R"));
	$T->Insert(3,$i,new Br());
        $T->Insert(3,$i,new Link("post.php?pm={$PM['From']}&qpm={$PM['PMID']}","Q"));	
	$T->Insert(3,$i,new Br());
	}
    $T->Insert(3,$i,new Link("pm.php?v={$GET['v']}&e={$PM['PMID']}","X"));
    $T->Insert(3,$i,new Br());
    $T->Insert(3,$i,new Link("pm.php?v={$GET['v']}&d={$PM['PMID']}","XX"));

//    $T->SetClass(1,$i,($PM['From']==$_SESSION['AID'])?"pmout":"pmin");
    $T->aRowClass[$i]=($PM['From']==$_SESSION['AID'])?"pmout":"pmin";
    $T->SetClass(2,$i,"pmtopic forumdir");
    
    $T->SetClass(2,$i+1,"q");
    if ($PM['FromSig']!="" and ThemeUseBackgroundImage())
	{
	$C=$T->Get(2,$i+1);
	$C->sStyle="background-image : url(" . htmlentities($PM['FromSig']) . ")";
	}
    $T->Join(1,$i,1,2);
    $T->Join(3,$i,1,2);
    $i+=2;
}


$H->Insert($T);
include("part/mainsubmenu.php");
$H->Draw();
CloseSQL($sql);
?>
