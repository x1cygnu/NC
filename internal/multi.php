<?php

//multi semi-class

function ip_separate($strIP) {
  $Ans=sscanf($strIP,"%d.%d.%d.%d");
  $Ans[0]=makeinteger($Ans[0]);
  $Ans[1]=makeinteger($Ans[1]);
  $Ans[2]=makeinteger($Ans[2]);
  $Ans[3]=makeinteger($Ans[3]);
  return $Ans;
}

function ip_merge($ip1, $ip2, $ip3, $ip4) {
	return $ip1.'.'.$ip2.'.'.$ip3.'.'.$ip4;
}

function multi_add_watchlist(&$sql, $AID, $with, $strength) {
  $AID=makeinteger($AID);
  $with=makeinteger($with);
  $strength=makeinteger($strength);
  $sql->query("INSERT INTO NC_MultiWatchlist VALUES(" .
      " $AID, $with, $strength)" .
      " ON DUPLICATE KEY UPDATE " .
      " With=$with, Strength=Strength+$strength");
}

function multi_is_ltid_changing_ip(&$sql, $LTID) {
  $LTID=makeinteger($LTID);
  $V=$sql->query("SELECT ChangingIP FROM NC_MultiLoginThread WHERE LTID=$LTID");
  return $V[0]['ChangingIP'];
}

function multi_ltid_get_allowed_players(&$sql, $LTID) {
  $LTID=makeinteger($LTID);
  $Vs=$sql->query("SELECT aAID FROM NC_MultiLTAllowed WHERE LTID=$LTID");
  $V=array();
  foreach ($Vs as $Ve)
    $V[]=$Ve['aAID'];
  return $V;
}

function multi_ltid_add_allowed_player(&$sql, $LTID, $AID) {
  $AID=makeinteger($AID);
  $LTID=makeinteger($LTID);
  $sql->query("INSERT INTO NC_MultiLTAllowed VALUES ($LTID,$AID)");
}

function multi_count_new_ltid(&$sql, $AID) {
  $Ans=$sql->query("SELECT count(*) AS C FROM NC_LogLogin WHERE NewAID=$AID AND CookieNew=1");
  return $Ans[0]['C'];
}

function multi_log_login(&$sql, $pSAID,$pAID, $AID, $IP, $ForwardIP, $succesfull, $cookieNew, $cookieVal)
{
    $Now=EncodeNow();
    $AID=makeinteger($AID);
    $pSAID=makeinteger($pSAID);
    $pAID=makeinteger($pAID);
    if ($cookieNew)
      $cookieNew=1;
    else
      $cookieNew=0;
    $cookieVal=makeinteger($cookieVal);

    $IP=ip_separate($IP);
    $FIP=ip_separate($ForwardIP);
    if ($succesfull) {
      $sql->query("INSERT INTO NC_Login VALUES ($AID, $Now, " .
  	  "{$IP[0]}, {$IP[1]}, {$IP[2]}, {$IP[3]}, " .
  	  "{$FIP[0]}, {$FIP[1]}, {$FIP[2]}, {$FIP[3]}, 1) " .
  	  "ON DUPLICATE KEY UPDATE Count=Count+1");

      if ($cookieNew)
	$sql->query("INSERT INTO NC_MultiLTAllowed VALUES ($cookieVal, $AID)");
    }
    $New=EncodeNow();
    if ($succesfull)
      $succesfull=1;
    else
      $succesfull=0;
    $sql->query("INSERT INTO NC_LogLogin VALUES (NULL, $Now," .
	"1, $succesfull, " .
	"{$IP[0]}, {$IP[1]}, {$IP[2]}, {$IP[3]}, " .
      	"{$FIP[0]}, {$FIP[1]}, {$FIP[2]}, {$FIP[3]}, " .
	"$pAID, $pSAID, $AID, $cookieVal, $cookieNew)");

    /* perform multi checks */
    if ($succesfull) {
      $Pls=multi_ltid_get_allowed_players($sql,$cookieVal);
      if (count($Pls)==0) {
	multi_ltid_add_allowed_player($sql,$cookieVal,$AID);
        $Pls=multi_ltid_get_allowed_players($sql,$cookieVal);
      }
      $found=0;
      foreach ($Pls as $Pl)
	if ($Pl==$AID)
	  $found=1;
      if ($found==0)
	multi_add_watchlist($sql,$AID,$Pls[0],70);
      if ($pSAID>0 and $pSAID!=$AID) {
	$pPresent=0;
	$cPresent=0;
	foreach ($Pls as $Pl) {
	  if ($Pl==$AID)
	    $cPresent=1;
	  if ($Pl==$pSAID)
	    $pPresent=0;
	}
	if ($pPresent and $cPresent)
	  multi_add_watchlist($sql,$AID,$pSAID,15);
	else {
	  multi_punish($sql,$AID,"Login after someone else");
	  multi_punish($sql,$pSAID,"Login after someone else");
	}
      }

      if ($pAID>0 and $pAID!=$AID) {
	multi_punish($sql,$AID,"Login when someone else is logged in");
	multi_punish($sql,$pAID,"Login when someone else is logged in");
      }

      if (multi_count_new_ltid($sql,$AID)>10)
	multi_add_watchlist($sql,$AID,0,1);

    }

}

