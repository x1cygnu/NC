<?php

// Account semi-class

include_once("./internal/common.php");
include_once("./internal/security/validator.php");
include_once("./internal/player.php");
include_once("./internal/forumfunc.php");
include_once("./internal/group.php");
include_once("./internal/log.php");
include_once("./internal/multi.php");
include_once("./internal/ip.php");

function account_create(&$sql, $nick, $password, $email, $regcode)
{
	$Log=log_entry($sql,"acc create", $_SERVER['REMOTE_ADDR'],
			$_SERVER['REMOTE_HOST'],
			$_SERVER['REMOTE_PORT'], $nick, $email);
	if (ipban_block_check($sql, $_SERVER['REMOVE_ADDR']) ||
			ipban_block_check($sql, $_SERVER['HTTP_X_FORWARDED_FOR'])) {
		log_result($sql, $Log, "ipban");
		print("Error 404\n");
		die;
	}
	$email=makequotedstring($email);
	$nickq=makequotedstring($nick);
	$regcode=makequotedstring($regcode);
	$passmd5=makequotedstring(md5($password));
	$c=$sql->query("SELECT count(*) AS C FROM NC_Account WHERE Nick=$nickq");
	if ($c[0]['C']>0)
	{
		log_result($sql, $Log, "already");
		return false;
	}
	$Now=EncodeNow();
	$sql->query("INSERT INTO NC_Account VALUES (NULL, $nickq, $passmd5, \"\", 0, 0, 0, $email, 0, 0, $Now, \"\", \"\", 1, 1, 1, 0, 0, 0, 0)");
	//    $pid=$sql->query("SELECT LAST_INSERT_ID() AS ID");
	//    $sql->query("INSERT INTO NC_Regcode VALUES ({$pid[0]['ID']}, $regcode)");

	log_result($sql, $Log, "OK");
	return true;
}

function account_remove(&$sql, $name, $password) {
    $Log=log_entry($sql,"acc remove", $_SERVER['REMOTE_ADDR'],
		$_SERVER['REMOTE_HOST'],
		$_SERVER['REMOTE_PORT'], $nick);
    $name=makequotedstring($name);
    $passmd5=makequotedstring(md5($password));
    $V=$sql->query("SELECT PID FROM NC_Account WHERE Nick=$name AND password=$passmd5");
    if (count($V)==0) {
	log_result($sql,$Log,"inv pass");
	return "Player not found or invalid password";
    }
    if ($V[0]['PID']>0) {
	log_result($sql,$Log,"active");
	return "You must resign in-game before you remove your account";
    }
    $sql->query("DELETE FROM NC_Account WHERE Nick=$name AND password=$passmd5");
    log_result($sql,$Log,"OK");
    return true;
}

function account_get_id(&$sql, $nick)
{
    $nick=makequotedstring($nick);
    $answer=$sql->query("SELECT AID FROM NC_Account WHERE nick=$nick");
    if (count($answer)==0)
	return NULL;
    else
	return $answer[0]['AID'];
}

function account_get_pid(&$sql, $AID)
{
    $AID=makeinteger($AID);
    $answer=$sql->query("SELECT PID FROM NC_Account WHERE AID=$AID");
    if ((count($answer)==0) or ($answer[0]['PID']==0))
	return NULL;
    else
	return $answer[0]['PID'];
}

function account_get_pid_from_nick(&$sql, $Nick)
{
    $Nick=makequotedstring($Nick);
    $answer=$sql->query("SELECT PID FROM NC_Account WHERE NICK=$Nick");
    if ((count($answer)==0) or ($answer[0]['PID']==0))
	return NULL;
    else
	return $answer[0]['PID'];
}

function account_assign_login_thread(&$sql) {
  $Answer=$sql->query("SELECT GET_LOCK(\"cygnus NC loginthread\",10) AS L");
  if ($Answer[0]['L']==0)
    return 0;
  $R=$sql->query("Select LoginThread From NC_globalsettings");
  $sql->query("UPDATE NC_globalsettings SET LoginThread=LoginThread+1");
  $sql->query("SELECT RELEASE_LOCK(\"cygnus NC loginthread\")");
  return $R[0]['LoginThread']+1;

}

