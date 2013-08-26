<?php

//Starsystem semi-class

include_once("./internal/common.php");
include_once("./internal/security/validator.php");

include_once("./internal/planet.php");
include_once("./internal/agreement.php");

function starsystem_get_all(&$sql, $sid)
{
	$s=$sql->query("SELECT * FROM NC_Map WHERE SID=$sid");
	return $s[0];
}

$starsystemPrefixName = array(
		0 => '',
		1 => '',
		2 => 'Alpha ',
		3 => 'Beta ',
		4 => 'Gamma ',
		5 => 'Delta ',
		6 => 'Epsilon '
		);

function starsystem_create(&$sql, $x, $y, $ring, $prefix, $name, $spawnCategory, $maxPlanets, $delay)
{
	if ($name=='') {
		$name=starsystem_pick_name($sql,$ring);
	}
	global $starsystemPrefixName;
	if ($ring>0 and $prefix=='') {
		$prefix = $starsystemPrefixName[$ring];
	}
	$x=makeinteger($x);
	$y=makeinteger($y);
	$name=makequotedstring($prefix . $name);
	if (strlen($name)<5)
		return false;
	$already=$sql->query("SELECT SID FROM NC_Map WHERE x=$x AND y=$y");
	if (count($already)>0)
		return false;
	$special=0;
	$Now=EncodeNow()+$delay;
	$sql->query("INSERT INTO NC_Map VALUES (NULL, $x, $y, $name, 0, 0, $spawnCategory, $maxPlanets, $Now)");
	return true;
}

function starsystem_main_create(&$sql, $x, $y, $ring, $prefix, $name, $numSattelite=0, $mayBeSpecial=true)
{
	if (!starsystem_create($sql, $x, $y, $ring, $prefix, $name, 1, mt_rand(11,14), 0))
		return false;
	$W=$sql->query("SELECT LAST_INSERT_ID() AS L");
	$L=$W[0]['L'];
	$W=$sql->query("SELECT count(*) AS R FROM NC_Map WHERE ABS($x-X)<9 AND ABS($y-Y)<9 AND Special=1");
	$R=$W[0]['R'];
	$W=$sql->query("SELECT count(*) AS R FROM NC_Map WHERE Special=1");
	$RTot=$W[0]['R'];
	if ($mayBeSpecial and $R==0 and $RTot<1 and mt_rand(0,10)<6)
		starsystem_make_special($sql,$L);

	/* Throw in some random neighbouring starsystems */
	for ($n=1; $n<=$numSattelite; ++$n) {
		starsystem_create($sql, $x+mt_rand(-2,2), $y+mt_rand(-2,2), $ring, '', '', 1+$n, mt_rand(3,5), 3600*24*7*$n);
	}
	return true;
}

function starsystem_make_special(&$sql, $SID)
{
	$SID=makeinteger($SID);
	$sql->query("UPDATE NC_Map SET Special=1 WHERE SID=$SID");
	$PLID[0]=planet_create($sql,$SID,22,0,0);
	$PLID[1]=planet_create($sql,$SID,11,0,0);
	$PLID[2]=planet_create($sql,$SID,8,0,0);
	$PLID[3]=planet_create($sql,$SID,12,0,0);
	$PLID[4]=planet_create($sql,$SID,3,0,0);
	$PLID[5]=planet_create($sql,$SID,4,0,0);
	$PLID[6]=planet_create($sql,$SID,2,0,0);
	$PLID[7]=planet_create($sql,$SID,1,0,0);
	$PLID[8]=planet_create($sql,$SID,18,0,0);
	$PLID[9]=planet_create($sql,$SID,6,0,0);
	$PLID[10]=planet_create($sql,$SID,15,0,0);
	$PLID[11]=planet_create($sql,$SID,19,0,0);
	$PLID[12]=planet_create($sql,$SID,7,0,0);
	$PLID[13]=planet_create($sql,$SID,20,0,0);
	$PLID[14]=planet_create($sql,$SID,21,0,0);
	for ($i=1; $i<=13; ++$i) {
		planet_conquer($sql, $PLID[$i], 1);
		$SB=ceil(24-2*$i+sqrt($i*2))+1;
		$sql->query("UPDATE NC_Planet SET Starbase=$SB WHERE PLID={$PLID[$i]}");
	}
}

