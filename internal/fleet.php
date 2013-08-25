<?php

include_once("./internal/common.php");
include_once("./internal/building.php");
include_once("./internal/race.php");
include_once("./internal/account.php");
include_once("./internal/log.php");
//fleet semi-class

function get_AV($ship)
{
    if (($ship=="Int") or ($ship=="Interceptor")) return 2;
    elseif (($ship=="Fr") or ($ship=="Frigate")) return 12;
    elseif (($ship=="Bs") or ($ship=="Battleship")) return 48;
    elseif (($ship=="Vpr") or ($ship=="Viper")) return 1;
    elseif (($ship=="Drn") or ($ship=="Dreadnought")) return 158;
    else return 0;
}

function get_DV($ship)
{
    if (($ship=="Int") or ($ship=="Interceptor")) return 2;
    elseif (($ship=="Fr") or ($ship=="Frigate")) return 16;
    elseif (($ship=="Bs") or ($ship=="Battleship")) return 37;
    elseif (($ship=="Vpr") or ($ship=="Viper")) return 1;
    elseif (($ship=="Drn") or ($ship=="Dreadnought")) return 156;
    else return 0;
}

function get_speed($ship)
{
    if (($ship=="Int") or ($ship=="Interceptor")) return 1.0;
    elseif (($ship=="Fr") or ($ship=="Frigate")) return 1.0;
    elseif (($ship=="Bs") or ($ship=="Battleship")) return 1.0;
    elseif (($ship=="Vpr") or ($ship=="Viper")) return 3.0;
    elseif (($ship=="Drn") or ($ship=="Dreadnought")) return 0.5;
		elseif (($ship=="Tr") or ($ship=="Transporter")) return 3.0;
		elseif (($ship=="Cs") or ($ship=="Colony Ship")) return 3.0;
    else return 0;
}

function missionID($missionName) {
	if ($missionName=="Attack") return 1;
	if ($missionName=="Kamikaze") return 2;
	if ($missionName=="Raid") return 3;
	if ($missionName=="Scout") return 4;
	if ($missionName=="Retreat") return 5;
	return 0;
}

$missionTypeCount = 5;

function missionName($missionID) {
	if ($missionID==1) return "Attack";
	if ($missionID==2) return "Kamikaze";
	if ($missionID==3) return "Raid";
	if ($missionID==4) return "Scout";
	if ($missionID==5) return "Retreat";
	return "Unknown";
}

/*
function fleet_distance($x1, $y1, $p1, $x2, $y2, $p2, $energy, $modifier)
{
    return (sqrt(1155*sqrt(sqr($x2-$x1)+sqr($y2-$y1))+115.75*abs($p2-$p1))+14.15)*pow(0.91,$energy)/$modifier;
}
*/
function building_starbase_AV($lvl)
{
    return 1.5*pow(1.3,$lvl);
}

function building_starbase_DV($lvl)
{
    return 1.4*pow(1.3,$lvl);
}


function fleet_roll_dice($results,$print) {
	$roll=mt_rand(1,999999)/1000000;
	if ($print)
		printf("Roll=%.4f  ",$roll);
	foreach ($results as $key => $val) {
		if ($roll<$val)
			return $key;
		$roll=$roll-$val;
	}
	echo "Invalid roll result: ".$roll;
	return "";
}

function fleet_chance($AAV, $ADV, $AAmod, $ADmod, $DAV, $DDV, $DAmod, $DDmod) {
	$swapped=false;
	if ($AAV*$AAmod<$DAV*$DAmod)
	{$swapped=true; $BAV=$DAV*$DAmod; $WAV=$AAV*$AAmod;}
	else
	{$swapped=false; $BAV=$AAV*$AAmod; $WAV=$DAV*$DAmod;}
	if ($BAV>$WAV*2.0)
		$chance=1;
	elseif ($WAV==0) //and $BAV==0
		$chance=0.5;
	else
		$chance=(1+sin(0.5*3.141593*($BAV-$WAV)/$WAV))/2;
	if ($swapped)
		return 1-$chance;
	else
		return $chance;
}

