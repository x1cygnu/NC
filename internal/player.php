<?php

include_once("./internal/building.php");
include_once("./internal/account.php");
include_once("./internal/starsystem.php");
include_once("./internal/planet.php");
include_once("./internal/news.php");
include_once("./internal/race.php");
include_once("./internal/log.php");
include_once("./internal/armageddon.php");
include_once("./internal/common.php");
include_once("./internal/tech.php");
//Player semi-class

function player_count_buildings(&$sql, $pid, $name)
{
    $R=$sql->query("SELECT count(*) AS C FROM NC_Planet WHERE Owner=$pid AND $name=1");
    return $R[0]['C'];
}

function player_count_points(&$sql, $pid)
{
    $pid=makeinteger($pid);
    $R=$sql->query("SELECT "
		." GREATEST(P.Sensory,20)-20"
		."+GREATEST(P.Engineering,20)-20"
		."+GREATEST(P.Warp,20)-20"
		."+GREATEST(P.Physics,20)-20"
		."+GREATEST(P.Mathematics,20)-20"
		."+GREATEST(P.Urban,20)-20"
		."+CultureLvl*2"
		."+PL*3"
		."+VL*3"
		."+(SELECT SUM(Population) FROM NC_Planet Pl WHERE Pl.Owner=P.PID) AS Pts"
		." FROM NC_Player P WHERE PID=$pid");
    return $R[0]['Pts'];
}

function player_get_rank(&$sql, $pid)
{
    $pid=makeinteger($pid);
    $U=$sql->query("SELECT Points, Rank, Countdown FROM NC_Player WHERE PID=$pid");
    return $U[0];
}

function player_get_artefact_use(&$sql, $pid)
{
    $pid=makeinteger($pid);
    $Arts=$sql->query("SELECT Al.Growth, Al.Science, Al.Culture, Al.Production, Al.Speed, Al.Attack, Al.Defence " .
			    "FROM NC_ArtefactList Al " .
			    "JOIN NC_Artefact A ON Al.ARID=A.Artefact " .
			    "WHERE A.PID=$pid AND A.InUse=1");
    if (count($Arts)==1)
	return $Arts[0];
    else
	return array('Biology' => 0, 'Science' => 0, 'Culture' => 0, 'Production' => 0, 'Speed' => 0, 'Attack' => 0, 'Defence' => 0);
}

function player_get_full_race(&$sql, $pid)
{
    $pid=makeinteger($pid);
    $U=$sql->query("SELECT TA, Growth, Science, Culture, Production, Speed, Attack, Defence FROM NC_Player WHERE PID=$pid");
    $Art=player_get_artefact_use($sql, $pid);
    $TA=$U[0]['TA'];
    $P['Growth']=makeinteger(Growth($U[0]['Growth'])*(1+$TA/100)+$Art['Growth']);
    $P['Science']=makeinteger(Science($U[0]['Science'])*(1+$TA/100)+$Art['Science']);
    $P['Culture']=makeinteger(Culture($U[0]['Culture'])*(1+$TA/100)+$Art['Culture']);
    $P['Production']=makeinteger(Production($U[0]['Production'])*(1+$TA/100)+$Art['Production']);
    $P['Speed']=makeinteger(Speed($U[0]['Speed'])+$Art['Speed']);
    $P['Attack']=makeinteger(Attack($U[0]['Attack'])+$Art['Attack']);
    $P['Defence']=makeinteger(Defence($U[0]['Defence'])+$Art['Defence']);
    return $P;
}

function player_get_ta(&$sql, $pid)
{
    $pid=makeinteger($pid);
    $W=$sql->query("SELECT TA FROM NC_Player WHERE PID=$pid");
    return $W[0]['TA'];
}

function player_multiple_id()
{
$GLOBALS['Error']=new CError("Multiple players with provided ID","Uniqness of ID value has been compromised. Cannot reference player",2);
}

function player_get_tag(&$sql,$pid)
{
    $pid=makeinteger($pid);
    $A=$sql->query("SELECT TAG FROM NC_Player WHERE PID=$pid");
    return $A[0]['TAG'];
}

