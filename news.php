<?php


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

get("e","integer");
get("clear","integer");

$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");

$H->sTitle="Northern Cross - News";

$sql=&OpenSQL($H);

ForceActivePlayer($sql, $H, "news.php");

ForceFrozen($sql, $H);

ForceNoSitting($sql, $H, $_SESSION['PID']);

$H->AddStyle("news.css");

include("part/sitpid.php");

if (exists($GET['e']))
{
    news_delete($sql,$MainPID,$GET['e']);
}


$menuselected="News";
include("part/mainmenu.php");


if (exists($GET['clear']))
{
    if ($GET['clear']==1)
    {
	$H->Insert(new Link("news.php?clear=2" . $sitAddition,"Click here to confirm removal of all old messages"));
    }
    elseif ($GET['clear']==2)
    {
	news_clear_time($sql,$MainPID);
	$H->Insert(new Info("Old messages have been removed"));
    }
}

$list=news_list($sql,$MainPID,$GET['f']);
$c=news_count($sql, $MainPID);

if (account_is_multi($sql, $_SESSION['AID']))
    $H->Insert(new Error("You are marked multi.<br>Contact <a href=\"post.php?pm=".MULTIADMINAID."\">MultiAdmin</a> as soon as possible"));

$T=new Table();
$s=sprintf("Time (GMT%+d)",$_SESSION['TimeZone']);
$T->Insert(1,1,$s);
$T->Insert(2,1,"Message");
$T->SetClass(2,1,"newstext");
$T->Insert(3,1,"Func");
$T->aRowClass[1]="legend";
$T->sClass='block';
$i=1;
$Race=player_get_full_race($sql, $MainPID);
$PScience=player_get_sciences($sql, $MainPID);
$Now=EncodeNow();
foreach ($list as $entry)
{
    ++$i;
    $T->Insert(1,$i,DecodeTime($entry['Time']));
    $T->SetClass(1,$i,"newstype" . $entry['Type']);
    $T->Insert(2,$i,$entry['Text']);
    $T->SetClass(2,$i,"normtext");
    if ($entry['Time']>$_SESSION['LastLogin'])
	$T->SetClass(2,$i,"normtext newsnew");
    if ($entry['Time']<$Now or $entry['Type']!=2)
    {
        $T->Insert(3,$i,new Link("news.php?e={$entry['NID']}&f={$GET['f']}xx" . $sitAddition,"X"));
	$T->Insert(3,$i,new Br());
	}
    if ($entry['IncPID']>0)
    {
	$T->Insert(3,$i,new Link("pinfo.php?id={$entry['IncPID']}","Ifo"));
	$T->Insert(3,$i,new Br());
	$TAID=account_get_id_from_pid($sql, $entry['IncPID']);
	$T->Insert(3,$i,new Link("post.php?pm=$TAID" . $sitAddition,"Msg"));
	$T->Insert(3,$i,new Br());
    }
    if ($entry['IncVpr']>0 or $entry['IncInt']>0 or $entry['IncFr']>0 or $entry['IncBs']>0 or $entry['IncDrn']>0)
    {
//        echo "<pre>";
//	echo "Debug:\n";
//	print_r($entry);
//	echo "</pre>";
/*	$AttSensory=player_get_science($sql, $entry['IncPID'], 'sensory');
	if ($AttSensory<=$PScience['Sensory']-6)
	    {
	    $AttRace=player_get_full_race($sql, $entry['IncPID']);
	    $AttScience=player_get_sciences($sql, $entry['IncPID']);
	    }*/
	$T->Insert(3,$i,new Link("battlecalculator.php?AVpr={$entry['IncVpr']}&AInt={$entry['IncInt']}&AFr={$entry['IncFr']}&ABs={$entry['IncBs']}&ADrn={$entry['IncDrn']}&DPhy={$PScience['Physics']}&DMat={$PScience['Mathematics']}&DAtt={$Race['Attack']}&DDef={$Race['Defence']}&APhy={$AttScience['Physics']}&AMat={$AttScience['Mathematics']}&AAtt={$AttRace['Attack']}&ADef={$AttRace['Defence']}","BC"));
	$T->Insert(3,$i,new Br());
    }
}
++$i;
if ($GET['f']>0 or ($GET['f']+8<=$c))
{
if ($GET['f']>0)
{
    $prev=max(0,$GET['f']-8);
    $T->Insert(2,$i,new Link("news.php?f=$prev" . $sitAddition,"<< Previous"));
}
$T->Insert(2,$i," ");
if ($GET['f']+8<=$c)
{
    $next=max(0,$GET['f']+8);
    $T->Insert(2,$i,new Link("news.php?f=$next" . $sitAddition,"Next >>"));
}
}
++$i;
$T->Insert(1,$i,new Link("news.php?clear=1" . $sitAddition,"Remove old messages"));
$T->Join(1,$i,3,1);
$T->aRowClass[$i]='sublegend';
$H->Insert($T);
include("part/mainsubmenu.php");


$H->Draw();
CloseSQL($sql);
?>
