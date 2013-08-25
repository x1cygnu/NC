<?php
chdir('..');

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");
include_once("internal/pm.php");
include_once("internal/account.php");
session_start();


$H = new HTML();
$H->AddStyle("default.css");


$sql=&OpenSQL($H);

ForcePlayer($sql, $H, "pm.php");

$H->AddStyle("news.css");


get("v","string");


get("ee","integer");
if ($GET['ee']>0)
    pm_user_remove($sql, $GET['ee'], $_SESSION['AID']);

get("dd","integer");
if ($GET['dd']>0)
{
    pm_user_remove_several($sql, $GET['dd'], $_SESSION['AID'], $GET['v']);
    }

post("post","string");
if ($POST['post']=="Send")
{
    post("to","string");
    post("topic","string");
    post("mess","string");
    if (!double_post($POST['to'] . $POST['topic'] . $POST['mess']))
    {
    $id=account_get_id($sql,$POST['to']);
    if (!exists($id))
	$H->Insert(new Error("Unknown addressat"));
    elseif (strlen($POST['topic'])<1)
	$H->Insert(new Error("Topic not specified"));
    elseif (strlen($POST['mess'])<1)
	$H->Insert(new Error("Message body missing"));
    else
    {
	pm_send($sql, $_SESSION['AID'], $id, $POST['topic'], $POST['mess']);
	$H->Insert(new Info("Message has been sent"));
	}
    }
    else
    $H->Insert(new Error("Double post"));
    $GET['v']="out";
}

$menuselected="PM";
include("mobile/part/mainmenu.php");

$T=new Table();
$T->Insert(1,1,"Northern Cross Mail");
$T->SetClass(1,1,"t");
$T->Insert(1,2,new Link("pm.php?v=all","All"));
$T->Insert(2,2,new Link("pm.php?v=in","Inbox"));
$T->Insert(3,2,new Link("pm.php?v=out","Outbox"));
$T->Insert(4,2,new Link("post.php?pm=0","Compose"));
$T->Join(1,1,4,1);

$H->Insert($T);
get("e","integer");
if ($GET['e']>0)
    $H->Insert(new Link("pm.php?v={$GET['v']}&ee={$GET['e']}","Confirm deletion"));

get("d","integer");
if ($GET['d']>0)
    $H->Insert(new Link("pm.php?v={$GET['v']}&dd={$GET['d']}","Confirm deletion"));

$PMs=array();

if ($GET['v']=='all') $PMs=pm_get_all($sql,$_SESSION['AID']);
elseif ($GET['v']=='in' or $GET['v']=='') $PMs=pm_get_in($sql,$_SESSION['AID']);
elseif ($GET['v']=='out') $PMs=pm_get_out($sql,$_SESSION['AID']);


$i=1;
$T=new Table();

foreach ($PMs as $PM)
{
    $Time=DecodeTime($PM['Time']);
    $T->Insert(2,$i,"$Time");
    $T->Insert(1,$i,"<b>{$PM['FromNick']}</b>-><b>{$PM['ToNick']}</b>");
    

    $T->Insert(1,$i+1,"{$PM['Topic']}");
    $T->Insert(1,$i+2,decode($PM['Text']));
    $T->SetClass(1,$i+2,"nrm");
    
    if ($PM['To']==$_SESSION['AID'])
	{
        $T->Insert(2,$i+1,new Link("post.php?pm={$PM['From']}","R"));
        $T->Insert(2,$i+1,new Link("post.php?pm={$PM['From']}&qpm={$PM['PMID']}","Q"));	
	}
    $T->Insert(2,$i+1,new Link("pm.php?v={$GET['v']}&e={$PM['PMID']}","X"));
    $T->Insert(2,$i+1,new Link("pm.php?v={$GET['v']}&d={$PM['PMID']}","#"));

    $T->Join(1,$i+2,2,1);
    $i+=3;
}


$H->Insert($T);
include("part/mainsubmenu.php");
$H->Draw();
CloseSQL($sql);
?>