function player_get_given_ta(&$sql, $pid)
{
    $pid=makeinteger($pid);
    $R=$sql->query("SELECT SUM(P.Population*5+POW(GREATEST(P.Population-15.0,0),2))/100 AS X FROM NC_Planet P JOIN NC_PlanetType T ON T.PTID=P.Type WHERE P.Owner=$pid AND T.CultureSlot=1");
    return $R[0]['X'];
}

function player_get_all(&$sql,$pid)
{
$pid=makeinteger($pid);
$result=$sql->query("SELECT * FROM NC_Player P LEFT JOIN NC_Account A ON A.PID=P.PID WHERE P.PID=$pid");
return $result[0];
}

function player_get_tx_control(&$sql, $pid)
{
    $pid=makeinteger($pid);
    $Urbs=$sql->query("SELECT Urban FROM NC_Player WHERE PID=$pid");
    $Urb=makeinteger($Urbs[0]['Urban']);
    if ($Urb<10)
	$Urb=5+floor($Urb/2);
    return round(pow(0.9995,$Urb*$Urb)*100);
}

function player_get_science(&$sql,$pid,$science)
{
    $pid=makeinteger($pid);
    $science=makestring($science);
    $A=$sql->query("SELECT $science FROM NC_Player WHERE PID=$pid");
    return $A[0][$science];
}

function player_get_bio_ranges2(&$sql,$pid)
{
    $pid=makeinteger($pid);
    $A=$sql->query("SELECT X, Y, FLOOR(Sensory/2) AS 'Range' FROM NC_Player P JOIN NC_Map M ON P.HomeSID=M.SID AND P.PID=$pid");
    return $A;
}


function player_get_bio_ranges(&$sql,$pid)
{
    $pid=makeinteger($pid);
    $A=$sql->query("SELECT M.X, M.Y, FLOOR(P2.Sensory/2) AS 'Range' FROM NC_Agreement Agr"
		. " JOIN NC_Player P2 ON (Agr.PID2=P2.PID AND Agr.PID=$pid) OR (Agr.PID=P2.PID AND Agr.PID2=$pid)"
		. " JOIN NC_Map M ON M.SID=P2.HomeSID WHERE Agr.Type=2 AND Agr.Status=2");
    $B=$sql->query("SELECT X, Y, FLOOR(Sensory/2) AS 'Range' FROM NC_Player P JOIN NC_Map M ON P.HomeSID=M.SID AND P.PID=$pid");
    $A[]=$B[0];
    return $A;
}


function player_set_science(&$sql,$pid,$science)
{
    $pid=makeinteger($pid);
    $science=makeinteger($science);
    if ($science>=0 and $science<=5)
	$sql->query("UPDATE NC_Player SET SelectedScience=$science WHERE PID=$pid");
}

function player_get_sciences(&$sql, $pid)
{
    $pid=makeinteger($pid);
    global $sciences;
    $S="SELECT ";
    foreach($sciences as $science)
	$S .= "{$science}, {$science}Remain, ";
    $A=$sql->query($S . "Science, SelectedScience, CultureLvl, CultureRemain, TechDevelop, TechSelected, TechRemain FROM NC_Player WHERE PID=$pid");
    return $A[0];
}

function player_get_sc_gain(&$sql, $pid)
{
    $pid=makeinteger($pid);
    $products=$sql->query("SELECT SUM(P.Cybernet*PT.Culture/100.0) AS C, SUM((P.Lab+P.Population)*PT.Science/100) AS L FROM NC_Planet P JOIN NC_PlanetType PT ON P.Type=PT.PTID WHERE Owner=$pid AND (FleetOwner=0 OR FleetOwner=Owner)");
    return $products[0];
}