function fleet_damage($AAV, $ADV, $AAmod, $ADmod, $DAV, $DDV, $DAmod, $DDmod, $aggr, $winModifier) {
//        echo "<br>DDmod=",$DDmod;
	if ($ADV>0) {
		$unevenBattleSpread = 0.5;
		$winModifierSignificance = 1;
		$deviationSignificance = 0;

//		$dam=pow(($DAV+$DDV)/(5*$ADV*$ADmod),1.3)*$winModifier;
//                $dam=2*atan(pow($DDV/($ADV+$DDV)/$ADmod,1.0)*$winModifier)/3.14159;
		$EDDV = $DDV; //*$DDmod;
                $EADV = $ADV*$ADmod;
		$winModifier *= $winModifierSignificance;
		$ratio = ($aggr*$EDDV/$EADV)*$winModifier;
		$rAdj = ($ratio - 0.5)*$unevenBattleSpread;
		$damDeviation = (atan($rAdj)/3.14159) * $deviationSignificance;
		$dam = $ratio + $damDeviation;
		$dam = $ratio;

	} else
		return 1;
	if ($dam<0) $dam=0;
	if ($dam>1) $dam=1;
	return $dam;
}

function fleet_battle(&$result, &$fleetA, &$fleetD, &$resultFleetAWin, &$resultFleetDWin, &$resultFleetALoose, &$resultFleetDLoose, $Aattmod, $Adefmod, $Aphysic, $Amath, $APL, $Dattmod, $Ddefmod, $Dphysic, $Dmath, $DPL, $mission=0, $PlanetAttack=100, $PlanetDefense=100)
{
	global $anyships;
	foreach ($anyships as $S) {
		$fleetA[$S]=makeinteger($fleetA[$S]);
		$fleetD[$S]=makeinteger($fleetD[$S]);
	}
	$AAV=$fleetA['Int']*get_AV("Int")+$fleetA['Fr']*get_AV("Fr")+$fleetA['Bs']*get_AV("Bs")+$fleetA['Vpr']*get_AV("Vpr")+$fleetA['Drn']*get_AV("Drn");
	$DAV=$fleetD['Int']*get_AV("Int")+$fleetD['Fr']*get_AV("Fr")+$fleetD['Bs']*get_AV("Bs")+$fleetD['Vpr']*get_DV("Vpr")+$fleetD['Drn']*get_AV("Drn");
	$AAttmod=$Aattmod*0.01*(1+0.015*($Aphysic-$Dphysic)+0.01*$APL);
	$DAttmod=$Dattmod*0.01*(1+0.015*($Dphysic-$Aphysic)+0.01*$DPL);

	$ADV=$fleetA['Int']*get_DV("Int")+$fleetA['Fr']*get_DV("Fr")+$fleetA['Bs']*get_DV("Bs")+$fleetA['Vpr']*get_DV("Vpr")+$fleetA['Drn']*get_DV("Drn");
	$DDV=$fleetD['Int']*get_DV("Int")+$fleetD['Fr']*get_DV("Fr")+$fleetD['Bs']*get_DV("Bs")+$fleetD['Vpr']*get_DV("Vpr")+$fleetD['Drn']*get_DV("Drn");

	$ADefmod=$Adefmod*0.01*(pow(1.02,($Amath-$Dmath))+0.007*$APL);
	$DDefmod=$Ddefmod*0.01*(pow(1.02,($Dmath-$Amath))+0.007*$DPL);

	$AAttWinmod=1.0;
	$ADefWinmod=1.0;
	$DDefWinmod=1.0;
	$AAttLoosemod=1.0;
	$ADefLoosemod=1.0;

        $loseCoefficient = 1.5;
        $aggressiveness = 0.3;
	$crushingWinAttModifier = 2.5;
	//apply mission modifications:
	$AAttWinmod=0.85;
	$AAttLoosemod=1.15;
	if ($mission==2) { //Kamikaze
		$AAttWinmod=1;
		$AAttLoosemod=1;
		$AAttmod*=1.2;
		$ADefmod*=0.85;
		$aggressiveness = 0.5;
	} else if ($mission==3) { //Raid
		$AAttWinmod=0.85;
		$AAttLoosemod=1.4;
		$ADefLoosemod=1.2;
		$DDefWinmod*=1.1;
	} else if ($mission==4) { //Scout
                $aggressiveness = 0.2;
		$AAttWinmod=0.7;
		$AAttLoosemod=1.5;
		$ADefLoosemod*=1.4;
		$DDefWinmod*=1.10;
		$DDefmod*=1.05;
	} else if ($mission==5) { //Retreat
		$AAttWinmod=1;
		$AAttLoosemod=1;
		$AAttmod*=0.8;
		$ADefmod*=0.8;
		$DDefmod*=1.2;
	}

	$i=1;
	$cost=0;
	$SBAV=0;
	$SBDV=0;
	for ($i=1; $i<=$fleetD['Starbase']; ++$i)
	{
		$SBAV+=building_starbase_AV($i);
		$SBDV+=building_starbase_DV($i);
		$cost+=building_points_for_lvl($i);
	}
	$SBAV*=$PlanetAttack/100;
	$SBDV*=$PlanetDefense/100;
	$DAV+=$SBAV;
	$DDV+=$SBDV;
	$cost+=building_points_for_lvl($i)*($fleetD['Starbase']-$i+1);


	$ADamage=fleet_damage($AAV, $ADV, $AAttmod, $ADefmod, $DAV, $DDV, $DAttmod, $DDefmod, $aggressiveness, 1.0);
	$DDamage=fleet_damage($DAV, $DDV, $DAttmod, $DDefmod*$DDefWinmod, $AAV, $ADV, $AAttmod, $ADefmod, $aggressiveness, 1.0);
	$DLDamage=fleet_damage($DAV, $DDV, $DAttmod, $DDefmod, $AAV, $ADV, $AAttmod, $ADefmod, $aggressiveness, $loseCoefficient);

	if ($mission!=2 and $mission!=5) //Kamikaze or Retreat
		$ALDamage=fleet_damage($AAV, $ADV, $AAttmod, $ADefmod*$ADefLoosemod, $DAV, $DDV, $DAttmod, $DDefmod, $aggressiveness, $loseCoefficient);
	else
		$ALDamage=1;

	$fleetA['Killed']=floor($ADamage*100);
	$fleetD['Killed']=floor($DDamage*100);
	$resultFleetAWin['Killed']=floor($ADamage*100);
	$resultFleetDWin['Killed']=floor($DDamage*100);
	$resultFleetALoose['Killed']=floor($ALDamage*100);
	$resultFleetDLoose['Killed']=floor($DLDamage*100);

	if ($DDV-$SBDV>0) {
		$DLDamage=($DLDamage*$DDV-$SBDV)/($DDV-$SBDV);
		if ($DLDamage<$DDamage)
			$DLDamage=$DDamage;
	}

	global $fightships;
	foreach ($fightships as $S) {
		$resultFleetAWin[$S]=$fleetA[$S]*(1-$ADamage);
		$resultFleetDWin[$S]=$fleetD[$S]*(1-$DDamage);
		$resultFleetALoose[$S]=$fleetA[$S]*(1-$ALDamage);
		$resultFleetDLoose[$S]=$fleetD[$S]*(1-$DLDamage);
	}
	global $transships;
	foreach ($transships as $S) {
		$resultFleetAWin[$S]=$fleetA[$S];
		$resultFleetDWin[$S]=$fleetD[$S];
		$resultFleetALoose[$S]=$fleetA[$S]*(1-$ADamage);
		$resultFleetDLoose[$S]=$fleetD[$S]*(1-$DDamage);
	}

	$costremain=$cost*(1-$DDamage);

	$i=1;
	$resultFleetDWin['Starbase']=0;
	$resultFleetDLoose['Starbase']=0;

	while ($costremain>building_points_for_lvl($resultFleetDWin['Starbase']+1))
	{
		$costremain-=building_points_for_lvl($resultFleetDWin['Starbase']+1);
		++$resultFleetDWin['Starbase'];
	}
	$resultFleetDWin['Starbase']+=$costremain/building_points_for_lvl($resultFleetDWin['Starbase']+1);

	$fleetD['AV']=$DAV; $fleetD['DV']=$DDV; $fleetD['CV']=$DAV+$DDV; 
	$fleetA['AV']=$AAV; $fleetA['DV']=$ADV; $fleetA['CV']=$AAV+$ADV;

	//Compute chance values:
	$AttCrushChance=fleet_chance($AAV, $ADV, $AAttmod*$AAttWinmod, $ADefmod, $DAV, $DDV, $DAttmod*$crushingWinAttModifier, $DDefmod);
	$AttWinChance=fleet_chance($AAV, $ADV, $AAttmod*$AAttWinmod, $ADefmod, $DAV, $DDV, $DAttmod, $DDefmod);
	$RaidChance=$AttWinChance;
//	if ($mission==3)
		$RaidChance=fleet_chance($AAV, $ADV, $AAttmod*$AAttLoosemod, $ADefmod, $DAV, $DDV, $DAttmod, $DDefmod);
	$DefWinChance=1-$RaidChance;
//	$DefWinChance=fleet_chance($DAV, $DDV, $DAttmod, $DDefmod, $AAV, $ADV, $AAttmod, $ADefmod);
	$DefCrushChance=fleet_chance($DAV, $DDV, $DAttmod, $DDefmod, $AAV, $ADV, $AAttmod*$crushingWinAttModifier*$AAttLoosemod, $ADefmod);

	if ($mission==2 || $mission==5) //Kamikaze or Retreat
		$DefCrushChance=$DefWinChance;

	$result['DE']=$AttCrushChance;
	$result['DU']=$AttWinChance-$AttCrushChance;
	$Draw = $RaidChance - $AttWinChance;
        if ($mission==3) { //Raid
		$result['R']=$Draw;
		$result['=']=0;
	} else {
		$result['R']=0;
		$result['=']=$Draw;
	}
	$result['AU']=$DefWinChance-$DefCrushChance;
	$result['AE']=$DefCrushChance;

//	echo "sum=".($result['DE']+$result['DU']+$result['R']+$result['AU']+$result['AE'])." ";

}



