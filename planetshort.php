<?php
ini_set('display_errors','On');
error_reporting(E_ALL);
ob_start("ob_gzhandler");
include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/validator.php");
include_once("internal/planet.php");
include_once("internal/progress.php");
include_once("internal/player.php");
include_once("internal/hint.php");
include_once("internal/tech.php");

session_start();

include_once("part/planetcommon.php");

if ($makeChanges)
{
post("newname","string");
post("namechange","string");
if (isset($POST['namechange']))
    planet_change_custom_name($sql, $Index['here'], $MainPID, $POST['newname']);
}

$P=planet_get_all($sql, $Index['here']);
if ($P['Owner']!=$MainPID)
    {
	$H->Insert(new Error("You have no control over chosen planet"));
	$H->Draw();
	die;
    }

$buy=false;

if ($makeChanges)
{
post("ccode","string");
if ($POST['ccode']=='Change' and $P['Gateway']!="")
{
    post("gcode","string");
    if ($POST['gcode']=="")
	$H->Insert(new Error("Cannot apply null code to gateway"));
    else
	planet_gateway_change_code($sql, $MainPID, $P['PLID'], $POST['gcode']);
    $buy=true;
}
}


if ($makeChanges && $_POST['spend']=="spend PP")
{
B('Farm');
B('Factory');
B('Cybernet');
B('Lab');
B('Refinery');
B('Starbase');

    S('Vpr');
S('Int');
if (tech_check_name($Techs,'Fr'))
    S('Fr');
if (tech_check_name($Techs,'Bs'))
    S('Bs');
if (tech_check_name($Techs,'Drn'))
    S('Drn');
S('Tr');
S('CS');

post("embassy","integer");
post("gateway","integer");
post("spacestation","integer");
if ($POST['gateway']==1 and tech_check_name($Techs,'WHole'))
{
    $R=planet_gateway_build($sql, $MainPID, $P['PLID']);
    if ($R!="")
	$H->Insert(new Error($R));
    else
	$H->Insert(new Info("Gateway constructed"));
    $buy=true;
}

if ($POST['embassy']==1)	//build embassy
{
    $R=planet_construction_build($sql, $MainPID, $P['PLID'], "Embassy", 512);
    if ($R!="")
	$H->Insert(new Error($R));
    else
	$H->Insert(new Info("Embassy constructed"));
    $buy=true;
}

if ($POST['spacestation']==1)	//build embassy
{
    $R=planet_construction_build($sql, $MainPID, $P['PLID'], "SpaceStation", 256);
    if ($R!="")
	$H->Insert(new Error($R));
    else
	$H->Insert(new Info("Space Station constructed"));
    $buy=true;
}
}
elseif ($makeChanges && $_POST['spend']=="spend RP")
{
BRP('Farm');
BRP('Factory');
BRP('Cybernet');
BRP('Lab');
BRP('Refinery');
}

elseif ($makeChanges && $_POST['spend']=="spend CS")
{
    planet_decompose_colony_ships($sql, $MainPID, $P['PLID']);
    $buy=true;
}

if ($buy)
    $P=planet_get_all($sql, $Index['here']);


$H->Insert(planetSummary($P));

//////////////////////////////////////
// Environment
//////////////////////////////////////
$PData = planetCompute($P);

$Table = new Table();
$Table->SetCols(2);
$Table->SetRows(4);
$Table->sClass='block object';
$Table->SetClass(1,1,"legend planetcell");
$Table->Join(1,1,2,1);
$Table->SetClass(1,2,"levelnum smallcell");
$Table->Join(1,2,2,1);
$Table->SetClass(1,3,"additional");
$Table->Join(1,3,2,1);
$Table->SetClass(1,4,"additional");


$T = new EntryConfig();
$T->table = $Table;
$T->titleXY = new V2D(1,1);
$T->valueXY = new V2D(1,2);
$T->progressXY = new V2D(1,3);
$T->timeremXY = new V2D(2,4);
$T->incomeXY = new V2D(1,4);




