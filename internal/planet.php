<?php

include_once("./internal/error.php");
include_once("./internal/common.php");
include_once("./internal/building.php");
include_once("./internal/race.php");
include_once("./internal/artefact.php");
include_once("./internal/log.php");

// Planet semi-class

function planet_get_name(&$sql, $plid)
{
    $plid=makeinteger($plid);
    $A=$sql->query("SELECT Name, Ring FROM NC_Map M JOIN NC_Planet P ON M.SID=P.SID WHERE PLID=$plid");
    return $A[0]['Name'] . ' ' . $A[0]['Ring'];
}

function planet_get_custom_name(&$sql, $plid)
{
    $plid=makeinteger($plid);
    $A=$sql->query("SELECT CustomName FROM NC_Planet P WHERE PLID=$plid");
    return $A[0]['CustomName'];
}

function planet_multi_id()
{
$GLOBALS['Error']=new CError("Multiple planets with provided ID","Uniqness of ID value has been compromised. Cannot reference planet",2);
}

function planet_wrong_owner()
{
$GLOBALS['Error']=new CError("Invalid owner","You are not owning the selected planet, thus you cannot build there",0);
}

function planet_under_siege()
{
$GLOBALS['Error']=new CError("Planet under siege","You cannot spend PPs on planet under siege",0);
}