function fleet_fast_start(&$sql, $fleetowner, $Planet)
{
    $Planet=planet_get_all($sql, $Planet);
    if (player_same_alliance($sql, $fleetowner, $Planet['Owner']) and $Planet['SpaceStation']==1)
	return 1;
    return 0;
}

function fleet_fast_land(&$sql, $fleetowner, $Planet)
{
    $Planet=planet_get_all($sql, $Planet);
    if (player_same_alliance($sql, $fleetowner, $Planet['Owner']) and $Planet['SpaceStation']==1)
	{
	    if (tech_check_player($sql, $Planet['Owner'], 20))
		return 2;
	    return 1;
	}
    return 0;
}

function subsqr($x)
{
    return pow($x,1.85);
}

function fleet_time(&$sql, $owner, $from, $to, $speed, $energy, $speedFactor, &$details, $UseSS=2, $UseArrF=2)
{

$owner=makeinteger($owner);
$speed=Speed(makeinteger($speed));
$energy=makeinteger($energy)+1;

$FromPLID=planet_get_id_from_position($sql, $from['X'], $from['Y'], $from['Ring']);
$ToPLID=planet_get_id_from_position($sql, $to['X'], $to['Y'], $to['Ring']);

if (!isset($FromPLID) or !isset($ToPLID))
    return -1;

$fromSID=starsystem_get_sid_from_coords($sql,$from['X'],$from['Y']);
$toSID=starsystem_get_sid_from_coords($sql,$to['X'],$to['Y']);
$fromSize=starsystem_get_size($sql,$fromSID);
$toSize=starsystem_get_size($sql,$toSID);

$FromStart=fleet_fast_start($sql, $owner, $FromPLID);
$ToLand=fleet_fast_land($sql, $owner, $ToPLID);

$startTime=3.0;
if (($FromStart>0 and $ToLand>0 and $UseSS==2) or ($UseSS==1))
    $startTime=0.15;

$acceleration=$speed*pow(1.1,$energy)*$speedFactor;
$details['acc']=$acceleration;
if ($from['X']==$to['X'] and $from['Y']==$to['Y'])
{
    if ($from['Ring']>$to['Ring'])
	$mul=0.95;
    elseif ($from['Ring']<$to['Ring'])
	$mul=1.05;
    $dist=abs(subsqr($from['Ring'])-subsqr($to['Ring']))*$mul;

}
else {
  $dist=abs(subsqr($fromSize-0.750)-subsqr($from['Ring']))*1.05+abs(subsqr($toSize-0.750)-subsqr($to['Ring']))*0.95;
}
/*
if ($from['X']!=$to['X'] or $from['Y']!=$to['Y'] or $from['Ring']!=$to['Ring'])
  $dist=$dist+30;*/
$T=12*sqrt(4*$dist/$acceleration);

if (($ToLand==2 and $UseArrF==2) or ($UseArrF==1))
    $T*=0.70;

if ($from['Ring']==0)
  $startTime+=2;
if ($to['Ring']==0)
  $startTime+=2;

$details['STL']=($T+$startTime)*3600;
$warp=sqrt(sqr($from['X']-$to['X'])+sqr($from['Y']-$to['Y']));
$TW=7.0*$warp/(pow(1.1,$energy)*($speed/100.0));
$details['FTL']=$TW*3600;
$T=$T+$TW;
$details['TT']=($T+$startTime)*3600;
return ($T+$startTime)*3600;
}

