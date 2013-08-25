<?php

include_once("./internal/news.php");
include_once("./internal/log.php");
// alliance semi-class

function alliance_get_members(&$sql, $tag)
{
	$tag=makequotedstring($tag);
	return $sql->query("SELECT A.AID, PP.*, IF(A.Nick IS NULL,\"unknown\",A.Nick) AS Nick, "
			. " SUM(Cybernet*(PT.Culture/100)) AS C,"
			. " SUM((Lab+Population)*(PT.Science/100)) AS L,"
		  . "	SUM((Factory+Population)*(PT.Production/100)) AS P,"
			. " SUM(Population) AS Pop, count(*) AS PCount, SUM(CultureSlot) AS PCulCount, ArtL.Short"
			. " FROM NC_Player PP LEFT JOIN NC_Planet Pl ON Pl.Owner=PP.PID JOIN NC_PlanetType PT ON Pl.Type=PT.PTID LEFT JOIN NC_Account A ON A.PID=PP.PID "
			. " LEFT JOIN NC_Artefact Art ON Art.PID=PP.PID AND Art.InUse=1"
			. " LEFT JOIN NC_ArtefactList ArtL ON Art.Artefact=ArtL.ARID"
			. " WHERE PP.TAG=$tag GROUP BY PP.PID ORDER BY PP.Rank");
}

function alliance_get_member_pid_by_index(&$sql, $index, $tag)
{
	$tag=makequotedstring($tag);
	$index=makeinteger($index);
	$A=$sql->query("SELECT PID"
			. " FROM NC_Player "
			. " WHERE TAG=$tag ORDER BY Rank LIMIT $index, 1");
	return $A[0]['PID'];
}


function alliance_get_founder(&$sql, $tag)
{
	$tag=makequotedstring($tag);
	$A=$sql->query("SELECT Founder FROM NC_AlliancePermanent WHERE TAG=$tag");
	return $A[0]['Founder'];
}

function alliance_get_name(&$sql, $tag)
{
	$tag=makequotedstring($tag);
	$A=$sql->query("SELECT Name FROM NC_AlliancePermanent WHERE TAG=$tag");
	return $A[0]['Name'];
}

function alliance_add_member(&$sql, $tag, $pid)
{
	$Log=log_entry($sql,"alliance add",$tag);
	$tag=makequotedstring($tag);
	$playertag=player_get_tag($sql,$pid);
	$aid=account_get_id_from_pid($sql, $pid);
	if ($playertag!="")
	{
		log_result($sql,$Log,"already");
		return "You are already in an alliance";
	}
	$sql->query("UPDATE NC_Player SET TAG=$tag WHERE PID=$pid");
	$sql->query("UPDATE NC_Account SET PermTAG=$tag WHERE AID=$aid");
	$sql->query("UPDATE NC_Sections SET OwnerTag=$tag WHERE Owner=$aid");
	$sql->query("UPDATE NC_Alliance SET NoMembers=NoMembers+1 WHERE TAG=$tag");
	log_result($sql,$Log,"OK");
	return "";
}

function alliance_add_perm_member(&$sql, $tag, $aid)
{
	$Log=log_entry($sql,"alliance add",$tag);
	$tag=makequotedstring($tag);
	$playertag=account_get_perm_tag($sql,$aid);
	$aid=makeinteger($aid);
	if ($playertag!="")
	{
		log_result($sql,$Log,"already");
		return "Player already in a group";
	}
	$sql->query("UPDATE NC_Account SET PermTAG=$tag WHERE AID=$aid");
	$sql->query("UPDATE NC_Sections SET OwnerTag=$tag WHERE Owner=$aid");
	log_result($sql,$Log,"OK");
}

function alliance_remove_perm_member(&$sql, $aid, $caller) {
	$caller=makeinteger($caller);
	$accounttag=account_get_perm_tag($sql,$aid);
	if ($accounttag=="")
		return "Player is not in the group";
	$aid=makeinteger($aid);
	$pid=account_get_pid($sql, $aid);
	if ($pid!=0) {
		$playertag=player_get_tag($sql, $pid);
		if ($playertag!="")
			return "Player already got ingame tag";
	}
	$founder=alliance_get_founder($sql, $accounttag);
	if ($founder==$aid)
		return "Group leader cannot be removed";
	if ($founder!=$caller and $aid!=$caller)
		return "Only group leader may kick players of his group";
	$sql->query("UPDATE NC_Account SET PermTAG=\"\" WHERE AID=$aid");
}