function starsystem_pick_name(&$sql, $ring)
{
	$namelist=$sql->query("SELECT * FROM NC_Starsystemnames WHERE $ring>LastRing");
	$size=count($namelist);
	if ($size<2)
		return false;
	$pick=mt_rand(0,$size-2);
	$pickedname=$namelist[$pick]['Name'];
	$sql->query("UPDATE NC_Starsystemnames SET LastRing=$ring WHERE Name=\"$pickedname\"");
	return $pickedname;
}

function starsystem_reset_picked_names(&$sql)
{
	$sql->query("UPDATE NC_Starsystemnames SET LastRing=0");
}

function starsystem_ring_create(&$sql)
{
	$ringlvls=$sql->query("SELECT Ringlvl FROM NC_globalsettings");
	$ringlvl=$ringlvls[0]['Ringlvl'];
	$Now=EncodeNow();
	if ($ringlvl==0)
	{
		starsystem_main_create($sql,7,-5,0,'',"Albireo A",2,false);
		starsystem_main_create($sql,-7,5,0,'',"Albireo B",2,false);
		starsystem_make_special($sql,1);
		starsystem_make_special($sql,2);
	}
	else
	{
		$ring = makeinteger($ringlvl/10)+1;

		$translation=0;
		$PI=3.14159;
		for ($i=0; $i<$ringlvl; ++$i)
			$translation+=0.25*$PI/($i+1);

		$radfrom=-$PI/pow(($ringlvl+20)/10.0,2.3)+$translation;
		$radto=+$PI/pow(($ringlvl+20)/10.0,2.3)+$translation;
		$raddiff=$radto-$radfrom;
		if ($raddiff>$PI)
			$raddiff=$PI;

		$ringnumofstars=floor($raddiff*$ringlvl);
		if ($ringnumofstars==0)
			$ringnumofstars=1;

		for ($i=0; $i<$ringnumofstars; ++$i)
		{
			$angle=$radfrom+($raddiff*$i)/$ringnumofstars;
			$x=round($ringlvl*3*cos($angle)+mt_rand(-1,+1));
			$y=round($ringlvl*3*sin($angle)+mt_rand(-1,+1));
			$sattelite=mt_rand(0,1);
			if ($i==0) $sattelite+=2;
			if ($i==$ringnumofstars-1) $sattelite+=1;
			starsystem_main_create($sql,$x,$y,$ring,'','',$sattelite,false);
		}

		for ($i=0; $i<$ringnumofstars; ++$i)
		{
			$angle=$radfrom+($raddiff*$i)/$ringnumofstars;
			$x=-round($ringlvl*3*cos($angle)+mt_rand(-1,+1));
			$y=-round($ringlvl*3*sin($angle)+mt_rand(-1,+1));
			$sattelite=mt_rand(0,1);
			if ($i==0) $sattelite+=2;
			if ($i==$ringnumofstars-1) $sattelite+=1;
			starsystem_main_create($sql,$x,$y,$ring,'','',$sattelite,false);
		}
	}

	$sql->query("UPDATE NC_globalsettings SET Ringlvl=$ringlvl+1");    
	return true;
}

function starsystem_compute_level(&$sql, $sid)
{
	$sid=makeinteger($sid);
	$system=$sql->query("SELECT Population AS P FROM NC_Planet WHERE sid=$sid");
	if (count($system)==0)
		return 0;
	$min=100; $max=0;
	foreach ($system as $planet)
	{
		if ($planet['P']>$max)
			$max=$planet['P'];
		if ($planet['P']<$min)
			$min=$planet['P'];
	}
	return round(($max+$min)/2);
}