function fleet_get_speed($Vpr, $Int, $Fr, $Bs, $Drn, $CS, $Tr) {
	$speed=10;
	if ($Vpr>0) $speed=min($speed,get_speed("Vpr"));
	if ($Int>0) $speed=min($speed,get_speed("Int"));
	if ($Fr>0) $speed=min($speed,get_speed("Fr"));
	if ($Bs>0) $speed=min($speed,get_speed("Bs"));
	if ($Drn>0) $speed=min($speed,get_speed("Drn"));
	if ($CS>0) $speed=min($speed,get_speed("Cs"));
	if ($Tr>0) $speed=min($speed,get_speed("Tr"));
	return $speed;
}

function fleet_max_slot(&$sql, $pid)
{
    $pid=makeinteger($pid);
    $Math=player_get_science($sql, $pid, "Mathematics");
    return 3+floor($Math/6);
}

function fleet_slot_count(&$sql, $pid)
{
    $pid=makeinteger($pid);
		$noSlotMission=missionID("Retreat");
    $Slots=$sql->query("SELECT count(*) AS Sloty FROM NC_FleetMovement WHERE Owner=$pid AND Mission!=$noSlotMission");
    return $Slots[0]['Sloty'];
}

function fleet_create(&$sql, $owner, $Vpr, $Int, $Fr, $Bs, $Drn, $CS, $Tr, $from, $to, $GatewayJump, $energy=99999, $mission=0) {
	$Vpr=makeinteger($Vpr);
	$Int=makeinteger($Int);
	$Fr=makeinteger($Fr);
	$Bs=makeinteger($Bs);
	$Drn=makeinteger($Drn);
	$CS=makeinteger($CS);
	$Tr=makeinteger($Tr);
	$from=makeinteger($from);
	$to=makeinteger($to);
	$energy=makeinteger($energy);
	$owner=makeinteger($owner);
	$mission=makeinteger($mission);

	print ("FLEET CREATE PID=$owner Ships=($Vpr $Int $Fr $Bs $Drn $CS $Tr) From=$from To=$to Energy=$energy Mission=$mission \n");

	$Controlers=$sql->query("SELECT * FROM NC_Player WHERE PID=$owner");
	if (count($Controlers)!=1)
	{
		return "Internal error";
	}
	$Controler=$Controlers[0];
	if ($Controler['Warp']<$energy)
		$energy=$Controler['Warp'];

	$FPos=planet_get_position($sql,$from);
	$TPos=planet_get_position($sql,$to);
	$details=array();

	$time=fleet_time($sql, $owner, $FPos, $TPos, $Controler['Speed'], $energy, fleet_get_speed($Vpr, $Int, $Fr, $Bs, $Drn, $CS, $Tr), $details)+EncodeNow();

	if ($GatewayJump)
		$time=EncodeNow()+3600;

	if ($GatewayJump)    
		$sql->query("INSERT INTO NC_FleetMovement VALUES(NULL, $owner, -$to, $Vpr, $Int, $Fr, $Bs, $Drn, $CS, $Tr, $time, $mission)");
	else
		$sql->query("INSERT INTO NC_FleetMovement VALUES(NULL, $owner, $to, $Vpr, $Int, $Fr, $Bs, $Drn, $CS, $Tr, $time, $mission)");

	if (!$GatewayJump)
	{
		if ($TPlanet['Owner']!=$owner)
		{
			$Message="<b>Attention!</b><br>Enemy fleet is going to arrive around that time<br>";
			if ($Tr>0) $Message .= "$Tr Transporter" . ($Tr>1?"s":"") . "<br>";
			if ($Vpr>0) $Message .= "$Vpr Viper" . ($Vpr>1?"s":"") . "<br>";
			if ($Int>0) $Message .= "$Int Interceptor" . ($Int>1?"s":"") . "<br>";
			if ($Fr>0) $Message .= "$Fr Frigate" . ($Fr>1?"s":"") . "<br>";
			if ($Bs>0) $Message .= "$Bs Battleship" . ($Bs>1?"s":"") . "<br>";
			if ($Drn>0) $Message .= "$Drn Dreadnought" . ($Drn>1?"s":"") . "<br>";
			$Message .= "under command of " . account_get_name_from_pid($sql,$owner);
			$Message .= "<br>The fleet is heading towards ";
			if ($TPlanet['CustomName']!="")
				$Message .= $TPlanet['CustomName'] . " ({$TPlanet['Name']} {$TPlanet['Ring']})";
			else
				$Message .= "{$TPlanet['Name']} {$TPlanet['Ring']}";
			news_set_on_time($sql,$TPlanet['Owner'],$time,$Message,2,$owner,$Vpr,$Int,$Fr,$Bs,$Drn,$Tr,$to);
		}
	}
	else //Gateway jump
		news_set_on_time($sql,$TPlanet['Owner'],$time,"Incoming wormhole at {$TPlanet['Name']} {$TPlanet['Ring']}<br>Unknown fleet will emerge around that time",2);

	$sql->query("DELETE FROM NC_PlanetCreated WHERE PLID=$to AND PID!={$owner}");
}

