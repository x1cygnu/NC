<?php
include_once("./internal/html.php");
include_once("./internal/xml.php");
include_once("./internal/error.php");
include_once("./internal/security/sqlconf.php");
include_once("./internal/security/sql.php");
include_once("./internal/armageddon.php");
include_once("./internal/account.php");

class Info extends Table
{
    function Info($sMessage)
    {
	Table::Table();
	$this->sClass = "info";
	$this->sId = "infocnt";
	$this->Insert(1,1,$sMessage);	
	$this->Get(1,1)->sId='info';
    }
}

class Suggestion extends Table
{
    function Suggestion($sMessage)
    {
	Table::Table();
	$this->sClass = "error";
	$this->sId = "suggestion";
	$this->Insert(1,1,$sMessage);	
    }
}


class Error extends Table
{
    function Error($sMessage)
    {
	Table::Table();
	$this->sClass = "error";
	$this->sId = "errorcnt";
	$this->Insert(1,1,$sMessage);	
	$this->Get(1,1)->sId="error";
    }
}

function &OpenSQL($H = NULL)
{
  $sql=new SQL(DB_HOST,DB_USER,DB_PASSWORD,DB_DATABASE,DB_PORT);
  $S=$sql->query("SELECT Status FROM NC_globalsettings");
    if ($S[0]['Status']==0)
	return $sql;
    else
	{
	if (isset($H))
	{
	    $H->Insert(new Error("Database is temporarly unavailable. Please try again soon"));
	    $H->Draw();
	    die;
	}
	return NULL;
	}
}

function CloseSQL(&$sql, $showcount=false)
{
    if ($showcount)
        echo $sql->querycount;
    $sql->Close();
}

function Relog(&$sql, &$H, $target)
{
    global $POST;
    post("login","string");
    post("password","string");
    if ($POST['login']!="" and $POST['password']!="")
    {
	if (account_login($sql, $POST['login'], $POST['password']))
	{
	    $H->Insert(new Info("Login successful"));
	    return true;
	}
	else
	{
	    include("part/mainmenu.php");
	    $H->Insert(new Error("Login failed"));
	}
    }
    else
	{    include("part/mainmenu.php");}

	    $F=new Form($target,true);
	    $T=new Table();
	    $T->sClass='block';
	    $T->Insert(1,1,"Login");
	    $T->Insert(1,2,"Name");
	    $T->Insert(1,3,"Password");
	    $T->Insert(2,2,new Input("text","login","","text"));
	    $T->Insert(2,3,new Input("password","password","","text"));
	    $T->Insert(1,4,new Input("submit","","Login","smbutton"));
	    $T->Join(1,1,2,1);
	    $T->Join(1,4,2,1);
	    $T->SetClass(1,1,'title');
	    $T->SetClass(1,2,'legend');
	    $T->SetClass(1,3,'legend');
	    $T->SetClass(1,4,'title');

    $F->Insert($T);
    $H->Insert($F);
    
    return false;
}


function def(&$variable, $defvalue)
{
    if (!isset($variable) or is_null($variable))
	$variable=$defvalue;
}

function CheckFrozen(&$sql)
{
//    return true;
    $U=round_get_frozen($sql);
    $Now=EncodeNow();
    return ($U['FrozenFrom']<$Now and $U['FrozenTo']>$Now);
}

function CheckFrozenBack(&$sql)
{
    $U=round_get_frozen($sql);
    $Now=EncodeNow();
    return ($U['FrozenFrom']<$Now and $U['FrozenDone']==0);
}


function CheckLogin()
{
    return (exists($_SESSION['AID']) and $_SESSION['AID']>0);
}

function CheckActivePlayer()
{
    return (exists($_SESSION['AID']) and $_SESSION['AID']>0 and $_SESSION['PID']>0);
}

function CheckPlayer()
{
    return (exists($_SESSION['AID']) and $_SESSION['AID']>0);
}


function ForceLogin(&$sql, &$H, $target)
{
    if (!CheckLogin())
	{
	    if (!Relog($sql, $H, $target))
	    {
	    $H->Insert(new Error("You have to login in order to see this page"));
	    include("part/mainsubmenu.php");
	    $H->Draw();
	    die;
	    }
	}
}

function CheckLoginXML(&$sql, &$X)
{
    if (!CheckLogin())
	{
	    $E=new XMLEntity("error");
	    $E->Insert("You are not logged in");
	    $X->Insert($E);
	    return false;
	}
    return true;
}


function ForceFrozen(&$sql, &$H)
{
    if (CheckFrozen($sql) and $_SESSION['IsAdmin']!=1)
    {
	include("part/mainmenu.php");
	$H->Insert(new Error("Game is frozen at the moment"));
	$U=round_get_frozen($sql);
	$H->Insert(DecodeTime($U['FrozenFrom']) . " - " . DecodeTime($U['FrozenTo']));
	include("part/mainsubmenu.php");
	$H->Draw();
	CloseSQL($sql);
	die;
    }
}

function ForceNoSitting(&$sql, $H, $pid)
{
    $pid=makeinteger($pid);
    if (player_get_sitted($sql, $pid)>0)
    {
	include("part/mainmenu.php");
	$H->Insert(new Error("You are currently being sitted by another player<br>You may end the Account Sitting agreement in the Settings"));
	include("part/mainsubmenu.php");
	$H->Draw();
	CloseSQL($sql);
	die;    
    }
}

function CheckFrozenXML(&$sql, &$X)
{
    if (CheckFrozen($sql) and $_SESSION['IsAdmin']!=1)
    {
	$E=new XMLEntity("error");
	$E->Insert("Game is frozen at the moment");
	$X->Insert($E);
	return false;
    }
    return true;
}



