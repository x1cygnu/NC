<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/validator.php");

include_once("internal/building.php");
include_once("internal/graph.php");
include_once("internal/fleet.php");

session_start();

//include("part/mainmenu.php");

global $GET;
$GET=array();

get("DVpr","integer"); get("AVpr","integer");
get("DInt","integer"); get("AInt","integer");
get("DFr","integer"); get("AFr","integer");
get("DBs","integer"); get("ABs","integer");
get("DDrn","integer"); get("ADrn","integer");
get("DAtt","integer"); get("AAtt","integer");
get("DDef","integer"); get("ADef","integer");
get("DPhy","integer"); get("APhy","integer");
get("DMat","integer"); get("AMat","integer");
get("DSB","float");
get("DPL","integer");
get("APL","integer");
get("ptype","integer");

if ($GET['AAtt']==0) $GET['AAtt']=100;
if ($GET['DAtt']==0) $GET['DAtt']=100;
if ($GET['ADef']==0) $GET['ADef']=100;
if ($GET['DDef']==0) $GET['DDef']=100;

foreach ($fightships as $S) {
	$defender[$S]=$GET['D'.$S];
	$attacker[$S]=$GET['A'.$S];
}
$defender['Starbase']=$GET['DSB'];

$H = new HTML();
$H->AddJavascriptFile('js/showname.js');
$sql=&OpenSQL($H);
$ps=planet_get_types($sql);

$DWinResult=array();
$DLooseResult=array();
$AWinResult=array();
$ALooseResult=array();
$Result=array();

$numMissions=5;

$battle=false;
if (($defender['Vpr']>0 or $defender['Drn']>0 or $defender['Int']>0 or $defender['Fr']>0 or $defender['Bs']>0 or $defender['Starbase']>0)
    and
    ($attacker['Int']>0 or $attacker['Fr']>0 or $attacker['Bs']>0 or $attacker['Vpr']>0 or $attacker['Drn']>0))
{	//there is a battle!

	for ($i=1; $i<=$numMissions; ++$i) {
		$DWinResult[$i]=array();
		$DLooseResult[$i]=array();
		$AWinResult[$i]=array();
		$ALooseResult[$i]=array();
		$Result[$i]=array();
		fleet_battle(&$Result[$i], &$attacker,&$defender,$AWinResult[$i],$DWinResult[$i],$ALooseResult[$i],$DLooseResult[$i],
				$GET['AAtt'],$GET['ADef'],$GET['APhy'],$GET['AMat'],$GET['APL'],
				$GET['DAtt'],$GET['DDef'],$GET['DPhy'],$GET['DMat'],$GET['DPL'],$i,
				$ps[$GET['ptype']-1]['Attack'],$ps[$GET['ptype']-1]['Defense']);
	}
	$battle=true;
}

$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");
$H->AddStyle("hint.css");

$H->sTitle="Northern Cross - Battlecalculator";

if (exists($_SESSION['AID']))
    {
    $menuselected="BC";
    include("part/mainmenu.php");
    $calcmenuselected="BC";
    include("part/calcmenu.php");
    }

$F = new Form("battlecalculator.php",false);
$T = new Table();
$T->Insert(2,1,"Defender");
$T->Get(2,1)->sStyle="background : #229922; width : 150px";
$T->Insert(3,1,"Attacker");
$T->Get(3,1)->sStyle="background : #992222; width : 150px";
$T->aRowClass[1]='title';