function account_login(&$sql, $nick, $password)
{
	$Log=log_entry($sql,"login", $_SERVER['REMOTE_ADDR'],
			$_SERVER['HTTP_X_FORWARDED_FOR'],
			$_SERVER['REMOTE_PORT'], $nick);
	$passmd5=makequotedstring(md5($password));
	$nick=makequotedstring($nick);
	$account=$sql->query("SELECT AC.*, P.TAG, IF(R.Code IS NULL,1,0) AS Registered "
			. " FROM NC_Account AC" 
			. " LEFT JOIN NC_Regcode R ON AC.AID=R.AID "
			. " LEFT JOIN NC_Player P ON AC.PID=P.PID "
			. " WHERE AC.nick=$nick AND AC.password=$passmd5");
	if (count($account)==0)
	{
		log_result($sql, $Log, "inv pass");
		return false;
	}
	if (count($account)>1)
	{
		log_result($sql, $Log, "multiple");
		return false;
	}
	if ($account[0]['Registered']==0)
	{
		log_result($sql, $Log, "not reg");
		return false;
	}
	if (ipban_block_check(&$sql, $_SERVER['REMOVE_ADDR']) ||
			ipban_block_check(&$sql, $_SERVER['HTTP_X_FORWARDED_FOR'])) {
		log_result($sql, $Log, "ipban");
		print("Error 404\n");
		die;
	}
	if ($account[0]['Multi']>=9999) {
		log_result($sql, $Log, "permban");
		print("Error 404\n");
		die;
	}
	$previousAID=$_SESSION['AID'];
	$previousSecretAID=$_SESSION['SAID'];
	$_SESSION['AID']=makeinteger($account[0]['AID']);
	$_SESSION['SAID']=$_SESSION['AID'];
	$_SESSION['PID']=makeinteger($account[0]['PID']);
	$_SESSION['TAG']=$account[0]['TAG'];
	$_SESSION['PermTAG']=$account[0]['PermTAG'];
	$_SESSION['SitPID']=makeinteger($account[0]['SitPID']);
	if ($account[0]['SitFrom']<EncodeNow())
		$_SESSION['SitPID']=makeinteger($account[0]['SitPID']);
	else
		$_SESSION['SitPID']=0;
	$_SESSION['nick']=$account[0]['nick'];
	$_SESSION['TimeZone']=$account[0]['TimeZone']/10;
	$_SESSION['IsAdmin']=$account[0]['ForumAdmin'];
	$_SESSION['ConfirmLaunch']=$account[0]['FleetConfirmation'];
	$_SESSION['LastLogin']=makeinteger($account[0]['LastLogin']);
	$_SESSION['MapBackground']=$account[0]['MapBackground'];
	$_SESSION['DefMapX']=$account[0]['DefMapX'];
	$_SESSION['DefMapY']=$account[0]['DefMapY'];
	$_SESSION['DefMapR']=$account[0]['DefMapR'];
	$_SESSION['Hint']=$account[0]['Hint'];
	$newThread=false;
	if ($_COOKIE['LoginThread']==0) {
		$newThread=true;
		$v=account_assign_login_thread($sql);
		setcookie('LoginThread',$v,time()+315360000);
		$_COOKIE['LoginThread']=$v;
	}
	if (isset($_COOKIE['NC_Ajax']))
		$_SESSION['Ajax']=makeinteger($_COOKIE['NC_Ajax']);
	else
		$_SESSION['Ajax']=1;
	log_result($sql,$Log, "OK");
	forum_mark_unread($sql,$_SESSION['AID'],$_SESSION['LastLogin']);
	$sql->query("UPDATE NC_Account SET LastLogin=" . EncodeNow() . " WHERE AID={$_SESSION['AID']}");
	if ($_SESSION['PID']!=0)
		player_update_all($sql,$_SESSION['PID']);
	if ($_SESSION['SitPID']!=0)
		player_update_all($sql,$_SESSION['SitPID']);
	multi_log_login($sql,
			$previousSecretAID,
			$previousAID,
			$_SESSION['AID'],
			$_SERVER['REMOTE_ADDR'],
			$_SERVER['HTTP_X_FORWARDED_FOR'],
			true,
			$newThread,
			$_COOKIE['LoginThread']);
	$Multis=multi_check_lastlog($sql, $_SESSION['AID']);
	if (count($Multis)>0)
	{
		foreach ($Multis as $MultiAID)
		{
			multi_punish($sql, $MultiAID, "..");
		}
		multi_punish($sql, $_SESSION['AID'], "..");
	}

	return true;
}