function planet_generate_type(&$sql, $ring, $culture, $Log)
{
    $ring=makeinteger($ring);
    if ($ring==0)
	return 22; //star corona
    $random=mt_rand(0,99);
    log_update($sql, $Log, 3, $random);
    $a=array();
    if ($culture==true) //must be colonisable without technology
    {
/*
    Planet types:
    1 - Gaia,    2 - Aphrodite,    3 - Hephaestus,    4 - Zeus,    9 - Coeus,    18 - Hemera
*/	
	$a[1][1]=1; $a[1][2]=10; $a[1][3]=70; $a[1][4]=16; $a[1][9]=1; $a[1][18]=2;
	$a[2][1]=4; $a[2][2]=15; $a[2][3]=60; $a[2][4]=19; $a[2][9]=1; $a[2][18]=1;
	$a[3][1]=8; $a[3][2]=20; $a[3][3]=47; $a[3][4]=22; $a[3][9]=1; $a[3][18]=2;
	$a[4][1]=15; $a[4][2]=30; $a[4][3]=25; $a[4][4]=25; $a[4][9]=3; $a[4][18]=2;
	$a[5][1]=25; $a[5][2]=33; $a[5][3]=7; $a[5][4]=25; $a[5][9]=8; $a[5][18]=2;
	$a[6][1]=25; $a[6][2]=35; $a[6][3]=3; $a[6][4]=20; $a[6][9]=15; $a[6][18]=2;
	$a[7][1]=25; $a[7][2]=37; $a[7][3]=2; $a[7][4]=15; $a[7][9]=20; $a[7][18]=1;
	$a[8][1]=22; $a[8][2]=33; $a[8][3]=2; $a[8][4]=10; $a[8][9]=31; $a[8][18]=1;
	$a[9][1]=15; $a[9][2]=22; $a[9][3]=2; $a[9][4]=9; $a[9][9]=51; $a[9][18]=1;
	$a[10][1]=10; $a[10][2]=17; $a[10][3]=1; $a[10][4]=5; $a[10][9]=66; $a[10][18]=1;
	$a[11][1]=5; $a[11][2]=15; $a[11][3]=1; $a[11][4]=3; $a[11][9]=75; $a[11][18]=1;
	$a[12][1]=4; $a[12][2]=11; $a[12][3]=1; $a[12][4]=2; $a[12][9]=81; $a[12][18]=1;
	$a[13][1]=3; $a[13][2]=9; $a[13][3]=1; $a[13][4]=2; $a[13][9]=84; $a[13][18]=1;
	$a[14][1]=3; $a[14][2]=8; $a[14][3]=1; $a[14][4]=2; $a[14][9]=85; $a[14][18]=1;
	$a[15][1]=2; $a[15][2]=6; $a[15][3]=1; $a[15][4]=2; $a[15][9]=88; $a[15][18]=1;
	$a[16][1]=1; $a[16][2]=5; $a[16][3]=1; $a[16][4]=2; $a[16][9]=90; $a[16][18]=1;
	$a[17][1]=1; $a[17][2]=3; $a[17][3]=1; $a[17][4]=1; $a[17][9]=93; $a[17][18]=1;
	$a[18][1]=1; $a[18][2]=3; $a[18][3]=1; $a[18][4]=1; $a[18][9]=93; $a[18][18]=1;
	$a[19][1]=1; $a[19][2]=3; $a[19][3]=1; $a[19][4]=1; $a[19][9]=93; $a[19][18]=1;
    }
else //don't have to be colonisable
    {
/*
    Planet types:
    1 - Gaia,	2 - Aphrodite,  3 - Hephaestus,  4 - Zeus,    5 - Poseidon,    6 - Triton
    7 - Helios,	8 - Hyperion,	9 - Coeus,	10 - Athena,	11 - Ares,	12 - Hades
    13 - Charon, 14 - Nyx,	15 - Hecate, 	16 - Ceto, 	17 - Selene,	18 - Hemera,
    19 - Metis,	20 - Eris,	21 - Chaos,	22 - Corona
*/	
//	             0,  1,  2,  3,  4,  5,  6,  7,  8,  9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22
	$a[ 1]=array(0,  0,  0, 13,  6,  0,  0,  0, 20,  0,  5, 22, 19,  4,  0,  0,  0, 10,  1,  0,  0,  0,  0);
	$a[ 2]=array(0,  0,  1, 11,  9,  7,  0,  0,  4,  0,  8, 21, 18,  8,  0,  0,  0, 12,  1,  0,  0,  0,  0);
	$a[ 3]=array(0,  1,  2,  9, 10, 13,  0,  0,  1,  0,  6, 12, 15, 13,  0,  0,  0, 17,  1,  0,  0,  0,  0);
	$a[ 4]=array(0,  2,  3,  5,  7, 15,  0,  0,  0,  0, 12,  8, 10, 18,  0,  0,  0, 19,  1,  0,  0,  0,  0);
	$a[ 5]=array(0,  3,  5,  4,  4, 17,  0,  0,  1,  0, 14,  7,  9, 16,  0,  0,  0, 18,  1,  0,  1,  0,  0);
	$a[ 6]=array(0,  6,  8,  3,  4, 13,  0,  2,  0,  0, 15,  7,  8, 14,  0,  0,  1, 14,  2,  1,  2,  0,  0);
	$a[ 7]=array(0,  6,  8,  2,  1, 10,  2,  4,  0,  0, 13,  6,  7, 13,  0,  0,  5, 12,  1,  8,  2,  0,  0);
	$a[ 8]=array(0,  5,  5,  0,  1,  6,  3,  8,  0,  3, 12,  2,  3,  7,  3,  0,  9, 10,  1, 15,  7,  0,  0);
	$a[ 9]=array(0,  4,  4,  0,  0,  2,  4, 14,  0,  4,  9,  1,  1,  2,  4,  0, 13,  9,  1, 19,  9,  0,  0);
	$a[10]=array(0,  2,  2,  0,  0,  0,  7, 16,  0,  2,  7,  1,  1,  1,  5,  1, 16,  2,  1, 23, 13,  0,  0);
	$a[11]=array(0,  1,  1,  0,  0,  0,  4, 20,  0,  0,  5,  0,  1,  0,  8,  1, 19,  1,  1, 23, 15,  0,  0);
	$a[12]=array(0,  0,  1,  0,  0,  0,  9, 13,  0,  0,  3,  0,  0,  0,  9,  1, 23,  0,  1, 22, 18,  0,  0);
	$a[13]=array(0,  0,  0,  0,  0,  0,  7, 12,  0,  0,  2,  0,  0,  0, 10,  1, 24,  0,  1, 23, 20,  0,  0);
	$a[14]=array(0,  0,  0,  0,  0,  0,  6,  3,  0,  0,  1,  0,  0,  0, 23,  1, 21,  0,  0, 20, 25,  0,  0);
	$a[15]=array(0,  0,  0,  0,  0,  0,  4,  3,  0,  0,  1,  0,  0,  0, 28,  1, 18,  0,  0, 15, 30,  0,  0);
	$a[16]=array(0,  0,  0,  0,  0,  0,  4,  3,  0,  0,  0,  0,  0,  0, 30,  1, 16,  0,  0, 14, 32,  0,  0);
	$a[17]=array(0,  0,  0,  0,  0,  0,  1,  1,  0,  0,  0,  0,  0,  0, 35,  1, 14,  0,  0, 13, 35,  0,  0);
	$a[18]=array(0,  0,  0,  0,  0,  0,  1,  1,  0,  0,  0,  0,  0,  0, 35,  1, 14,  0,  0, 13, 35,  0,  0);
	$a[19]=array(0,  0,  0,  0,  0,  0,  1,  1,  0,  0,  0,  0,  0,  0, 35,  1, 14,  0,  0, 13, 35,  0,  0);
    }
    foreach ($a[$ring] as $k => $v)
    {
	if ($v>$random)
	    return $k;
	else
	    $random-=$v;
    }
    return 21; //if something went wrong
}

function planet_type_get_base_cost(&$sql, $type)
{
    $type=makeinteger($type);
    $A=$sql->query("SELECT BaseCost FROM NC_PlanetType WHERE PTID=$type");
    return $A[0]['BaseCost'];
}

