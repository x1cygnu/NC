<?php
chdir('..');

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");
include_once("internal/player.php");
include_once("internal/awards.php");
include_once("internal/hint.php");

session_start();


global $GET;
$GET=array();

get("id","integer");

$H = new HTML();
$H->AddStyle("default.css");


$sql=&OpenSQL($H);

ForceActivePlayer($sql, $H, "mobile/pinfo.php");

ForceFrozen($sql, $H);

$H->AddStyle("race.css");
$H->AddStyle("hint.css");

include("mobile/part/mainmenu.php");

if (!exists($GET['id']))
{
    $H->Insert(new Error("Player not specified"));
    $H->Draw();
    die;
}



$bio=player_get_science($sql,$_SESSION['PID'],"Sensory");
$player=player_get_all($sql,$GET['id']);

$T=new Table();
$T->SetCols(2);
$T->sClass='block';
$T->Insert(1,1,"<b>" . $player['Nick'] . "</b>");
$T->aRowClass[1]='t';
$T->Join(1,1,2,1);
$T->Insert(1,2,new Link("post.php?pm={$player['AID']}","PM"));
$T->Join(1,2,2,1);
$T->Insert(1,3,"Summary");
$T->aRowClass[3]='t';
$T->Join(1,3,2,1);
$T->Insert(1,4,"AID");
$T->Insert(2,4,$player['AID']);
$T->Insert(1,5,"PID");
$T->Insert(2,5,$player['PID']);
$pos=starsystem_get_coords($sql,$player['HomeSID']);
$T->Insert(1,6,"Home");
$T->Insert(2,6,"{$pos['X']}/{$pos['Y']}");

$SL=max($player['Sensory'],$player['Engineering'],$player['Warp'],$player['Mathematics'],$player['Physics'],$player['Urban']);
$T->Insert(1,7,"SL");
$T->Insert(2,7,"{$SL}");
$T->Insert(1,8,"Cul");
$T->Insert(2,8,$player['CultureLvl']);

$prc=floor((1-$player['PLRemain']/pl_points_for_lvl($player['PL']+1))*100);
$T->Insert(1,9,"PL");
$T->Insert(2,9,"{$player['PL']}-{$prc}%");
$prc=floor((1-$player['VLRemain']/vl_points_for_lvl($player['VL']+1))*100);
$T->Insert(1,10,"VL");
$T->Insert(2,10,"{$player['VL']}-{$prc}%");
$T->Insert(1,11,"Rank");
$T->Insert(1,12,"Pts");
if ($player['Rank']<100000)
{
    $T->Insert(2,11,"#{$player['Rank']}");
    $T->Insert(2,12,"#{$player['Points']}");
}

if (($bio>=$player['Sensory']+6 and starsystem_in_bio_range($sql,$player['HomeSID'],$_SESSION['PID'])) or $player['PID']==$_SESSION['PID'])
{
    $T->Insert(1,13,"IR");
    $T->aRowClass[13]='t';
    $T->Join(1,13,2,1);
    foreach ($sciences as $u => $science)
        {
	$T->Insert(1,$u+14,"" . $science);
	$T->Insert(2,14+$u,"{$player[$science]}");
	}

    $T->Insert(1,20,"Race");
    $T->aRowClass[20]='t';
    $T->Join(1,20,2,1);
    $T->Insert(1,21,"Gr"); $T->Insert(2,21,"{$player['Growth']}");
    $T->Insert(1,22,"Sci"); $T->Insert(2,22,"{$player['Science']}");
    $T->Insert(1,23,"Cul"); $T->Insert(2,23,"{$player['Culture']}");
    $T->Insert(1,24,"Prod"); $T->Insert(2,24,"{$player['Production']}");
    $T->Insert(1,25,"Spd"); $T->Insert(2,25,"{$player['Speed']}");
    $T->Insert(1,26,"Att"); $T->Insert(2,26,"{$player['Attack']}");
    $T->Insert(1,27,"Def"); $T->Insert(2,27,"{$player['Defence']}");
}



$H->Insert($T);
$H->Draw();
CloseSQL($sql);
?>