function player_update_all(&$sql, $pid)
{
    if (CheckFrozen($sql))
	return false;
    $Race=player_get_full_race($sql, $pid);
    $pid=makeinteger($pid);
    $result=$sql->query("SELECT GET_LOCK(\"danilewski AW player $pid\",10) AS L");
    if ($result[0]['L']==0 or $result[0]['L']=="NULL")
	{
	$GLOBALS['Error']=new CError("Unable to get a lock on a player","Function cannot get a lock on an account in order to update changes.<br>Some other thread is constantly blocking access.",1);
	return false;
	}
	
    //Get player and time elapsed
    $Now=EncodeNow();
    $players=$sql->query("SELECT *, $Now-LastUpdate AS Elapsed FROM NC_Player WHERE PID=$pid");
    if (count($players)!=1)
    {
	player_multiple_id();
        $sql->query("SELECT RELEASE_LOCK(\"danilewski AW player $pid\")");
	return false;
    }
    $player=$players[0];
    
    //Update planets
    $planets=$sql->query("SELECT PLID FROM NC_Planet WHERE Owner=$pid");
    foreach ($planets as $planet)
	planet_update($sql, $planet['PLID'], $player['Urban'], $Race['Growth']/100.0, $Race['Production']/100.0, $player['Elapsed']/3600.0);
	
    //Update Culture
    $product=player_get_sc_gain($sql, $pid);
//    settype($product['C'],"integer");
//    settype($product['L'],"integer");
    $player['CultureRemain']+=$product['C']*($Race['Culture']/100.0)*$player['Elapsed']/3600.0;


    while ($player['CultureRemain']>culture_points_for_lvl($player['CultureLvl']+1))
    {
	++$player['CultureLvl'];
	$player['CultureRemain']-=culture_points_for_lvl($player['CultureLvl']);
    }

    //Update Science
    global $sciences;
    $effective=$product['L']*($Race['Science']/100.0)*$player['Elapsed']/3600.0;
    $science=$sciences[$player['SelectedScience']];
    $sciremain=$science . "Remain";
    $player[$sciremain]+=($effective=tech_update($sql,$pid,$product['L']*($Race['Science']/100.0)*$player['Elapsed']/3600.0));
    while ($player[$sciremain]>science_points_for_lvl($player[$science]+1))
    {
	++$player[$science];
	$player[$sciremain]-=science_points_for_lvl($player[$science]);
    }
//    }
    //Store everything in the database
    $sql->query("UPDATE NC_Player SET LastUpdate=LastUpdate+{$player['Elapsed']},"
		. " CultureRemain={$player['CultureRemain']},"
		. " CultureLvl={$player['CultureLvl']},"
		. " $science={$player[$science]},"
		. " $sciremain={$player[$sciremain]}"
		. " WHERE PID=$pid");
    
    $sql->query("SELECT RELEASE_LOCK(\"danilewski AW player $pid\")");
}

