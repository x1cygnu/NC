<?php

include_once("./internal/html.php");
include_once("./internal/common.php");
include_once("./internal/security/config.php");
include_once("./internal/security/validator.php");

include_once("./internal/news.php");
include_once("./internal/planet.php");
include_once("./internal/fleet.php");
include_once("./internal/player.php");
include_once("./internal/account.php");
include_once("./internal/race.php");


function DamageResolve(&$Am)
{
  if (floor($Am)!=$Am)
  {
    $Am=( (($Am-floor($Am))>mt_rand(0,9)/10) ? ceil($Am) : floor($Am));
    settype($Am,"integer");
  }
}

$ReportStringComma=false;


function AddToReport(&$RS, $v, $sh) {
  global $ReportStringComma;
  if ($v>0) {
    if ($ReportStringComma)
      $RS.=", ";
    $ReportStringComma=true;
    $RS.=makeinteger($v) . $sh;
  }
}


function core_launch(&$sql) {
  $Now=EncodeNow();
  $Answer=$sql->query("SELECT GET_LOCK(\"cygnus NC corelaunch\",1) AS L");
  if ($Answer[0]['L']==0)
    return; //game overload?
  $LastTimes=$sql->query("SELECT LastCoreLaunch, FrozenDone FROM NC_globalsettings");
  $LastTime=$LastTimes[0]['LastCoreLaunch'];
  if ($Now-$LastTime<100) { //everything seems to be OK
    $sql->query("SELECT RELEASE_LOCK(\"cygnus NC corelaunch\")");
    return;
  }
  $FrozenDone=$LastTimes[0]['FrozenDone'];
  if (($Now-$LastTime>300) and ($LastTime!=0) and $FrozenDone==1) //over 5min delay
  {
    round_freeze_encoded($sql,$LastTime,$Now);
    $from=DecodeTime($LastTime,0);
    $to=DecodeTime($Now,0);
    news_broadcast($sql,"Game was frozen due to server malfunction\nfrom: $from GMT\nto: $to GMT");
    echo "Emergency freeze $from - $to\n";
  }
  $sql->query("UPDATE NC_globalsettings SET LastCoreLaunch=$Now");
  $sql->query("SELECT RELEASE_LOCK(\"cygnus NC corelaunch\")");
  core_main_loop($sql);
}