function planet_add_random(&$sql) {
  $sid=starsystem_get_empty($sql,1);
  if ($sid<0) {
    starsystem_ring_create($sql);
    $i=starsystem_get_empty($sql);
		if ($i<0)
			return 0;
  }
	$plid=planet_create($sql,$sid,0,0,0);
	for ($series=2; $series<5; ++$series) {
		$sid=starsystem_get_empty($sql,$series);
		if ($sid>0) {
			$plid=planet_create($sql,$sid,0,0,0);
		}
	}
}

function planet_mark_output(&$sql, $pid, $PopH, $PPH) {
  $pid=makeinteger($pid);
  $PopH=makereal($PopH);
  $PPH=makereal($PPH);
  $sql->query("UPDATE NC_Planet SET PPProd=$PPH, PopProd=$PopH WHERE PLID=$pid");
}

function planet_create(&$sql, $sid, $type=0, $PP=0, $pid=0)
{
	$Log=log_entry($sql,"planet cr",$sid);
	$PP=makeinteger($PP);
	$sid=makeinteger($sid);
	$type=makeinteger($type);
	$W=$sql->query("SELECT Ring FROM NC_Planet WHERE SID=$sid");
	$r=19;
	$Q=array(false,false,false,false,false,false,false,false,false,false,false,false,false,false,false, false, false, false, false);
	foreach ($W as $ps)
	{
		//	print_r(makeinteger($ps['Ring']));
		$Q[makeinteger($ps['Ring'])]=true;
	}
	for ($i=0; $i<=18; ++$i)
	{
		if ($Q[$i]==false)
		{
			$r=$i;
			break;
		}
	}
	if ($r>=19)
	{
		log_result($sql,$Log,"not found");
		return 0;
	}
	log_update($sql, $Log, 2, $r);
	$planetType=($type>0?$type:(planet_generate_type($sql, $r,($type==-1),$Log)));
	log_update($sql, $Log, 4, $planetType);
	$bC=planet_type_get_base_cost($sql, $planetType);
	$sql->query("INSERT INTO NC_Planet VALUES (NULL, $sid, " . 
			" 0, " . // owner
			" \"\", " . //name
			' ' . $planetType . ', ' . // type
			" 21, $bC, $bC, $bC, $bC, $bC, 10, " . //remains
			" 0, 0, 0, 0, 0, 0, 0, " . //pop+buildings
			" \"\", " . //gateway
			" $r, " . //ring
			" $PP, " . //PP
			" 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, " . //ships
			" 0," . //fleet owner
			" 0, 0, 0, 0, 0)"); //embassy, spacestation, STx, PPProd, PopProd
	$i=$sql->query("SELECT LAST_INSERT_ID() AS A");
	$PLID=makeinteger($i[0]['A']);
	$pid=makeinteger($pid);
	if ($pid>0)
	{
		$Now=EncodeNow();
		$sql->query("INSERT INTO NC_PlanetCreated VALUES ($PLID, $Now, $pid)");
	}
	log_result($sql,$Log,"OK");
	return $PLID;
}

function planet_is_colonisable(&$sql, $plid)
{
    $plid=makeinteger($plid);
    $a=$sql->query("SELECT Owner FROM NC_Planet WHERE PLID=$plid");
    return ($a[0]['Owner']==false);
}

function planet_building_build(&$sql, $user, $plid, $building, $value) 
{
    $Log=log_entry($sql,"build",planet_get_name($sql,$plid),$building,$value);
    $plid=makeinteger($plid);
    $value=makereal($value);
    $building=makestring($building);
    $buildingremain=$building . "Remain";
//    $HTx=0.0;
    $STx=0.0;
//    global $BuildHTx;
    global $BuildSTx;
//    global $DestroyHTx;
    global $DestroySTx;
    
    
    if (Lock($sql, $plid)!=1)
    {
	log_result($sql,$Log,"grab failed");
	return false;
	}
    $planets=$sql->query("SELECT * FROM NC_Planet P JOIN NC_PlanetType PT ON P.Type=PT.PTID WHERE PLID=$plid");
    if (count($planets)!=1)
    {
	planet_multi_id();
	log_result($sql,$Log,"multiple");
	Unlock($sql, $plid);
	return false;
    }
    $planet=$planets[0];
    if (($user!=0) and ($user!=$planet['Owner']))
    {
	planet_wrong_owner();
	Unlock($sql, $plid);
	log_result($sql,$Log,"wrong owner");
	return false;
    }
    if ($planet['FleetOwner']!=$user and $planet['FleetOwner']!=0)
    {
	planet_under_siege();
	Unlock($sql, $plid);
	log_result($sql,$Log,"siege");
	return false;
    }
    if ($building!="Starbase")
      $BaseCost=$planet['BaseCost'];
    else
      $BaseCost=10;
    $planet[$buildingremain]-=$value;
    while ($planet[$buildingremain]<=0)
	{
	    $planet[$building]++;
//	    $HTx+=$BuildHTx[$building];
	    $STx+=$BuildSTx[$building]; //*$planet['ToxicStability']/100;
	    $planet[$buildingremain]+=building_points_for_lvl($planet[$building]+1,$BaseCost);
	}
	
    //in case of negative build (damage)
    while ($planet[$buildingremain]>building_points_for_lvl($planet[$building]+1,$BaseCost))
	{
	    if ($planet[$building]==0)
		$planet[$buildingremain]=building_points_for_lvl(1,$BaseCost);
	    $planet[$buildingremain]-=building_points_for_lvl($planet[$building]+1,$BaseCost);
	    $planet[$building]--;
//	    $HTx+=$DestroyHTx[$building];
	    $STx+=$DestroySTx[$building]; //*$planet['ToxicStability']/100;
	}
    $sql->query("UPDATE NC_Planet SET {$buildingremain}={$planet[$buildingremain]}, " .
				"{$building}={$planet[$building]}, STx=STx+$STx WHERE PLID=$plid");
    Unlock($sql, $plid);
    log_result($sql,$Log,"OK");
    return true;
}