function player_create(&$sql, $aid, $growth, $science, $cul, $prod, $speed, $att, $def)
{
	$bonuss=$sql->query("SELECT BonusTime FROM NC_globalsettings");
	$bonus=(EncodeNow()-$bonuss[0]['BonusTime'])/3600;
	if ($bonus<0)
		$bonus=0;

	$Log=log_entry($sql,"player cr");
	$result=$sql->query("SELECT GET_LOCK(\"danilewski AW player create\",10) AS L");
	if ($result[0]['L']==0 or $result[0]['L']=="NULL")
	{
		$GLOBALS['Error']=new CError("Unable to create new civilisation","Function cannot get a lock. Probably to many people are trying to create their races at once. Please try later.",1);
		log_result($sql,$Log,"grab failed");
		return 0;
	}
	$i=starsystem_get_empty($sql,1,4);
	$isize=starsystem_get_size($sql, $i);
	if ($i<0)
	{
		starsystem_ring_create($sql);
		$i=starsystem_get_empty($sql,1,4);
		if ($i<0)
		{
			$GLOBALS['Error']=new CError("Unable to spawn new starting points","No available planets for spawning, and unable to create new starsystems",2);
			log_result($sql,$Log,"no space");
			return 0;
		}
	}
	$Now=EncodeNow();

	if ($bonus>72)
	{
		$cultRem=pow($bonus-72,1.25);
		$cult=1;
		while ($cultRem>culture_points_for_lvl($cult+1))
		{
			++$cult;
			$cultRem-=culture_points_for_lvl($cult);
		}
	}
	else
	{
		$cult=1;
		$cultRem=0;
	}
	$bioRem=pow($bonus,1.4);
	$bio=0;
	while ($bioRem>science_points_for_lvl($bio+1))
	{
		++$bio;
		$bioRem-=science_points_for_lvl($bio);
	}

	$engRem=0;
	$eng=0;

	$wrpRem=$bonus;
	$wrp=0;
	while ($wrpRem>science_points_for_lvl($wrp+1))
	{
		++$wrp;
		$wrpRem-=science_points_for_lvl($wrp);
	}

	$phyRem=pow($bonus,1.6);
	$phy=0;
	while ($phyRem>science_points_for_lvl($phy+1))
	{
		++$phy;
		$phyRem-=science_points_for_lvl($phy);
	}

	$matRem=pow($bonus,1.5);
	$mat=0;
	while ($matRem>science_points_for_lvl($mat+1))
	{
		++$mat;
		$matRem-=science_points_for_lvl($mat);
	}

	$urb=0;
	$urbRem=0;

	$PPb=pow($bonus,1.2);

	$sql->query("INSERT INTO NC_Player VALUES (NULL, 0, 0, $aid, $i, \"\", 0, 15, 999999999, 0, " .
			" $bio, $eng, $wrp, $phy, $mat, $urb, " .
			" $bioRem, $engRem, $wrpRem,  $matRem, $phyRem, $urbRem, " .
			//	    " 0, 0, 0, 0, 0, 0, " . //sciences
			//	    " 0, 0, 0, 0, 0, 0, " . //science remains
			" $growth, $science, $cul, $prod, $speed, $att, $def, " .
			" NULL, " . //country
			" $Now, " . //last update
			" $cult, $cultRem, " . //culture, culture remain
			" 0, " . //selected science (biology)
			" 0, 5, " . //Player Level / remain
			" 0, 0, " . //@ TA 
			" 0, 4, " . //Violence Level /remain
			" 1, 1, 10)"); //Technology (dev/sel/rem)
	$A=$sql->query("SELECT LAST_INSERT_ID() AS L");
	$result=$sql->query("SELECT RELEASE_LOCK(\"danilewski AW player create\")");
	$_SESSION['PID']=$A[0]['L'];

	if ($isize<1)
		planet_create($sql,$i,22,0,$_SESSION['PID']);
	if ($isize<3) {
		planet_create($sql,$i,0,0,$_SESSION['PID']);
		planet_create($sql,$i,0,0,$_SESSION['PID']);
	}
	if ((mt_rand(1,10)<4) and $isize<8)
		planet_create($sql,$i,0,0,$_SESSION['PID']);
	$plid1=planet_create($sql,$i,-1,0,$_SESSION['PID']);
	$plid2=planet_create($sql,$i,+1,10*(1+$PPb),$_SESSION['PID']);
	$plid3=planet_create($sql,$i,-1,0,$_SESSION['PID']);
	if (mt_rand(1,10)<4)
		planet_create($sql,$i,0,0,$_SESSION['PID']);
	planet_conquer($sql, $plid2,$_SESSION['PID']);

	$DefCoords=starsystem_get_coords($sql, $i);
	$sql->query("UPDATE NC_Account SET PID={$_SESSION['PID']}, DefMapX={$DefCoords['X']}, DefMapY={$DefCoords['Y']}, DefMapR=10 WHERE AID=$aid");
	$_SESSION['DefMapX']=$DefCoords['X'];
	$_SESSION['DefMapY']=$DefCoords['Y'];
	$_SESSION['DefMapR']=10;
	news_set($sql, $_SESSION['PID'], "Welcome to Northern Cross constellation!<br>Good economy and strong fleet are important, but even more important is diplomacy!<br>So do not be afraid to talk to people.<br>By staying alone you become an easy target!", 0);
	log_result($sql,$Log,"OK");
	return $_SESSION['PID'];
}

function player_has_culture_slot($sql, $pid)
{
    $A=$sql->query("SELECT P.CultureLvl AS Culture, count(*) AS Plan FROM NC_Player P JOIN NC_Planet Pl ON P.PID=Pl.Owner JOIN NC_PlanetType PT ON PT.PTID=Pl.Type WHERE P.PID=$pid AND PT.CultureSlot=1 GROUP BY P.PID");
    if (count($A)==0)
	return true;	//player has no planets
    return $A[0]['Culture']>$A[0]['Plan'];
}

