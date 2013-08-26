<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/validator.php");
include_once("internal/log.php");
include_once("internal/account.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");

$H->sTitle="Northern Cross - Multi tool";


$sql=&OpenSQL($H);

if (!$_SESSION['IsAdmin'])
{
    $H->Insert(new Error("Must be admin in order to see this page"));
    $H->Draw();
    die;
}

$menuselected="Multi";
include("part/mainmenu.php");
include("part/multi.php");

get("i","integer");
if (!isset($GET['i']))
    $GET['i']=1;

get("aid","integer");
get("eaid","integer");
get("ip","string");
get("fip","string");
$M=new Table();
$M->sClass='block';
$M->Insert(1,1,"Exception add");
$M->Insert(1,2,"AID");
$M->Insert(2,2,"IP (0 for any)");
$M->Join(1,1,2,1);
$M->aRowClass[1]='title';
$M->aRowClass[2]='legend';
$M->Insert(1,3,new Input("text","uz",$GET['aid'],"text number"));
$getip=ip_separate($GET['ip']);
$M->Insert(2,3,new Input("text","ip0",$getip[0],"text number"));
$M->Insert(2,3,".");
$M->Insert(2,3,new Input("text","ip1",$getip[1],"text number"));
$M->Insert(2,3,".");
$M->Insert(2,3,new Input("text","ip2",$getip[2],"text number"));
$M->Insert(2,3,".");
$M->Insert(2,3,new Input("text","ip3",$getip[3],"text number"));
$M->Insert(1,4,new Input("submit","add","add","smbutton"));
$M->Join(1,4,2,1);
$F=new Form("multiexceptions.php","true");
$F->Insert($M);
$H->Insert($F);

post("uz","integer");
post("ip0","integer");
post("ip1","integer");
post("ip2","integer");
post("ip3","integer");

if (isset($POST['uz']))
    multi_add_exception($sql, $POST['uz'],
	$POST['ip0'], $POST['ip1'], $POST['ip2'], $POST['ip3']);

if (isset($GET['eaid']))
    multi_delete_exception($sql, $GET['eaid'], $GET['ip']);

$MEsize=multi_get_exception_size($sql);
$MEs=multi_get_exception_list($sql, $GET['i']-1);

$T=new Table();
$T->Insert(1,1,"Multi exception list");
for ($u=1; $u<=$MEsize; $u=$u+100)
{
    $T->Insert(1,2,new Link("multiexceptions.php?i=$u","" . ceil($u/100)));
    $v=$u+50;
    $T->Insert(1,2,new Link("multiexceptions.php?i=$v","."));
}
$T->Insert(1,3,"Name");
$T->Insert(2,3,"ID");
$T->Insert(3,3,"IP");
$T->Insert(4,3,"Operations");
$T->aRowClass[1]='title';
$T->aRowClass[2]='legend';
$T->aRowClass[3]='legend';
$T->Join(1,1,4,1);
$T->Join(1,2,4,1);
$row=3;
foreach ($MEs as $ME)
{
    ++$row;
    $T->Insert(1,$row,"" . $ME['Nick']);
    $T->Insert(2,$row,"" . $ME['AID']);
    $IP=$ME['IP0'].'.'.$ME['IP1'].'.'.$ME['IP2'].'.'.$ME['IP3'];
    $T->Insert(3,$row,$IP);
    $T->Insert(4,$row,new Link("multiexceptions.php?eaid={$ME['AID']}&ip=$IP","X"));
}
$T->sClass='block';
$H->Insert($T);
include("part/mainsubmenu.php");
$H->Draw();
?>