function fleet_send(&$sql, $owner, $Vpr, $Int, $Fr, $Bs, $Drn, $CS, $Tr, $from, $to, $energy=99999, $mission=0)
{
	$Vpr=makeinteger($Vpr);
	$Int=makeinteger($Int);
	$Fr=makeinteger($Fr);
	$Bs=makeinteger($Bs);
	$Drn=makeinteger($Drn);
	$CS=makeinteger($CS);
	$Tr=makeinteger($Tr);
	$from=makeinteger($from);
	$to=makeinteger($to);
	$energy=makeinteger($energy);
	$owner=makeinteger($owner);
	$mission=makeinteger($mission);

	$GatewayJump=false;
	if ($to<0)
	{
		$to=-$to;
		$GatewayJump=true;
	}

	$Log=log_entry($sql,"launch","{$Vpr}Vpr {$Int}Int {$Fr}Fr {$Bs}Bs {$Drn}Drn {$CS}CS {$Tr}Tr",
			planet_get_name($sql,$from),planet_get_name($sql,$to),$energy);

	if (!tech_check_player($sql, $owner, 1)) //GEM
	{
		log_result($sql,$Log,"tech req");
		return "You need to research Gravitomagnetism first";
	}
	if ($energy<0)
	{
		log_result($sql,$Log,"wrong energy");
		return "You cannot send fleet with negative Warp factor";
	}
	if ($Vpr<0 or $Int<0 or $Fr<0 or $Bs<0 or $Drn<0 or $CS<0 or $Tr<0)	//ujemna flota?
	{
		log_result($sql,$Log,"wrong amount");
		return "You cannot send negative amount of ships";
	}
	if ($Vpr==0 and $Int==0 and $Fr==0 and $Bs==0 and $Drn==0 and $CS==0 and $Tr==0)		//brak floty?
	{
		log_result($sql,$Log,"wrong amount");
		return "Send some ships";
	}

	//jest slot lotu?
	$Slots=$sql->query("SELECT count(*) AS Sloty FROM NC_FleetMovement WHERE Owner=$owner");
	$Math=player_get_science($sql, $owner, "Mathematics");
	if (fleet_slot_count($sql, $owner)>=fleet_max_slot($sql, $owner))
		//    if ($Slots[0]['Sloty']>=3+floor($Math/6))
	{
		log_result($sql,$Log,"slot req");
		return "You are unable to have more fleets in transit";
	}


	if (Lock($sql,$from)!=1)
	{
		log_result($sql,$Log,"grab failed");
		return false;
	}
	$FPlanets=$sql->query("SELECT * FROM NC_Planet WHERE PLID=$from");
	$TPlanets=$sql->query("SELECT M.Name, P.Type, P.Ring, P.CustomName, P.Owner FROM NC_Planet P JOIN NC_Map M ON P.SID=M.SID WHERE PLID=$to");
	if (count($FPlanets)!=1 or count($TPlanets)!=1)
	{
		log_result($sql,$Log,"not found");
		return "Fleet command incorrect. Try again";
	}	
	$FPlanet=$FPlanets[0];
	$TPlanet=$TPlanets[0];
	if ($TPlanet['Type']==22 and (!tech_check_player($sql, $owner,62))) //wysylamy w corone gwiazdy?
	{
		Unlock($sql, $from);
		log_result($sql,$Log,"tech req");
		return "You need to research Plasmatic Shielding technology in order to survive in star corona";
	}
	if ($TPlanet['Type']==21 and (!tech_check_player($sql, $owner,42))) //wysylamy w Chaos?
	{
		Unlock($sql, $from);
		log_result($sql,$Log,"tech req");
		return "You need to develop Multiple Vector Movement Predictor in order to succesfully trace millions of rocks at target destination and not to crash on them";
	}
	if ($owner!=$FPlanet['FleetOwner'])	//wysyla wlasciciel floty?
	{
		Unlock($sql, $from);
		log_result($sql,$Log,"wrong owner");
		return "You may command only your own ships";
	}
	//za duzo statkow?
	if ($Vpr>$FPlanet['Vpr'] or $Int>$FPlanet['Int'] or $Fr>$FPlanet['Fr'] or $Bs>$FPlanet['Bs'] or $Drn>$FPlanet['Drn'] or $Tr>$FPlanet['Tr'] or $CS>$FPlanet['CS'])
	{
		Unlock($sql, $from);
		log_result($sql,$Log,"wrong amount");
		return "You cannot send more ships than you have";
	}


	$Controlers=$sql->query("SELECT * FROM NC_Player WHERE PID=$owner");
	if (count($Controlers)!=1)
	{
		Unlock($sql, $from);
		log_result($sql,$Log,"failed");
		return "Internal error";
	}
	$Controler=$Controlers[0];
	if ($Controler['Warp']<$energy)
	{
		$energy=$Controler['Warp'];
		log_update($sql, $Log, 4, "max" . $energy);
	}

	if ($GatewayJump)
		log_update($sql, $Log, 4, "gateway");

	$FPos=planet_get_position($sql,$from);
	$TPos=planet_get_position($sql,$to);
	$details=array();


	$time=fleet_time($sql, $owner, $FPos, $TPos, $Controler['Speed'], $energy, fleet_get_speed($Vpr, $Int, $Fr, $Bs, $Drn, $CS, $Tr), $details)+EncodeNow();

	if ($GatewayJump)
		$time=EncodeNow()+3600;

	//planet owner update
	Unlock($sql, $from);
	player_update_all($sql, $FPlanet['Owner']);
	if (Lock($sql,$from)!=1)
	{
		log_result($sql,$Log,"grab failed");
		return false;
	}


	if ($GatewayJump)    
		$sql->query("INSERT INTO NC_FleetMovement VALUES(NULL, $owner, -$to, $Vpr, $Int, $Fr, $Bs, $Drn, $CS, $Tr, $time, 0)");
	else
		$sql->query("INSERT INTO NC_FleetMovement VALUES(NULL, $owner, $to, $Vpr, $Int, $Fr, $Bs, $Drn, $CS, $Tr, $time, $mission)");
	$sql->query("UPDATE NC_Planet SET Tr=Tr-$Tr, CS=CS-$CS, Vpr=Vpr-$Vpr, `Int`=`Int`-$Int, Fr=Fr-$Fr, Bs=Bs-$Bs, Drn=Drn-$Drn WHERE PLID=$from");

	$sql->query("UPDATE NC_Planet SET FleetOwner=IF(" .
			"Tr=0 AND TrRemain=0 AND " .
			"CS=0 AND CSRemain=0 AND " .
			"Vpr=0 AND VprRemain=0 AND " .
			"`Int`=0 AND IntRemain=0 AND " .
			"Fr=0 AND FrRemain=0 AND " .
			"Drn=0 AND DrnRemain=0 AND " .
			"Bs=0 AND BsRemain=0,0,FleetOwner) WHERE PLID=$from");
	if (!$GatewayJump)
	{
		if ($TPlanet['Owner']!=$owner)
		{
			$Message="<b>Attention!</b><br>Enemy fleet is going to arrive around that time<br>";
			if ($Tr>0) $Message .= "$Tr Transporter" . ($Tr>1?"s":"") . "<br>";
			if ($Vpr>0) $Message .= "$Vpr Viper" . ($Vpr>1?"s":"") . "<br>";
			if ($Int>0) $Message .= "$Int Interceptor" . ($Int>1?"s":"") . "<br>";
			if ($Fr>0) $Message .= "$Fr Frigate" . ($Fr>1?"s":"") . "<br>";
			if ($Bs>0) $Message .= "$Bs Battleship" . ($Bs>1?"s":"") . "<br>";
			if ($Drn>0) $Message .= "$Drn Dreadnought" . ($Drn>1?"s":"") . "<br>";
			$Message .= "under command of " . account_get_name_from_pid($sql,$owner);
			$Message .= "<br>The fleet is heading towards ";
			if ($TPlanet['CustomName']!="")
				$Message .= $TPlanet['CustomName'] . " ({$TPlanet['Name']} {$TPlanet['Ring']})";
			else
				$Message .= "{$TPlanet['Name']} {$TPlanet['Ring']}";
			news_set_on_time($sql,$TPlanet['Owner'],$time,$Message,2,$owner,$Vpr,$Int,$Fr,$Bs,$Drn,$Tr,$to);
		}
	}
	else //Gateway jump
		news_set_on_time($sql,$TPlanet['Owner'],$time,"Incoming wormhole at {$TPlanet['Name']} {$TPlanet['Ring']}<br>Unknown fleet will emerge around that time",2);

	Unlock($sql, $from);
	$sql->query("DELETE FROM NC_PlanetCreated WHERE PLID=$to AND PID!={$owner}");
	log_result($sql,$Log,"OK");
	return true;
}

