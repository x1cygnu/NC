<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/validator.php");
include_once("internal/building.php");
include_once("internal/progress.php");
include_once("internal/player.php");
include_once("internal/tech.php");
include_once("internal/hint.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");
$H->AddStyle("hint.css");

$H->sTitle="Northern Cross - Science";

$sql=&OpenSQL($H);

ForceActivePlayer($sql, $H, "science.php");
ForceFrozen($sql, $H);
ForceNoSitting($sql, $H, $_SESSION['PID']);
include("part/sitpid.php");

$H->AddStyle("science.css");

if ($_SESSION['PID']!=0)
  player_update_all($sql,$_SESSION['PID']);
if ($_SESSION['SitPID']!=0)
  player_update_all($sql,$_SESSION['SitPID']);

get("sc","integer");
if (PostControl(false) and exists($GET['sc']))
    player_set_science($sql,$MainPID,$GET['sc']-3);


$player=player_get_sciences($sql,$MainPID);
$Race=player_get_full_race($sql,$MainPID);

$speed=player_get_sc_gain($sql, $MainPID);
//settype($speed['C'],"integer");
//settype($speed['L'],"integer");
$realspeed['L']=$speed['L']*$Race['Science']/100;
$realspeed['C']=$speed['C']*$Race['Culture']/100;

$Now=EncodeNow();
$T=new Table();
$T->sClass='block';

$menuselected="Science";
include("part/mainmenu.php");

$T->SetCols(7);
$T->Insert(1,1,"Science ({$Race['Science']}%)");
$T->Join(1,1,7,1);
$T->aRowClass[1]='title';

$T->Insert(1,2,"Field");
$T->Insert(2,2,"Lvl");
$T->Insert(3,2,"Status");
$T->Insert(4,2,"Remain");
$T->Join(4,2,3,1);
$T->Insert(7,2,"Change");
$T->aRowClass[2]='legend';

$i=2;
$hintstr="hinted" . $_SESSION['Hint'];
foreach ($sciences as $science)
{
    ++$i;
    $DO=new Div();
    $DO->sClass=$hintstr;
    if ($_SESSION['Hint']>0)
    {
    $D=new Div();
    switch ($science)
    {
	case "Sensory":
	    $D->Insert("Increases your view and operation range (the green area on your map)");
	    $D->Br();
	    $D->Insert("If your sensory level is high enough, you may gain some vital information about your opponents");
	    break;
	case "Engineering":
	    $D->Insert('Decreases ship price, approx. 1.25% of initial price every level');
	    break;
	case "Warp":
	    $D->Insert("Increases speed of your ships");
	    break;
	case "Physics":
	    $D->Insert("Increases chances of winning a battle.");
	    break;
	case "Mathematics":
	    $D->Insert("Increases number of survivours after a won battle");
	    break;
	case "Urban":
	    $D->Insert("Increases your growth, and reduces toxin emmisions");
	    break;
    }
    $DO->Insert($D);
    }
    $DO->Insert($science);
    $T->Insert(1,$i,$DO);
    $T->SetClass(1,$i,'sublegend');
    $T->Insert(2,$i,"{$player[$science]}");
    $rem=sprintf("%.2f",science_points_for_lvl($player[$science]+1)-$player[$science . "Remain"]);
    Progress($T->Get(3,$i),250,$player[$science . "Remain"],science_points_for_lvl($player[$science]+1),false);
    $T->Insert(4,$i,"$rem");
    $T->SetClass(4,$i,"remain");


    if ($realspeed['L']>0)
    {    
    if ($player['SelectedScience']==$i-3)
    {
	$sciRemainPts=$rem;
	$sciRemainTime=ceil(($rem*3600)/$realspeed['L']);
    }
    $ETA=ceil((($rem*3600)/$realspeed['L'])+$Now);
    $T->Insert(6,$i,''.DecodeTimeShort($ETA));
    $hrem=floor($rem/$realspeed['L']);
    $drem=floor($hrem/24);
    $mrem=floor(($rem/$realspeed['L']-$hrem)*60);
    $srem=floor(3600*$rem/$realspeed['L']-3600*$hrem-60*$mrem);
    $hrem=$hrem-$drem*24;
    
    $hrem=sprintf("%02d",$hrem);
    $mrem=sprintf("%02d",$mrem);
    $srem=sprintf("%02d",$srem);
    if ($drem>0) $T->Insert(5,$i,"{$drem}d{$hrem}h");
    elseif ($hrem>0) $T->Insert(5,$i,"{$hrem}:{$mrem}");
    elseif ($mrem>0) $T->Insert(5,$i,"{$mrem}.{$srem}s");
    elseif ($srem>0) $T->Insert(5,$i,"{$srem}s");
    else $T->Insert(5,$i,"Relog");
    }
    else { 
      $T->Insert(5,$i,"Infinity");
      $T->Insert(6,$i,"Never");
    }
    $T->SetClass(5,$i,"remain");
    $T->Insert(7,$i,new Link("science.php?sc=$i&orderid=".($_SESSION['PostCode']+1) . $sitAddition,"R"));
}

if ($player['TechSelected']>0):

