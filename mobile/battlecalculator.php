<?php

chdir('..');
include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");

include_once("internal/fleet.php");

session_start();



global $GET;
$GET=array();

get("DInt","integer"); get("AInt","integer");
get("DFr","integer"); get("AFr","integer");
get("DBs","integer"); get("ABs","integer");
get("DAtt","integer"); get("AAtt","integer");
get("DDef","integer"); get("ADef","integer");
get("DPhy","integer"); get("APhy","integer");
get("DMat","integer"); get("AMat","integer");
get("DSB","float");
get("DPL","integer");
get("APL","integer");

if ($GET['AAtt']==0) $GET['AAtt']=100;
if ($GET['DAtt']==0) $GET['DAtt']=100;
if ($GET['ADef']==0) $GET['ADef']=100;
if ($GET['DDef']==0) $GET['DDef']=100;

$defender['Int']=$GET['DInt'];
$defender['Fr']=$GET['DFr'];
$defender['Bs']=$GET['DBs'];
$defender['Starbase']=$GET['DSB'];
$attacker['Int']=$GET['AInt'];
$attacker['Fr']=$GET['AFr'];
$attacker['Bs']=$GET['ABs'];

$battle=false;
if (($defender['Int']>0 or $defender['Fr']>0 or $defender['Bs']>0 or $defender['Starbase']>0)
    and
    ($attacker['Int']>0 or $attacker['Fr']>0 or $attacker['Bs']>0))
{	//there is a battle!
    fleet_battle(&$attacker,&$defender,
	$GET['AAtt'],$GET['ADef'],$GET['APhy'],$GET['AMat'],$GET['APL'],
	$GET['DAtt'],$GET['DDef'],$GET['DPhy'],$GET['DMat'],$GET['DPL']);
    $battle=true;
}

$H = new HTML();
$H->AddStyle("default.css");


if (exists($_SESSION['AID']))
    {
    include("mobile/part/mainmenu.php");
    include("mobile/part/calcmenu.php");
    }

$F = new Form("battlecalculator.php",false);
$T = new Table();
$T->Insert(2,1,"Defender"); $T->Insert(3,1,"Attacker");
$T->aRowClass[1]='title';

$T->Insert(1,2,"Int");
$T->Insert(1,3,"Fr");
$T->Insert(1,4,"Bs");
$T->Insert(1,5,"SB");
$T->Insert(2,2,new Input("text","DInt",$GET['DInt'],"sh")); $T->Insert(3,2,new Input("text","AInt",$GET['AInt'],"sh"));
$T->Insert(2,3,new Input("text","DFr",$GET['DFr'],"sh")); $T->Insert(3,3,new Input("text","AFr",$GET['AFr'],"sh"));
$T->Insert(2,4,new Input("text","DBs",$GET['DBs'],"sh")); $T->Insert(3,4,new Input("text","ABs",$GET['ABs'],"sh"));
$T->Insert(2,5,new Input("text","DSB",$GET['DSB'],"sh"));

$T->Insert(1,6,"AM");
$T->Insert(1,7,"DM");
$T->Insert(1,8,"Phy");
$T->Insert(1,9,"Mat");
$T->Insert(1,10,"PL");


$AttMod=new Select();
$DefMod=new Select();
for ($i=-4; $i<=4; ++$i)
    {
    $DefMod->AddOption(sprintf("%d",Defence($i)),sprintf("%+d",$i,Defence($i)));
    $AttMod->AddOption(sprintf("%d",Attack($i)),sprintf("%+d",$i,Attack($i)));    
    }
$DefMod->sDefault=$GET['DDef'];
$AttMod->sDefault=$GET['DAtt'];
$AttMod->sName="DAtt";
$DefMod->sName="DDef";
$T->Insert(2,6,$AttMod);
$T->Insert(2,7,$DefMod);
$DefMod->sDefault=$GET['ADef'];
$AttMod->sDefault=$GET['AAtt'];
$AttMod->sName="AAtt";
$DefMod->sName="ADef";
$T->Insert(3,6,$AttMod);
$T->Insert(3,7,$DefMod);
$T->Insert(2,8,new Input("text","DPhy",$GET['DPhy'],"sh")); $T->Insert(3,8,new Input("text","APhy",$GET['APhy'],"sh"));
$T->Insert(2,9,new Input("text","DMat",$GET['DMat'],"sh")); $T->Insert(3,9,new Input("text","AMat",$GET['AMat'],"sh"));
$T->Insert(2,10,new Input("text","DPL",$GET['DPL'],"sh")); $T->Insert(3,10,new Input("text","APL",$GET['APL'],"sh"));

$T->sClass='block';

if ($battle)
{
    
    $T->Insert(2,2,sprintf("%5.1f",$defender['Int'])); $T->Insert(3,2,sprintf("%5.1f",$attacker['Int']));
    $T->Insert(2,3,sprintf("%5.1f",$defender['Fr'])); $T->Insert(3,3,sprintf("%5.1f",$attacker['Fr']));
    $T->Insert(2,4,sprintf("%5.1f",$defender['Bs'])); $T->Insert(3,4,sprintf("%5.1f",$attacker['Bs']));
    $T->Insert(2,5,sprintf("%5.2f",$defender['Starbase']));
    $T->Insert(2,11,sprintf("%1.5f",$defender['Chance'])); $T->Insert(3,11,sprintf("%1.5f",$attacker['Chance']));

    $T->Insert(1,11,"Chn");
    $T->Insert(1,12,"OV");
    $T->Insert(1,13,"TV");
    $T->Insert(1,14,"XP");

    $T->Insert(2,12,sprintf("%5.1f",$defender['AV'])); $T->Insert(3,12,sprintf("%5.1f",$attacker['AV']));
    $T->Insert(2,13,sprintf("%5.1f",$defender['DV'])); $T->Insert(3,13,sprintf("%5.1f",$attacker['DV']));
    $T->Insert(2,14,sprintf("%5.1f",$defender['AV']+$defender['DV'])); $T->Insert(3,14,sprintf("%5.1f",$attacker['AV']+$attacker['DV']));
    
}

$F->Insert(new Input("submit","Calculate","Calculate","smbutton"));
$F->Insert($T);
$F->Insert(new Input("submit","Calculate","Calculate","smbutton"));
$H->Insert($F);

$H->Draw();
?>