function multi_log_logout(&$sql, $AID, $IP, $ForwardIP, $cookieNew, $cookieVal)
{
    $Now=EncodeNow();
    $AID=makeinteger($AID);
    if ($cookieNew)
      $cookieNew=1;
    else
      $cookieNew=0;
    $cookieVal=makeinteger($cookieVal);

    $IP=ip_separate($IP);
    $FIP=ip_separate($ForwardIP);
    $New=EncodeNow();
    $sql->query("INSERT INTO NC_LogLogin VALUES (NULL, $Now," .
	"0, 1, " .
	"{$IP[0]}, {$IP[1]}, {$IP[2]}, {$IP[3]}, " .
      	"{$FIP[0]}, {$FIP[1]}, {$FIP[2]}, {$FIP[3]}, " .
	"0, $AID, 0, $cookieVal, $cookieNew)");
}

function multi_check_lastlog(&$sql, $AID)
{
    $AID=makeinteger($AID);
    $Vs=$sql->query("SELECT * FROM NC_Login WHERE AID=$AID ORDER BY Date LIMIT 0, 1");
    $IP=array($Vs[0]['IP0'],$Vs[0]['IP1'],$Vs[0]['IP2'],$Vs[0]['IP3']);
    $FIP=array($Vs[0]['FIP0'],$Vs[0]['FIP1'],$Vs[0]['FIP2'],$Vs[0]['FIP3']);

    /* check if given IP is allowed for given player */
    $Allowed=$sql->query("SELECT count(*) AS C FROM NC_AllowedMulti WHERE "
	. "(AID=0 OR AID=$AID) AND (((IP0=0 OR IP0={$IP[0]}) AND "
	. "(IP1=0 OR IP1={$IP[1]}) AND (IP2=0 OR IP2={$IP[2]}) AND "
	. "(IP3=0 OR IP3={$IP[3]})) OR "
	. "((IP1=0 OR IP0={$FIP[0]}) AND (IP1=0 OR IP1={$FIP[1]}) AND "
	. "(IP2=0 OR IP2={$FIP[2]}) AND (IP3=0 OR IP3={$FIP[3]})))");
    if ($Allowed[0]['C']==0) {
      $Multies=$sql->query("SELECT AID FROM NC_Login WHERE AID!=$AID AND "
	. "((IP0={$IP[0]} AND IP1={$IP[1]} AND IP2={$IP[2]} AND IP3={$IP[3]}) "
	. "OR (FIP0={$IP[0]} AND FIP1={$IP[1]} AND "
	. "FIP2={$IP[2]} AND FIP3={$IP[3]}))");
      foreach($Multies as $Multi)
	$Ans[]=makeinteger($Multi['AID']);
      if (count($Ans)>0)
	$Ans[]=$AID;
      return $Ans;
    }
    return array();
}

