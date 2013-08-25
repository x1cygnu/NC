<?php

set_time_limit(0);

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");

include_once("internal/news.php");
include_once("internal/planet.php");
include_once("internal/fleet.php");
include_once("internal/player.php");
include_once("internal/alliance.php");
include_once("internal/account.php");
include_once("internal/race.php");
include_once("internal/armageddon.php");
include_once("internal/awards.php");


printf("%s\n",DecodeTime(EncodeNow()));

if ($argc!=2 or $argv[1]!='321HakunaMatataDupad12')
{
    $H = new HTML();
    $H->AddStyle("default.css");
    $H->Insert(new Error("What are you looking at?"));
    $H->Insert("I just hope that if you do find a hole, you will report it");
    include("part/mainsubmenu.php");
    $H->Draw();
    die;
}


$sql=&OpenSQL();

if (CheckFrozen($sql))
{
    CloseSql($sql);
    die;
}

planet_add_random($sql);

$sql->query("UPDATE NC_Map M SET M.Level=" . 
		" COALESCE((SELECT CEIL((MAX(P.Population)+MIN(P.Population))/2)" .
		" FROM NC_Planet P JOIN NC_PlanetType PT ON PT.PTID=P.Type" .
		" WHERE P.SID=M.SID AND PT.CultureSlot=1)/IF(M.MaxPlanets>7,1,2),0)");
//price change

//$sql->debug=true;

$sql->query("LOCK TABLES NC_Player P WRITE, NC_Artefact A WRITE, NC_ArtefactList Art READ");
$sql->query("UPDATE NC_Player P SET P.AT=(" .
	    "P.AT+" .
	    "IFNULL((SELECT SUM(Art.Cost*A.Sell) FROM NC_Artefact A" .
	    " JOIN NC_ArtefactList Art ON A.Artefact=Art.ARID" .
	    " WHERE A.Sell>0 AND A.PID=P.PID),0))");

$sql->query("UPDATE NC_Player P JOIN NC_Artefact A ON P.PID=A.PID" .
	    " JOIN NC_ArtefactList Art ON A.Artefact=Art.ARID" .
	    " SET A.Amount=A.Amount-A.Sell, A.Sell=0 WHERE A.Sell!=0");
	    

$sql->query("UNLOCK TABLES");

$sql->query("DELETE FROM NC_Artefact WHERE Amount=0");	    

$sql->query("UPDATE NC_Artefact A JOIN NC_PlayerArtefactCount P ON P.PID=A.PID SET InUse=1 WHERE "
	    . " P.C=1 AND Artefact!=0");

// compute new TA

$sql->query("UPDATE NC_Player P SET TA=COALESCE(("
		. " SELECT FLOOR(SUM(Pl.Population*5+POW(GREATEST(Pl.Population-15,0),2.0))/100) FROM NC_Planet Pl"
		. " JOIN NC_PlanetType PlTyp ON PlTyp.PTID=Pl.Type"
		. " JOIN NC_Agreement Agr ON Pl.Owner=Agr.PID OR Pl.Owner=Agr.PID2"
		. " WHERE (Agr.PID=P.PID OR Agr.PID2=P.PID) AND Pl.Owner!=P.PID AND Agr.Status=2 AND Agr.Type=1 AND PlTyp.CultureSlot=1"
		. "),0)");

// compute points
$sql->query("UPDATE NC_Player P SET Points=COALESCE("
		." GREATEST(P.Sensory,20)-20"
		."+GREATEST(P.Engineering,20)-20"
		."+GREATEST(P.Warp,20)-20"
		."+GREATEST(P.Physics,20)-20"
		."+GREATEST(P.Mathematics,20)-20"
		."+GREATEST(P.Urban,20)-20"
		."+CultureLvl*2"
		."+PL*3"
		."+VL*3"
		."+(SELECT SUM(Population) FROM NC_Planet Pl WHERE Pl.Owner=P.PID),0)");


$sql->query("SET @a:=0");
$sql->query("UPDATE NC_Player SET Rank=(@a:=@a+1) Where AID>0 AND Sensory<127 AND PID>1"
	    . " ORDER BY Countdown ASC, Points DESC, PL DESC, PLRemain ASC, PID DESC");

