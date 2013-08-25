<?php

chdir('..');
include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");

include_once("internal/news.php");
include_once("internal/player.php");


session_start();

global $GET;
$GET=array();

get("f","integer");
def($GET['f'],0);
if ($GET['f']<0)
    $GET['f']=0;


$H = new HTML();
$H->AddStyle("default.css");

$sql=&OpenSQL($H);

ForceActivePlayer($sql, $H, "mobile/news.php");

ForceFrozen($sql, $H);

$menuselected="News";
include("mobile/part/mainmenu.php");


$list=news_list($sql,$_SESSION['PID'],$GET['f']);
$c=news_count($sql, $_SESSION['PID']);

if (player_is_multi($sql, $_SESSION['PID']))
    $H->Insert("<b>You are marked multi</b>");

$H->AddStyle("news.css");
$T=new Table();
$i=0;
$Race=player_get_full_race($sql, $_SESSION['PID']);
$PScience=player_get_sciences($sql, $_SESSION['PID']);
$Now=EncodeNow();
foreach ($list as $entry)
{
    $i+=2;
    $T->Insert(1,$i,"<b>" . DecodeTime($entry['Time']) . "</b>");
    $T->SetClass(1,$i,"n" . $entry['Type']);
    $T->Insert(1,$i+1,$entry['Text']);
    if ($entry['Time']>$_SESSION['LastLogin'])
	$T->SetClass(1,$i+1,"nn nrm");
    else
	$T->SetClass(1,$i+1,"nrm");
    if ($entry['IncPID']>0)
    {
	$T->Insert(2,$i,new Link("pinfo.php?id={$entry['IncPID']}","Ifo"));
	$T->Insert(2,$i," ");
	$TAID=account_get_id_from_pid($sql, $entry['IncPID']);
	$T->Insert(2,$i,new Link("post.php?pm=$TAID","Msg"));
	$T->Insert(2,$i," ");
    }
    if ($entry['IncInt']>0 or $entry['IncFr']>0 or $entry['IncDrn']>0)
    {
	$AttSensory=player_get_science($sql, $entry['IncPID'], 'sensory');
	if ($AttSensory<=$PScience['Sensory']-6)
	    {
	    $AttRace=player_get_full_race($sql, $entry['IncPID']);
	    $AttScience=player_get_sciences($sql, $entry['IncPID']);
	    }
	$T->Insert(2,$i,new Link("battlecalculator.php?AInt={$entry['IncInt']}&AFr={$entry['IncFr']}&ADr={$entry['IncDrn']}&DPhy={$PScience['Physics']}&DMat={$PScience['Mathematics']}&DAtt={$Race['Attack']}&DDef={$Race['Defence']}&APhy={$AttScience['Physics']}&AMat={$AttScience['Mathematics']}&AAtt={$AttRace['Attack']}&ADef={$AttRace['Defence']}","BC"));
	$T->Insert(2,$i," ");
    }
    $T->Join(1,$i+1,2,1);
}
$i+=2;
if ($GET['f']>0 or ($GET['f']+8<=$c))
{
if ($GET['f']>0)
{
    $prev=max(0,$GET['f']-8);
    $T->Insert(1,$i,new Link("news.php?f=$prev","<<"));
}
if ($GET['f']+8<=$c)
{
    $next=max(0,$GET['f']+8);
    $T->Insert(2,$i,new Link("news.php?f=$next",">>"));
}
}
$H->Insert($T);


$H->Draw();
CloseSQL($sql);
?>