<?php
include_once("./internal/log.php");

// Artefact semi-class

function artefact_get_own(&$sql, $pid)
{
    $pid=makeinteger($pid);
    $U=$sql->query("SELECT * FROM NC_Artefact WHERE PID=$pid");
    $N=array();
    foreach ($U as $Art)
    {
	$N[$Art['Artefact']]['Sell']=$Art['Sell'];
	$N[$Art['Artefact']]['InUse']=$Art['InUse'];
	$N[$Art['Artefact']]['Amount']=$Art['Amount'];
    }
    return $N;
}

function artefact_get_own_type(&$sql, $pid, $artefact)
{
    $pid=makeinteger($pid);
    $pid=makeinteger($artefact);
    $U=$sql->query("SELECT * FROM NC_Artefact WHERE PID=$pid AND Artefact=$artefact");
    if (count($U)==0)
	return array('Artefact' => $artefact, 'Sell' => 0, 'InUse' => 0, 'Amount' => 0);
    return $U[0];
}


function artefact_get_type_name(&$sql, $Art)
{
    $Art=makeinteger($Art);
    $Ans=$sql->query("SELECT Name FROM NC_ArtefactList WHERE ARID=$Art");
    return $Ans[0]['Name'];
}

function artefact_buy(&$sql, $pid, $Art, $Price, $Amount)
{
    $Log=log_entry($sql,"art buy",artefact_get_type_name($sql,$Art), $Price, $Amount);
    $pid=makeinteger($pid);
    $Price=makereal($Price);
    $Art=makeinteger($Art);
    $Amount=makeinteger($Amount);
    $sql->query("INSERT INTO NC_Artefact VALUES ($pid, $Art, 0, 0, -$Amount) ON DUPLICATE KEY UPDATE Sell=-$Amount");
    $total=$Price*$Amount;
    $sql->query("UPDATE NC_Player SET AT=AT-$total WHERE PID=$pid");
    log_result($sql,$Log,"OK");
}

function artefact_buy_instant(&$sql, $pid, $Art, $Amount)
{
    $pid=makeinteger($pid);
    $Art=makeinteger($Art);
    $Amount=makeinteger($Amount);
    $sql->query("INSERT INTO NC_Artefact VALUES ($pid, $Art, 0, $Amount, 0) ON DUPLICATE KEY UPDATE Amount=Amount+$Amount");
}

function artefact_sell(&$sql, $pid, $Art, $Price, $Amount)
{
    $Log=log_entry($sql,"art sell",artefact_get_type_name($sql,$Art), $Price, $Amount);
    $pid=makeinteger($pid);
    $Art=makeinteger($Art);
    $Price=makereal($Price);
    $Amount=makeinteger($Amount);
    $sql->query("UPDATE NC_Artefact SET Sell=$Amount WHERE PID=$pid AND Artefact=$Art AND Amount>=$Amount");
    log_result($sql, $Log,"OK");
}

function artefact_use(&$sql, $pid, $Art)
{
    $Log=log_entry($sql,"art use",artefact_get_type_name($sql,$Art));
    $pid=makeinteger($pid);
    $Art=makeinteger($Art);
    if ($Art>0)
    {
        $sql->query("UPDATE NC_Artefact SET InUse=0 WHERE PID=$pid");
	$sql->query("UPDATE NC_Artefact SET InUse=1 WHERE PID=$pid AND Artefact=$Art AND Amount>0");
	log_result($sql, $Log,"OK");
    }
    else
	log_result($sql, $Log, "not found");
}

function pp_buy(&$sql, $pid, $amount)
{
    $Log=log_entry($sql,"pp buy",$amount);
    $pid=makeinteger($pid);
    $amount=makeinteger($amount);
    if ($amount<=0)
    {
        log_result($sql,$Log,"wrong amount");
	return "Amount must be a positive integer number";
	}
    $Pl=planet_list($sql, $pid);
    if (count($Pl)==0)
    {
	log_result($sql,$Log,"no planets");
	return "You have no planets";
	}
    if (Lock($sql, $Pl[0]['PLID'])!=1)
	{
	log_result($sql,$Log,"grab failed");
	return "Unable to lock planet";
	}
    if ($Pl[0]['FleetOwner']!=$pid and $Pl[0]['FleetOwner']!=0)
	{
	log_result($sql,$Log,"siege");
	Unlock($sql, $Pl[0]['PLID']);
	return "Your capital is under siege. You cannot buy PP";
	}
    $sql->query("UPDATE NC_Player SET AT=AT-$amount WHERE PID=$pid");
    planet_add_pp($sql, $Pl[0]['PLID'], $amount);
    Unlock($sql, $Pl[0]['PLID']);
    log_result($sql,$Log,"OK");
    return "";
}

function ship_buy(&$sql, $pid, $amount, $type, $shipcost)
{
    $Log=log_entry($sql,"buy ship",$amount, $type);
    $pid=makeinteger($pid);
    $amount=makeinteger($amount);
    $type=makestring($type);
    $shipcost=makeinteger($shipcost);
    if ($amount<=0)
    {
        log_result($sql,$Log,"wrong amount");
	return "Amount must be a positive integer number";
	}
    $Pl=planet_list($sql, $pid);
    if (count($Pl)==0)
    {
	log_result($sql,$Log,"no planets");
	return "You have no planets";
	}
    $UnPLID=0;
    foreach ($Pl as $Pg)
    {
        if ($Pg['FleetOwner']==$pid or $Pg['FleetOwner']==0)
	    {
		$UnPLID=$Pg['PLID'];
		break;
	    }
    }
    if ($UnPLID==0)
    {
	log_result($sql,$Log,"siege");
	return "All your planets are under siege. You cannot buy ships";
    }
    if (Lock($sql, $UnPLID)!=1)
	{
	log_result($sql,$Log,"grab failed");
	return "Unable to lock planet";
	}
    if ($UnPID['FleetOwner']!=$pid and $UnPID['FleetOwner']!=0)
	{
	log_result($sql,$Log,"siege");
	Unlock($sql, $UnPLID);
	return "Your planet got sieged while buying ships";
	}
    $sql->query("UPDATE NC_Player SET AT=AT-($amount*$shipcost) WHERE PID=$pid");
    $sql->query("UPDATE NC_Planet SET `$type`=`$type`+$amount, FleetOwner=$pid WHERE PLID=$UnPLID");
    Unlock($sql, $UnPLID);
    log_result($sql,$Log,"OK");
    return "";
}


?>