$T->Insert(1,2,"Viper <b>(1/1)</b>");
$T->SetClass(1,2,"legend");
$T->Insert(1,3,"Interceptor <b>(2/2)</b>");
$T->SetClass(1,3,"legend");
$T->Insert(1,4,"Frigate <b>(12/16)</b>");
$T->SetClass(1,4,"legend");
$T->Insert(1,5,"Battleship <b>(48/37)</b>");
$T->SetClass(1,5,"legend");
$T->Insert(1,6,"Dreadnought <b>(158/156)</b>");
$T->SetClass(1,6,"legend");
$T->Insert(1,7,"Starbase");
$T->SetClass(1,7,"legend");
for ($row=2; $row<=7; ++$row) {
	$E=new Div();
	$E->sStyle='position : absolute';
	$T->Insert(2,$row,$E);
	$T->Insert(3,$row,$E);
}
$T->Insert(2,2,new Input("text","DVpr",$GET['DVpr'],"text number")); $T->Insert(3,2,new Input("text","AVpr",$GET['AVpr'],"text number"));
$T->Insert(2,3,new Input("text","DInt",$GET['DInt'],"text number")); $T->Insert(3,3,new Input("text","AInt",$GET['AInt'],"text number"));
$T->Insert(2,4,new Input("text","DFr",$GET['DFr'],"text number")); $T->Insert(3,4,new Input("text","AFr",$GET['AFr'],"text number"));
$T->Insert(2,5,new Input("text","DBs",$GET['DBs'],"text number")); $T->Insert(3,5,new Input("text","ABs",$GET['ABs'],"text number"));
$T->Insert(2,6,new Input("text","DDrn",$GET['DDrn'],"text number")); $T->Insert(3,6,new Input("text","ADrn",$GET['ADrn'],"text number"));
$T->Insert(2,7,new Input("text","DSB",$GET['DSB'],"text number"));

$T->Insert(1,8,"Attack modifier");
$T->SetClass(1,8,"legend");
$T->Insert(1,9,"Defence modifier");
$T->SetClass(1,9,"legend");
$T->Insert(1,10,"Physics (lvl)");
$T->SetClass(1,10,"legend");
$T->Insert(1,11,"Mathematics (lvl)");
$T->SetClass(1,11,"legend");
$T->Insert(1,12,"Player Level (PL)");
$T->SetClass(1,12,"legend");


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
$T->Insert(2,8,$AttMod);
$T->Insert(2,9,$DefMod);
$DefMod->sDefault=$GET['ADef'];
$AttMod->sDefault=$GET['AAtt'];
$AttMod->sName="AAtt";
$DefMod->sName="ADef";
$T->Insert(3,8,$AttMod);
$T->Insert(3,9,$DefMod);
$T->Insert(2,10,new Input("text","DPhy",$GET['DPhy'],"text number")); $T->Insert(3,10,new Input("text","APhy",$GET['APhy'],"text number"));
$T->Insert(2,11,new Input("text","DMat",$GET['DMat'],"text number")); $T->Insert(3,11,new Input("text","AMat",$GET['AMat'],"text number"));
$T->Insert(2,12,new Input("text","DPL",$GET['DPL'],"text number")); $T->Insert(3,12,new Input("text","APL",$GET['APL'],"text number"));
$T->Insert(1,13,"Planet type");
$T->SetClass(1,13,'legend');
$T->Join(2,13,2,1);
$T->sClass='block';

$PT=new Select();
$PT->sName='ptype';
foreach ($ps as $p) {
    $PT->AddOption($p['PTID'],$p['TypeName']);
}
$PT->sDefault=$GET['ptype'];
$T->Insert(2,13,$PT);

