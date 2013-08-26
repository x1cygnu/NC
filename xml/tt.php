<?php

chdir('..');
include_once("internal/xml.php");
include_once("internal/common.php");
include_once("internal/security/validator.php");
include_once("internal/player.php");
include_once("internal/fleet.php");

session_start();

global $GET;
$GET=array();

$X = new XML("NC");

$sql=&OpenSQL();

if (!CheckActivePlayerXML($sql, $X)) {$X->Draw(); die;}
if (!CheckFrozenXML($sql, $X)) {$X->Draw(); die;}

$Rcs=$sql->query("SELECT Speed FROM NC_Player WHERE PID={$_SESSION['PID']}");
$Spd=$Rcs[0]['Speed'];
$Wrp=player_get_science($sql, $_SESSION['PID'], "Warp");

get("wrp","integer");
if (isset($GET['wrp']))
    $Wrp=$GET['wrp'];
get("spd","integer");
if (isset($GET['spd']))
    $Spd=$GET['spd'];
get("FromS","integer");
get("ToS","integer");
get("FromO","integer");
get("ToO","integer");

get("Vpr","integer");
get("Int","integer");
get("Fr","integer");
get("Bs","integer");
get("Drn","integer");
get("CS","integer");
get("Tr","integer");

get("FromPID","integer");
if (isset($GET['FromPID']))
{
    $coords=planet_get_position($sql, $GET['FromPID']);
    if (isset($coords['SID']))
    {
	$GET['FromS']=$coords['SID'];
	$GET['FromO']=$coords['Ring'];
    }
    else
	$X->Insert(new XMLEntity("error","Incorrect start planet ID"));
}
else
{
    if (!isset($GET['FromS']))
	$X->Insert(new XMLEntity("error","Missing start system ID"));
    elseif (!isset($GET['FromO']))
	$X->Insert(new XMLEntity("error","Missing start orbit number"));
}

get("ToPID","integer");
if ($GET['ToPID']>0)
{
    $coords=planet_get_position($sql, $GET['ToPID']);
    if (isset($coords['SID']))
    {
    $GET['ToS']=$coords['SID'];
    $GET['ToO']=$coords['Ring'];
    }
    else
	$X->Insert(new XMLEntity("error","Incorrect destination planet ID"));
}
else
{
    if (!isset($GET['ToS']))
	$X->Insert(new XMLEntity("error","Missing destination system ID"));
    elseif (!isset($GET['ToO']))
	$X->Insert(new XMLEntity("error","Missing destination orbit number"));
}


if ($GET['FromS']>0 and $GET['ToS']>0 and $GET['FromO']>=0 and $GET['ToO']>=0 and $GET['FromO']<=18 and $GET['ToO']<=18)
{
	$fleetSpeed=fleet_get_speed($GET['Vpr'],$GET['Int'],$GET['Fr'],$GET['Bs'],$GET['Drn'],$GET['CS'],$GET['Tr']);
    $From=starsystem_get_coords($sql,$GET['FromS']);
    $To=starsystem_get_coords($sql,$GET['ToS']);
    $From['Ring']=$GET['FromO'];
    $To['Ring']=$GET['ToO'];
    $details=array();
    $TT=makeinteger(fleet_time($sql, $_SESSION['PID'],$From,$To,$Spd,$Wrp,$fleetSpeed,$details));
    if ($TT>0)
    {
    $R=new XMLEntity("traveltime");
    $R->Insert(new XMLEntity("seconds","$TT"));
    $STLprc=round($details['STL']*100/$details['TT']);
    $R->Insert(new XMLEntity("STL","$STLprc"));
    $FTLprc=100-$STLprc;
    $R->Insert(new XMLEntity("FTL","$FTLprc"));
    $ETA=ApplyTimezone(EncodeNow()+$TT);
    $R->Insert(new XMLEntity("ETA","$ETA"));
    $X->Insert($R);
    }
    else
    $X->Insert(new XMLEntity("error","Start or destination point does not exist"));
}
else
    $X->Insert(new XMLEntity("error","Incorrect data"));
    
$X->Draw();
CloseSQL($sql);
?>
