<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/validator.php");

include_once("internal/player.php");
include_once("internal/fleet.php");
session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");

$H->sTitle="Northern Cross - Travel time calculator";

$sql=&OpenSQL($H);

ForceActivePlayer($sql, $H, "tt.php");


ForceFrozen($sql, $H);


$menuselected="BC";
include("part/mainmenu.php");
$calcmenuselected="TT";
include("part/calcmenu.php");

$Lst=starsystem_bio($sql, $_SESSION['PID'], true);
$Sel = new Select;
foreach ($Lst as $Star)
    $Sel->AddOption($Star['SID'],($Star['YPC']>0?"*":"") . $Star['Name'] . " (" . $Star['X'] . "/" . $Star['Y'] . ")");
$Home=player_get_home($sql,$_SESSION['PID']);
$Rcs=$sql->query("SELECT Speed FROM NC_Player WHERE PID={$_SESSION['PID']}");
$Spd=$Rcs[0]['Speed'];
$Wrp=player_get_science($sql, $_SESSION['PID'], "Warp");

get("wrp","integer");
if (isset($GET['wrp']))
    $Wrp=$GET['wrp'];
get("spd","integer");
if (isset($GET['spd']))
    $Spd=$GET['spd'];
get("FromS","integer");
get("ToS","integer");
get("FromO","integer");
get("ToO","integer");
get("USS","integer");
get("UAF","integer");
def($GET['USS'],2);
def($GET['UAF'],2);

get("fleettype","string");

$SpdMod=new Select();
for ($i=-4; $i<=4; ++$i)
    {
    $SpdMod->AddOption(sprintf("%d",$i),sprintf("%+d",$i));
    }
$SpdMod->sDefault=$Spd;
$SpdMod->sName="spd";
    
$T = new Table();
$T->iCols=4;
$T->Insert(1,1,"Travel Time calculator");
$T->aRowClass[1]='title';
$T->Join(1,1,4,1);
$T->Insert(1,2,"Speed modifier");
 $T->SetClass(1,2,'sublegend');
 $T->Join(1,2,2,1);
 $T->Insert(3,2,$SpdMod);
 $T->Join(3,2,2,1);
$T->Insert(1,3,"Warp science");
 $T->SetClass(1,3,'sublegend');
 $T->Join(1,3,2,1);
 $T->Insert(3,3,new Input("text","wrp","{$Wrp}","text number"));
 $T->Join(3,3,2,1);

$T->Insert(1,4,"Fleet complement");
 $T->Join(1,4,2,1);
$SelType = new Select;
$SelType->AddOption("Int","no Dreadnoughts");
$SelType->AddOption("Vpr","Vipers and Transporters only");
$SelType->AddOption("Drn","with Dreadnoughts");
$SelType->sName='fleettype';
$SelType->sDefault=$GET['fleettype'];
$T->Insert(3,4,$SelType);
 $T->Join(3,4,2,1);
$T->SetClass(1,4,'sublegend');

$SelFld=new Select;
$SelFld->AddOption("0","No");
$SelFld->AddOption("1","Yes");
$SelFld->AddOption("2","If available");
$SelFld->sName='USS';
$SelFld->sDefault=$GET['USS'];

$T->Insert(1,5,"Space Station");
$T->Join(1,5,2,1);
$T->SetClass(1,5,'sublegend');
$T->Insert(3,5,$SelFld);
$T->Join(3,5,2,1);

$SelFld->sName='UAF';
$SelFld->sDefault=$GET['UAF'];

$T->Insert(1,6,"Arrestor Field");
$T->Join(1,6,2,1);
$T->SetClass(1,6,'sublegend');
$T->Insert(3,6,$SelFld);
$T->Join(3,6,2,1);


$row=7;
$T->aRowClass[$row]='legend';
$T->Insert(1,$row,"From");
  $T->Join(1,$row,2,1);
  $T->Insert(1,$row+1,"Starsystem");
    $T->SetClass(1,$row+1,'sublegend');
    $Sel->sName="FromS";
    if ($GET['FromS']>0)
	$Sel->sDefault=$GET['FromS'];
    else
	$Sel->sDefault=$Home;
    $T->Insert(2,$row+1,$Sel);
  $T->Insert(1,$row+2,"Orbit");
    $T->SetClass(1,$row+2,'sublegend');
    $T->Insert(2,$row+2,new Input("text","FromO",$GET['FromO'],"text number"));
$T->Insert(3,$row,"To");
  $T->Join(3,$row,2,1);
  $T->Insert(3,$row+1,"Starsystem");
    $T->SetClass(3,$row+1,'sublegend');
    $Sel->sName="ToS";
    if ($GET['ToS']>0)
	$Sel->sDefault=$GET['ToS'];
    else
	$Sel->sDefault=$Home;
    $T->Insert(4,$row+1,$Sel);
  $T->Insert(3,$row+2,"Orbit");
    $T->SetClass(3,$row+2,'sublegend');
    $T->Insert(4,$row+2,new Input("text","ToO",$GET['ToO'],"text number"));
$T->Insert(1,$row+3,new Input("submit","","Test flight","smbutton"));
$T->Join(1,$row+3,4,1);
$T->aRowClass[$row+3]='legend';
$T->sClass='block';
$F=new Form("tt.php",false);
$F->Insert($T);
$H->Insert($F);

if ($GET['FromS']>0 and $GET['ToS']>0 and $GET['FromO']>=0 and $GET['ToO']>=0 and $GET['FromO']<=19 and $GET['ToO']<=19)
{
    $From=starsystem_get_coords($sql,$GET['FromS']);
    $To=starsystem_get_coords($sql,$GET['ToS']);
    $From['Ring']=$GET['FromO'];
    $To['Ring']=$GET['ToO'];
//    if ($From>0 and $To>0)
    {
	$details=array();
	
	$TT=fleet_time($sql, $_SESSION['PID'], $From,$To,$Spd,$Wrp,get_speed($GET['fleettype']),$details,$GET['USS'],$GET['UAF']);
	if ($TT>0)
	{
	$Result=new Table();
	$Result->sClass='block';

	$Result->Insert(1,1,"Travel time");
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

	$Result->Insert(1,4,"ETA if launched now");
	$Result->SetClass(1,4,"legend");
	$Result->Insert(2,4,DecodeTime(EncodeNow()+$TT));
	$H->Insert($Result);
	}
	else
	$H->Insert(new Error("Start or destination point does not exist"));
    }
}


$H->Draw();
CloseSQL($sql);
?>