function player_add_xp($sql, $pid, $xp)
{
    $xp=makeinteger($xp);
    $pid=makeinteger($pid);
    $current=$sql->query("SELECT PL, PLRemain FROM NC_Player WHERE PID=$pid");
    $PL=$current[0]['PL'];
    $SU=0;
    $PLRemain=$current[0]['PLRemain'];
//    print_r($PLRemain);
    settype($PLRemain,"integer");
    while ($PLRemain<=$xp)
    {
	$xp-=$PLRemain;
	++$PL;
	$SU+=$PL;
	$PLRemain=pl_points_for_lvl($PL+1);
    }
//    print_r($PLRemain);
    $PLRemain-=$xp;
//    print_r($PLRemain);
    $sql->query("UPDATE NC_Player SET PL=$PL, PLRemain=$PLRemain WHERE PID=$pid");
    artefact_buy_instant($sql, $pid, 0, $SU);	//0 == RP
    //add su!
}

function player_add_violence($sql, $pid, $vp)
{
    $vp=makeinteger($vp);
    $pid=makeinteger($pid);
    $current=$sql->query("SELECT VL, VLRemain FROM NC_Player WHERE PID=$pid");
    $VL=$current[0]['VL'];
    $VLRemain=$current[0]['VLRemain'];
    settype($VLRemain,"integer");
    while ($VLRemain<=$vp)
    {
	$vp-=$VLRemain;
	++$VL;
	$VLRemain=vl_points_for_lvl($VL+1);
    }
    $VLRemain-=$vp;
    $sql->query("UPDATE NC_Player SET VL=$VL, VLRemain=$VLRemain WHERE PID=$pid");
}


function player_spend_all(&$sql, $pid)
{
    $Log=log_entry($sql,"spend all");
    $pid=makeinteger($pid);
    $sql->query("UPDATE NC_Player P SET P.AT=P.AT+(SELECT SUM(IF(V.FleetOwner=0 OR V.FleetOwner=V.Owner,V.PP,V.PP/2)) FROM NC_Planet V WHERE V.Owner=$pid) WHERE P.PID=$pid");
    $sql->query("UPDATE NC_Planet SET PP=0 WHERE Owner=$pid");
    log_result($sql,$Log,"OK");
}

function player_get_RP(&$sql, $pid)
{
    $pid=makeinteger($pid);
    $M=$sql->query("SELECT Amount, Sell FROM NC_Artefact WHERE PID=$pid AND Artefact=0");
    if (isset($M[0])) {
    if ($M[0]['Sell']>0)
        return $M[0]['Amount']-$M[0]['Sell'];
    else
	return $M[0]['Amount'];
    } else
      return 0;
}

function player_get_AT(&$sql, $pid)
{
    $pid=makeinteger($pid);
    $M=$sql->query("SELECT AT FROM NC_Player WHERE PID=$pid");
    if (count($M)==1)
	return $M[0]['AT'];
    else
	return 0;
}

function player_spend_AT(&$sql, $pid, $amount)
{
    $pid=makeinteger($pid);
    $amount=makeinteger($amount);
    $sql->query("UPDATE NC_Player SET AT=AT-$amount WHERE PID=$pid");
}

function player_resign(&$sql, $pid, $password)
{
    $Log=log_entry($sql,"resign",$_SERVER['REMOTE_ADDR'],
		$_SERVER['REMOTE_HOST'],
		$_SERVER['REMOTE_PORT']);
    $pid=makeinteger($pid);
    if (!account_check_password_for_pid($sql, $pid, $password))
    {
	log_result($sql,$Log,"inv pass");
	return "Incorrect password";
	}
    
    //resign the player !
    
/*    $sql->query("DELETE NC_FleetMovement WHERE Owner=$pid");
    $sql->query("DELETE NC_News WHERE PID=$pid");
    $sql->query("DELETE NC_Artefact WHERE PID=$pid");
    $sql->query("UPDATE NC_Planet SET Owner=0 WHERE Owner=$pid");*/
    $result=$sql->query("SELECT GET_LOCK(\"danilewski AW player create\",10) AS L");
    if ($result[0]['L']==0 or $result[0]['L']=="NULL")
    {
	log_result($sql,$Log,"grab failed");
	return "Unable to get a lock";
	}
    $sql->query("DELETE FROM NC_Invitations WHERE PID=$pid");
    $sql->query("UPDATE NC_Player SET AID=0, TAG=\"\" WHERE PID=$pid");
    $sql->query("UPDATE NC_Account SET PID=0 WHERE PID=$pid");
    
    $Now=EncodeNow()-86400*7;
    $PlanetDel=$sql->query("SELECT PLID FROM NC_PlanetCreated WHERE PID=$pid AND Time>$Now");
    if (count($PlanetDel)>=3)
	{
	    foreach ($PlanetDel as $PLID)
	    {
		planet_delete($sql, $PLID['PLID']);
	    }
	}
    $result=$sql->query("SELECT RELEASE_LOCK(\"danilewski AW player create\")");
    log_result($sql,$Log,"OK");
    return "";
}