function ForceActivePlayer(&$sql, &$H, $target)
{
    if (!CheckLogin())
	Relog($sql, $H, $target);
    if (!CheckActivePlayer())
	{
	    $H->Insert(new Error("You have to be active player in order to see this page"));
	    include("part/mainsubmenu.php");
	    $H->Draw();
	    die;
	}    
}

function CheckActivePlayerXML(&$sql, $X)
{
    if (CheckLoginXML($sql, $X))
	{
        if (!CheckActivePlayer())
	{
	    $E=new XMLEntity("error");
	    $E->Insert("You must be an active player");
	    $X->Insert($E);
	    return false;
	}
	return true;
	}    
    return false;
}


function ForceAdmin(&$H)
{
    if (!$_SESSION['IsAdmin'])
	{
	    $H->Insert(new Error("You must be admin in order to see this page"));
	    $H->Insert("Do not hack, please ;)");
	    include("part/mainsubmenu.php");
	    $H->Draw();
	    die;
	}
}

function ForcePlayer(&$sql, &$H, $target)
{
    if (!CheckLogin())
	Relog($sql, $H, $target);
    if (!CheckPlayer())
	{
	    $H->Insert(new Error("You have to be active player in order to see this page"));
	    include("part/mainsubmenu.php");
	    $H->Draw();
	    die;
	}
    
}

function TrimUp(&$variable, $value)
{
    if ($variable>$value)
    {
	$variable=$value;
	return true;
    }
    return false;
}

function TrimDown(&$variable, $value)
{
    if ($variable<$value)
    {
	$variable=$value;
	return true;
    }
    return false;
}


function sqr($x) { return $x*$x;}

function random()
{
    return (mt_rand()*1.0)/mt_getrandmax();
}

function Lock(&$sql, $plid)
{
    $plid=makeinteger($plid);
    $result=$sql->query("SELECT GET_LOCK(\"danilewski AW planet $plid\",10) AS L");
    if ($result[0]['L']==0 or $result[0]['L']=="NULL")
	$GLOBALS['Error']=new CError("Unable to get a lock on a planet","Function cannot get a lock on a planet to apply the changes.<br>Some other thread is constantly blocking access.",1);
    return $result[0]['L'];
}

function Unlock(&$sql, $plid)
{
    $plid=makeinteger($plid);
    $sql->query("SELECT RELEASE_LOCK(\"danilewski AW planet $plid\")");
}

function DecodeTime($time,$TimeZone=NULL)
{
    if (!isset($TimeZone))
	$TimeZone=$_SESSION['TimeZone'];
    return strftime("%d %b %T",$time+$TimeZone*3600-date('Z'));

}

function DecodeTimeShort($time,$TimeZone=NULL)
{
    if (!isset($TimeZone))
	$TimeZone=$_SESSION['TimeZone'];
    return strftime("%d/%m %R",$time+$TimeZone*3600-date('Z'));
}


function FullDecodeTime($time,$TimeZone=NULL)
{
    if (!isset($TimeZone))
	$TimeZone=$_SESSION['TimeZone'];
    return strftime("%d %b %Y %T",$time+$TimeZone*3600-date('Z'));

}

function EncodeTime($time,$TimeZone=NULL)
{
    if (!isset($TimeZone))
	$TimeZone=$_SESSION['TimeZone'];
    return strtotime($time)-$TimeZone*3600+date('Z');
}

function EncodeNow()
{
  return time();
//    return time()+970;
}

function ApplyTimezone($UnixTime,$TimeZone=NULL)
{
    if (!isset($TimeZone))
	$TimeZone=$_SESSION['TimeZone'];
    return $UnixTime+$TimeZone*3600;
}

function global_lock(&$sql)
{
    $sql->query("UPDATE NC_globalsettings SET Status=1");
}

function global_unlock(&$sql)
{
    $sql->query("UPDATE NC_globalsettings SET Status=0");
}

function time_period($u)
{
    $S="";
    if ($u>86400)
	{
	    $d=floor($u/86400);
	    $u=$u-$d*86400;
	    $S="{$d}d";
	}
    if ($u>3600)
	{
	    $d=floor($u/3600);
	    $u=$u-$d*3600;
	    $S.="{$d}h";
	}
    if ($u>60)
	{
	    $d=floor($u/60);
	    $u=$u-$d*60;
	    $S.="{$d}m";
	}
    $u=round($u);
    $S.="{$u}s";
    return $S;
}

function time_period_short($u)
{
    $S="";
    $g=0;
    if ($u>86400)
	{
	    $d=floor($u/86400);
	    $u=$u-$d*86400;
	    $S="{$d}d";
	    ++$g;
	}
    if ($u>3600)
	{
	    $d=floor($u/3600);
	    $u=$u-$d*3600;
	    $S.="{$d}h";
	    ++$g;
	}
    if ($g==2) return $S;
    if ($u>60)
	{
	    $d=floor($u/60);
	    $u=$u-$d*60;
	    $S.="{$d}m";
	    ++$g;
	}
    if ($g==2) return $S;
    $u=round($u);
    $S.="{$u}s";
    return $S;
}

function ThemeUseBackgroundImage()
{
    if (
	($_COOKIE['NC_StyleTheme']=='dark') or
	($_COOKIE['NC_StyleTheme']==''))
	return true;
    return false;
}

function PostControl($usePost)
{
    if ($usePost)
	$v=(isset($_POST['orderid'])?makeinteger($_POST['orderid']):-1);
    else
	$v=(isset($_GET['orderid'])?makeinteger($_GET['orderid']):-1);
    if (isset($_SESSION['PostCode']) and $_SESSION['PostCode']>=$v)
	return false;
    $_SESSION['PostCode']=$v;
    return true;
}

?>
