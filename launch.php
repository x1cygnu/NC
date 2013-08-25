<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");
include_once("internal/planet.php");
include_once("internal/starsystem.php");
include_once("internal/fleet.php");

session_start();

global $GET;
$GET=array();


$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");

if ($_SESSION['Ajax'])
{
    $H->AddJavascriptFile('js/ajax.js');
    $H->AddJavascriptFile('js/common.js');
    $H->AddJavascriptFile('js/launch.js');    
}
$H->sTitle="Northern Cross - Launch fleet";

$sql=&OpenSQL($H);

ForceActivePlayer($sql, $H, "launch.php");
ForceFrozen($sql, $H);
ForceNoSitting($sql, $H, $_SESSION['PID']);

include("part/sitpid.php");


$H->AddStyle("fleet.css");
$menuselected="Fleet";
include("part/mainmenu.php");

if (!tech_check_player($sql, $MainPID, 1)) //GEM
    $H->Insert(new Error("You need to research Gravitomagnetism first in order to launch a fleet"));

get("id","integer");
if (!exists($GET['id']))
{
    post("from","integer");

    post("system","integer");
    post("ring","integer");
    post("planet","integer");
    
    post("vpr","integer");
    post("int","integer");
    post("fr","integer");
    post("bs","integer");
    post("drn","integer");
    post("cs","integer");
    post("tr","integer");
    
    if ($POST['system']==0 and $POST['ring']==0 and $POST['planet']!=0)
	$to=$POST['planet'];
    else
        $to=planet_get_id($sql,$POST['system'],$POST['ring']);
    $details=array();
    $Answer=fleet_simulate($sql,$MainPID,$POST['from'],$to,
	fleet_get_type($POST['vpr'],$POST['int'],$POST['fr'],
			$POST['bs'],$POST['drn'],$POST['cs'],$POST['tr']),
	   $details);
    if (gettype($Answer)=="string")
	$H->Insert(new Error("$Answer"));
    else
    {
	$H->Insert("Launching at " . DecodeTime(EncodeNow()));
	$H->Insert(new Info("ETA is " . DecodeTime($Answer)));
    }
    
    $GET['id']=$POST['from'];
}

$P=planet_get_all($sql,$GET['id']);

if ($P['FleetOwner']!=$MainPID)
{
    $H->Insert(new Error("You command no fleet there"));
    $H->Draw();
    die;
}

if ($MainPID==$_SESSION['PID'])
    $F=new Form("fleet.php",true);
else
    $F=new Form("fleet.php?sit=1",true);

$F->sName=$F->sId="fleet";

$T=new Table();
$T->sClass='block launcher';
$T->Insert(1,1,"{$P['Name']} {$P['Ring']}");
$T->Insert(1,2,"Fleet count");
$T->Insert(1,3,"Ship type");
$T->Insert(2,3,"Amount");
$T->Insert(3,3,"Max");

$T->Join(1,1,3,1);
$T->Join(1,2,3,1);
$B=new Input("button","hump","All","smbutton");
$B->onClick("document.fleet.vpr.value={$P['Vpr']}; document.fleet.ints.value={$P['Int']}; document.fleet.fr.value={$P['Fr']}; document.fleet.bs.value={$P['Bs']}; document.fleet.drn.value={$P['Drn']};document.fleet.cs.value={$P['CS']}; document.fleet.tr.value={$P['Tr']}");
$T->Insert(3,3,$B);

$T->aRowClass[1]='legend';
$T->aRowClass[2]='title';
$T->aRowClass[3]='legend';

$T->Insert(1,4,"Viper");
$B=new Input("text","vpr","0","text number");
$B->sId='vpr';
$B->onKeyUp("checkTT({$GET['id']})");
$T->Insert(2,4,$B);
$T->Insert(3,4,"{$P['Vpr']}");

$T->Insert(1,5,"Interceptor");
$B=new Input("text","int","0","text number");
$B->sId='ints';
$B->onKeyUp("checkTT({$GET['id']})");
$T->Insert(2,5,$B);
$T->Insert(3,5,"{$P['Int']}");

$T->Insert(1,6,"Frigate");
$B=new Input("text","fr","0","text number");
$B->sId='fr';
$B->onKeyUp("checkTT({$GET['id']})");
$T->Insert(2,6,$B);
$T->Insert(3,6,"{$P['Fr']}");

$T->Insert(1,7,"Battleship");
$B=new Input("text","bs","0","text number");
$B->sId='bs';
$B->onKeyUp("checkTT({$GET['id']})");
$T->Insert(2,7,$B);
$T->Insert(3,7,"{$P['Bs']}");

$T->Insert(1,8,"Dreadnought");
$B=new Input("text","drn","0","text number");
$B->sId='drn';
$B->onKeyUp("checkTT({$GET['id']})");
$T->Insert(2,8,$B);
$T->Insert(3,8,"{$P['Drn']}");