function player_force_resign(&$sql, $pid)
{
    $Log=log_entry($sql,"resign",$_SERVER['REMOTE_ADDR'],
		$_SERVER['REMOTE_HOST'],
		$_SERVER['REMOTE_PORT']);
    $pid=makeinteger($pid);
    
    //resign the player !
    
/*    $sql->query("DELETE NC_FleetMovement WHERE Owner=$pid");
    $sql->query("UPDATE NC_Planet SET Owner=0 WHERE Owner=$pid");*/
    $result=$sql->query("SELECT GET_LOCK(\"danilewski AW player create\",10) AS L");
    if ($result[0]['L']==0 or $result[0]['L']=="NULL")
    {
	log_result($sql,$Log,"grab failed");
	return "Unable to get a lock";
	}
    $sql->query("DELETE FROM NC_Invitations WHERE PID=$pid");
    $sql->query("UPDATE NC_Player SET AID=0, TAG=\"\" WHERE PID=$pid");
    $sql->query("UPDATE NC_Account SET PID=0 WHERE PID=$pid");
    
    $Now=EncodeNow()-86400*7;
    $PlanetDel=$sql->query("SELECT PLID FROM NC_PlanetCreated WHERE PID=$pid AND Time>$Now");
    if (count($PlanetDel)>=3)
	{
	    foreach ($PlanetDel as $PLID)
	    {
		planet_delete($sql, $PLID['PLID']);
	    }
	}
    $result=$sql->query("SELECT RELEASE_LOCK(\"danilewski AW player create\")");
    
    log_result($sql,$Log,"OK");
    return "";
}



function player_get_home(&$sql, $pid)
{
    $pid=makeinteger($pid);
    $A=$sql->query("SELECT HomeSID FROM NC_Player WHERE PID=$pid");
    return $A[0]['HomeSID'];
}


function player_capital_available(&$sql, $pid)
{
    $pid=makeinteger($pid);
    $P=$sql->query("SELECT FleetOwner FROM NC_Planet WHERE Owner=$pid ORDER BY Population DESC, PopulationRemain ASC");
    return (count($P)>0 and ($P[0]['FleetOwner']==0 or $P[0]['FleetOwner']==$pid));
}

function get_capital(&$sql, $pid) {
	$pid=makeinteger($pid);
	$P=$sql->query("SELECT PLID FROM NC_Planet WHERE Owner=$pid ORDER BY Population DESC, PopulationRemain ASC");
	if (count($P)>0)
		return makeinteger($P[0]['PLID']);
	return 0;
}

function player_same_alliance(&$sql, $pid1, $pid2)
{
    if ($pid1==$pid2)
	return true;
    $tag1=player_get_tag($sql, $pid1);
    $tag2=player_get_tag($sql, $pid2);
    if ($tag1!="" and $tag1==$tag2)
	return true;
    return false;
}

function player_may_be_sitted(&$sql, $pid)
{
    $pid=makeinteger($pid);
    $A=$sql->query("SELECT SitCount FROM NC_Player WHERE PID=$pid");
    return ($A[0]['SitCount']==0);
}

function player_get_sitted(&$sql, $pid)
{
    $pid=makeinteger($pid);
    $A=$sql->query("SELECT SitAID FROM NC_Player WHERE PID=$pid");
    return $A[0]['SitAID'];
}

?>