function planet_building_reset(&$sql, $plid, $building) //No lock here !!!
{
    $building=makestring($building);
    $plid=makeinteger($plid);
    $planets=$sql->query("SELECT P.{$building}, PT.BaseCost FROM NC_Planet P JOIN NC_PlanetType PT ON PT.PTID=P.Type WHERE P.PLID=$plid");
    if (count($planets)!=1)
	return false;
    $planet=$planets[0];
    $remain=building_points_for_lvl($planet[$building],$planet['BaseCost']);
    $sql->query("UPDATE NC_Planet SET {$building}Remain={$remain} WHERE PLID=$plid");
    return true;
}

function planet_build_RP(&$sql, $user, $plid, $building, $amount)
{
//    print_r($building);
    $Log=log_entry($sql,"build rp",$plid,$building,$amount);
    if ($building=="Starbase")
    {
	log_result($sql,$Log,"inv object");
	return false;
	}
    if (player_get_RP($sql, $user)<$amount)
    {
	log_result($sql,$Log,"wrong amount");
	return false;
	}
    $building=makestring($building);
    $buildingremain=$building . "Remain";
    $plid=makeinteger($plid);
    if (Lock($sql, $plid)!=1)
    {
	log_result($sql,$Log,"grab failed");
	return false;
    }
    $planets=$sql->query("SELECT P.{$building}, P.$buildingremain, PT.BaseCost, PT.ToxicStability FROM NC_Planet P JOIN NC_PlanetType PT ON PT.PTID=P.Type WHERE P.PLID=$plid");
    $blvl=$planets[0][$building];
		if ($blvl>100) {
			return false;
		}
    $bperc=1-$planets[0][$buildingremain]/building_points_for_lvl($blvl+1,$planets[0]['BaseCost']);
    $nbperc=$bperc+$amount*0.1;
    $nblvl=$blvl+floor($nbperc);
    $nbperc=$nbperc-floor($nbperc);
    if ($nbperc>0.999)
    {
	$nbperc=0;
	++$nblvl;
    }

    global $BuildSTx;
    $STx=$BuildSTx[$building]*($nblvl-$blvl); //*$planets[0]['ToxicStability']/100;

    $nbperc=(1-$nbperc)*building_points_for_lvl($nblvl+1,$planets[0]['BaseCost']);
    $sql->query("UPDATE NC_Planet SET {$building}=$nblvl, $buildingremain=$nbperc, STx=STx+$STx WHERE PLID=$plid");
    artefact_buy_instant($sql, $user, 0, -$amount);
    Unlock($sql, $plid);
    log_result($sql,$Log,"OK");
    return true;
}

