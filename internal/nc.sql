/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_Account` (
  `AID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `Nick` varchar(32) NOT NULL,
  `Password` varchar(32) NOT NULL,
  `PermTAG` varchar(5) NOT NULL,
  `PID` mediumint(8) unsigned NOT NULL,
  `SitPID` mediumint(8) unsigned NOT NULL,
  `SitFrom` int(11) NOT NULL,
  `email` varchar(64) NOT NULL,
  `TimeZone` smallint(6) NOT NULL,
  `ForumAdmin` tinyint(1) NOT NULL DEFAULT '0',
  `LastLogin` int(11) NOT NULL,
  `Avatar` varchar(96) NOT NULL,
  `BackgroundSig` varchar(96) NOT NULL,
  `FleetConfirmation` tinyint(1) NOT NULL DEFAULT '1',
  `MapBackground` tinyint(4) NOT NULL DEFAULT '1',
  `Hint` tinyint(4) NOT NULL DEFAULT '1',
  `DefMapX` smallint(6) NOT NULL,
  `DefMapY` smallint(6) NOT NULL,
  `DefMapR` tinyint(3) unsigned NOT NULL,
  `Multi` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`AID`),
  UNIQUE KEY `Nick` (`Nick`),
  KEY `PermTAG` (`PermTAG`)
) ENGINE=MyISAM AUTO_INCREMENT=2054 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_Agreement` (
  `PID` mediumint(9) NOT NULL,
  `PID2` mediumint(9) NOT NULL,
  `Type` tinyint(4) NOT NULL,
  `Status` tinyint(4) NOT NULL,
  KEY `PID` (`PID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_Alliance` (
  `TAG` varchar(5) NOT NULL,
  `Rank` smallint(5) unsigned NOT NULL,
  `NoMembers` smallint(6) NOT NULL,
  `Points` smallint(6) NOT NULL COMMENT 'AVG(Player.Points)',
  `Countdown` tinyint(4) NOT NULL DEFAULT '7',
  `TCP` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`TAG`),
  KEY `TCP` (`TCP`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_AlliancePermanent` (
  `TAG` varchar(5) CHARACTER SET latin1 NOT NULL,
  `Name` varchar(48) COLLATE utf8_polish_ci NOT NULL,
  `Descrption` text COLLATE utf8_polish_ci NOT NULL,
  `URL` varchar(64) COLLATE utf8_polish_ci NOT NULL,
  `Founder` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`TAG`),
  UNIQUE KEY `Founder` (`Founder`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_AllowedMulti` (
  `AID` mediumint(8) unsigned NOT NULL,
  `IP0` tinyint(3) unsigned NOT NULL,
  `IP1` tinyint(3) unsigned NOT NULL,
  `IP2` tinyint(3) unsigned NOT NULL,
  `IP3` tinyint(3) unsigned NOT NULL,
  KEY `AID` (`AID`),
  KEY `IP0` (`IP0`,`IP1`,`IP2`,`IP3`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_Artefact` (
  `PID` mediumint(9) NOT NULL,
  `Artefact` smallint(6) NOT NULL,
  `InUse` tinyint(1) NOT NULL,
  `Amount` smallint(5) NOT NULL,
  `Sell` smallint(5) NOT NULL,
  UNIQUE KEY `PID` (`PID`,`Artefact`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_ArtefactList` (
  `ARID` smallint(6) NOT NULL AUTO_INCREMENT,
  `Name` varchar(48) NOT NULL,
  `Short` varchar(8) NOT NULL,
  `Growth` tinyint(4) NOT NULL,
  `Science` tinyint(4) NOT NULL,
  `Culture` tinyint(4) NOT NULL,
  `Production` tinyint(4) NOT NULL,
  `Speed` tinyint(4) NOT NULL,
  `Attack` tinyint(4) NOT NULL,
  `Defence` tinyint(4) NOT NULL,
  `Cost` mediumint(9) NOT NULL,
  PRIMARY KEY (`ARID`)
) ENGINE=MyISAM AUTO_INCREMENT=53 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_Awards` (
  `AID` mediumint(8) unsigned NOT NULL,
  `Round` tinyint(3) unsigned NOT NULL,
  `Rank` tinyint(3) unsigned NOT NULL,
  `Type` tinyint(3) unsigned NOT NULL,
  KEY `AID` (`AID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_Background` (
  `X` smallint(6) NOT NULL,
  `Y` smallint(6) NOT NULL,
  `BGID` mediumint(9) NOT NULL,
  PRIMARY KEY (`X`,`Y`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_BackgroundList` (
  `BGID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `BaseID` smallint(5) unsigned NOT NULL,
  `X` tinyint(4) NOT NULL,
  `Y` tinyint(4) NOT NULL,
  `File` varchar(64) NOT NULL,
  PRIMARY KEY (`BGID`)
) ENGINE=MyISAM AUTO_INCREMENT=4339 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_FavoriteThreads` (
  `AID` mediumint(8) unsigned NOT NULL,
  `ThID` mediumint(8) unsigned NOT NULL,
  UNIQUE KEY `AID` (`AID`,`ThID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_FleetMovement` (
  `FID` mediumint(9) NOT NULL AUTO_INCREMENT,
  `Owner` mediumint(9) NOT NULL COMMENT 'ref AW_Player.PID',
  `Target` mediumint(9) NOT NULL COMMENT 'ref AW_Planet.PLID',
  `Vpr` mediumint(9) NOT NULL,
  `Int` mediumint(9) NOT NULL,
  `Fr` mediumint(9) NOT NULL,
  `Bs` mediumint(9) NOT NULL,
  `Drn` mediumint(9) NOT NULL,
  `CS` mediumint(9) NOT NULL,
  `Tr` mediumint(9) NOT NULL,
  `ETA` int(11) NOT NULL DEFAULT '0',
  `Mission` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`FID`),
  KEY `ETA` (`ETA`),
  KEY `Owner` (`Owner`)
) ENGINE=MyISAM AUTO_INCREMENT=2766 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_Fleet` (
  `FID` mediumint(9) NOT NULL AUTO_INCREMENT,
  `Owner` mediumint(9) NOT NULL,
  `Vpr` mediumint(9) NOT NULL,
  `Int` mediumint(9) NOT NULL,
  `Fr` mediumint(9) NOT NULL,
  `Bs` mediumint(9) NOT NULL,
  `Drn` mediumint(9) NOT NULL,
  `CS` mediumint(9) NOT NULL,
  `Tr` mediumint(9) NOT NULL,
  PRIMARY KEY (`FID`),
  KEY `Owner` (`Owner`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_FleetGroup` (
  `FID` mediumint(9) NOT NULL,
  `FGID` mediumint(9) NOT NULL,
  UNIQUE KEY `FID` (`FID`),
  KEY `FGID` (`FGID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_FleetStationary` (
  `FGID` mediumint(9) NOT NULL,
  `Location` mediumint(9) NOT NULL,
  UNIQUE KEY `FID` (`FGID`),
  KEY `Location` (`Location`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_FleetMoving` (
  `FMID` mediumint(9) NOT NULL AUTO_INCREMENT,
  `FGID` mediumint(9) NOT NULL,
  `Owner` mediumint(9) NOT NULL,
  `Target` mediumint(9) NOT NULL,
  `ETA` int(11) NOT NULL DEFAULT '0',
  `Mission` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`FMID`),
  UNIQUE KEY `FGID` (`FGID`),
  KEY `Owner` (`Owner`),
  KEY `Target` (`Target`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_Help` (
  `HID` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `Page` varchar(32) NOT NULL,
  `Description` tinytext NOT NULL,
  `Text` text NOT NULL,
  PRIMARY KEY (`HID`),
  UNIQUE KEY `Page` (`Page`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_IPBan` (
  `IP1` int(3) NOT NULL DEFAULT '0',
  `IP2` int(3) NOT NULL DEFAULT '0',
  `IP3` int(3) NOT NULL DEFAULT '0',
  `IP4` int(3) NOT NULL DEFAULT '0',
  UNIQUE KEY `IP1` (`IP1`,`IP2`,`IP3`,`IP4`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_Invitations` (
  `Time` int(11) NOT NULL,
  `TAG` varchar(5) NOT NULL,
  `PID` mediumint(9) NOT NULL,
  `Status` tinyint(4) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_Log` (
  `LID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Time` int(11) NOT NULL,
  `AID` mediumint(9) NOT NULL,
  `Command` tinyint(4) NOT NULL,
  `Result` tinyint(4) NOT NULL,
  `Arg1` varchar(32) NOT NULL,
  `Arg2` varchar(64) NOT NULL,
  `Arg3` varchar(64) NOT NULL,
  `Arg4` varchar(40) NOT NULL,
  `Arg5` tinytext NOT NULL,
  `Arg6` tinytext NOT NULL,
  PRIMARY KEY (`LID`),
  KEY `Time` (`Time`,`AID`,`Command`),
  KEY `AID` (`AID`),
  KEY `Command` (`Command`)
) ENGINE=MyISAM AUTO_INCREMENT=56740 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_LogLogin` (
  `LID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Time` int(10) unsigned NOT NULL,
  `In` tinyint(1) NOT NULL,
  `Succesfull` tinyint(1) NOT NULL,
  `IP0` tinyint(3) unsigned NOT NULL,
  `IP1` tinyint(3) unsigned NOT NULL,
  `IP2` tinyint(3) unsigned NOT NULL,
  `IP3` tinyint(3) unsigned NOT NULL,
  `FIP0` tinyint(3) unsigned NOT NULL,
  `FIP1` tinyint(3) unsigned NOT NULL,
  `FIP2` tinyint(3) unsigned NOT NULL,
  `FIP3` tinyint(3) unsigned NOT NULL,
  `PrevAID` mediumint(8) unsigned NOT NULL,
  `SecretAID` mediumint(8) unsigned NOT NULL,
  `NewAID` mediumint(8) unsigned NOT NULL,
  `CookieID` int(10) unsigned NOT NULL,
  `CookieNew` tinyint(1) NOT NULL,
  PRIMARY KEY (`LID`),
  KEY `Time` (`Time`),
  KEY `In` (`In`),
  KEY `IP0` (`IP0`,`IP1`,`IP2`,`IP3`),
  KEY `FIP0` (`FIP0`,`FIP1`,`FIP2`,`FIP3`),
  KEY `NewAID` (`NewAID`),
  KEY `CookieID` (`CookieID`)
) ENGINE=MyISAM AUTO_INCREMENT=71729 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_Login` (
  `AID` mediumint(9) NOT NULL,
  `Date` int(11) NOT NULL,
  `IP0` tinyint(3) unsigned NOT NULL,
  `IP1` tinyint(3) unsigned NOT NULL,
  `IP2` tinyint(3) unsigned NOT NULL,
  `IP3` tinyint(3) unsigned NOT NULL,
  `FIP0` tinyint(3) unsigned NOT NULL,
  `FIP1` tinyint(3) unsigned NOT NULL,
  `FIP2` tinyint(3) unsigned NOT NULL,
  `FIP3` tinyint(3) unsigned NOT NULL,
  `Count` mediumint(8) unsigned NOT NULL,
  UNIQUE KEY `AID_2` (`AID`,`IP0`,`IP1`,`IP2`,`IP3`,`FIP0`,`FIP1`,`FIP2`,`FIP3`),
  KEY `Date` (`Date`),
  KEY `IP0` (`IP0`,`IP1`,`IP2`,`IP3`),
  KEY `FIP0` (`FIP0`,`FIP1`,`FIP2`,`FIP3`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_Map` (
  `SID` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `X` smallint(6) NOT NULL,
  `Y` smallint(6) NOT NULL,
  `Name` varchar(96) NOT NULL,
  `Level` tinyint(3) unsigned NOT NULL COMMENT '(minpop+maxpop)/2',
  `Special` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `PlayerSpawn` tinyint(3) NOT NULL DEFAULT '0',
  `MaxPlanets` tinyint(4) NOT NULL DEFAULT '11',
  `BeginSpawnTime` int(11) NOT NULL,
  PRIMARY KEY (`SID`),
  UNIQUE KEY `SpawningIndex` (`PlayerSpawn`,`SID`),
  KEY `X` (`X`,`Y`),
  KEY `BeginSpawnTimeIdx` (`BeginSpawnTime`)
) ENGINE=MyISAM AUTO_INCREMENT=76 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_Members` (
  `AID` mediumint(9) NOT NULL COMMENT 'ref. NC_Account.AID',
  `GID` smallint(6) NOT NULL COMMENT 'ref. NC_Groups.GID',
  KEY `AID` (`AID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_MultiLTAllowed` (
  `LTID` int(10) unsigned NOT NULL,
  `aAID` mediumint(8) unsigned NOT NULL,
  KEY `LTID` (`LTID`),
  KEY `aAID` (`aAID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_MultiLoginThread` (
  `LTID` int(10) unsigned NOT NULL,
  `ChangingIP` tinyint(1) NOT NULL,
  PRIMARY KEY (`LTID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_MultiWatchlist` (
  `AID` mediumint(8) unsigned NOT NULL,
  `With` mediumint(8) unsigned NOT NULL,
  `Strength` int(11) NOT NULL,
  PRIMARY KEY (`AID`),
  KEY `Strength` (`Strength`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_NewBackground` (
  `X1` smallint(6) NOT NULL,
  `Y1` smallint(6) NOT NULL,
  `X2` smallint(6) NOT NULL,
  `Y2` smallint(6) NOT NULL,
  `NBgX` smallint(5) unsigned NOT NULL,
  `Z` tinyint(4) NOT NULL,
  `Transparency` tinyint(3) unsigned NOT NULL,
  KEY `X1` (`X1`),
  KEY `Y1` (`Y1`),
  KEY `X2` (`X2`),
  KEY `Y2` (`Y2`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_NewBackgroundList` (
  `NBgX` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `FileName` varchar(18) NOT NULL,
  PRIMARY KEY (`NBgX`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_News` (
  `NID` int(11) NOT NULL AUTO_INCREMENT,
  `PID` mediumint(9) NOT NULL COMMENT 'ref. AW_Player.PID',
  `Time` int(11) NOT NULL,
  `Text` text NOT NULL,
  `Type` tinyint(4) NOT NULL,
  `IncPID` mediumint(9) NOT NULL DEFAULT '0',
  `IncVpr` mediumint(9) NOT NULL DEFAULT '0',
  `IncInt` mediumint(9) NOT NULL DEFAULT '0',
  `IncFr` mediumint(9) NOT NULL DEFAULT '0',
  `IncBs` mediumint(9) NOT NULL DEFAULT '0',
  `IncDrn` mediumint(9) NOT NULL DEFAULT '0',
  `IncTr` mediumint(8) unsigned NOT NULL,
  `IncTarget` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`NID`),
  KEY `PID_2` (`PID`,`Time`)
) ENGINE=MyISAM AUTO_INCREMENT=7370 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_PM` (
  `PMID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Owner` mediumint(8) unsigned NOT NULL,
  `From` mediumint(8) unsigned NOT NULL COMMENT 'ref AW_Account.AID',
  `To` mediumint(8) unsigned NOT NULL COMMENT 'ref AW_Account.AID',
  `Time` int(11) NOT NULL,
  `Topic` tinytext NOT NULL,
  `Text` text NOT NULL,
  PRIMARY KEY (`PMID`),
  UNIQUE KEY `Owner_2` (`Owner`,`From`,`Time`),
  KEY `From` (`From`),
  KEY `Owner` (`Owner`,`To`,`Time`)
) ENGINE=MyISAM AUTO_INCREMENT=30753 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_Permissions` (
  `SectID` smallint(6) NOT NULL,
  `User` mediumint(8) unsigned NOT NULL,
  `TAG` varchar(5) NOT NULL,
  KEY `SectID` (`SectID`),
  KEY `User` (`User`),
  KEY `TAG` (`TAG`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_Planet` (
  `PLID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `SID` smallint(5) unsigned NOT NULL COMMENT 'ref AW_Map.SID',
  `Owner` mediumint(8) unsigned NOT NULL COMMENT 'ref AW_Player.PID',
  `CustomName` varchar(32) NOT NULL,
  `Type` smallint(5) unsigned NOT NULL DEFAULT '1',
  `PopulationRemain` float unsigned NOT NULL DEFAULT '21',
  `FarmRemain` float NOT NULL DEFAULT '5',
  `FactoryRemain` float NOT NULL DEFAULT '5',
  `CybernetRemain` float NOT NULL DEFAULT '5',
  `LabRemain` float NOT NULL DEFAULT '5',
  `RefineryRemain` float NOT NULL DEFAULT '5',
  `StarbaseRemain` float unsigned NOT NULL DEFAULT '5',
  `Population` smallint(6) NOT NULL,
  `Farm` smallint(6) unsigned NOT NULL,
  `Factory` smallint(6) unsigned NOT NULL,
  `Cybernet` smallint(6) unsigned NOT NULL,
  `Lab` smallint(6) unsigned NOT NULL,
  `Refinery` smallint(6) unsigned NOT NULL DEFAULT '0',
  `Starbase` smallint(6) unsigned NOT NULL,
  `Gateway` varchar(32) NOT NULL,
  `Ring` tinyint(3) unsigned NOT NULL,
  `PP` double unsigned NOT NULL,
  `Tr` mediumint(9) NOT NULL,
  `TrRemain` smallint(6) NOT NULL,
  `CS` mediumint(9) NOT NULL,
  `CSRemain` smallint(6) NOT NULL,
  `Vpr` mediumint(9) NOT NULL,
  `VprRemain` smallint(6) NOT NULL,
  `Int` mediumint(9) NOT NULL,
  `IntRemain` smallint(6) NOT NULL,
  `Fr` mediumint(9) NOT NULL,
  `FrRemain` smallint(6) NOT NULL,
  `Bs` mediumint(9) NOT NULL,
  `BsRemain` smallint(6) NOT NULL,
  `Drn` mediumint(9) NOT NULL,
  `DrnRemain` smallint(6) NOT NULL,
  `FleetOwner` mediumint(9) NOT NULL COMMENT 'ref AW_Player.PID',
  `Embassy` tinyint(1) NOT NULL DEFAULT '0',
  `SpaceStation` tinyint(1) NOT NULL DEFAULT '0',
  `STx` float NOT NULL DEFAULT '0',
  `PPProd` float unsigned NOT NULL DEFAULT '0',
  `PopProd` float unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`PLID`),
  UNIQUE KEY `SID` (`SID`,`Ring`),
  KEY `Owner` (`Owner`),
  KEY `Gateway` (`Gateway`)
) ENGINE=MyISAM AUTO_INCREMENT=672 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_PlanetCreated` (
  `PLID` mediumint(8) unsigned NOT NULL,
  `Time` int(11) NOT NULL,
  `PID` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`PLID`),
  KEY `PID` (`PID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_PlanetType` (
  `PTID` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `TypeName` varchar(16) NOT NULL,
  `Growth` smallint(5) unsigned NOT NULL DEFAULT '100',
  `Science` smallint(5) unsigned NOT NULL DEFAULT '100',
  `Culture` smallint(5) unsigned NOT NULL DEFAULT '100',
  `Production` smallint(5) unsigned NOT NULL DEFAULT '100',
  `ToxicStability` smallint(5) unsigned NOT NULL DEFAULT '100',
  `Attack` smallint(5) unsigned NOT NULL DEFAULT '100',
  `Defense` smallint(5) unsigned NOT NULL DEFAULT '100',
  `CultureSlot` tinyint(1) NOT NULL DEFAULT '1',
  `BaseCost` smallint(5) unsigned NOT NULL DEFAULT '10',
  `TechReq` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Description` text NOT NULL,
  PRIMARY KEY (`PTID`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_Player` (
  `PID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `SitAID` mediumint(8) unsigned NOT NULL,
  `SitCount` tinyint(3) unsigned NOT NULL,
  `AID` mediumint(8) unsigned NOT NULL COMMENT 'ref. AW_Account.AID',
  `HomeSID` smallint(5) unsigned NOT NULL COMMENT 'ref AW_MAP.SID',
  `TAG` varchar(5) NOT NULL COMMENT 'ref. AW_Alliance.TAG',
  `Points` smallint(6) NOT NULL,
  `Countdown` tinyint(4) NOT NULL DEFAULT '15',
  `Rank` mediumint(9) NOT NULL,
  `WasInCountdown` tinyint(3) unsigned NOT NULL,
  `Sensory` tinyint(4) NOT NULL,
  `Engineering` tinyint(4) NOT NULL,
  `Warp` tinyint(4) NOT NULL,
  `Physics` tinyint(4) NOT NULL,
  `Mathematics` tinyint(4) NOT NULL,
  `Urban` tinyint(4) NOT NULL,
  `SensoryRemain` float unsigned NOT NULL DEFAULT '0',
  `EngineeringRemain` float unsigned NOT NULL DEFAULT '0',
  `WarpRemain` float unsigned NOT NULL DEFAULT '0',
  `MathematicsRemain` float unsigned NOT NULL DEFAULT '0',
  `PhysicsRemain` float unsigned NOT NULL DEFAULT '0',
  `UrbanRemain` float unsigned NOT NULL DEFAULT '0',
  `Growth` tinyint(4) NOT NULL DEFAULT '0',
  `Science` tinyint(4) NOT NULL DEFAULT '0',
  `Culture` tinyint(4) NOT NULL DEFAULT '0',
  `Production` tinyint(4) NOT NULL DEFAULT '0',
  `Speed` tinyint(4) NOT NULL DEFAULT '0',
  `Attack` tinyint(4) NOT NULL DEFAULT '0',
  `Defence` tinyint(4) NOT NULL DEFAULT '0',
  `Country` varchar(4) DEFAULT NULL,
  `LastUpdate` int(11) NOT NULL DEFAULT '0' COMMENT 'Unix time',
  `CultureLvl` smallint(6) NOT NULL,
  `CultureRemain` float NOT NULL,
  `SelectedScience` tinyint(4) NOT NULL DEFAULT '0',
  `PL` smallint(6) NOT NULL DEFAULT '0',
  `PLRemain` int(11) NOT NULL DEFAULT '5',
  `AT` double NOT NULL DEFAULT '0',
  `TA` float NOT NULL DEFAULT '0',
  `VL` smallint(6) NOT NULL,
  `VLRemain` int(11) NOT NULL,
  `TechDevelop` tinyint(3) unsigned NOT NULL,
  `TechSelected` smallint(5) unsigned NOT NULL,
  `TechRemain` float unsigned NOT NULL,
  PRIMARY KEY (`PID`)
) ENGINE=MyISAM AUTO_INCREMENT=53 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `NC_PlayerArtefactCount` (
  `PID` mediumint(9),
  `C` bigint(21)
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_Post` (
  `PstID` int(11) NOT NULL AUTO_INCREMENT,
  `ThID` mediumint(9) unsigned NOT NULL COMMENT 'ref. AW_Threads.ThID',
  `Author` mediumint(9) unsigned NOT NULL COMMENT 'ref. NC_Account.AID',
  `Time` int(11) NOT NULL,
  `Text` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `Censure` tinytext NOT NULL,
  PRIMARY KEY (`PstID`),
  KEY `Time` (`Time`),
  KEY `ThID` (`ThID`)
) ENGINE=MyISAM AUTO_INCREMENT=25429 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_Regcode` (
  `AID` mediumint(9) NOT NULL COMMENT 'ref. AW_Account.AID',
  `Code` varchar(32) NOT NULL,
  PRIMARY KEY (`AID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_Sections` (
  `SectID` smallint(6) NOT NULL AUTO_INCREMENT,
  `Name` varchar(64) NOT NULL,
  `Description` text NOT NULL,
  `Newest` int(11) NOT NULL,
  `Owner` mediumint(8) unsigned NOT NULL,
  `OwnerTag` varchar(5) NOT NULL,
  `Read` tinyint(4) NOT NULL COMMENT 'none/alliance/group/all',
  `Write` tinyint(4) NOT NULL,
  `New` tinyint(4) NOT NULL,
  `Moderate` tinyint(4) NOT NULL,
  PRIMARY KEY (`SectID`),
  KEY `Owner` (`Owner`),
  KEY `OwnerTag` (`OwnerTag`)
) ENGINE=MyISAM AUTO_INCREMENT=134 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_Starsystemnames` (
  `Name` varchar(96) NOT NULL,
  `LastRing` tinyint(4) NOT NULL DEFAULT '0',
  `Comment` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_TechList` (
  `TechID` smallint(5) unsigned NOT NULL,
  `Name` varchar(48) NOT NULL,
  `Help` varchar(16) NOT NULL,
  `Hint` text NOT NULL,
  `Sensory` smallint(5) unsigned NOT NULL,
  `Engineering` smallint(5) unsigned NOT NULL,
  `Warp` smallint(5) unsigned NOT NULL,
  `Physics` smallint(5) unsigned NOT NULL,
  `Mathematics` smallint(5) unsigned NOT NULL,
  `Urban` smallint(5) unsigned NOT NULL,
  `Tech1` smallint(5) unsigned NOT NULL,
  `Tech2` smallint(5) unsigned NOT NULL,
  `ScienceCost` int(10) unsigned NOT NULL,
  `ATCost` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`TechID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_Technology` (
  `PID` mediumint(8) unsigned NOT NULL,
  `Technology` smallint(5) unsigned NOT NULL,
  KEY `PID` (`PID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_Threads` (
  `SectID` smallint(6) NOT NULL,
  `ThID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(64) NOT NULL,
  `Description` text NOT NULL,
  `Newest` int(11) NOT NULL,
  `Locked` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ThID`)
) ENGINE=MyISAM AUTO_INCREMENT=2118 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_Unread` (
  `AID` mediumint(9) NOT NULL,
  `ThID` mediumint(9) NOT NULL,
  `LastRead` int(11) NOT NULL,
  PRIMARY KEY (`AID`,`ThID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NC_globalsettings` (
  `Ringlvl` smallint(5) unsigned NOT NULL DEFAULT '0',
  `RoundName` varchar(32) NOT NULL DEFAULT 'None',
  `Start` int(10) unsigned NOT NULL,
  `Status` tinyint(1) NOT NULL,
  `Version` varchar(32) NOT NULL,
  `fleetlanding` int(11) NOT NULL,
  `FrozenFrom` int(11) NOT NULL DEFAULT '0',
  `FrozenTo` int(11) NOT NULL DEFAULT '0',
  `FrozenDone` tinyint(1) NOT NULL DEFAULT '1',
  `SingleWon` mediumint(9) NOT NULL,
  `AllianceWon` varchar(5) NOT NULL,
  `LastCoreLaunch` int(11) NOT NULL,
  `BonusTime` int(11) NOT NULL,
  `LoginThread` int(10) unsigned NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50001 DROP TABLE IF EXISTS `NC_PlayerArtefactCount`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = latin1 */;
/*!50001 SET character_set_results     = latin1 */;
/*!50001 SET collation_connection      = utf8_polish_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`my3470_nc`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `NC_PlayerArtefactCount` AS select `A`.`PID` AS `PID`,count(0) AS `C` from `NC_Artefact` `A` where (`A`.`Artefact` <> 0) group by `A`.`PID` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