function fleet_simulate($sql, $owner, $from, $to, $speedFactor, &$details, $energy=999999)
{
    $owner=makeinteger($owner);
    $from=makeinteger($from);
    $to=makeinteger($to);
    $energy=makeinteger($energy);

    if ($energy<0)
	return "You cannot send fleet with negative Warp factor";
    $FPlanets=$sql->query("SELECT * FROM NC_Planet WHERE PLID=$from");
    $TPlanets=$sql->query("SELECT M.Name, P.Ring, P.Owner FROM NC_Planet P JOIN NC_Map M ON P.SID=M.SID WHERE PLID=$to");
    if (count($FPlanets)!=1 or count($TPlanets)!=1)
	return "Fleet command incorrect. Try again";
    $FPlanet=$FPlanets[0];
    $TPlanet=$TPlanets[0];

    $Controlers=$sql->query("SELECT * FROM NC_Player WHERE PID=$owner");
    if (count($Controlers)!=1)
    {
	return "Internal error";
    }
    $Controler=$Controlers[0];
    if ($Controler['Warp']<$energy)
	$energy=$Controler['Warp'];
    $FPos=planet_get_position($sql,$from);
    $TPos=planet_get_position($sql,$to);
    return fleet_time($sql, $owner, $FPos, $TPos, $Controler['Speed'], $energy, $speedFactor, $details)+EncodeNow();
}

function fleet_get_stationary(&$sql, $pid)
{
    return $sql->query("SELECT *, IF(FleetOwner!=P.Owner,1,0) AS Siege FROM NC_Planet P JOIN NC_Map M ON M.SID=P.SID WHERE FleetOwner=$pid AND (Vpr>0 OR Drn>0 OR `Int`>0 OR Fr>0 OR Bs>0 OR CS>0 OR Tr>0)");
}

function fleet_get_intransit(&$sql, $pid)
{
    return $sql->query("SELECT F.*, P.Ring, IF(M.Name IS NULL,\"Gateway jump\",M.Name) AS Name FROM NC_FleetMovement F LEFT JOIN NC_Planet P ON P.PLID=F.Target LEFT JOIN NC_Map M ON P.SID=M.SID WHERE F.Owner=$pid ORDER BY ETA");
}

?>