/*
if (count($Winner)>0)	//there is a winner
{
    $sql->query("UPDATE NC_globalsettings SET SingleWon={$Winner[0]['AID']}");
    
    $WinName=account_get_name($sql, $Winner[0]['AID']);
    news_broadcast($sql,"$WinName has won single-player competition of Northern Cross\nCongratulations!");
    //apply medals


    //RANK MEDALS (TYPE=3)
    
    $Wns=$sql->query("SELECT * FROM NC_Player WHERE Countdown<15 ORDER BY Rank");
    $Ranklvl=0;
    $CntdownSilver=15;
    foreach ($Wns as $Wn)
    {
	if ($Ranklvl==0)
	{
	    awards_give_medal($sql, $Wn['AID'], 3, 1);
	    news_set($sql, $Wn['PID'], "You won Rank Gold Medal of Northern Cross!", 0);
	    $Ranklvl=1;
	}
	elseif ($Ranklvl==1 and $Wn['Countdown']==0)
	{
	    awards_give_medal($sql, $Wn['AID'], 3, 2);
	    news_set($sql, $Wn['PID'], "You won Rank Silver Medal of Northern Cross!", 0);
	}
	elseif ($Ranklvl==1 and $Wn['Countdown']<=$CntdownSilver)
	{
	    awards_give_medal($sql, $Wn['AID'], 3, 2);
	    news_set($sql, $Wn['PID'], "You won Rank Silver Medal of Northern Cross!", 0);
	    $CntdownSilver=$Wn['Countdown'];
	}
	else
	{
	    $Ranklvl=2;
	    awards_give_medal($sql, $Wn['AID'], 3, 3);
	    news_set($sql, $Wn['PID'], "You won Rank Bronze Medal of Northern Cross!", 0);
	}
    }
    
    //RANK BLACK MEDALS (TYPE=3)
    $Wns=$sql->query("SELECT * FROM NC_Player WHERE Countdown>=15 AND WasInCountdown=1");
    foreach ($Wns as $Wn)
    {
	    awards_give_medal($sql, $Wn['AID'], 3, 4);
	    news_set($sql, $Wn['PID'], "You won Rank Black Medal of Northern Cross!", 0);
    }

    //RANK PL MEDALS (TYPE=2)
    $Wns=$sql->query("SELECT * FROM NC_Player "
		    . "WHERE PID>0 "
		    . "ORDER BY PL DESC, PLRemain ASC "
		    . "LIMIT 0, 3");

    awards_give_medal($sql, $Wns[0]['AID'], 2, 1);
    news_set($sql, $Wns[0]['PID'], "You won Warrior Gold Medal of Northern Cross!", 0);
    awards_give_medal($sql, $Wns[1]['AID'], 2, 2);
    news_set($sql, $Wns[1]['PID'], "You won Warrior Silver Medal of Northern Cross!", 0);
    awards_give_medal($sql, $Wns[2]['AID'], 2, 3);
    news_set($sql, $Wns[2]['PID'], "You won Warrior Bronze Medal of Northern Cross!", 0);
    
    //RANK CUL MEDALS (TYPE=1)
    $Wns=$sql->query("SELECT * FROM NC_Player "
		    . "WHERE PID>0 "
		    . "ORDER BY CultureLvl DESC, CultureRemain DESC "
		    . "LIMIT 0, 3");

    awards_give_medal($sql, $Wns[0]['AID'], 1, 1);
    news_set($sql, $Wns[0]['PID'], "You won Expansion Gold Medal of Northern Cross!", 0);
    awards_give_medal($sql, $Wns[1]['AID'], 1, 2);
    news_set($sql, $Wns[1]['PID'], "You won Expansion Silver Medal of Northern Cross!", 0);
    awards_give_medal($sql, $Wns[2]['AID'], 1, 3);
    news_set($sql, $Wns[2]['PID'], "You won Expansion Bronze Medal of Northern Cross!", 0);
    
    //RANK SCI MEDALS (TYPE=4)
    $Wns=$sql->query("SELECT * FROM NC_Player "
		    . "WHERE PID>0 "
		    . "ORDER BY GREATEST(Sensory, Engineering, Warp, Physics, Mathematics, Urban) DESC "
		    . "LIMIT 0, 3");

    awards_give_medal($sql, $Wns[0]['AID'], 4, 1);
    news_set($sql, $Wns[0]['PID'], "You won Science Gold Medal of Northern Cross!", 0);
    awards_give_medal($sql, $Wns[1]['AID'], 4, 2);
    news_set($sql, $Wns[1]['PID'], "You won Science Silver Medal of Northern Cross!", 0);
    awards_give_medal($sql, $Wns[2]['AID'], 4, 3);
    news_set($sql, $Wns[2]['PID'], "You won Science Bronze Medal of Northern Cross!", 0);
    
}
}

*/