if ($battle)
{
	$fmt="%5.1f/%5.1f";


	$i=1;
	$fmt="%1.3f%%";

function LosesDiv($i,$win,$loose) {
	$E = new Div();
	$E->sName='loses'.$i;
	$E->sStyle='display : none; width : 150px; height : 21px; background : #333377;';
	$E->Insert(sprintf("%5.1f/%5.1f",$win,$loose));
	return $E;
}

	for($i=1; $i<=$numMissions; ++$i) {
		global $fightships;
		foreach ($fightships as $idx => $ship) {
			$T->Get(2,2+$idx)->aLines[0]->Insert(LosesDiv($i,$DWinResult[$i][$ship],$DLooseResult[$i][$ship]));
			$T->Get(3,2+$idx)->aLines[0]->Insert(LosesDiv($i,$AWinResult[$i][$ship],$ALooseResult[$i][$ship]));
		}
		$T->Get(2,7)->aLines[0]->Insert(LosesDiv($i,$DWinResult[$i]['Starbase'],0));

		$T->Insert(1,13+$i,missionName($i));
		$T->SetClass(1,13+$i,"legend");

		$chance[0]['value']=$Result[$i]['AE'];
		$chance[0]['color']='008800';
		$chance[0]['name']='defender flawless win='.sprintf($fmt,$Result[$i]['AE']*100);
		$chance[1]['value']=$Result[$i]['AU'];
		$chance[1]['color']='44aa00';
		$chance[1]['name']='defender win='.sprintf($fmt,$Result[$i]['AU']*100);
		$chance[2]['value']=$Result[$i]['R'];
		$chance[2]['color']='996699';
		$chance[2]['name']='raid='.sprintf($fmt,$Result[$i]['R']*100);
		$chance[3]['value']=$Result[$i]['='];
		$chance[3]['color']='999900';
		$chance[3]['name']='draw='.sprintf($fmt,$Result[$i]['=']*100);
		$chance[4]['value']=$Result[$i]['DU'];
		$chance[4]['color']='aa4400';
		$chance[4]['name']='attacker win='.sprintf($fmt,$Result[$i]['DU']*100);
		$chance[5]['value']=$Result[$i]['DE'];
		$chance[5]['color']='880000';
		$chance[5]['name']='attacker crushing win='.sprintf($fmt,$Result[$i]['DE']*100);

		$D=graphResults($chance, 302, 17);

		$E = new Div();
		$E->sStyle="position : absolute; left : 1px; top : 2px; z-index : 1; font-size : 7pt";
		$E->Insert(sprintf("%d%%/%d%%",$DWinResult[$i]['Killed'],$DLooseResult[$i]['Killed']));
		$D->aLines[0]->Insert($E);

		$E = new Div();
		$E->sStyle="position : absolute; right : 1px; top : 2px; z-index : 1; font-size : 7pt";
		$E->Insert(sprintf("%d%%/%d%%",$AWinResult[$i]['Killed'],$ALooseResult[$i]['Killed']));
		$D->aLines[0]->Insert($E);

		$D->onMouseOver("showByName('loses$i')");
		$D->onMouseOut("hideByName('loses$i')");

		$T->Insert(2,13+$i,$D);
		$T->Join(2,13+$i,2,1);

	}
	$T->Insert(1,14+$numMissions,"Offensive Value: ");
	$T->SetClass(1,14+$numMissions,"legend");
	$T->Insert(1,15+$numMissions,"Tactical Value: ");
	$T->SetClass(1,15+$numMissions,"legend");
	$T->Insert(1,16+$numMissions,"XP: ");
	$T->SetClass(1,17,"legend");

	$T->Insert(2,14+$numMissions,sprintf("%5.1f",$defender['AV'])); $T->Insert(3,14+$numMissions,sprintf("%5.1f",$attacker['AV']));
	$T->Insert(2,15+$numMissions,sprintf("%5.1f",$defender['DV'])); $T->Insert(3,15+$numMissions,sprintf("%5.1f",$attacker['DV']));
	$T->Insert(2,16+$numMissions,sprintf("%5.1f",$defender['AV']+$defender['DV'])); $T->Insert(3,16+$numMissions,sprintf("%5.1f",$attacker['AV']+$attacker['DV']));
	$T->Insert(1,17+$numMissions,new Input("submit","Calculate","Calculate","smbutton"));
	$T->Insert(2,17+$numMissions,''.fleet_roll_dice($Result));
}
else
    $T->Insert(1,14,new Input("submit","Calculate","Calculate","smbutton"));


$F->Insert($T);
$H->Insert($F);
CloseSQL($sql);

$H->Insert(new Image("IMG/BattleExplain2.png","Battlecalculator Legend","Battlecalculator Legend"));
include("part/mainsubmenu.php");


$H->Draw();

?>
