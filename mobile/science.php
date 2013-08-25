<?php

chdir('..');

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
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
$H->AddStyle("default.css");


$sql=&OpenSQL($H);

ForceActivePlayer($sql, $H, "science.php");
ForceFrozen($sql, $H);

get("sc","integer");
if (exists($GET['sc']))
    player_set_science($sql,$_SESSION['PID'],$GET['sc']-1);


$player=player_get_sciences($sql,$_SESSION['PID']);
$Race=player_get_full_race($sql, $_SESSION['PID']);

$speed=player_get_sc_gain($sql, $_SESSION['PID']);
$realspeed['L']=$speed['L']*$Race['Science']/100;
$realspeed['C']=$speed['C']*$Race['Culture']/100;

$T=new Table();
$T->sClass='block';

include("mobile/part/mainmenu.php");

$T->SetCols(4);

$speed=player_get_sc_gain($sql, $_SESSION['PID']);
$realspeed['L']=$speed['L']*$Race['Science']/100;
$realspeed['C']=$speed['C']*$Race['Culture']/100;

$i=0;
foreach ($sciences as $science)
{
    ++$i;
    $T->Insert(1,$i,$science);
    $T->Insert(2,$i,"{$player[$science]}");
    $max=science_points_for_lvl($player[$science]+1);
//    $T->Insert(3,$i,floor($player[$science . "Remain"]) . "/" . $max);
    $rem=$max-$player[$science . "Remain"];


    if ($realspeed['L']>0)
    {    
    $hrem=floor($rem/$realspeed['L']);
    $drem=floor($hrem/24);
    $mrem=floor(($rem/$realspeed['L']-$hrem)*60);
    $srem=floor(3600*$rem/$realspeed['L']-3600*$hrem-60*$mrem);
    $hrem=$hrem-$drem*24;
    
    $hrem=sprintf("%02d",$hrem);
    $mrem=sprintf("%02d",$mrem);
    $srem=sprintf("%02d",$srem);
    if ($drem>0) $T->Insert(3,$i,"{$drem}d{$hrem}h");
    elseif ($hrem>0) $T->Insert(3,$i,"{$hrem}h{$mrem}m");
    elseif ($mrem>0) $T->Insert(3,$i,"{$mrem}m{$srem}s");
    elseif ($srem>0) $T->Insert(3,$i,"{$srem}s");
    else $T->Insert(3,$i,"Relog");
    }
    else $T->Insert(3,$i,"Inf");

    $T->Insert(4,$i,new Link("science.php?sc=$i","R"));
}

if ($player['TechSelected']>0):

++$i;
    if ($player['TechSelected']==0)
    {
        $T->Insert(1,$i,"None");
    }
    else
    {
	$Th=tech_get_info($sql, $player['TechSelected']);
        $T->Insert(1,$i,"" . $Th['Help']);
	$max=$Th['ScienceCost'];
	$rem=$player["TechRemain"];

        if ($realspeed['L']>0)
	{
	$hrem=floor($rem/$realspeed['L']);
        $drem=floor($hrem/24);
	$mrem=floor(($rem/$realspeed['L']-$hrem)*60);
        $srem=floor(3600*$rem/$realspeed['L']-3600*$hrem-60*$mrem);
        $hrem=$hrem-$drem*24;
    
        $hrem=sprintf("%02d",$hrem);
        $mrem=sprintf("%02d",$mrem);
        $srem=sprintf("%02d",$srem);
        if ($drem>0) $T->Insert(3,$i,"{$drem}d{$hrem}h");
        elseif ($hrem>0) $T->Insert(3,$i,"{$hrem}h{$mrem}m");
        elseif ($mrem>0) $T->Insert(3,$i,"{$mrem}m{$srem}s");
        elseif ($srem>0) $T->Insert(3,$i,"{$srem}s");
        else $T->Insert(3,$i,"Relog");
	}
	else
	$T->Insert(3,$i,"Inf");


	if ($player['TechDevelop']!=1)
	    $T->Insert(4,$i,"Halt");
    }

endif;

    ++$i;
    $T->Insert(1,$i,"Culture");
    $T->SetClass(1,$i,'sublegend');
    $T->Insert(2,$i,"{$player['CultureLvl']}");
    $max=culture_points_for_lvl($player['CultureLvl']+1);
    $rem=$max-$player["CultureRemain"];

    if ($realspeed['C']>0)
    {
    $hrem=floor($rem/$realspeed['C']);
    $drem=floor($hrem/24);
    $mrem=floor(($rem/$realspeed['C']-$hrem)*60);
    $srem=floor(3600*$rem/$realspeed['C']-3600*$hrem-60*$mrem);
    $hrem=$hrem-$drem*24;
    
    $hrem=sprintf("%02d",$hrem);
    $mrem=sprintf("%02d",$mrem);
    $srem=sprintf("%02d",$srem);
    if ($drem>0) $T->Insert(3,$i,"{$drem}d{$hrem}h");
    elseif ($hrem>0) $T->Insert(3,$i,"{$hrem}h{$mrem}m");
    elseif ($mrem>0) $T->Insert(3,$i,"{$mrem}m{$srem}s");
    elseif ($srem>0) $T->Insert(3,$i,"{$srem}s");
    else $T->Insert(3,$i,"Relog");
    }
    else
	$T->Insert(3,$i,"Inf");




$Cell=new Cell();
$Cell->Insert("+");
$T->Set(4,$player['SelectedScience']+1,$Cell);
$T->aRowClass[$player['SelectedScience']+1]='r';

$H->Insert($T);
$H->Draw();
CloseSQL($sql);
?>
