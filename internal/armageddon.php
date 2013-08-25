<?php
include_once("internal/common.php");
include_once("internal/starsystem.php");
include_once("internal/log.php");

function round_restart(&$sql, $name, $time, $sendmail)
{
set_time_limit(0);
    $Log=log_entry($sql,"round restart",$name,$time);
    global_lock($sql);
    $name=makequotedstring($name);
    print_r($time);
    $time=EncodeTime($time,0);
    print_r($time);
    $sql->query("TRUNCATE NC_Player");
    $sql->query("UPDATE NC_Account SET PID=0");
    $sql->query("TRUNCATE NC_Planet");
    starsystem_reset_picked_names($sql);
    $sql->query("UPDATE NC_globalsettings SET Ringlvl=0, RoundName=$name, Start=$time, BonusTime=$time, SingleWon=0, AllianceWon=\"\", fleetlanding=0, FrozenFrom=0, FrozenTo=0, LastCoreLaunch=0");
    $sql->query("TRUNCATE NC_Agreement");
    $sql->query("TRUNCATE NC_Alliance");
    $sql->query("TRUNCATE NC_Artefact");
    $sql->query("TRUNCATE NC_FleetMovement");
    $sql->query("TRUNCATE NC_Invitations");
    $sql->query("TRUNCATE NC_Map");
    $sql->query("TRUNCATE NC_News");
    $sql->query("TRUNCATE NC_Technology");
    $sql->query("TRUNCATE NC_Unread");
    $sql->query("TRUNCATE NC_PlanetCreated");
    $sql->query("TRUNCATE NC_Log");
    
    if ($sendmail)
    {
    $Players=$sql->query("SELECT * FROM NC_Account");
    foreach ($Players as $P)
    {
	$to=$P['email'];
	$title="Next round of Northern Cross";
	$from="From: Northern Cross <admin@ncgame.pl>\n";
	$message="Greetings!
We are happy to infrom that next round of Northern Cross will start soon.
The game and the starting time is available over here:
http://ncgame.pl
http://northerncross.eu

The game is still new. Every player and every suggestion how to improve
the program counts. We will be happy to find you together with us.

For your safety, we advice you not to use deprececated web browsers,
like Internet ExploDer. Our pages are written according to
HTML 4.01 Transitional standard which may be misunderstood
by such browsers.

If you wish to remove your account and not to be bothered by these mails,
you may remove your account at the page http://ncgame.pl/rem.php

Greetings,
NC Management
";
mail($to,$title,$message,$from);
printf("$to <br>");
    }
    }
    global_unlock($sql);
    log_result($sql,$Log,"OK");
}

function round_present(&$sql)
{
    $Start=$sql->query("SELECT Start from NC_globalsettings");
    return ($Start[0]['Start']<EncodeNow());
}

function round_get_info(&$sql)
{
    $Start=$sql->query("SELECT RoundName, Version, Start from NC_globalsettings");
    return $Start[0];
}

function round_freeze(&$sql, $from, $to)
{
    $from=EncodeTime($from,0);
    $to=EncodeTime($to,0);
    $sql->query("UPDATE NC_globalsettings SET FrozenFrom=$from, FrozenTo=$to, FrozenDone=0");
}

function round_freeze_encoded(&$sql, $from, $to)
{
    $from=makeinteger($from);
    $to=makeinteger($to);
    $sql->query("UPDATE NC_globalsettings SET FrozenFrom=$from, FrozenTo=$to, FrozenDone=0");
}

function round_get_frozen(&$sql)
{
    $A=$sql->query("SELECT FrozenFrom, FrozenTo, FrozenDone FROM NC_globalsettings");
    return $A[0];
}

function round_get_winner(&$sql)
{
    $A=$sql->query("SELECT SingleWon, AllianceWon FROM NC_globalsettings");
    return $A[0];
}

?>