$T->Insert(1,9,"Colony Ship");
$B=new Input("text","cs","0","text number");
$B->sId='cs';
$B->onKeyUp("checkTT({$GET['id']})");
$T->Insert(2,9,$B);
$T->Insert(3,9,"{$P['CS']}");

$T->Insert(1,10,"Transport");
$B=new Input("text","tr","0","text number");
$B->sId='tr';
$B->onKeyUp("checkTT({$GET['id']})");
$T->Insert(2,10,$B);
$T->Insert(3,10,"{$P['Tr']}");

$F->Insert($T);

$T=new Table();
$T->sClass='block launcher';
$T->SetCols(2);
$T->Insert(1,1,"Orders");
$T->Join(1,1,2,1);
$T->aRowClass[1]='title';
$T->Insert(1,2,'Mission');

$MS=new Select();
$MS->sName='mis';
for ($ji=1; $ji<=$missionTypeCount-1; ++$ji) //skip last mission: Retreat
  $MS->AddOption($ji,missionName($ji));
$MS->sClass='text';
$T->Insert(2,2,$MS);
$F->Insert($T);



$T=new Table();
$T->sClass='block launcher';
$T->SetCols(3);
$T->Insert(1,1,"Target");
$T->Join(1,1,3,1);
$T->aRowClass[1]='title';

$i=1;

if ($MainPID==$_SESSION['PID'])
{
$L=starsystem_bio($sql,$MainPID,true);
foreach ($L as $S)
{
    ++$i;
    $T->Insert(1,$i,$S['Name']);
    $T->Insert(2,$i,"{$S['X']}/{$S['Y']}");
    $Inpt=new Input("radio","system","{$S['SID']}");
    $Inpt->onChange('checkTT('.$GET['id'].')');
    $T->Insert(3,$i,$Inpt);
    if ($S['YPC']>0)
	$T->SetClass(1,$i,'ypc');
}

++$i;
$T->aRowClass[$i]='sublegend';
$T->Insert(1,$i,"Orbit");
$SelRing=new Select();
$SelRing->sName='ring';
for ($ji=0; $ji<=18; ++$ji)
  $SelRing->AddOption($ji,$ji);
$SelRing->sClass='text number';
if ($_SESSION['Ajax'])
{
    $SelRing->sId='toorb';
    $SelRing->onChange('checkTT('.$GET['id'].')');
}
$T->Insert(2,$i,$SelRing);
$T->Join(2,$i,2,1);
}
else //if sitting someone
{
    $Ps=planet_list($sql, $MainPID);
    foreach ($Ps as $P) {
	++$i;
	$T->Insert(1,$i,$P['Name'] . ' ' . $P['Ring']);
        $Inpt=new Input("radio","planet","{$P['PLID']}");
	if ($_SESSION['Ajax'])
	    $Inpt->onChange('checkTT('.$GET['id'].')');
        $T->Insert(3,$i,$Inpt);
	$T->Join(1,$i,2,1);
    }
}

++$i;
$T->aRowClass[$i]='sublegend';
$T->Insert(1,$i,"Simulation");
$I=new Input("checkbox","sim","1");

$T->Insert(2,$i,$I);
$T->Join(2,$i,2,1);

$F->Insert($T);
$F->Insert(new Input("hidden","from",$GET['id']));

$F->Insert("Fleet " . (fleet_slot_count($sql, $MainPID)+1) . " out of " . fleet_max_slot($sql, $MainPID));
$F->Br();
$F->Insert(new Input("submit","send","Launch!","smbutton"));

if ($_SESSION['Ajax'])
{
$TInfo=new Info("");
$TInfo->sId='ttcont';
$TInfo->Get(1,1)->sId='ttsum';
$TInfo->sStyle="display : none;";

$F->Insert($TInfo);

$Err=new Error("");
$Err->sStyle="display : none;";
$F->Insert($Err);
}

$F->onSubmit("if (document.fleet.sim.checked){document.fleet.action='launch.php" . ($MainPID==$_SESSION['PID']?"":"?sit=1") . "';}");

if (planet_gateway_available($sql, $GET['id'], $MainPID) and ($MainPID==$_SESSION['PID']))
{
    $T=new Table();
    $T->Insert(1,1,"Gateway jump");
    $T->aRowClass[1]='title';
    $T->Insert(1,2,"Gate address");
    $T->SetClass(1,2,'legend');
    $T->Insert(2,2,new Input("text","GateAddr","","text"));
    $T->Insert(1,3,new Input("submit","send","Jump!","smbutton"));
    $T->Insert(1,3,new Br());
    $T->Insert(1,3,"Warning: no confirmation! Constant travel time 1h");
    $T->aRowClass[3]='legend';
    $T->Join(1,1,2,1);
    $T->Join(1,3,2,1);
    $F->Insert($T);
}

$F->Insert(new Input("hidden","orderid",$_SESSION['PostCode']+1));
$H->Insert($F);
include("part/mainsubmenu.php");
$H->Draw();
CloseSQL($sql);
?>