function account_su(&$sql, $nick)
{
    $Log=log_entry($sql,"su", $_SERVER['REMOTE_ADDR'],
		    $_SERVER['REMOTE_HOST'],
		    $_SERVER['REMOTE_PORT'], $nick);
    if (!$_SESSION['IsAdmin'])
	{
	log_result($sql, $Log, "Must be admin");
	return "Must be an admin";
	}
    $nick=makequotedstring($nick);
    $account=$sql->query("SELECT AC.*, IF(R.Code IS NULL,1,0) AS Registered FROM NC_Account AC LEFT JOIN NC_Regcode R ON AC.AID=R.AID WHERE AC.nick=$nick");
    if (count($account)==0)
	{
	log_result($sql, $Log, "not found");
	return "Player not found";
	}
    if (count($account)>1)
	{
	log_result($sql, $Log, "multiple");
	return "Multiple players under this nick! (bug)";
	}
    if ($account[0]['Registered']==0)
	{
	log_result($sql, $Log, "not reg");
	return "Player not registered";
	}
    $_SESSION['AID']=$account[0]['AID'];
    $_SESSION['PID']=$account[0]['PID'];
    $_SESSION['nick']=$account[0]['nick'];
    $_SESSION['TimeZone']=$account[0]['TimeZone']/10;
    $_SESSION['DefMapX']=$account[0]['DefMapX'];
    $_SESSION['DefMapY']=$account[0]['DefMapY'];
    $_SESSION['DefMapR']=$account[0]['DefMapR'];
    $_SESSION['PermTAG']=$account[0]['PermTAG'];
    unset($_SESSION['starsystem_bio']);
//    $_SESSION['IsAdmin']=$account[0]['ForumAdmin'];
    log_result($sql,$Log, "OK");
    return "";
}


function account_logout(&$sql)
{
    $Log=log_entry($sql,"logout");

    $newThread=false;
    if ($_COOKIE['LoginThread']==0) {
      $newThread=true;
      $v=account_assign_login_thread($sql);
      setcookie('LoginThread',$v,time()+315360000);
      $_COOKIE['LoginThread']=$v;
    }

    $v=makeinteger($_COOKIE['LoginThread']);
    multi_log_logout($sql,$_SESSION['AID'],
	$_SERVER['REMOTE_ADDR'],
	$_SERVER['HTTP_X_FORWARDED_FOR'],
	$newThread,
	$_COOKIE['LoginThread']);

    unset($_SESSION['AID']);
    unset($_SESSION['PID']);
    unset($_SESSION['nick']);
    unset($_SESSION['TimeZone']);
    unset($_SESSION['IsAdmin']);
    unset($_SESSION['ConfirmLaunch']);
    unset($_SESSION['MapBackground']);
    unset($_SESSION['starsystem_bio']);
    unset($_SESSION['DefMapX']);
    unset($_SESSION['DefMapY']);
    unset($_SESSION['DefMapR']);
    log_result($sql,$Log,"OK");
}

function account_get_name_from_pid(&$sql, $pid)
{
    $pid=makeinteger($pid);
    $A=$sql->query("SELECT Nick FROM NC_Account WHERE PID=$pid");
    return $A[0]['Nick'];
}

function account_get_admin_from_pid(&$sql, $pid)
{
    $pid=makeinteger($pid);
    $A=$sql->query("SELECT ForumAdmin FROM NC_Account WHERE PID=$pid");
    return $A[0]['ForumAdmin'];
}

function account_get_name(&$sql, $aid)
{
    $aid=makeinteger($aid);
    $A=$sql->query("SELECT Nick FROM NC_Account WHERE AID=$aid");
    return $A[0]['Nick'];
}


function account_get_email(&$sql, $aid)
{
    $aid=makeinteger($aid);
    $A=$sql->query("SELECT email FROM NC_Account WHERE AID=$aid");
    return $A[0]['email'];
}

function account_get_avatar(&$sql, $aid)
{
    $aid=makeinteger($aid);
    $A=$sql->query("SELECT Avatar FROM NC_Account WHERE AID=$aid");
    return $A[0]['Avatar'];
}

function account_get_background_sig(&$sql, $aid)
{
    $aid=makeinteger($aid);
    $A=$sql->query("SELECT BackgroundSig FROM NC_Account WHERE AID=$aid");
    return $A[0]['BackgroundSig'];
}

function account_get_id_from_pid(&$sql, $pid)
{
    $pid=makeinteger($pid);
    $A=$sql->query("SELECT AID FROM NC_Account WHERE PID=$pid");
    return $A[0]['AID'];
}

function account_get_perm_tag(&$sql, $aid) {
  $aid=makeinteger($aid);
  $A=$sql->query("SELECT PermTAG from NC_Account WHERE AID=$aid");
  return $A[0]['PermTAG'];
}