function planet_update(&$sql, $plid, $urban, $growthmod, $prodmod, $time)
{

/*    echo "Planet update<br/>";
    echo "Planetid:$plid<br/>";
    echo "Maxpop:$maxpop<br/>";
    echo "Growthmod:$growthmod<br/>";
    echo "Prodmod:$prodmod<br/>";
    echo "Time:$time<br/>";*/
    $plid=makeinteger($plid);
    if (Lock($sql, $plid)!=1)
	return false;
    $planets=$sql->query("SELECT * FROM NC_Planet P JOIN NC_PlanetType PT ON PT.PTID=P.Type WHERE PLID=$plid");
    if (count($planets)!=1)
	{
	planet_multi_id();
	Unlock($sql, $plid);
	return false;
	}
    $planet=$planets[0];

    //Pollution update
    $STx=-40*$time;
    global $buildings;
    global $WorkSTx;
    foreach ($buildings as $building)
    {
	if ($building!="Refinery")
	    $STx+=$WorkSTx[$building]*$planet[$building]*pow(0.9995,$urban*$urban)*$time;
	else
	    $STx+=$WorkSTx[$building]*$planet[$building]*$time; // (ref*coefficient -1)*time
    }
    $STx+=$WorkSTx["Population"]*$planet["Population"]*pow(0.9995,$urban*$urban)*$time;
    $STx*=$planet['ToxicStability']/100;
		$STx-=$planet['Refinery']*$time;
    
    $STx=max(0,$planet['STx']+$STx);
    
    $TxE=pow(0.995,floor($STx/1000));

    //if planet under siege - no update
    if ($planet['FleetOwner']!=0 and $planet['FleetOwner']!=$planet['Owner'])
    {
	$sql->query("UPDATE NC_Planet SET STx=$STx, PopProd=0, PPProd=0 WHERE PLID=$plid");
    	Unlock($sql, $plid);
	return true;
	}

    settype($planet["Population"],"integer");

    

    //Population update
    $PopH=($planet["Farm"]+1)*$growthmod*($planet['Growth']/100)*$TxE*(1-($planet['Population']-$urban)/100);
    $planet["PopulationRemain"]-=$PopH*$time;
    while ($planet["PopulationRemain"]<0)
	{
	    $planet["Population"]++;
	    $planet["PopulationRemain"]+=growth_points_for_lvl($planet["Population"]+1);
	}
	
    
    //Production
    settype($planet["PP"],"float");
    $PPH=($planet["Population"]+$planet["Factory"])*$prodmod*($planet['Production']/100)*$TxE;
    $planet["PP"]+=$PPH*$time*$TxE;
    

    //Update the database
    $sql->query("UPDATE NC_Planet " .
		    "SET Population={$planet['Population']}, " .
		    "PopulationRemain={$planet['PopulationRemain']}, " .
		    "PP={$planet['PP']}, PopProd=$PopH, PPProd=$PPH, " .
		    "STx=$STx " .
		"WHERE PLID=$plid");
    Unlock($sql, $plid);
    return true;
}

function planet_spend_pp(&$sql, $plid, $amount)
{
    $plid=makeinteger($plid);
    $amount=makeinteger($amount);
    $planets=$sql->query("SELECT PP FROM NC_Planet WHERE PLID=$plid");
    $PP=$planets[0]['PP'];
    settype($PP,"float");
    if ($PP<$amount)
	return false;
    $sql->query("UPDATE NC_Planet SET PP=IF(FleetOwner=Owner OR FleetOwner=0,PP-$amount,PP) WHERE PLID=$plid");
    return true;
}

function planet_add_pp(&$sql, $plid, $amount)
{
    $plid=makeinteger($plid);
    $amount=makeinteger($amount);
    $sql->query("UPDATE NC_Planet SET PP=PP+$amount WHERE PLID=$plid");
}

function planet_pop_kill(&$sql, $plid, &$transporters)
{
    $Log=log_entry($sql,"popkill",planet_get_name($sql,$plid),$transporters);
    if ($transporters<0)
    {
	log_result($sql,$Log,"wrong amount");
	return false;
	}
    $plid=makeinteger($plid);
    $planets=$sql->query("SELECT * FROM NC_Planet WHERE PLID=$plid");
    if (count($planets)!=1)
    {
	log_result($sql,$Log,"not found");
	return false;
	}
    $planet=$planets[0];
    $eff=min($transporters,$planet["Population"]);
    $transporters-=$eff;
    $planet["Population"]-=$eff;
    global $DestroySTx;
    $planet['STx']=$planet['STx']+$DestroySTx['population']*$eff;
    $returnus=false;
    if ($planet["Population"]==0)
    {
	$returnus=true;
	$planet["Population"]=1;
    }
    $remain=growth_points_for_lvl($planet["Population"]+1);
    $sql->query("UPDATE NC_Planet SET PopulationRemain=$remain, " .
				    "Population={$planet['Population']}, " .
				    "STx={$planet['STx']} " .
				"WHERE PLID=$plid");
    log_result($sql,$Log,"OK");
    return $returnus;
}

