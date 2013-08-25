<?php

chdir('..');
include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");

include_once("internal/player.php");
include_once("internal/fleet.php");
session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->AddStyle("default.css");


$sql=&OpenSQL($H);

ForceActivePlayer($sql, $H, "tt.php");


ForceFrozen($sql, $H);


include("mobile/part/mainmenu.php");
include("mobile/part/calcmenu.php");

$Lst=starsystem_bio($sql, $_SESSION['PID'], true);
$Sel = new Select;
foreach ($Lst as $Star)
    $Sel->AddOption($Star['SID'],($Star['YPC']>0?"*":"") . $Star['Name'] . " (" . $Star['X'] . "/" . $Star['Y'] . ")");
$Home=player_get_home($sql,$_SESSION['PID']);
$Rcs=$sql->query("SELECT Speed FROM NC_Player WHERE PID={$_SESSION['PID']}");
$Spd=$Rcs[0]['Speed'];
$Wrp=player_get_science($sql, $_SESSION['PID'], "Warp");

get("wrp","integer");
if ($GET['wrp']>0)
    $Wrp=$GET['wrp'];
get("spd","integer");
if ($GET['spd']>0)
    $Spd=$GET['spd'];
get("FromS","integer");
get("ToS","integer");
get("FromO","integer");
get("ToO","integer");


$SpdMod=new Select();
for ($i=-4; $i<=4; ++$i)
    {
    $SpdMod->AddOption(sprintf("%d",$i),sprintf("%+d",$i));
    }
$SpdMod->sDefault=$Spd;
$SpdMod->sName="spd";
    
$T = new Table();
$T->iCols=4;
$T->Insert(1,1,"Speed");
$T->Insert(2,1,$SpdMod);
$T->Insert(1,2,"Warp");
$T->Insert(2,2,new Input("text","wrp",$Wrp,"sh"));
$T->Insert(1,3,"From");
    $Sel->sName="FromS";
    if ($GET['FromS']>0)
	$Sel->sDefault=$GET['FromS'];
    else
	$Sel->sDefault=$Home;
    $T->Insert(2,3,$Sel);
    $T->Insert(2,4,new Input("text","FromO",$GET['FromO'],"text number"));
$T->Insert(1,5,"To");
    $Sel->sName="ToS";
    if ($GET['ToS']>0)
	$Sel->sDefault=$GET['ToS'];
    else
	$Sel->sDefault=$Home;
    $T->Insert(2,5,$Sel);
    $T->Insert(2,6,new Input("text","ToO",$GET['ToO'],"text number"));
$T->Insert(2,7,new Input("submit","","Test flight","smbutton"));
$F=new Form("tt.php",false);
$F->Insert($T);
$H->Insert($F);

if ($GET['FromS']>0 and $GET['ToS']>0 and $GET['FromO']>0 and $GET['ToO']>0 and $GET['FromO']<=13 and $GET['ToO']<=13)
{
    $From=starsystem_get_coords($sql,$GET['FromS']);
    $To=starsystem_get_coords($sql,$GET['ToS']);
    $From['Ring']=$GET['FromO'];
    $To['Ring']=$GET['ToO'];
    if ($From>0 and $To>0)
    {
	$details=array();
	$TT=fleet_time($From,$To,$GET['spd'],$GET['wrp'],$details);
	$Result=new Table();
	$Result->sClass='block';

	$Result->Insert(1,1,"TT");
	$Result->SetClass(1,1,"legend");
	$Result->Insert(2,1,time_period($TT));

	$Result->Insert(1,2,"STL");
	$Result->SetClass(1,2,"legend");
	$STLprc=round($details['STL']*100/$details['TT']);
	$Result->Insert(2,2,"$STLprc%");

	$FTLprc=100-$STLprc;
	$Result->Insert(1,3,"FTL");
	$Result->SetClass(1,3,"legend");
	$Result->Insert(2,3,"$FTLprc%");

	$Result->Insert(1,4,"ETA");
	$Result->SetClass(1,4,"legend");
	$Result->Insert(2,4,DecodeTime(EncodeNow()+$TT));
	$H->Insert($Result);
    }
}



$H->Draw();
CloseSQL($sql);
?>