function account_update(&$sql, $AID, $tz, $pass, $pass2, $email, $avatar, $bsig)
{
    $Log=log_entry($sql, "acc upd");
    $change=false;
    if ($tz!=="")
	{
	$change=true;
	$_SESSION['TimeZone']=makereal($tz);
	$htz=floor($_SESSION['TimeZone']*10);
	log_update($sql, $Log, 1, $htz);
	}
    if ($pass==$pass2 and $pass!="")
	{
	$change=true;
	$passmd5=makequotedstring(md5($pass));
	log_update($sql, $Log, 2, "new pass");
	}
    if ($email!="")
	{
	$change=true;
	$email=makequotedstring($email);
	log_update($sql, $Log, 3, $email);
	}
    if ($avatar!="")
    {
	$change=true;
	$avatar=makequotedstring($avatar);
	log_update($sql, $Log, 4, $avatar);
    }
    if ($bsig!="")
    {
	$change=true;
	$bsig=makequotedstring($bsig);
	log_update($sql, $Log, 5, $bsig);
    }
    if (!$change)
    {
	log_result($sql, $Log, "no change");
	return 'No changes were made';
	}
    if ($pass!=$pass2)
    {
	log_result($sql, $Log, "inv pass");
	return "Passwords don't match";
	}
    $S="UPDATE NC_Account SET ";
    if ($tz!=="")
	$S.="TimeZone=$htz, ";
    if ($passmd5!="")
	$S.="Password=$passmd5, ";
    if ($email!="")
	$S.="Email=$email, ";
    if ($avatar!="")
	$S.="Avatar=$avatar, ";
    if ($bsig!="")
	$S.="BackgroundSig=$bsig, ";
    $S.="PID=PID WHERE AID=$AID";
    $sql->query($S);
    log_result($sql, $Log, "OK");
    return "";
}

function account_check_password_for_pid(&$sql, $pid, $password)
{
    $pid=makeinteger($pid);
    $password=makequotedstring(md5($password));
    $A=$sql->query("SELECT count(*) AS A FROM NC_Account WHERE PID=$pid AND Password=$password");
    return ($A[0]['A']==1);
}

function account_set_confirm_launch(&$sql, $aid, $nval)
{
    $nval=makeinteger($nval);
    $aid=makeinteger($aid);
    $sql->query("UPDATE NC_Account SET FleetConfirmation=$nval WHERE AID=$aid");
}

function account_set_hint(&$sql, $aid, $nval)
{
    $nval=makeinteger($nval);
    $aid=makeinteger($aid);
    $sql->query("UPDATE NC_Account SET Hint=$nval WHERE AID=$aid");
}

function account_set_map_background(&$sql, $aid, $nval)
{
    $nval=makeinteger($nval);
    $aid=makeinteger($aid);
    $sql->query("UPDATE NC_Account SET MapBackground=$nval WHERE AID=$aid");
}

function account_set_map_defaults(&$sql, $aid, $X, $Y, $R)
{
    $nval=makeinteger($nval);
    $aid=makeinteger($aid);
    $X=makeinteger($X);
    $Y=makeinteger($Y);
    $R=makeinteger($R);
    $sql->query("UPDATE NC_Account SET DefMapX=$X, DefMapY=$Y, DefMapR=$R WHERE AID=$aid");
}

function account_get_sit_from_nick(&$sql, $nick)
{
    $nick=makequotedstring($nick);
    $A=$sql->query("SELECT AID, Nick, PID, SitPID FROM NC_Account WHERE Nick=$nick");
    return $A[0];
}

function account_sit(&$sql, $ownerPID, $sitterAID)
{
    $ownerPID=makeinteger($ownerPID);
    $sitterAID=makeinteger($sitterAID);
    $sql->query("UPDATE NC_Account SET SitPID=$ownerPID WHERE AID=$sitterAID");
    $sql->query("UPDATE NC_Player SET SitAID=$sitterAID, SitCount=SitCount+1 WHERE PID=$ownerPID");
    
}

function account_sit_free(&$sql, $ownerPID, $sitterAID)
{
    $ownerPID=makeinteger($ownerPID);
    $sitterAID=makeinteger($sitterAID);
    $sql->query("UPDATE NC_Account SET SitPID=0 WHERE AID=$sitterAID");
    $sql->query("UPDATE NC_Player SET SitAID=0 WHERE PID=$ownerPID");    
}

function account_is_multi(&$sql, $aid)
{
    $aid=makeinteger($aid);
    $Ans=$sql->query("SELECT Multi FROM NC_Account WHERE AID=$aid");
    return ($Ans[0]['Multi']>0);
}

function account_get_multies(&$sql)
{
    return $sql->query("SELECT AID, Nick, PID, Multi FROM NC_Account A WHERE Multi>0");
}

function account_set_multi(&$sql, $aid, $value)
{
    $aid=makeinteger($aid);
    $value=makeinteger($value);
    $sql->query("UPDATE NC_Account SET Multi=$value WHERE AID=$aid");
}

?>