++$i;
$T->Insert(1,$i,"Technology");
$T->Join(1,$i,7,1);
$T->aRowClass[$i]='title';

    ++$i;
    $techRow=$i;
    if ($player['TechSelected']==0)
    {
        $T->Insert(1,$i,"None selected");
	$T->Join(1,$i,7,1);
    }
    else
    {
	$Th=tech_get_info($sql, $player['TechSelected']);
        $T->Insert(1,$i,"" . $Th['Name']);
        $T->SetClass(1,$i,'sublegend');
        $rem=sprintf("%.2f",$player["TechRemain"]);
	Progress($T->Get(3,$i),250,$Th['ScienceCost']-$player["TechRemain"],$Th['ScienceCost'],false);
        $T->Insert(4,$i,"$rem");

        if ($realspeed['L']>0)
	{
	if ($player['TechDevelop']==1)
	{
	    $sciRemainPts=$rem;
	    $sciRemainTime=ceil(($rem*3600)/$realspeed['L']);
	}
    $ETA=ceil(($rem*3600)/$realspeed['L']+$Now);
    $T->Insert(6,$i,''.DecodeTimeShort($ETA));
	$hrem=floor($rem/$realspeed['L']);
        $drem=floor($hrem/24);
	$mrem=floor(($rem/$realspeed['L']-$hrem)*60);
        $srem=floor(3600*$rem/$realspeed['L']-3600*$hrem-60*$mrem);
        $hrem=$hrem-$drem*24;
    
        $hrem=sprintf("%02d",$hrem);
        $mrem=sprintf("%02d",$mrem);
        $srem=sprintf("%02d",$srem);
        if ($drem>0) $T->Insert(5,$i,"{$drem}d{$hrem}h");
        elseif ($hrem>0) $T->Insert(5,$i,"{$hrem}:{$mrem}");
        elseif ($mrem>0) $T->Insert(5,$i,"{$mrem}.{$srem}s");
        elseif ($srem>0) $T->Insert(5,$i,"{$srem}s");
        else $T->Insert(5,$i,"Relog");
	}
    else { 
      $T->Insert(5,$i,"Infinity");
      $T->Insert(6,$i,"Never");
    }

	if ($player['TechDevelop']==1)
	{
	    $T->Insert(7,$i,sprintf("+%.1f/h",$realspeed['L']));
    	    $T->aRowClass[$i]='selectedscience';
	}
	else
	    $T->Insert(7,$i,"Halted");
        $T->Join(1,$i,2,1);
    }

endif;

++$i;
$T->Insert(1,$i,"Culture ({$Race['Culture']}%)");
$T->Join(1,$i,7,1);
$T->aRowClass[$i]='title';

    ++$i;
    $T->Insert(1,$i,"Culture");
    $T->SetClass(1,$i,'sublegend');
    $T->Insert(2,$i,"{$player['CultureLvl']}");
    $rem=sprintf("%.2f",culture_points_for_lvl($player['CultureLvl']+1)-$player["CultureRemain"]);
    Progress($T->Get(3,$i),250,$player["CultureRemain"],culture_points_for_lvl($player['CultureLvl']+1),false);
    $T->Insert(4,$i,"$rem");
    $T->Get(4,$i)->sId='culRemainPtsBox';

    if ($realspeed['C']>0)
    {
    $culRemainPts=$rem;
    $culRemainTime=ceil(($rem*3600)/$realspeed['C']);
    $ETA=$culRemainTime+$Now;
    $T->Insert(6,$i,''.DecodeTimeShort($ETA));
    $hrem=floor($rem/$realspeed['C']);
    $drem=floor($hrem/24);
    $mrem=floor(($rem/$realspeed['C']-$hrem)*60);
    $srem=floor(3600*$rem/$realspeed['C']-3600*$hrem-60*$mrem);
    $hrem=$hrem-$drem*24;
    
    $hrem=sprintf("%02d",$hrem);
    $mrem=sprintf("%02d",$mrem);
    $srem=sprintf("%02d",$srem);
    if ($drem>0) $T->Insert(5,$i,"{$drem}d{$hrem}h");
    elseif ($hrem>0) $T->Insert(5,$i,"{$hrem}:{$mrem}");
    elseif ($mrem>0) $T->Insert(5,$i,"{$mrem}.{$srem}s");
    elseif ($srem>0) $T->Insert(5,$i,"{$srem}s");
    else $T->Insert(5,$i,"Relog");
    }
    else {
      $T->Insert(5,$i,"Infinity");
      $T->Insert(6,$i,"Never");
    }

    $T->Get(5,$i)->sId='culRemainTimeBox';

    $T->Insert(7,$i,sprintf("+%.1f/h",$realspeed['C']));
    $T->aRowClass[$i]='selectedscience';



$selectedRow=$player['SelectedScience']+3;
$T->aRowClass[$selectedRow]='selectedscience';
$Cell=new Cell();
if ($player['TechDevelop']==1)
{
    $Cell->Insert("+0/h");
    $T->Get(4,$techRow)->sId='sciRemainPtsBox';
    $T->Get(5,$techRow)->sId='sciRemainTimeBox';
    $T->aRowClass[$selectedRow]='selectedstop';
    }
else
{
    $Cell->Insert(sprintf("+%.1f/h",$realspeed['L']));
    $T->Get(4,$selectedRow)->sId='sciRemainPtsBox';
    $T->Get(5,$selectedRow)->sId='sciRemainTimeBox';    
    $T->aRowClass[$techRow]='selectedstop';
}
$T->Set(7,$selectedRow,$Cell);

$H->Script(
    (isset($sciRemainTime)?
    "var sciRemainPts=$sciRemainPts;var sciRemainTime=$sciRemainTime;":"")
    .
    (isset($culRemainTime)?
    "var culRemainPts=$culRemainPts;var culRemainTime=$culRemainTime;":"")
);
$H->AddJavascriptFile("js/common.js");
$H->AddJavascriptFile("js/science.js");
$H->onLoad("runTimers()");
$H->Insert($T);
include("part/mainsubmenu.php");
$H->Draw();
CloseSQL($sql);
?>