function planet_conquer(&$sql, $plid, $newowner)
{
    $Log=log_entry($sql,"conquer",planet_get_name($sql,$plid));
    $newowner=makeinteger($newowner);
    log_set_owner($sql, $Log, account_get_id_from_pid($sql,$newowner));
    $plid=makeinteger($plid);
    if (Lock($sql, $plid)!=1)
    {
	log_result($sql,$Log,"grab failed");
	return false;
	}
    $planets=$sql->query("SELECT * FROM NC_Planet P JOIN NC_PlanetType PT ON PT.PTID=P.Type WHERE P.PLID=$plid");
    if (count($planets)!=1)
    {
	log_result($sql,$Log,"not found");
	return false;
	}
    $planet=$planets[0];
    global $buildings;
//    global $DestroyHTx;
    global $DestroySTx;
    $STx=0.0;
//    $HTx=0.0;
    foreach ($buildings as $building)
    {
	$buildingremain=$building . "Remain";
	$bld=$planet[$building]+1.0-$planet[$buildingremain]/building_points_for_lvl($planet[$building]+1,$planet['BaseCost']);
	$bld=$bld-$bld*mt_rand(10,20)*0.01-1.0;
	if ($bld<0) $bld=0;
	$bldf=floor($bld);
	$STx+=($planet[$building]-$bldf)*$DestroySTx[$building]*$planet['ToxicStability']/100;
//	$HTx+=($planet[$building]-$bld)*$DestroyHTx[$building];
	$planet[$building]=$bldf;
	$planet[$buildingremain]=ceil((1-($bld-$bldf))*building_points_for_lvl($planet[$building]+1,$planet['BaseCost']));
    }
    $planet["Owner"]=$newowner;
    $sql->query("UPDATE NC_Planet SET " .
		    "Population=1, PopulationRemain=21, " . 
		    "Farm={$planet['Farm']}, FarmRemain={$planet['FarmRemain']}, " .
		    "Factory={$planet['Factory']}, FactoryRemain={$planet['FactoryRemain']}, " .
		    "Cybernet={$planet['Cybernet']}, CybernetRemain={$planet['CybernetRemain']}, " .
		    "Lab={$planet['Lab']}, LabRemain={$planet['LabRemain']}, " .
		    "Refinery={$planet['Refinery']}, RefineryRemain={$planet['RefineryRemain']}, " .
		    "Starbase=0, StarbaseRemain={$planet['BaseCost']}," .
		    "PP=PP/10, " .
		    "STx=STx+$STx, " .
//		    "Starbase=0, DS=0, DSRemain=0, CR=0, CRRemain=0, BS=0, BSRemain=0, TR=0, TRRemain=0, CS=0, CSRemain=0, ".
		    "Owner={$planet['Owner']} " .
		"WHERE PLID=$plid");
    Unlock($sql,$plid);
    log_result($sql,$Log,"OK");
    return true;
}

function planet_check_ship_remain(&$sql, $plid, $eco) //No lock here !!
{
    $newowner=makeinteger($newowner);
    $plid=makeinteger($plid);
    $planets=$sql->query("SELECT * FROM NC_Planet WHERE PLID=$plid");
    if (count($planets)!=1)
	return false;
    $planet=$planets[0];
    $do1=TrimUp($planet['IntRemain'],Int_points($eco));
    $do2=TrimUp($planet['FrRemain'],Fr_points($eco));
    $do3=TrimUp($planet['BsRemain'],Bs_points($eco));
    $do4=TrimUp($planet['VprRemain'],Vpr_points($eco));
    $do5=TrimUp($planet['DrnRemain'],Drn_points($eco));
    if ($do1 or $do2 or $do3 or $do4 or $do5)
        $sql->query("UPDATE NC_Planet SET IntRemain={$planet['IntRemain']}, " .
			"FrRemain={$planet['FrRemain']}, " .
			"BsRemain={$planet['BsRemain']} " .
			"VprRemain={$planet['VprRemain']} " .
			"DrnRemain={$planet['DrnRemain']} " .
			"WHERE PLID=$plid");	    
    return true;
}

function planet_build_ship(&$sql, $pid, $plid, $ship, $amount)
{
    $Log=log_entry($sql,"build ship",planet_get_name($sql,$plid), $ship, $amount);
    $plid=makeinteger($plid);
    $pid=makeinteger($pid);
    $ecos=$sql->query("SELECT Engineering FROM NC_Player WHERE PID=$pid");
    $eco=$ecos[0]['Engineering'];
    
    if (Lock($sql, $plid)!=1)
    {
	log_result($sql,$Log,"grab failed");
	return false;
	}
    $planets=$sql->query("SELECT * FROM NC_Planet WHERE PLID=$plid");
    if (count($planets)!=1)
    {
	planet_mutli_id();
	log_result($sql,$Log,"multiple");
	Unlock($sql, $plid);
	return false;
    }
    $planet=$planets[0];
    if ($pid!=$planet['Owner'])
    {
	planet_wrong_owner();
	Unlock($sql, $plid);
	log_result($sql,$Log,"wrong owner");
	return false;
    }
    if ($planet['FleetOwner']!=$pid and $planet['FleetOwner']!=0)
    {
	planet_under_siege();
	Unlock($sql, $plid);
	log_result($sql,$Log,"siege");
	return false;
    }
    $cost=eval("return " . $ship . "_points($eco);");
    $fullybuild=floor(($planet[$ship . "Remain"]+$amount)/$cost);
    $planet[$ship]+=$fullybuild;
    
    $remain=$planet[$ship . "Remain"]+$amount-$fullybuild*$cost;
    $sql->query("UPDATE NC_Planet SET `{$ship}`={$planet[$ship]}, {$ship}Remain={$remain}, FleetOwner={$pid} WHERE PLID=$plid");
    Unlock($sql, $plid);
    log_result($sql,$Log,"OK");
    return true;
}


function planet_gateway_available(&$sql, $plid, $forpid)
{
    $plid=makeinteger($plid);
    $forpid=makeinteger($forpid);
    $R=$sql->query("SELECT Owner, Gateway FROM NC_Planet WHERE PLID=$plid");
    if (count($R)!=1)
	return false;
    return $R[0]['Owner']==$forpid and $R[0]['Gateway']!="";
}