function alliance_perm_disband(&$sql, $tag) {
	$tag=makequotedstring($tag);
	$sql->query("UPDATE NC_Account SET PermTAG=\"\" WHERE PermTAG=$tag");
	$sql->query("UPDATE NC_Sections SET OwnerTag=\"\" WHERE OwnerTag=$tag");
	$sql->query("DELETE FROM NC_Permissions WHERE TAG=$tag");
	$sql->query("DELETE FROM NC_AlliancePermanent WHERE TAG=$tag");
}

function alliance_find_which_am_i_founding(&$sql, $aid) {
	$aid=makeinteger($aid);
	$A=$sql->query("SELECT TAG FROM NC_AlliancePermanent WHERE Founder=$aid");
	return $A[0]['TAG'];
}

function alliance_leave_perm(&$sql, $aid, $caller) {
	$aid=makeinteger($aid);
	$caller=makeinteger($caller);
	$tag=alliance_find_which_am_i_founding($sql, $caller);
	if ($aid==$caller and $tag!="") {
		return "You cannot leave alliance which you are the founder<br>You may disband the alliance instead";
	}
	if ($aid!=$caller and $tag!=account_get_perm_tag($sql, $aid)) {
		return "You cannot throw someone else from the alliance which you are not the founder of";
	}
	$sql->query("UPDATE NC_Account SET PermTAG=\"\" WHERE AID=$aid");
	$sql->query("UPDATE NC_Sections SET OwnerTag=\"\" WHERE Owner=$aid");
	return "";
}

function alliance_exists(&$sql, $tag) {
	$tag=makequotedstring($tag);
	$A=$sql->query("SELECT count(*) AS C FROM NC_Alliance WHERE TAG=$tag");
	return $A[0]['C']>0;
}

function alliance_perm_exists(&$sql, $tag) {
	$tag=makequotedstring($tag);
	$A=$sql->query("SELECT count(*) AS C FROM NC_AlliancePermanent WHERE TAG=$tag");
	return $A[0]['C']>0;
}

function alliance_create(&$sql, $tag, $founder)
{
	$Log=log_entry($sql,"alliance cr",$tag);
	if ($tag=="")
	{
		log_result($sql,$Log,"no tag");
		return "Tag not specified";
	}
	$qtag=makequotedstring($tag);
	$founder=makeinteger($founder);
	if (alliance_exists($sql, $tag)) {
		log_result($sql,$Log,"already");
		return "Tag already exists";
	}
	$founderAID=account_get_id_from_pid($sql, $founder);
	$permTag=account_get_perm_tag($sql, $founderAID);
	if (alliance_perm_exists($sql, $tag) and $permTag!=$tag) {
		log_result($sql,$Log,"already");
		return "Tag is already reserved by someone else";
	}
	if ($founderAID==0) {
		log_result($sql,$Log,"unknown");
		return "Internal error: You do not exist";
	}

	$sql->query("INSERT INTO NC_Alliance VALUES ($qtag, 9999999999, 0, 0, 21, 0)");
	$sql->query("INSERT INTO NC_AlliancePermanent VALUES ($qtag, $qtag, \"\", \"\", $founderAID) " 
			. " ON DUPLICATE KEY UPDATE TAG=$qtag, Name=$qtag, Descrption=\"\", URL=\"\", Founder=$founderAID");
	log_result($sql,$Log,"OK");
	alliance_add_member($sql, $tag, $founder);
	return "";
}

function alliance_perm_create(&$sql, $tag, $AID)
{
	$Log=log_entry($sql,"alliance cr",$tag);
	if ($tag=="")
	{
		log_result($sql,$Log,"no tag");
		return "Tag not specified";
	}
	$qtag=makequotedstring($tag);
	$AID=makeinteger($AID);
	if (alliance_perm_exists($sql, $tag)) {
		log_result($sql,$Log,"already");
		return "Tag is already reserved by someone else";
	}
	if ($AID==0) {
		log_result($sql,$Log,"unknown");
		return "Internal error: You do not exist";
	}

	$sql->query("INSERT INTO NC_AlliancePermanent VALUES ($qtag, $qtag, \"\", \"\", $AID) " 
			. " ON DUPLICATE KEY UPDATE Founder=$AID");
	log_result($sql,$Log,"OK");
	alliance_add_perm_member($sql, $tag, $AID);
	return "";
}