/* this function should never be called directly. Instead, use core_launch() */
function core_main_loop(&$sql) {
  $vs=$sql->query("SELECT fleetlanding FROM NC_globalsettings");
  $fleetlanding=$vs[0]['fleetlanding']+1;
  $sql->query("UPDATE NC_globalsettings SET fleetlanding=$fleetlanding");
  print("launching main loop with fleetlanding=" . $fleetlanding . "\n");
$ScriptStart=EncodeNow();
while(1) {
  $curfleetlandings=$sql->query("SELECT fleetlanding FROM NC_globalsettings");
  $curfleetlanding=$curfleetlandings[0]['fleetlanding'];
  if ($fleetlanding!=$curfleetlanding) {
    print("quiting main loop with old fleetlanding=" . $fleetlanding . "\n");
    return;
  }
  $Now=EncodeNow();
  $sql->query("UPDATE NC_globalsettings SET LastCoreLaunch=$Now");


  if (CheckFrozenBack($sql))
  {
    printf(DecodeTime($Now) . ": Game is frozen\n");
    $Frozen=round_get_frozen($sql);
    $Z=$sql->query("SELECT FrozenDone FROM NC_globalsettings");
    if ($Z[0]['FrozenDone']==0)
    {
      printf("Applying frozen shift\n");
      $timediff=$Frozen['FrozenTo']-$Frozen['FrozenFrom'];
      $sql->query("UPDATE NC_globalsettings SET FrozenDone=1");
      $sql->query("UPDATE NC_FleetMovement SET ETA=ETA+$timediff");
      $sql->query("UPDATE NC_Player SET LastUpdate=LastUpdate+$timediff");
      $sql->query("UPDATE NC_News SET Time=Time+$timediff WHERE Time>{$Frozen['FrozenFrom']} AND Type=2");
    }
    $sleepTime=min(120,$Frozen['FrozenTo']-$Now);
    echo "longsleep($sleepTime)\n";
    sleep($sleepTime);
    continue; //repeat main loop
  }

  $Fleets=$sql->query("SELECT F.* FROM NC_FleetMovement F WHERE F.ETA<$Now+60 ORDER BY ETA ASC");
  if (count($Fleets)==0) {
    sleep(59);
    continue; //repeat main loop
  }

  foreach ($Fleets as $Attacker)
  {
    $Now=EncodeNow();
    $curfleetlandings=$sql->query("SELECT fleetlanding FROM NC_globalsettings");
    $curfleetlanding=$curfleetlandings[0]['fleetlanding'];
    if ($fleetlanding!=$curfleetlanding) {
      print("quiting main loop at point B with old fleetlanding=" . $fleetlanding . "\n");
      return;
    }
    $sql->query("UPDATE NC_globalsettings SET LastCoreLaunch=$Now");
    $MegaFID=makeinteger($Attacker['FID']);
    $ETA=$Attacker['ETA'];
    settype($ETA,"integer");
    if ($ETA>$Now)
      sleep($ETA-$Now);
    $Now=EncodeNow();
    printf("($MegaFID) " . DecodeTime($Now) . " : ");

    player_update_all($sql, $Attacker['Owner']);
    $Attackers=$sql->query("SELECT F.*, P.PID, P.Physics, P.Mathematics, P.Attack, P.Defence, P.PL FROM NC_FleetMovement F JOIN NC_Player P ON F.Owner=P.PID WHERE F.FID={$Attacker['FID']}");
    $Attacker=$Attackers[0];

    $GatewayJump=false;
    if ($Attacker['Target']<0)
    {
      $Attacker['Target']=-$Attacker['Target'];    
      $GatewayJump=true;
    }
    $curfleetlandings=$sql->query("SELECT fleetlanding FROM NC_globalsettings");
    $curfleetlanding=$curfleetlandings[0]['fleetlanding'];
    if ($fleetlanding!=$curfleetlanding) {
      print("quiting main loop at point C with old fleetlanding=" . $fleetlanding . "\n");
      return;
    }
    if (Lock($sql, $Attacker['Target'])!=1)
    {
      printf("Failed to lock a planet {$Attacker['Target']}\n");
      continue; //proceed to next incoming
    }
    $Defenders=$sql->query("SELECT F.*, P.PID, F.Owner AS PlanetOwner FROM NC_Planet F LEFT JOIN NC_Player P ON IF(F.FleetOwner>0,F.FleetOwner,F.Owner)=P.PID WHERE F.PLID={$Attacker['Target']}");
    $Defender=$Defenders[0];
    player_update_all($sql, $Defender['PID']);
    if ($Defender['PID']!=$Defender['PlanetOwner'])
      player_update_all($sql, $Defender['PlanetOwner']);

    $Defenders=$sql->query("SELECT PT.Attack PlanetAttack, PT.Defense PlanetDefense, PT.BaseCost BaseCost, F.*, P.PID, P.Physics, P.Mathematics, P.Attack, P.Defence, P.PL, F.Owner AS PlanetOwner FROM NC_Planet F JOIN NC_PlanetType PT ON PT.PTID=F.Type LEFT JOIN NC_Player P ON IF(F.FleetOwner>0,F.FleetOwner,F.Owner)=P.PID WHERE F.PLID={$Attacker['Target']}");
    $Defender=$Defenders[0];

    $sbfraction=building_points_for_lvl($Defender['Starbase']+1,10);
    $Defender['Starbase']=$Defender['Starbase']+1-$Defender['StarbaseRemain']/$sbfraction;
    $PLID=$Attacker['Target'];
    $Name=planet_get_name($sql, $PLID);
    $AttackerName=account_get_name_from_pid($sql, $Attacker['PID']);
    $DefenderName=account_get_name_from_pid($sql, $Defender['PID']);

    echo $Name . ':';

    if ($GatewayJump)
    {
      news_set($sql,$Attacker['PID'],"We emerged at $Name",4);
      news_set($sql,$Defender['PID'],"Fleet of $AttackerName emerged at $Name",2);
    }


    $AttackerWon=true;
    $Destroyed=0;


    if (($Defender['Vpr']>0 or $Defender['Int']>0 or $Defender['Fr']>0
          or $Defender['Bs']>0 or $Defender['Drn']>0 or $Defender['CS']>0
          or $Defender['Tr']>0 or $Defender['Starbase']>0) and $Attacker['PID']!=$Defender['PID'])
      //there is a battle
    {
      global $ReportStringComma;
      $ReportStringComma=false;
      $ReportString="";
      AddToReport($ReportString,$Attacker['Vpr'],'Vpr');
      AddToReport($ReportString,$Attacker['Int'],'Int');
      AddToReport($ReportString,$Attacker['Fr'],'Fr');
      AddToReport($ReportString,$Attacker['Bs'],'Bs');
      AddToReport($ReportString,$Attacker['Drn'],'Drn');
      $ReportString.=" vs ";
      $ReportStringComma=false;
      AddToReport($ReportString,$Defender['Vpr'],'Vpr');
      AddToReport($ReportString,$Defender['Int'],'Int');
      AddToReport($ReportString,$Defender['Fr'],'Fr');
      AddToReport($ReportString,$Defender['Bs'],'Bs');
      AddToReport($ReportString,$Defender['Drn'],'Drn');
      AddToReport($ReportString,floor($Defender['Starbase']),'SB');
//      echo '(' . $ReportString . ')';
      echo '(' .
        $Attacker['Vpr'] . 'Vpr '.
        $Attacker['Int'] . 'Int '.
        $Attacker['Fr'] . 'Fr '.
        $Attacker['Bs'] . 'Bs '.
        $Attacker['Drn'] . 'Drn '.
        $Attacker['Tr'] . 'Tr '.
        $Attacker['CS'] . 'CS '.
        'vs '.
        $Defender['Vpr'] . 'Vpr '.
        $Defender['Int'] . 'Int '.
        $Defender['Fr'] . 'Fr '.
        $Defender['Bs'] . 'Bs '.
        $Defender['Drn'] . 'Drn '.
        $Defender['Tr'] . 'Tr '.
        $Defender['CS'] . 'CS) ';
      printf("[A:%+d/%+d %d/%d %d]",$Attacker['Attack'],$Attacker['Defence'],$Attacker['Physics'],$Attacker['Mathematics'],$Attacker['PL']);
      printf("[D:%+d/%+d %d/%d %d]",$Defender['Attack'],$Defender['Defence'],$Defender['Physics'],$Defender['Mathematics'],$Defender['PL']);

      $DWinResult=array();
      $DLooseResult=array();
      $AWinResult=array();
      $ALooseResult=array();
      $Result=array();
      global $fightships;

      fleet_battle(&$Result, &$Attacker,&$Defender,&$AWinResult,&$DWinResult,&$ALooseResult,&$DLooseResult,
          Attack($Attacker['Attack']),Defence($Attacker['Defence']),$Attacker['Physics'],$Attacker['Mathematics'],$Attacker['PL'],
          Attack($Defender['Attack']),Defence($Defender['Defence']),$Defender['Physics'],$Defender['Mathematics'],$Defender['PL'],
          $Attacker['Mission']
          ,$Defender['PlanetAttack'],$Defender['PlanetDefense']);

      $ReportStringComma=false;
      $RemainString="WinRem:";
      foreach ($fightships as $S)
        AddToReport($RemainString,$AWinResult[$S],$S);
      $RemainString.="/";
      $RemainStringComma=false;
      foreach ($fightships as $S)
        AddToReport($RemainString,$DWinResult[S],'S');
      AddToReport($RemainString,floor($DWinResult['Starbase']),'SB');

      $ReportStringComma=false;
      $RemainString.="  LooseRem:";
      foreach ($fightships as $S)
        AddToReport($RemainString,$ALooseResult[$S],$S);
      $RemainString.="/";
      $RemainStringComma=false;
      foreach ($fightships as $S)
        AddToReport($RemainString,$DLooseResult[$S],$S);
      AddToReport($RemainString,floor($DLooseResult['Starbase']),'SB');

      $RemainString.="  ";

      $curfleetlandings=$sql->query("SELECT fleetlanding FROM NC_globalsettings");
      $curfleetlanding=$curfleetlandings[0]['fleetlanding'];
      if ($fleetlanding!=$curfleetlanding) {
        print("quiting main loop at point D with old fleetlanding=" . $fleetlanding . "\n");
        return;
      }
      echo $RemainString;
      $roll=fleet_roll_dice($Result,true);
      echo $roll;
      if ($roll == 'DU' || $roll == 'DE')
      {
        $AttackerWon=true;
        if ($roll == 'DU') {
          news_set($sql,$Attacker['PID'],sprintf("We were victorious at %s!<br/>We destroyed %d%% of %s fleet before it was forced to retreat.<br/>XP gained: %d<br/>%s",
                $Name,$DLooseResult['Killed'],$DefenderName,$Defender['CV'],$ReportString),4);
          news_set($sql,$Defender['PID'],sprintf("We were defeated at %s!<br/>%s lost about %d%% of his fleet before ours was forced to retreat.",
                $Name,$AttackerName,$AWinResult['Killed']),5);
        } else {
          news_set($sql,$Attacker['PID'],sprintf("We were victorious at %s!<br/>We crushed %s fleet.<br/>XP gained: %d<br/>%s",
                $Name,$DefenderName,$Defender['CV'],$ReportString),4);
          news_set($sql,$Defender['PID'],sprintf("We were defeated at %s!<br/>%s lost about %d%% of his fleet before blowing ours away.",
                $Name,$AttackerName,$AWinResult['Killed']),5);
        }
        player_add_xp($sql, $Attacker['PID'],$Defender['CV']);
        foreach ($fightships as $S) {
          DamageResolve($AWinResult[$S]);
          DamageResolve($DLooseResult[$S]);
        }
        $sql->query("UPDATE NC_Planet SET Vpr={$AWinResult['Vpr']}, `Int`={$AWinResult['Int']}, Fr={$AWinResult['Fr']}, Bs={$AWinResult['Bs']}, Drn={$AWinResult['Drn']}, CS={$AWinResult['CS']}, Tr={$AWinResult['Tr']}, " .
            "TrRemain=0, CSRemain=0, VprRemain=0, IntRemain=0, FrRemain=0, BsRemain=0, DrnRemain=0, Starbase=0, StarbaseRemain=10, FleetOwner={$Attacker['PID']} WHERE PLID=$PLID");
        if ($roll != 'DE') {
          $retreatPLID=get_capital($sql,$Defender['PID']);
          fleet_create($sql, $Defender['PID'], $DLooseResult['Vpr'], $DLooseResult['Int'], $DLooseResult['Fr'], $DLooseResult['Bs'], $DLooseResult['Drn'], $DLooseResult['CS'], $DLooseResult['Tr'],
              $PLID, $retreatPLID, 0, 9999, missionID("Retreat"));
        }
        $Conqueror=&$Attacker;
      }	else if ($roll == 'AE' || $roll == 'AU') {
        $AttackerWon=false;
        if ($roll == 'AU') {
          news_set($sql,$Defender['PID'],sprintf("We defended %s!<br/>We destroyed %d%% of %s fleet before it was forced to retreat.<br/>XP gained: %d<br/>%s",
                $Name,$ALooseResult['Killed'],$AttackerName,$Attacker['CV'],$ReportString),4);
          news_set($sql,$Attacker['PID'],sprintf("Our attack at %s was stopped!<br/>%s lost about %d%% of his fleet before ours was forced to retreat.",
                $Name,$DefenderName,$DWinResult['Killed']),5);
        } else {
          news_set($sql,$Defender['PID'],sprintf("We defended %s!<br/>We crushed %s fleet.<br/>XP gained: %d<br/>%s",
                $Name,$AttackerName,$Attacker['CV'],$ReportString),4);
          news_set($sql,$Attacker['PID'],sprintf("Our attack at %s was stopped!<br/>%s lost about %d%% of his fleet before blowing ours away.",
                $Name,$DefenderName,$DWinResult['Killed']),5);
        }
        foreach ($fightships as $S) {
          DamageResolve($DWinResult[$S]);
          DamageResolve($ALooseResult[$S]);
        }
        player_add_xp($sql, $Defender['PID'],$Attacker['CV']);

        $SB=floor($DWinResult['Starbase']);
        $SBRemain=ceil(($SB+1-$DWinResult['Starbase'])*building_points_for_lvl($SB+1,10));
        $Conqueror=&$Defender;

        $sql->query("UPDATE NC_Planet SET Vpr={$DWinResult['Vpr']}, `Int`={$DWinResult['Int']}, Fr={$DWinResult['Fr']}, Bs={$DWinResult['Bs']}, Drn={$DWinResult['Drn']}, " .	     //Colony ships and Transporters remain intact
            "TrRemain=0, CSRemain=0, VprRemain=0, IntRemain=0, FrRemain=0, BsRemain=0, DrnRemain=0, " . //FleetOwner sie nie zmienil
            "Starbase=$SB, StarbaseRemain=$SBRemain WHERE PLID=$PLID");
        if ($roll != 'AE') {
          $retreatPLID=get_capital($sql,$Attacker['PID']);
          fleet_create($sql, $Attacker['PID'], $ALooseResult['Vpr'], $ALooseResult['Int'], $ALooseResult['Fr'], $ALooseResult['Bs'], $ALooseResult['Drn'], $ALooseResult['CS'], $ALooseResult['Tr'],
              $PLID, $retreatPLID, 0, 9999, missionID("Retreat"));
        }
      } else if ($roll == '=') {
        $AttackerWon=false;
/*        news_set($sql,$Defender['PID'],sprintf("[IGNORE THIS]The opponent raided %s!<br/>We destroyed %d%% of %s fleet before it was forced to retreat, but some damage to the surface of our planet was inflicted.<br/>XP gained: %d<br/>%s",
              $Name,$ALooseResult['Killed'],$AttackerName,$Attacker['CV'],$ReportString),4);
        news_set($sql,$Attacker['PID'],sprintf("[IGNORE THIS]The opponent raided %s!<br/>We destroyed %d%% of %s fleet before it was forced to retreat, but some damage to the surface of our planet was inflicted.<br/>XP gained: %d<br/>%s",
              $Name,$ALooseResult['Killed'],$AttackerName,$Attacker['CV'],$ReportString),4);*/

	news_set($sql,$Defender['PID'],sprintf("After a short fire exchange %s withdrew from further attacking %s.<br/>We destroyed %d%% of their fleet<br/>XP gained: %d<br/>%s",
				$AttackerName,$Name,$AWinResult['Killed'],makeinteger($Attacker['CV']/2),$ReportString),4);
	news_set($sql,$Attacker['PID'],sprintf("After a short fire exchange with %s we withdrew from further attacking %s.<br/>We destroyed %d%% of their fleet<br/>XP gained: %d<br/>%s",
				$DefenderName,$Name,$DWinResult['Killed'],makeinteger($Defender['CV']/2),$ReportString),4);
        foreach ($fightships as $S) {
          DamageResolve($DWinResult[$S]);
          DamageResolve($AWinResult[$S]);
        }
        player_add_xp($sql, $Defender['PID'],$Attacker['CV']/2);
        player_add_xp($sql, $Attacker['PID'],$Defender['CV']/2);

        $SB=floor($DWinResult['Starbase']);
        $SBRemain=ceil(($SB+1-$DWinResult['Starbase'])*building_points_for_lvl($SB+1,10));
        $Conqueror=&$Defender;

        $sql->query("UPDATE NC_Planet SET Vpr={$DWinResult['Vpr']}, `Int`={$DWinResult['Int']}, Fr={$DWinResult['Fr']}, Bs={$DWinResult['Bs']}, Drn={$DWinResult['Drn']}, " .	     //Colony ships and Transporters remain intact
            "TrRemain=0, CSRemain=0, VprRemain=0, IntRemain=0, FrRemain=0, BsRemain=0, DrnRemain=0, " . //FleetOwner sie nie zmienil
            "Starbase=$SB, StarbaseRemain=$SBRemain WHERE PLID=$PLID");
	$retreatPLID=get_capital($sql,$Attacker['PID']);
	fleet_create($sql, $Attacker['PID'], $AWinResult['Vpr'], $AWinResult['Int'], $AWinResult['Fr'], $AWinResult['Bs'], $AWinResult['Drn'], $AWinResult['CS'], $AWinResult['Tr'],
              $PLID, $retreatPLID, 0, 9999, missionID("Retreat"));
      } else if ($roll == 'R') {
        $AttackerWon=false;
        news_set($sql,$Defender['PID'],sprintf("The opponent raided %s!<br/>We destroyed %d%% of %s fleet before it was forced to retreat, but some damage to the surface of our planet was inflicted.<br/>XP gained: %d<br/>%s",
              $Name,$ALooseResult['Killed'],$AttackerName,$Attacker['CV'],$ReportString),4);
        news_set($sql,$Attacker['PID'],sprintf("We managed to raid the surface of %s, but ultimately we were forced to retreat!<br/>%s lost about %d%% of his fleet in the process.",
              $Name,$DefenderName,$DWinResult['Killed']),5);
        foreach ($fightships as $S) {
          DamageResolve($DWinResult[$S]);
          DamageResolve($ALooseResult[$S]);
        }
        player_add_xp($sql, $Defender['PID'],$Attacker['CV']);

        $SB=floor($DWinResult['Starbase']);
        $SBRemain=ceil(($SB+1-$DWinResult['Starbase'])*building_points_for_lvl($SB+1,10));
        $Conqueror=&$Defender;

        $sql->query("UPDATE NC_Planet SET Vpr={$DWinResult['Vpr']}, `Int`={$DWinResult['Int']}, Fr={$DWinResult['Fr']}, Bs={$DWinResult['Bs']}, Drn={$DWinResult['Drn']}, " .	     //Colony ships and Transporters remain intact
            "TrRemain=0, CSRemain=0, VprRemain=0, IntRemain=0, FrRemain=0, BsRemain=0, DrnRemain=0, " . //FleetOwner sie nie zmienil
            "Starbase=$SB, StarbaseRemain=$SBRemain WHERE PLID=$PLID");

        //apply raid result
        if ($Attacker['PID']!=$Defender['PlanetOwner'] and !planet_is_colonisable($sql, $PLID)) {
          $transporters=min($ALooseResult['Tr'],2);
          $CurPop=planet_get_pop($sql, $PLID);
          $PopDrop=$transporters;
          planet_pop_kill($sql, $PLID, $transporters);
          $PopDrop=$PopDrop-$transporters;
          $EndPop=$CurPop-$PopDrop;
          news_set($sql, $Attacker['PID'], "Population on $Name was reduced by $PopDrop.",4);
          news_set($sql, $Defender['PlanetOwner'], "Population on $Name was reduced by $PopDrop", 5);
          $ALooseResult['Tr']-=$PopDrop;
        }
        $retreatPLID=get_capital($sql,$Attacker['PID']);
        fleet_create($sql, $Attacker['PID'], $ALooseResult['Vpr'], $ALooseResult['Int'], $ALooseResult['Fr'], $ALooseResult['Bs'], $ALooseResult['Drn'], $ALooseResult['CS'], $ALooseResult['Tr'],
            $PLID, $retreatPLID, 0, 9999, missionID("Retreat"));
      } else {
        $AttackerWon=false;
        news_set($sql,$Defender['PID'],sprintf("A Spacebug prevented the battle at %s from taking place!<br/>Our fleet is heading back home<br/>"
              ."(as with all bugs, please report this one!)",$Name),5);
        news_set($sql,$Attacker['PID'],sprintf("A Spacebug prevented the battle at %s from taking place!<br/>Enemy fleet is heading back home<br/>"
              ."(as with all bugs, please report this one!)",$Name),5);

        $retreatPLID=get_capital($sql,$Attacker['PID']);
        fleet_create($sql, $Attacker['PID'], $Attacker['Vpr'], $Attacker['Int'], $Attacker['Fr'], $Attacker['Bs'], $Attacker['Drn'], $Attacker['CS'], $Attacker['Tr'],
            $PLID, $retreatPLID, 0, 9999, missionID("Retreat"));
      }
    }
    else	//there was no battle
    {
      $sql->query("UPDATE NC_Planet SET Vpr=Vpr+{$Attacker['Vpr']}, `Int`=`Int`+{$Attacker['Int']}, Fr=Fr+{$Attacker['Fr']}, Bs=Bs+{$Attacker['Bs']}, Drn=Drn+{$Attacker['Drn']}, CS=CS+{$Attacker['CS']}, Tr=Tr+{$Attacker['Tr']}, FleetOwner={$Attacker['Owner']} WHERE PLID=$PLID");
      $A=$sql->query("SELECT * FROM NC_Planet WHERE PLID=$PLID");
      $Conqueror=$A[0];
      $Conqueror['PID']=$Conqueror['FleetOwner'];
			$Conqueror['PlanetOwner']=$Conqueror['Owner'];
      echo " no!\n";
    }

    //No matter if there was a battle or not, do the following:

    if ($Conqueror['PID']!=$Defender['PlanetOwner'])	//Hostile planet
    {
/*
			echo "Conqueror = ";
			print_r($Conqueror);
			echo "Defender = ";
			print_r($Defender);
*/
      if (planet_is_colonisable($sql, $PLID))
      {
        if ($Conqueror['CS']>0)
        {
          $Req=planet_technology_required($sql, $PLID);
          $cultureOK=true;
          $technologyOK=true;
          if ($Req['CultureSlot']==1) {
            if (player_has_culture_slot($sql, $Conqueror['PID']))
            {
              $cultureOK=true;			    
            }
            else //no culture slot
            {
              news_set($sql, $Conqueror['PID'], "Our culture level is not high enough to control another planet.", 5);
              $cultureOK=false;
            }
          }
          if ($Req['TechReq']>0) {
            $technologyOK=tech_check_player($sql, $Conqueror['PID'], $Req['TechReq']);
            if (!$technologyOK) {
              $Info=tech_get_info($sql, $Req['TechReq']);
              $TechName=$Info['Name'];
              news_set($sql, $Conqueror['PID'], "You need to research " . $TechName . " in order to colonise this planet",5);
            }
          }
          if ($cultureOK and $technologyOK) {
            planet_conquer($sql, $PLID, $Conqueror['PID']);
            news_set($sql, $Conqueror['PID'], "We have colonised $Name", 4);
            $sql->query("UPDATE NC_Planet SET CS=CS-1 WHERE PLID=$PLID");
          }
        }
      }
      else //planet is owned by someone
      {
        if ($Conqueror['Tr']>0)
        {
          $CurPop=planet_get_pop($sql, $PLID);
          $PopDrop=$Conqueror['Tr'];
          $takeplanet=planet_pop_kill($sql, $PLID, $Conqueror['Tr']);
          $PopDrop=$PopDrop-$Conqueror['Tr'];
          $EndPop=$CurPop-$PopDrop;
          if ($takeplanet)
          {
            $Req=planet_technology_required($sql, $PLID);
            $cultureOK=true;
            $technologyOK=true;
            if ($Req['CultureSlot']==1) {
              if (player_has_culture_slot($sql, $Conqueror['PID']))
              {
                $cultureOK=true;			    
              }
              else //no culture slot
              {
                news_set($sql, $Conqueror['PID'], "Our culture level is not high enough to control another planet.", 5);
                $cultureOK=false;
              }
            }
            if ($Req['TechReq']>0) {
              $technologyOK=tech_check_player($sql, $Conqueror['PID'], $Req['TechReq']);
              if (!$technologyOK) {
                $Info=tech_get_info($sql, $Req['TechReq']);
                $TechName=$Info['Name'];
                news_set($sql, $Conqueror['PID'], "You need to research " . $TechName . " in order to colonise this planet",5);
              }
            }
            if ($cultureOK and $technologyOK) 
            {
              planet_conquer($sql, $PLID, $Conqueror['PID']);
              news_set($sql, $Conqueror['PID'], "We conquered $Name", 4);
              news_set($sql, $Defender['PlanetOwner'], "We lost $Name", 5);
            }
          }
          news_set($sql, $Conqueror['PID'], "Population on $Name was reduced by $PopDrop.",4);
          news_set($sql, $Defender['PlanetOwner'], "Population on $Name was reduced by $PopDrop", 5);
          player_add_violence($sql, $Conqueror['PID'], (($CurPop*($CurPop-1))/2)-(($EndPop*($EndPop-1))/2));
          $sql->query("UPDATE NC_Planet SET Tr={$Conqueror['Tr']} WHERE PLID=$PLID");
        }
      }
    }	//planet is owned by attacker

    //Remove the incomming
    $sql->query("DELETE FROM NC_FleetMovement WHERE FID=$MegaFID");
    $sql->query("UPDATE NC_Planet SET FleetOwner=IF(Vpr>0 OR `Int`>0 OR Fr>0 OR Bs>0 OR Drn>0 OR Tr>0 OR CS>0,FleetOwner,0) WHERE PLID=$PLID");
    printf("\n");
  } //end of foreach loop
} //end of main loop
} //end of function

?>
