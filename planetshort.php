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
include_once("part/planetobj.php");

//Check if one of the rows should be detailed with all the buttons

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
$Table->sClass='block';
$Table->SetClass(1,1,"legend planetcell");
$Table->Join(1,1,2,1);
$Table->SetClass(1,2,"levelnum smallcell");
$Table->Join(1,2,2,1);
$Table->SetClass(1,3,"additional");
$Table->Join(1,3,2,1);
$Table->SetClass(1,4,"additional");

function prepTable($rows, $cols, $short, $name) {
  $T = new Table();
  $T->SetRows($rows);
  if ($cols>0) {
    $cols = $cols + 1;
      $T->SetCols($cols);
      $T->Insert($cols,1," ");
      $T->SetClass($cols,1,$short . 'gap');
  } else {
      $T->SetCols(1);
  }
  $T->Insert(1,1,$name);
  $T->sClass = 'planetrow';
  $T->SetClass(1,1,'legend '.$short.'label');
  $T->Join(1,1,1,$rows);
  return $T;
}


//$expandResources = true;
$H->Insert(planetResources(prepTable(1,4,"resources", "Resources"), new V2D(2,1), $PData, 'prepObj'));

$F=new Form("planet.php?id={$GET['id']}" . $sitAddition,true);

$F->Insert(planetBuildings(prepTable(1,0,"buildings", "Buildings"), new V2D(2,1), $P,'prepBld'));
$F->Insert(planetConstructs(prepTable(1,3,"construct", "Constructs"), new V2D(2,1), $P,'prepCnstr'));

$F->Insert(planetLowOrbit(prepTable(1,2,"low", "Low Orbit"), new V2D(2,1), $P,'prepBld'));

$HighOrbitTable = prepTable(2,5,"high", "High Orbit");
if ($Siege) {
       $Table->SetClass(1,2,"levelnum smallcell negative");
       $Enemy=account_get_name_from_pid($sql, $P['FleetOwner']);
       $HighOrbitTable->Insert(1,1,new Br());
       $HighOrbitTable->Insert(1,1,"($Enemy)");
}
$F->Insert(planetHighOrbit($HighOrbitTable, new V2D(2,1), $P,'prepBld'));

//Ultimate spend PP button
$F->Place(new Input("submit","spend","spend PP","ppspend"));

$H->Insert($F);
$H->Draw();
CloseSQL($sql);
?>