function starsystem_show_range(&$sql, $pid, $x, $y, $range)
{
	$pid=makeinteger($pid);
	$x=makeinteger($x);
	$y=makeinteger($y);
	$range=makeinteger($range);
	return $sql->query("SELECT *, " . 
			" IF (" .
			"ABS(x-(SELECT x FROM NC_Map WHERE SID=(SELECT HomeSID FROM NC_Player WHERE PID=$pid)))" .
			"<=(SELECT FLOOR(Sensory/2) FROM NC_Player WHERE PID=$pid)" .
			" AND " .
			"ABS(y-(SELECT y FROM NC_Map WHERE SID=(SELECT HomeSID FROM NC_Player WHERE PID=$pid)))" .
			"<=(SELECT FLOOR(Sensory/2) FROM NC_Player WHERE PID=$pid)" .
			" , 1, 0) AS InRange, " .
			"ABS(X-(SELECT X FROM NC_Map WHERE SID=(SELECT HomeSID FROM NC_Player WHERE PID=$pid))) AS DX, " .
			"ABS(Y-(SELECT Y FROM NC_Map WHERE SID=(SELECT HomeSID FROM NC_Player WHERE PID=$pid))) AS DY" .
			" FROM NC_Map WHERE".
			" x>=$x-$range AND x<=$x+$range AND" .
			" y>=$y-$range AND y<=$y+$range" .
			" ORDER BY y ASC, x ASC");
}

function starsystem_show_range_advanced(&$sql, $pid, $x, $y, $range)
{
	$pid=makeinteger($pid);
	$x=makeinteger($x);
	$y=makeinteger($y);
	$range=makeinteger($range);
	$SAs=agreements_get($sql, $pid);
	$Cmp="";
	foreach ($SAs as $SA) {
		if ($SA['Type']==2 //sensory
				&& $SA['Status']==2) //active
			$Cmp.=" OR PID=" . $SA['PID'];
	}
	return $sql->query("SELECT *, " . 
			" IF (" .
			"(SELECT MAX((ABS(M.x-U.x)<=FLOOR(Sensory/2)) AND (ABS(M.y-U.y)<=FLOOR(Sensory/2)))" . 
			" FROM NC_Map U JOIN NC_Player P ON P.HomeSID=U.SID WHERE PID=$pid $Cmp)" .
			" , 1, 0) AS InRange, " .
			"ABS(X-(SELECT X FROM NC_Map WHERE SID=(SELECT HomeSID FROM NC_Player WHERE PID=$pid))) AS DX, " .
			"ABS(Y-(SELECT Y FROM NC_Map WHERE SID=(SELECT HomeSID FROM NC_Player WHERE PID=$pid))) AS DY" .
			" FROM NC_Map M WHERE".
			" x>=$x-$range AND x<=$x+$range AND" .
			" y>=$y-$range AND y<=$y+$range" .
			" ORDER BY y ASC, x ASC");
}


function starsystem_get_coords(&$sql, $sid)
{
	$sid=makeinteger($sid);
	$pos=$sql->query("SELECT X, Y FROM NC_Map WHERE sid=$sid");
	if (count($pos)!=1)
		return array();
	return $pos[0];
}

function starsystem_get_sid_from_coords(&$sql, $x, $y) {
	$x=makeinteger($x);
	$y=makeinteger($y);
	$sid=$sql->query("SELECT SID FROM NC_Map WHERE x=$x AND y=$y");
	if (count($sid)>0)
		return $sid[0]['SID'];
	else
		return 0;
}

function starsystem_get_empty(&$sql, $category=1, $extrafree=0)
{
	$category=makeinteger($category);
	$extrafree=makeinteger($extrafree);
	$Now=EncodeNow();
	$systems=$sql->query("SELECT M.SID FROM NC_Map M LEFT JOIN NC_Planet P ON M.SID=P.SID" .
			" WHERE M.PlayerSpawn=$category AND BeginSpawnTime<=$Now" .
			" GROUP BY M.SID HAVING count(P.PLID)<=MIN(M.MaxPlanets)-$extrafree ORDER BY M.SID ASC LIMIT 0, 5");
	$size=count($systems);
	if ($size==0)
		return -1;
	else
		return $systems[mt_rand(0,$size-1)]['SID'];
}

function starsystem_get_size(&$sql, $sid)
{
	$sid=makeinteger($sid);
	$a=$sql->query("SELECT count(*) AS A FROM NC_Planet WHERE SID=$sid");
	return $a[0]['A'];
}

function starsystem_get_name(&$sql, $sid)
{
	$sid=makeinteger($sid);
	$a=$sql->query("SELECT Name FROM NC_Map WHERE SID=$sid");
	return $a[0]['Name'];
}