function planet_gateway_find(&$sql, $gateway)
{
    $gateway=makequotedstring($gateway);
    $F=$sql->query("SELECT PLID FROM NC_Planet WHERE Gateway=$gateway");
    if (count($F)==0)
	return 0;
    else return $F[mt_rand(0,count($F)-1)]['PLID'];
}

function planet_gateway_change_code(&$sql, $pid, $plid, $code)
{
    $pid=makeinteger($pid);
    $plid=makeinteger($plid);
    $code=makequotedstring($code);
    $sql->query("UPDATE NC_Planet SET Gateway=$code WHERE PLID=$plid AND Owner=$pid AND Gateway!=\"\"");
}

function planet_gateway_build(&$sql, $pid, $plid)
{
    $Log=log_entry($sql,"build",planet_get_name($sql,$plid),"Gateway",6144);
    $pid=makeinteger($pid);
    $plid=makeinteger($plid);
    if (Lock($sql, $plid)!=1)
    {
	log_result($sql,$Log,"grab failed");
	return "Internal error (grab failed)";
    }
    $Ps=$sql->query("SELECT PP, Gateway, Owner, FleetOwner FROM NC_Planet WHERE PLID=$plid");
    if (count($Ps)!=1)
    {
        Unlock($sql, $plid);
	log_result($sql,$Log,"not found");
	return "Planet not found";
    }
    $P=$Ps[0];
    if ($P['Owner']!=$pid)
    {
        Unlock($sql, $plid);
	log_result($sql,$Log,"wrong owner");
	return "You have no control over chosen planet";
    }
    if ($P['Owner']!=$P['FleetOwner'] and $P['FleetOwner']!=0)
    {
        Unlock($sql, $plid);
	log_result($sql,$Log,"siege");
	return "Planet under siege";
    }
    if ($P['PP']<6144)
    {
        Unlock($sql, $plid);
	log_result($sql,$Log,"no money");
	return "Not enough PPs";
    }
    if ($P['Gateway']!="")
    {
        Unlock($sql, $plid);
	log_result($sql,$Log,"already");
	return "Gateway already present on selected planet";
    }
    $RandomString=makequotedstring(str_shuffle("abdegilmnorstuyzc"));
    $sql->query("UPDATE NC_Planet SET Gateway=$RandomString, PP=PP-6144 WHERE PLID=$plid");
    log_result($sql,$Log,"OK");
    Unlock($sql, $plid);
}

function planet_construction_build(&$sql, $pid, $plid, $name, $cost)
{
    $name=makestring($name);
    $cost=makeinteger($cost);
    $Log=log_entry($sql,"build",planet_get_name($sql,$plid),"$name",$cost);
    $pid=makeinteger($pid);
    $plid=makeinteger($plid);
    if (Lock($sql, $plid)!=1)
    {
	log_result($sql,$Log,"grab failed");
	return "Internal error (grab failed)";
    }
    $Ps=$sql->query("SELECT PP, $name, Owner, FleetOwner FROM NC_Planet WHERE PLID=$plid");
    if (count($Ps)!=1)
    {
        Unlock($sql, $plid);
	log_result($sql,$Log,"not found");
	return "Planet not found";
    }
    $P=$Ps[0];
    if ($P['Owner']!=$pid)
    {
        Unlock($sql, $plid);
	log_result($sql,$Log,"wrong owner");
	return "You have no control over chosen planet";
    }
    if ($P['Owner']!=$P['FleetOwner'] and $P['FleetOwner']!=0)
    {
        Unlock($sql, $plid);
	log_result($sql,$Log,"siege");
	return "Planet under siege";
    }
    if ($P['PP']<$cost)
    {
        Unlock($sql, $plid);
	log_result($sql,$Log,"no money");
	return "Not enough PPs";
    }
    if ($P[$name]!=0)
    {
        Unlock($sql, $plid);
	log_result($sql,$Log,"already");
	return "$name already present on selected planet";
    }
    $sql->query("UPDATE NC_Planet SET $name=1, PP=PP-$cost WHERE PLID=$plid");
    log_result($sql,$Log,"OK");
    Unlock($sql, $plid);
}


function planet_get_owner(&$sql, $plid)
{
    $plid=makeinteger($plid);
    $A=$sql->query("SELECT Owner FROM NC_Planet WHERE PLID=$plid");
    return $A[0]['Owner'];
}

function planet_get_position(&$sql, $plid)
{
    $A=$sql->query("SELECT M.SID, X, Y, Ring FROM NC_Map M JOIN NC_Planet P ON M.SID=P.SID WHERE PLID=$plid");
    return $A[0];
}