$sql->query("UPDATE NC_Alliance A SET Points="
		."(SELECT ROUND(SUM(P.Points)/A.NoMembers) FROM NC_Player P WHERE P.TAG=A.TAG)");

$W=$sql->query("SELECT count(DISTINCT P.SID) Wrum FROM NC_Planet P JOIN NC_Map M ON P.SID=M.SID "
	    ."WHERE M.Special=1");

$Wa=makeinteger($W[0]['Wrum']);

$sql->query("UPDATE NC_Alliance A SET A.TCP=$Wa-("
	. " SELECT count(DISTINCT Pl.SID) FROM NC_Planet Pl"
	. " JOIN NC_Map M ON M.SID=Pl.SID"
	. " LEFT JOIN NC_Player P ON P.PID=Pl.Owner"
	. " WHERE (P.TAG!=A.TAG OR P.TAG IS NULL) AND M.Special=1 AND Pl.Type!=22"
	. ")");

$Wnnrs=$sql->query("SELECT AllianceWon FROM NC_globalsettings");
$Wnnr=$Wnnrs[0];

if ($Wnnr['AllianceWon']=="")
{
$sql->query("UPDATE NC_Alliance SET Countdown=Countdown-1 WHERE" .
	    " TCP>=1");
//	    " TCP>=CEIL(2+NoMembers/6)");
	    
$sql->query("UPDATE NC_Alliance SET Countdown=LEAST(21,Countdown+1) WHERE TCP<1");
//$sql->query("UPDATE NC_Alliance SET Countdown=LEAST(21,Countdown+1) WHERE TCP<CEIL(2+NoMembers/6)");

$sql->query("SET @a:=0");
$sql->query("UPDATE NC_Alliance SET Rank=(@a:=@a+1)"
	    . " ORDER BY Countdown ASC, TCP DESC, Points DESC, NoMembers DESC");

$Winner=$sql->query("SELECT TAG FROM NC_Alliance WHERE Countdown=0 ORDER BY Rank");
if (count($Winner)>0)
{
    $WinnerTag=makequotedstring($Winner[0]['TAG']);
    $sql->query("UPDATE NC_globalsettings SET AllianceWon=$WinnerTag");
    $WinName=alliance_get_name($sql, $Winner[0]['TAG']);
    news_broadcast($sql,"[{$Winner[0]['TAG']}] $WinName has won alliance competition of Northern Cross\nCongratulations!");

    //Rank medals:
    
    $Wns=$sql->query("SELECT TAG FROM NC_Alliance ORDER BY Rank LIMIT 0, 3");
    for ($RNK=1; $RNK<=3; ++$RNK)
    {
	$Ws=alliance_get_members($sql, $Wns[$RNK-1]['TAG']);
	if ($RNK==1) $Lvl="Gold";
	if ($RNK==2) $Lvl="Silver";
	if ($RNK==3) $Lvl="Bronze";
	foreach ($Ws as $Player)
	{
	    awards_give_medal($sql, $Player['AID'], 4, $RNK+10);
	    news_set($sql, $Player['PID'], "Your alliance won Rank $Lvl Medal of Northern Cross!", 0);
	}
    }
    
    //PL medals:

/*
    $Wns=$sql->query("SELECT A.TAG FROM NC_Alliance A JOIN NC_Player P ON P.TAG=A.TAG GROUP BY A.TAG ORDER BY AVG(P.PL) LIMIT 0, 3");
    for ($RNK=1; $RNK<=3; ++$RNK)
    {
	$Ws=alliance_get_members($sql, $Wns[$RNK-1]['TAG']);
	if ($RNK==1) $Lvl="Gold";
	if ($RNK==2) $Lvl="Silver";
	if ($RNK==3) $Lvl="Bronze";
	foreach ($Ws as $Player)
	{
	    awards_give_medal($sql, $Player['AID'], 4, $RNK+10);
	    news_set($sql, $Player['PID'], "Your alliance won Rank $Lvl Medal of Northern Cross!", 0);
	}
    }
*/    
}
}//if new alliance winner


// remove old planet_creation entries
$Now=EncodeNow()-86400*7; //seven days
$sql->query("DELETE FROM NC_PlanetCreated WHERE Time<$Now");
CloseSQL($sql);
?>