function alliance_get_all(&$sql, $tag)
{
	$tag=makequotedstring($tag);
	$A=$sql->query("SELECT * FROM NC_AlliancePermanent PA " 
			. " LEFT JOIN NC_Alliance A ON PA.TAG=A.TAG WHERE A.TAG=$tag");
	return $A[0];
}

function alliance_get_permanent(&$sql, $tag) {
	$tag=makequotedstring($tag);
	$A=$sql->query("SELECT * FROM NC_AlliancePermanent WHERE TAG=$tag");
	return $A[0];
}


function alliance_update(&$sql, $tag, $name, $description, $URL)
{
	$Log=log_entry($sql,"alliance upd",$tag,$name,$description,$URL);
	$A=alliance_get_permanent($sql, $tag);
	if (!exists($A))
	{
		log_result($sql,$Log,"not found");
		return;
	}
	if ($name=="") $name=$A['Name'];
	if ($description=="") $description=$A['Descrption'];
	if ($URL=="") $URL=$A['URL'];
	$tag=makequotedstring($tag);
	$name=makequotedstring($name);
	$description=makequotedstring($description);
	$URL=makequotedstring($URL);
	$sql->query("UPDATE NC_AlliancePermanent SET Name=$name, Descrption=$description, URL=$URL WHERE TAG=$tag");
	flush();
	log_result($sql,$Log,"OK");
}

function alliance_set_founder(&$sql, $tag, $aid)
{
	$Log=log_entry($sql,"alliance fnd",$tag,$aid);
	$aid=makeinteger($aid);
	$T=account_get_perm_tag($sql, $aid);
	if ($T!=$tag)
	{
		log_result($sql,$Log,"not found");
		return;
	}
	$T=makequotedstring($T);
	$sql->query("UPDATE NC_AlliancePermanent SET Founder=$aid WHERE TAG=$T");
	log_result($sql,$Log,"OK");
}

function invitation_status(&$sql, $tag, $pid)
{
	$tag=makequotedstring($tag);
	$pid=makeinteger($pid);
	$A=$sql->query("SELECT Status FROM NC_Invitations WHERE TAG=$tag AND PID=$pid");
	if (exists($A[0]['Status']))
		return $A[0]['Status'];
	else
		return 0;
}

function invitation_get(&$sql, $tag)
{
	$tag=makequotedstring($tag);
	$A=$sql->query("SELECT * FROM NC_Invitations WHERE TAG=$tag");
	return $A;
}

function alliance_get_potential_member_count(&$sql, $tag) {
	$tag=makequotedstring($tag);
	$A=$sql->query("SELECT count(*) AS C FROM NC_Invitations WHERE TAG=$tag AND Status=1");
	$B=$sql->query("SELECT count(*) AS C FROM NC_Player WHERE TAG=$tag");
	return ($A[0]['C']+$B[0]['C']);
}

function invitation_for(&$sql, $pid)
{
	$pid=makeinteger($pid);
	$A=$sql->query("SELECT * FROM NC_Invitations WHERE PID=$pid");
	return $A;
}


function invitation_purge(&$sql, $tag)
{
	$Now=EncodeNow()-2*24*3600;
	$tag=makequotedstring($tag);
	$sql->query("DELETE FROM NC_Invitations WHERE TAG=$tag AND Time<$Now");
}

function alliance_invite(&$sql, $founder, $tag, $target)
{
	$Log=log_entry($sql,"alliance inv",$founder,$tag,$target);
	$Ctd=alliance_get_countdown($sql, $tag);
	if ($Ctd<21) {
		log_result($sql, $Log, "countdown");
		return "Your alliance is already in countdown<br>You cannot invite anyone";
	}
	$potpl=alliance_get_potential_member_count($sql,$tag);
	if ($potpl>=6) {
		log_result($sql, $Log, "no space");
		return "You cannot invite more people into your alliance";
	}
	$target=makeinteger($target);
	if ($target==0)
	{
		log_result($sql,$Log,"not found");
		return "Selected player does not exist";
	}
	$U=player_get_tag($sql, $target);
	if ($U!="")
	{
		log_result($sql,$Log,"already");
		return "Selected player is already in an alliance";
	}
	if (invitation_status($sql, $tag, $target)==0)
	{
		$name=htmlstring(alliance_get_name($sql,$tag));
		$htag=makequotedstring($tag);
		$now=EncodeNow();
		$sql->query("INSERT INTO NC_Invitations VALUES ($now, $htag, $target, 1)");
		news_set($sql, $target, "You are being invited to [$tag] $name", 3);
		log_result($sql,$Log,"OK");
	}
	else
		log_result($sql,$Log,"already");
}