function starsystem_detail(&$sql, $sid)
{
	$sid=makeinteger($sid);
	$a=$sql->query("SELECT PT.*, Pl.*, Tech.Name AS TechName, P.TAG, A.Nick FROM NC_Planet Pl" .
			" JOIN NC_PlanetType PT ON PT.PTID=Pl.Type" .
			" LEFT JOIN NC_TechList Tech ON PT.TechReq=Tech.TechID" .
			" LEFT JOIN NC_Player P ON Pl.Owner=P.PID" .
			" LEFT JOIN NC_Account A ON A.PID=P.PID" .
			" WHERE Pl.SID=$sid ORDER BY Pl.Ring ASC");
	return $a;
}

function starsystem_in_bio_range(&$sql, $sid, $pid)
{
	$sid=makeinteger($sid);
	$a=$sql->query("SELECT X, Y FROM NC_Map WHERE SID=$sid");
	$TX=$a[0]['X'];
	$TY=$a[0]['Y'];

	$AGT=$sql->query("SELECT count(*) AS STR FROM NC_Agreement Agr"
			. " JOIN NC_Player P2 ON (Agr.PID2=P2.PID AND Agr.PID=$pid) OR (Agr.PID=P2.PID AND Agr.PID2=$pid)"
			. " JOIN NC_Map M ON M.SID=P2.HomeSID WHERE Agr.Type=2 AND Agr.Status=2"
			. " AND (ABS($TX-M.X)<=FLOOR(P2.Sensory/2) AND ABS($TY-M.Y)<=FLOOR(P2.Sensory/2) )");
	$pid=makeinteger($pid);
	$b=$sql->query("SELECT M.X AS X, M.Y AS Y, P.Sensory AS Bio FROM NC_Map M JOIN NC_Player P ON M.SID=P.HomeSID WHERE PID=$pid");

	$r=floor($b[0]['Bio']/2);
	$dx=abs($b[0]['X']-$a[0]['X']);
	$dy=abs($b[0]['Y']-$a[0]['Y']);
	//    return (($dx<=$r and $dy<=$r));
	return (($dx<=$r and $dy<=$r) or $AGT[0]['STR']>0);
}

function starsystem_bio(&$sql, $pid)
{
	if (isset($_SESSION['starsystem_bio']))
		return $_SESSION['starsystem_bio'];
	$pid=makeinteger($pid);
	$_SESSION['starsystem_bio']=$sql->query("SELECT M.*, count(P.PlID) AS YPC FROM NC_Map M "
			. " LEFT JOIN NC_Planet P ON P.SID=M.SID AND P.Owner=$pid"
			. " WHERE " .
			" (ABS(X-(SELECT MM.X FROM NC_Map MM JOIN NC_Player PP ON PP.HomeSID=MM.SID WHERE PP.PID=$pid))" .
			"<=(SELECT FLOOR(Sensory/2) FROM NC_Player WHERE PID=$pid)" .
			" AND " .    
			" ABS(Y-(SELECT MM.Y FROM NC_Map MM JOIN NC_Player PP ON PP.HomeSID=MM.SID WHERE PP.PID=$pid))" .
			"<=(SELECT FLOOR(Sensory/2) FROM NC_Player WHERE PID=$pid)) OR " .
			"(" .
			"SELECT count(*) AS STR FROM NC_Agreement Agr " .
			" JOIN NC_Player P2 ON (Agr.PID2=P2.PID AND Agr.PID=$pid) OR (Agr.PID=P2.PID AND Agr.PID2=$pid)"
			. " JOIN NC_Map MD ON MD.SID=P2.HomeSID WHERE Agr.Type=2 AND Agr.Status=2"
			. " AND (ABS(M.X-MD.X)<=FLOOR(P2.Sensory/2) AND ABS(M.Y-MD.Y)<=FLOOR(P2.Sensory/2) ))"
			. " GROUP BY M.SID"
			. " ORDER BY Name");
	return $_SESSION['starsystem_bio'];
}

function starsystem_fill(&$sql)
{
	$result=$sql->query("SELECT GET_LOCK(\"danilewski AW player create\",10) AS L");
	if ($result[0]['L']==0 or $result[0]['L']=="NULL")
		return 0;
	$i=1;
	while ($i>0)
	{
		$i=starsystem_get_empty($sql);
		if ($i>0)
		{
			planet_create($sql,$i,0,0,0);
			planet_create($sql,$i,0,0,0);
			planet_create($sql,$i,0,0,0);
		}
	}
}

?>
