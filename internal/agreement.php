<?php
include_once("./internal/log.php");
//Agreement semi-class

/* Agreement types
1 - Trade Agreement
2 - Sensory Agreement
*/

/* Agreement status
1 - pending
2 - accepted
*/

$AgreementType = array ( 1 => "Trade", 2 => "Sensory");

function ta_count(&$sql, $pid, $typ=1)
{
    $pid=makeinteger($pid);
    $typ=makeinteger($typ);
    $W=$sql->query("SELECT count(*) A FROM NC_Agreement WHERE (PID=$pid OR PID2=$pid) AND Status=2 AND Type=$typ");
    return $W[0]['A'];
}

function ta_pending_count(&$sql, $pid, $typ=1)
{
    $pid=makeinteger($pid);
    $typ=makeinteger($typ);
    $W=$sql->query("SELECT count(*) A FROM NC_Agreement WHERE (PID=$pid OR PID2=$pid) AND (Status=2 OR Status=1) AND Type=$typ");
    return $W[0]['A'];
}

function ta_invite(&$sql, $from, $to, $typ=1)
{
	global $AgreementType;
	$from=makeinteger($from);
	$to=makeinteger($to);
	$typ=makeinteger($typ);
	$Log=log_entry($sql,"ta invite",account_get_name_from_pid($sql,$from),account_get_name_from_pid($sql,$to),$AgreementType[$typ]);
	if ($from==$to)
	{
		log_result($sql,$Log,"yourself");
		return "You cannot make an agreement with yourself";
	}
	$fromtag=player_get_tag($sql,$from);
	if ($typ==1 && $fromtag=='') {
		log_result($sql,$Log,"no tag");
		return "You must be in an alliance to sign a trade agreement";
	}
	$totag=player_get_tag($sql,$from);
	if ($typ==1 && $totag!=$fromtag) {
		log_result($sql,$Log,"not same tag");
		return "You and your partner must be in the same alliance";
	}
	$FC=ta_cost(ta_pending_count($sql,$from,$typ),$typ);
	log_update($sql, $LID, 1, $FC);
	log_update($sql, $LID, 2, '?');
	//    $TC=ta_cost(ta_pending_count($sql,$to));
	if (player_get_AT($sql, $from)<$FC)
	{
		log_result($sql,$Log,"no money");
		return "Not enough money";
	}
	$Z=$sql->query("SELECT count(*) AS Z FROM NC_Agreement WHERE ((PID=$from AND PID2=$to) OR (PID2=$from AND PID=$to)) AND Type=$typ");
	if ($Z[0]['Z']>0)
	{
		log_result($sql,$Log,"already");
		return "Agreement already established or pending";
	}
	player_spend_AT($sql, $from, $FC);
	$sql->query("INSERT INTO NC_Agreement VALUES ($from, $to, $typ, 1)");
	news_set($sql, $to, account_get_name_from_pid($sql,$from) . " offers you a {$AgreementType[$typ]} Agreement", 3);
	log_result($sql,$Log,"OK");
	return "";
}

function ta_accept(&$sql, $from, $to, $typ=1)
{
    global $AgreementType;
    $Log=log_entry($sql,"ta accept",account_get_name_from_pid($sql,$from),account_get_name_from_pid($sql,$to),$AgreementType[$typ]);
    $from=makeinteger($from);
    $to=makeinteger($to);
    $typ=makeinteger($typ);
    $FC=ta_cost(ta_count($sql,$from,$typ),$typ);
    $TC=ta_cost(ta_count($sql,$to,$typ),$typ);
    log_update($sql, $LID, 1, $FC);
    log_update($sql, $LID, 2, $TC);
    if (player_get_AT($sql, $to)<$TC)
    {
	log_result($sql,$Log,"no money");
	return "Not enough money";
	}
    $Z=$sql->query("SELECT Status AS Z FROM NC_Agreement WHERE ((PID=$from AND PID2=$to) OR (PID2=$from AND PID=$to)) AND Status=1 AND Type=$typ");
    if ($Z[0]['Z']!=1)
    {
	log_result($sql,$Log,"not found");
	return "Agreement not pending";
    }
    player_spend_AT($sql, $to, $TC);
    $sql->query("UPDATE NC_Agreement SET Status=2 WHERE ((PID=$from AND PID2=$to) OR (PID2=$from AND PID=$to)) AND Status=1 AND Type=$typ");
    news_set($sql, $from, account_get_name_from_pid($sql,$to) . " accepted your offer of {$AgreementType[$typ]} Agreement", 3);
    log_result($sql,$Log,"OK");
    return "";
}

function ta_decline(&$sql, $from, $to, $typ=1)
{
    global $AgreementType;
    $Log=log_entry($sql,"ta decline",account_get_name_from_pid($sql,$from),account_get_name_from_pid($sql,$to));
    $from=makeinteger($from);
    $to=makeinteger($to);
    $typ=makeinteger($typ);
    $FC=ta_cost(ta_pending_count($sql,$from,$typ)-1,$typ);
    $Z=$sql->query("SELECT Status AS Z FROM NC_Agreement WHERE ((PID=$from AND PID2=$to) OR (PID2=$from AND PID=$to)) AND Status=1 AND Type=$typ");
    if ($Z[0]['Z']!=1)
    {
	log_result($sql,$Log,"not found");
	return "Agreement not pending";	
	}
    $sql->query("DELETE FROM NC_Agreement WHERE ((PID=$from AND PID2=$to) OR (PID2=$from AND PID=$to)) AND Status=1 AND Type=$typ");
    player_spend_AT($sql, $from, -$FC);
    news_set($sql, $from, account_get_name_from_pid($sql,$to) . " declined your offer of {$AgreementType[$typ]} Agreement", 3);
    log_result($sql,$Log,"OK");
    return "";
}


function ta_cost($num, $type=1)
{
    switch ($type)
    {
    case 1:return floor(5000+pow($num,1.5)*5000);
    case 2:return 2000+$num*2000;
    }
    return 0;
}

function agreements_get(&$sql, $pid)
{
    $pid=makeinteger($pid);
    $M=$sql->query("SELECT * FROM NC_Agreement WHERE PID=$pid or PID2=$pid");
    $A=array();
    $i=0;
    foreach ($M as $Agr)
    {
	$A[$i]['PID']=(($Agr['PID']!=$pid)?$Agr['PID']:$Agr['PID2']);
	$A[$i]['Initiator']=($Agr['PID']==$pid);
	$A[$i]['Type']=$Agr['Type'];
	$A[$i]['Status']=$Agr['Status'];
	++$i;
    }
    return $A;
}
/*
function ta_value(&$sql, $pid)
{
    $sql->query("SELECT SUM(Pl.Population) AS S FROM NC_Planet Pl "
	. " JOIN NC_Agreement Agr ON Pl.Owner=Agr.PID OR Pl.Owner=Agr.PID2"
	. " WHERE ((Agr.PID=$pid OR Agr.PID2=$pid) AND Agr.Type=1) AND Pl.Owner!=$pid");
    return round(S/15);
}*/
?>