function alliance_accept(&$sql, $tag, $pid)
{
	$Log=log_entry($sql,"alliance accpt",$tag);
	$Ctd=alliance_get_countdown($sql, $tag);
	if ($Ctd<21) {
		news_set($sql, $Member['PID'], 'The alliance you are trying to join is already in countdown<br>You cannot join them', 3);
		return false;
	}
	$pid=makeinteger($pid);
	$pname=htmlstring(account_get_name_from_pid($sql, $pid));
	$ptag=player_get_tag($sql, $pid);
	$htag=makequotedstring($tag);
	$R=$sql->query("SELECT Status FROM NC_Invitations WHERE PID=$pid AND TAG=$htag");
	if (exists($R[0]['Status']) and $R[0]['Status']==1 and $ptag=="")
	{
		$sql->query("UPDATE NC_Invitations SET Status=IF(TAG=$htag,3,2) WHERE PID=$pid");
		$Members=alliance_get_members($sql, $tag);
		foreach ($Members as $Member)
			news_set($sql, $Member['PID'], "$pname joined the alliance", 3);
		log_result($sql,$Log,"OK");
		alliance_add_member($sql, $tag, $pid);
	}
}

function alliance_deny($sql, $tag, $pid)
{
	$Log=log_entry($sql,"alliance deny",$tag);
	$pid=makeinteger($pid);
	$tag=makequotedstring($tag);
	$sql->query("UPDATE NC_Invitations SET Status=2 WHERE PID=$pid AND TAG=$tag AND Status=1");
	log_result($sql,$Log,"OK");
}

function alliance_sieging_fleets($sql, $pid)
{
	$pid=makeinteger($pid);
	return $sql->query("SELECT M.Name, Pl.* FROM NC_Planet Pl JOIN NC_Map M ON M.SID=Pl.SID WHERE Pl.Owner!=$pid AND Pl.FleetOwner=$pid");
}

function alliance_get_countdown(&$sql, $tag)
{
	$tag=makequotedstring($tag);
	$As=$sql->query("SELECT Countdown FROM NC_Alliance WHERE TAG=$tag");
	return $As[0]['Countdown'];
}

function alliance_get_fleet_moves(&$sql, $tag)
{
	$Now=EncodeNow();
	$tag=makequotedstring($tag);
	$Incomings=array();
	$Incomings=$sql->query("SELECT N.*, DefP.TAG DefTAG, DefA.Nick DefNick,
			AttP.TAG AttTAG, AttA.Nick AttNick,
			T.Ring, TS.Name,
			T.Vpr DefVpr, T.Int DefInt, T.Fr DefFr, T.Bs DefBs, T.Drn DefDrn, T.Starbase DefSb
			FROM NC_News N JOIN NC_Player NewsP ON NewsP.PID=N.PID
			JOIN NC_Planet T ON T.PlID=N.IncTarget
			JOIN NC_Map TS ON TS.SID=T.SID
			JOIN NC_Player AttP ON N.IncPID=AttP.PID
			LEFT JOIN NC_Account AttA ON AttP.PID=AttA.PID
			LEFT JOIN NC_Player DefP ON DefP.PID=T.Owner
			LEFT JOIN NC_Account DefA ON DefP.PID=DefA.PID
			WHERE Time>$Now AND NewsP.TAG=$tag
			AND AttP.TAG!=DefP.TAG 
			ORDER BY N.Time ASC");
	$FleetMoves=$sql->query("SELECT F.*, TS.*, T.Ring, DefP.TAG DefTAG, DefA.Nick DefNick,
			DefA.PID DefPID, AttA.PID AttPID,
			AttP.TAG AttTAG, AttA.Nick AttNick,
			T.Vpr DefVpr, T.Int DefInt, T.Fr DefFr, T.Bs DefBs, T.Drn DefDrn, T.Starbase DefSb
			FROM NC_FleetMovement F JOIN NC_Player AttP ON AttP.PID=F.Owner
			JOIN NC_Account AttA ON AttP.PID=AttA.PID
			JOIN NC_Planet T ON T.PlID=F.Target
			JOIN NC_Map TS ON TS.SID=T.SID
			LEFT JOIN NC_Player DefP ON DefP.PID=T.Owner
			LEFT JOIN NC_Account DefA ON DefA.PID=DefP.PID
			WHERE AttP.TAG=$tag ORDER BY F.ETA ASC");
	$Ans[0]=$Incomings;
	$Ans[1]=$FleetMoves;
	return $Ans;
}

?>