function multi_punish(&$sql, $AID, $reason)
{
  $AID=makeinteger($AID);
  $PID=account_get_pid($sql, $AID);
  if ($PID!=0)
  {
    if (!account_is_multi($sql, $AID))
    {
      $sql->query("UPDATE NC_Account SET Multi=100 WHERE AID=$AID");
      news_set($sql, $PID, "You are marked as multi.<br>
	  You may continue playing but you may be resigned in the nearest future<br>
	  if you do not clarify your situation.<br>
	  Please contact MultiAdmin through PM if you think it is a mistake<br>
	  Do not resign. It will not solve the problem.<br>
	  <br>
	  This is only an informative text. If, besides this message, you don't see any huge red warning above
	  it means you are not locked anymore.", 0);
    }
  }
}


function multi_get_log(&$sql, $from=0)
{
    return $sql->query(
	  "SELECT A.Nick, L.* FROM NC_Login L JOIN NC_Account A ON A.AID=L.AID "
	. " ORDER BY L.IP0, L.IP1, L.IP2, L.IP3, L.FIP0, L.FIP1, L.FIP2, L.FIP3 LIMIT $from, 100");
}

function multi_ip(&$sql, $ip)
{
    $ip=ip_separate($ip);
    return $sql->query("SELECT A.Nick, L.* FROM NC_Login L JOIN NC_Account A "
	. "ON A.AID=L.AID WHERE L.IP0={$ip[0]} AND L.IP1={$ip[1]} "
	. "AND L.IP2={$ip[2]} AND L.IP3={$ip[3]}");
}

function multi_find(&$sql, $AID)
{
    $AID=makeinteger($AID);
    return $sql->query("SELECT A.Nick, L.* FROM NC_Login LB JOIN NC_Login L "
	. "ON L.IP0=LB.IP0 AND L.IP1=LB.IP1 AND L.IP2=LB.IP2 AND L.IP3=LB.IP3 "
	. "AND LB.AID=$AID "
	. "JOIN NC_Account A ON A.AID=L.AID "
	. "ORDER BY L.IP0, L.IP1, L.IP2, L.IP3");
}

function multi_get_log_size(&$sql)
{
    $V=$sql->query("SELECT count(*) AS C FROM NC_Login");
    return makeinteger($V[0]['C']);
}

function multi_get_exception_list(&$sql, $from=0)
{
    $from=makeinteger($from);
    return $sql->query("SELECT A.Nick, E.* FROM NC_AllowedMulti E LEFT JOIN NC_Account A ON A.AID=E.AID ORDER BY AID LIMIT $from, 100");
}

function multi_get_exception_size(&$sql)
{
    $V=$sql->query("SELECT count(*) AS C FROM NC_AllowedMulti");
    return makeinteger($V[0]['C']);
}

function multi_add_exception(&$sql, $AID, $IP0, $IP1, $IP2, $IP3)
{
    $AID=makeinteger($AID);
    $IP0=makeinteger($IP0);
    $IP1=makeinteger($IP1);
    $IP2=makeinteger($IP2);
    $IP3=makeinteger($IP3);
    $sql->query("INSERT INTO NC_AllowedMulti VALUES "
	."($AID, $IP0, $IP1, $IP2, $IP3)");
}

function multi_delete_exception(&$sql, $AID, $IP)
{
    $AID=makeinteger($AID);
    $IP=ip_separate($IP);
    $sql->query("DELETE FROM NC_AllowedMulti WHERE AID=$AID AND "
	. "IP0={$IP[0]} AND IP1={$IP[1]} AND IP2={$IP[2]} AND IP3={$IP[3]}");
}

function multi_get_watchlist(&$sql) {
  return $sql->query("SELECT W.*, P.Nick FROM NC_MultiWatchlist W "
      . "JOIN NC_Account P ON P.AID=W.AID WHERE P.Multi=0 AND W.Strength>0 "
      . " ORDER By W.Strength DESC");
}

?>