$H->Insert(planetResources($PData,$T));
$F=new Form("planet.php?id={$GET['id']}" . $sitAddition,true);

$F->Insert(planetBuildings($P,$T));
$H->Insert($F);
$H->Draw();
//////////////////////////////////
// ORBIT
//////////////////////////////////
if (false) {
$F->Insert($BuildT);

$BuildT=new Table();
$BuildT->SetCols(4);
$BuildT->Insert(1,1,"Orbit");
$BuildT->SetClass(1,1,'title');
$BuildT->Join(1,1,4,1);

if (!$Siege)
    $ObjTTemplate->SetClass(1,2,'shipnum');
else
    $ObjTTemplate->SetClass(1,2,'shipnum negative');


$BuildT->Insert(1,2,
    BuildingInfoBox("Vipers","Vpr",'vprs',"Manurevalbe and fast light fighter",false,$VprMax=Vpr_points($Pl['Engineering']),tech_check_name($Techs,'Vpr')));
$BuildT->Insert(2,2,
    BuildingInfoBox("Interceptors","Int",'ints',"Standard light fighter",false,$IntMax=Int_points($Pl['Engineering'])));
$BuildT->Insert(1,3,
    BuildingInfoBox("Frigates","Fr",'frs',"Well-armoured warship",false,$FrMax=Fr_points($Pl['Engineering']),tech_check_name($Techs,'Fr')));
$BuildT->Insert(2,3,
    BuildingInfoBox("Battleships","Bs",'bss',"Big, overpowered ship",false,$BsMax=Bs_points($Pl['Engineering']),tech_check_name($Techs,'Bs')));
$BuildT->Insert(3,2,
    BuildingInfoBox("Dreadnoughts","Drn",'drns',"Strongest of all warhips, yet relatively slow",false,$DrnMax=Drn_points($Pl['Engineering']),tech_check_name($Techs,'Drn')));
$BuildT->Insert(4,2,
    BuildingInfoBox("Transporters","Tr",'trs',"Defenceless ship carrying infrantry for onground desant.<br>Use these to conquer enemy planets.",false,$TrMax=Tr_points($Pl['Engineering'])));
$BuildT->Insert(4,3,
    BuildingInfoBox("Colony Ships","CS",'css',"Defenceless ship carrying settlers for new worlds.<br>Use these to take over free planets",false,$CSMax=CS_points($Pl['Engineering'])));

if ($Siege)
{
    $Enemy=account_get_name_from_pid($sql, $P['FleetOwner']);
    $BuildT->Insert(1,1," ($Enemy)");
}

$BuildT->Insert(4,4,new Input("submit","spend","spend PP","smbutton"));
$BuildT->Insert(3,4,'(' . $RP . ')');
$BuildT->Insert(3,4,new Input("submit","spend","spend RP","smbutton"));
$BuildT->Insert(2,4,new Input("submit","spend","spend CS","smbutton"));
$ResetButton=new Input("button","","Reset","smbutton");
$ResetButton->onClick("resetAll(); resetInput(); show()");
$BuildT->Insert(1,4,$ResetButton);
$BuildT->aRowClass[4]='title';
$F->Insert($BuildT);

///////////////////////////////////////////
// CONSTRUCTIONS
///////////////////////////////////////////


$BuildT=new Table();
$BuildT->SetCols(3);
$BuildT->SetRows(4);
$BuildT->Insert(1,1,"Constructions");
$BuildT->Join(1,1,3,1);
$BuildT->SetClass(1,1,'title');
$BuildT->Insert(1,2,MakeHint("Space Station","Landing base for your and ally fleets. May reduce travel times even further when equipped with Arrestor Field generator"));
$BuildT->Insert(2,2,MakeHint("Embassy","Representative building, allowing you to form or join an alliance and have access to alliance screen. You need only one embassy on any of your planets."));
$BuildT->Insert(3,2,MakeHint("Gateway","Hi-tech nearly-instant travel device based on artifically created wormholes."));
$BuildT->SetClass(1,2,'legend construction');
$BuildT->SetClass(2,2,'legend construction');
$BuildT->SetClass(3,2,'legend construction');

/////////////////////////////
// Space Station
/////////////////////////////

if ($P['SpaceStation']==1)
{
    $BuildT->Insert(1,3,'Present');
    $BuildT->Join(1,3,1,2);
}
else
{
    $BuildT->Insert(1,3,'256PP');
    $EmbCh=new Input("checkbox","spacestation",1,"chbx");
    $EmbCh->onChange("spacestationBuild(); show();");
    $EmbCh->sId='spacestation';
    $BuildT->Insert(1,4,$EmbCh);
    $BuildT->Insert(1,4,"build");
}


/////////////////////////////
// Embassy
/////////////////////////////

if ($P['Embassy']==1)
{
    $BuildT->Insert(2,3,'Present');
    $BuildT->Join(2,3,1,2);
}
else
{
    $BuildT->Insert(2,3,'512PP');
    $EmbCh=new Input("checkbox","embassy",1,"chbx");
    $EmbCh->onChange("embassyBuild(); show();");
    $EmbCh->sId='embassy';
    $BuildT->Insert(2,4,$EmbCh);
    $BuildT->Insert(2,4,"build");
}

/////////////////////////////
// Gateway
/////////////////////////////

if ($P['Gateway']!="")
{
    $BuildT->Insert(3,3,'' . $P['Gateway']);
    $BuildT->Insert(3,4,new Input("submit","ccode","Change","smbutton"));
    $BuildT->Insert(3,4,new Input("text","gcode",'' . $P['Gateway'],"text"));
}
else
{
    $BuildT->Insert(3,3,'6144PP');
    if (tech_check_name($Techs,'WHole'))
    {
    $GtwCh=new Input("checkbox","gateway",1,"chbx");
    $GtwCh->onChange("gatewayBuild(); show();");
    $GtwCh->sId='gateway';
    $BuildT->Insert(3,4,$GtwCh);
    $BuildT->Insert(3,4,"build");
    }
    else
    {
    $BuildT->Insert(3,4,"Wormhole tech required");
    $BuildT->SetClass(3,4,'negative');
    }
}


$F->Insert($BuildT);

$H->AddJavascriptFile("js/common.js");
$H->AddJavascriptFile("js/planet.js");
$VprRemain=$VprMax-$P['VprRemain'];
$IntRemain=$IntMax-$P['IntRemain'];
$FrRemain=$FrMax-$P['FrRemain'];
$DrnRemain=$DrnMax-$P['DrnRemain'];
$BsRemain=$BsMax-$P['BsRemain'];
$CSRemain=$CSMax-$P['CSRemain'];
$TrRemain=$TrMax-$P['TrRemain'];

$F->Insert(new Input("hidden","orderid",$_SESSION['PostCode']+1));
$H->Insert($F);
$H->onLoad("initAll($PP,$PPH,$Pop,$ProdMod,{$P['Farm']},{$P['FarmRemain']}," .
    $P['Factory'].','.$P['FactoryRemain'].','.
    $P['Cybernet'].','.$P['CybernetRemain'].','.
    $P['Lab'].','.$P['LabRemain'].','.
    $P['Refinery'].','.$P['RefineryRemain'].','.
    $P['Starbase'].','.$P['StarbaseRemain'].','.
    $P['Vpr'].','.($VprMax-$P['VprRemain']).','.
    $P['Int'].','.($IntMax-$P['IntRemain']).','.
    $P['Fr'].','.($FrMax-$P['FrRemain']).','.
    $P['Bs'].','.($BsMax-$P['BsRemain']).','.
    $P['Drn'].','.($DrnMax-$P['DrnRemain']).','.
    $P['CS'].','.($CSMax-$P['CSRemain']).','.
    $P['Tr'].','.($TrMax-$P['TrRemain']).','.
    "$VprMax,$IntMax,$FrMax,$BsMax,$DrnMax,$CSMax,$TrMax," . $P['BaseCost'] . "); identifyBoxes(); resetAll()");

include("part/mainsubmenu.php");
$H->Draw();
CloseSQL($sql);
}
?>