function planet_list(&$sql, $pid)
{
    $pid=makeinteger($pid);
    $A=$sql->query("SELECT PT.*, Pl.*, S.Name FROM NC_Planet Pl" .
			" JOIN NC_PlanetType PT ON PT.PTID=Pl.Type" .
			" LEFT JOIN NC_Map S ON S.SID=Pl.SID" .
			" WHERE Pl.Owner = $pid ORDER BY Population DESC, PopulationRemain ASC");
    return $A;
}

function planet_get_nick(&$sql, $plid)
{
    $plid=makeinteger($plid);
    $a=$sql->query("SELECT P.TAG, A.Nick FROM NC_Planet Pl" .
		" LEFT JOIN NC_Player P ON Pl.Owner=P.PID" .
		" LEFT JOIN NC_Account A ON A.PID=P.PID" .
		" WHERE Pl.PLID=$plid");
    return $a[0];
}


function planet_index(&$sql, $pid, $id)
{
    $pid=makeinteger($pid);
    $id=makeinteger($id);
    if ($id<0) $id=0;
    $from=max(0,$id-1);
    $cnt=$id+1-$from+1;
    $A=$sql->query("SELECT PLID FROM NC_Planet" .
			" WHERE Owner = $pid ORDER BY Population DESC, PopulationRemain ASC" .
			" LIMIT $from, $cnt");
    if ($id>0)
	{
	$R['prev']=$A[0]['PLID'];
	$R['here']=$A[1]['PLID'];
	$R['next']=$A[2]['PLID'];
	}
    else
	{
	$R['here']=$A[0]['PLID'];
	$R['next']=$A[1]['PLID'];
	}
    return $R;
}

function planet_get_all(&$sql, $plid)
{
    $plid=makeinteger($plid);
    $A=$sql->query("SELECT PT.*, P.*, M.Name FROM NC_Planet P JOIN NC_PlanetType PT ON PT.PTID=P.Type JOIN NC_Map M ON M.SID=P.SID WHERE $plid=PLID");
    return $A[0];
}

function planet_get_pop(&$sql, $plid)
{
    $plid=makeinteger($plid);
    $A=$sql->query("SELECT Population FROM NC_Planet WHERE PLID=$plid");
    return $A[0]['Population'];
}

function planet_get_id(&$sql, $sid, $ring)
{
    $sid=makeinteger($sid);
    $ring=makeinteger($ring);
    $A=$sql->query("SELECT PLID FROM NC_Planet WHERE SID=$sid AND Ring=$ring");
    return $A[0]['PLID'];
}

function planet_get_id_from_position(&$sql, $X, $Y, $ring)
{
    $X=makeinteger($X);
    $Y=makeinteger($Y);
    $ring=makeinteger($ring);
    $A=$sql->query("SELECT P.PLID FROM NC_Planet P JOIN NC_Map M ON M.SID=P.SID WHERE M.X=$X AND M.Y=$Y AND Ring=$ring");
    return $A[0]['PLID'];
}

function planet_sum_PP(&$sql, $pid)
{
    $pid=makeinteger($pid);
    $A=$sql->query("SELECT SUM(IF(FleetOwner=0 OR FleetOwner=Owner,PP,PP/2)) AS A FROM NC_Planet WHERE Owner=$pid");
    return $A[0]['A'];
}

function planet_delete(&$sql, $plid)
{
    $plid=makeinteger($plid);
    $sql->query("DELETE FROM NC_FleetMovement WHERE Target=$plid");
    $sql->query("DELETE FROM NC_Planet WHERE PLID=$plid");
    $sql->query("DELETE FROM NC_PlanetCreated WHERE PLID=$plid");
}

function planet_change_custom_name(&$sql, $plid, $pid, $name)
{
    $plid=makeinteger($plid);
    $pid=makeinteger($pid);
    $name=makequotedstring(htmlentities($name));
    $sql->query("UPDATE NC_Planet SET CustomName=$name WHERE PLID=$plid and Owner=$pid");
}

function planet_technology_required(&$sql, $plid) {
    $plid=makeinteger($plid);
    $Anss=$sql->query("SELECT PT.TechReq, PT.CultureSlot FROM NC_Planet P JOIN NC_PlanetType PT ON PT.PTID=P.Type WHERE P.PlID=$plid");
    return $Anss[0];
}

function planet_get_types(&$sql) {
    return $sql->query("SELECT * FROM NC_PlanetType");
}

function planet_get_ext_types(&$sql) {
    return $sql->query("SELECT PT.*, T.Name FROM NC_PlanetType PT LEFT JOIN NC_TechList T ON T.TechID=PT.TechReq");
}

function planet_decompose_colony_ships(&$sql, $pid, $plid) {
    $pid=makeinteger($pid);
    $plid=makeinteger($plid);
    if (Lock($sql, $plid)!=1) 
	return "Unable to lock on the planet";
    $sql->query("UPDATE NC_Planet SET PP=PP+15*CS, CS=0 WHERE PLID=$plid AND Owner=$pid AND (FleetOwner=Owner OR FleetOwner=0)");
    Unlock($sql, $plid);
}
?>
