<?php

include_once("./internal/common.php");
include_once("./internal/building.php");
include_once("./internal/player.php");
include_once("./internal/fleet.php");

function missionID($missionName) {
        if ($missionName=="Attack") return 1;
        if ($missionName=="Kamikaze") return 2;
        if ($missionName=="Raid") return 3;
        if ($missionName=="Scout") return 4;
        if ($missionName=="Retreat") return 5;
        return 0;
}

$missionTypeCount = 5;

function missionName($missionID) {
        if ($missionID==1) return "Attack";
        if ($missionID==2) return "Kamikaze";
        if ($missionID==3) return "Raid";
        if ($missionID==4) return "Scout";
        if ($missionID==5) return "Retreat";
        return "Unknown";
}


function fleetgroup_get_arity(&$sql, $FGID) {
        $FGID = makeinteger($FGID);
        $c = $sql->query("SELECT count(*) AS C FROM NC_FleetGroup WHERE FGID=$FGID");
        return $c[0]['C'];
}

function fleetgroup_delete(&$sql, $FGID) {
	$FGID = makeinteger($FGID);
	$sql->query("DELETE FROM NC_FleetGroup WHERE FGID=$FGID");
	$sql->query("DELETE FROM NC_FleetStationary WHERE FGID=$FGID");
	$sql->query("DELETE FROM NC_FleetMoving WHERE FGID=$FGID");
}

function fleetgroup_member_drop(&$sql, $FGID, $FID) {
	$sql->query("DELETE FROM NC_FleetGroup WHERE FID=$FID AND FGID=$FGID");
	$A = fleetgroup_get_arity($sql, $FGID);
	if ($A == 0)
		fleetgroup_delete(&$sql, $FGID);
}

function fleetgroup_get_all(&$sql, $FGID) {
	$FGID = makeinteger($FGID);
	$F = $sql->query("SELECT * FROM NC_FleetGroup FG" .
			" LEFT JOIN NC_FleetStationary FS ON FG.FGID=FS.FGID" .
			" LEFT JOIN NC_FleetMoving FM ON FG.FGID=FM.FGID"
			" WHERE FG.FGID=$FGID");
	return $F[0];
}

function fleetgroup_get_fleets_and_players(&$sql, $FGID) {
	$FGID = makeinteger($FGID);
	return $sql->query("SELECT * FROM NC_FleetGroup FG" .
		" JOIN NC_Fleet F ON FG.FID=F.FID" .
		" JOIN NC_Player P ON P.PID=F.Owner");
}

function planetfleet_get_fleets_and_players(&$sql, $PLID) {
  $PLID = makeinteger($PLID);
  return $sql->query("SELECT * FROM NC_FleetStationary FS" .
      " JOIN NC_FleetGroup FG ON FG.FGID=FS.FGID" .
      " JOIN NC_Fleet F ON F.FGID=FG.FGID" .
      " JOIN NC_Player P ON P.PID=F.Owner" .
      " WHERE FS.Location = $PLID");
}

function fleetgroup_join(&$sql, $FGID1, $FGID2) {
	$FGID1 = makeinteger($FGID1);
	$FGID2 = makeinteger($FGID2);
	if ($G1 == $G2)
		return "Selected fleets are already joined";
	$fleet1 = fleetgroup_get_all($sql, $FGID1);
	$fleet2 = fleetgroup_get_all($sql, $FGID2);
	if ($fleet1['Location'] != $fleet2['Location'] or $fleet1['FMID'] != $fleet2['FMID'])
		return "Selected fleets are not in the same place";
	$sql->query("UPDATE NC_FleetGroup SET FGID=$FGID1 WHERE FGID=$FGID2");
	$sql->query("DELETE FROM NC_FleetStationary WHERE FGID=$FGID2");
	$sql->query("DELETE FROM NC_FleetMoving WHERE FGID=$FGID2"); //this probably will never happen, but for the sake of completeness...
}

function fleetgroup_compute_combat_sql(&$sql, $FGID) {
	$FGID = makeinteger($FGID);
	$Fs = fleetgroup_get_fleets_and_players($sql, $FGID);
	return fleetgroup_compute_combat($Fs);
}

function fleetgroup_compute_combat($Fleets) {
	global $anyships;
	$ans['AV']=0.0;
	$ans['DV']=0.0;
	foreach($Fleets as $F) {
		$V = fleet_compute_combat_arr($F);
		$ans['AV']+=$V['AV'];
		$ans['DV']+=$V['DV'];
	}
	return $ans;
}


?>
