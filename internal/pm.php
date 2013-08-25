<?php

/// pm semi-class

function pm_get_all(&$sql, $AID)
{
    return $sql->query("SELECT PM.*, AF.Nick AS FromNick, AF.BackgroundSig AS FromSig, AT.Nick AS ToNick, PF.Tag AS FromTag, PT.Tag AS ToTag"
		. " FROM NC_PM PM"
		. " JOIN NC_Account AF ON AF.AID=PM.From"
		. " JOIN NC_Account AT ON AT.AID=PM.To"
		. " LEFT JOIN NC_Player PF ON PF.AID=AF.AID"
		. " LEFT JOIN NC_Player PT ON PT.AID=AT.AID"
		. " WHERE PM.Owner=$AID AND (PM.From=$AID OR PM.To=$AID) ORDER By PM.Time DESC LIMIT 0, 100");
}

function pm_get_in(&$sql, $AID)
{
    return $sql->query("SELECT PM.*, AF.Nick AS FromNick, AF.BackgroundSig AS FromSig,  AT.Nick AS ToNick, PF.Tag AS FromTag, PT.Tag AS ToTAG"
		. " FROM NC_PM PM"
		. " JOIN NC_Account AF ON AF.AID=PM.From"
		. " JOIN NC_Account AT ON AT.AID=PM.To"
		. " LEFT JOIN NC_Player PF ON PF.AID=AF.AID"
		. " LEFT JOIN NC_Player PT ON PT.AID=AT.AID"
		. " WHERE PM.Owner=$AID AND PM.To=$AID ORDER By PM.Time DESC LIMIT 0, 100");
}

function pm_get_out(&$sql, $AID)
{
    return $sql->query("SELECT PM.*, AF.Nick AS FromNick, AF.BackgroundSig AS FromSig,  AT.Nick AS ToNick, PF.Tag AS FromTag, PT.Tag AS ToTag"
		. " FROM NC_PM PM"
		. " JOIN NC_Account AF ON AF.AID=PM.From"
		. " JOIN NC_Account AT ON AT.AID=PM.To"
		. " LEFT JOIN NC_Player PF ON PF.AID=AF.AID"
		. " LEFT JOIN NC_Player PT ON PT.AID=AT.AID"
		. " WHERE PM.Owner=$AID AND PM.From=$AID ORDER By PM.Time DESC LIMIT 0, 100");
}

function pm_send(&$sql, $From, $To, $Topic, $Message)
{
    $From=makeinteger($From);
    $To=makeinteger($To);
    $ToPID=account_get_pid($sql, $To);
    $FromPID=account_get_pid($sql, $From);
    $FromNick=account_get_name($sql, $From);
    if ($ToPID>0)
        news_set($sql, $ToPID, "You have new message from $FromNick<br>$Topic", 1, $FromPID);
    $Message=makequotedstring($Message);
    $Topic=makequotedstring($Topic);
    $Now=EncodeNow();
    $sql->query("INSERT INTO NC_PM VALUES(NULL, $From, $From, $To, $Now, $Topic, $Message)");
    $sql->query("INSERT INTO NC_PM VALUES(NULL, $To, $From, $To, $Now, $Topic, $Message)");
}

function pm_user_remove(&$sql, $PMID, $AID)
{
    $AID=makeinteger($AID);
    $PMID=makeinteger($PMID);
    $sql->query("DELETE FROM NC_PM WHERE Owner=$AID AND PMID=$PMID");
}

function pm_user_remove_several(&$sql, $PMID, $AID, $V)
{
    $AID=makeinteger($AID);
    $PMID=makeinteger($PMID);
    if ($V=="in" or $V=="")
        $sql->query("DELETE FROM NC_PM WHERE Owner=$AID AND PMID<=$PMID AND `To`=$AID");
    elseif ($V=="out")
        $sql->query("DELETE FROM NC_PM WHERE Owner=$AID AND PMID<=$PMID AND `From`=$AID");    
    elseif ($V=="all")
        $sql->query("DELETE FROM NC_PM WHERE Owner=$AID AND PMID<=$PMID");
}

function pm_user_get(&$sql, $PMID, $AID)
{
    $AID=makeinteger($AID);
    $PMID=makeinteger($PMID);
    $U=$sql->query("SELECT * FROM NC_PM WHERE Owner=$AID AND PMID=$PMID");
    return $U[0];
}

?>
