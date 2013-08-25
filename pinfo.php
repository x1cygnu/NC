<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");
include_once("internal/player.php");
include_once("internal/awards.php");
include_once("internal/hint.php");
include_once("internal/planet.php");

session_start();


global $GET;
$GET=array();

get("id","integer");

$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");

$H->sTitle="Northern Cross - Player info";

$sql=&OpenSQL($H);

ForceActivePlayer($sql, $H, "pinfo.php");

ForceFrozen($sql, $H);

$H->AddStyle("race.css");
$H->AddStyle("hint.css");

if ($_SESSION['IsAdmin'])
{
    get("m","integer");
    if (isset($GET['m']))
    {
	if ($GET['m']==1)
	    player_set_multi($sql, $GET['id'], 100);
	else
	    player_set_multi($sql, $GET['id'], 0);
    }
}

include("part/mainmenu.php");

if (!exists($GET['id']))
{
    $H->Insert(new Error("Player not specified"));
    $H->Draw();
    die;
}



$bio=player_get_science($sql,$_SESSION['PID'],"Sensory");
$player=player_get_all($sql,$GET['id']);

$T=new Table();
$T->sClass='block';
if ($player['Nick']=="")
    $isUnknown=true;
else
    $isUnknown=false;
    
if ($isUnknown)
    $T->Insert(1,1,"--- unknown ---");
else
{
    if ($player['TAG']!="")
	$T->Insert(1,1,new Link("alliance.php?tag=" . $player['TAG'], '[' . $player['TAG'] . ']'));
    $T->Insert(1,1," " . $player['Nick']);
    }


$I=new Cell();
get("tf","integer");
if ($GET['tf']!=1)
    $I->Insert(new Link("pinfo.php?id={$GET['id']}&tf=1","text form"));
else
{
    $I->Insert("<pre>Time                   Player      (Se En Wr Ph Mt Ur)(Gr Sc Cu Pr Sp At Df)</pre>");
    $I->Insert("<pre>" . DecodeTime(EncodeNow()) . sprintf("(GMT%+d)",$_SESSION['TimeZone']) . " " . sprintf("%-12s",$player['Nick'] . ":"));
    $I->sStyle="text-align : left;";
}

$T->Insert(1,3,"Player Summary");
$T->SetClass(1,3,"scan");
$T->aRowClass[3]='legend';
$T->Insert(1,4,MakeHint("Account ID","permanent account ID number, constant over rounds")); $T->Insert(2,4,"" . $player['AID']);
$T->Insert(1,5,MakeHint("Player ID","civilisation ID number, may vary over rounds and will change when player resigns")); $T->Insert(2,5,"" . $player['PID']);
$pos=starsystem_get_coords($sql,$player['HomeSID']);
//$T->Insert(1,6,MakeHint("Home","coordinates of starsystem where player was spawned")); $T->Insert(2,6,"{$pos['X']}/{$pos['Y']}");

$T->Insert(1,7,MakeHint("Country","country from which player connected to NC. Not yet supported")); $T->Insert(2,7,"{$player['Country']}");

$SL=max($player['Sensory'],$player['Engineering'],$player['Warp'],$player['Mathematics'],$player['Physics'],$player['Urban']);
$T->Insert(1,8,MakeHint("Science","level of player's highest science")); $T->Insert(2,8,"" . $SL); 
$T->Insert(1,9,MakeHint("Culture","Maximum planets the player may own")); $T->Insert(2,9,"" . $player['CultureLvl']);
$prc=floor((1-$player['PLRemain']/pl_points_for_lvl($player['PL']+1))*100);
$T->Insert(1,10,MakeHint("PL","Player Level -- points given for killing enemy fleets")); $T->Insert(2,10,"{$player['PL']}-{$prc}%");
$prc=floor((1-$player['VLRemain']/vl_points_for_lvl($player['VL']+1))*100);
$T->Insert(1,11,MakeHint("VL","Violence Level -- points given for killin enemy population")); $T->Insert(2,11,"{$player['VL']}-{$prc}%");
$T->Insert(1,12,"Rank (pts)");
if (!$isUnknown and $player['ForumAdmin']==0)
{
if ($player['Rank']<100000)
    $T->Insert(2,12,MakeHint("#{$player['Rank']} ({$player['Points']})","points = sum(population) + culture*2 + PL*3 + VL + sum(science over 20)"));
else
    $T->Insert(2,12,MakeHint("Newcomer","Player connected today and is not yet included in the rankings"));
}
else
    $T->Insert(2,12,"not ranked");
for ($i=4; $i<=12; ++$i)
    $T->SetClass(1,$i,'sublegend');

$PlayerTechnology=tech_get_tech($sql, $_SESSION['PID']);

$TableWidth=2;


$T->Join(1,3,2,1);
if (($bio>=$player['Sensory']+4 and tech_check($PlayerTechnology,2) and starsystem_in_bio_range($sql,$player['HomeSID'],$_SESSION['PID'])) or $player['PID']==$_SESSION['PID'])
{
    $T->Insert(3,3,"Technology Report");
    $TableWidth=4;
    $T->SetCols(4);
    $T->Join(3,3,2,1);
    $T->Join(3,4,2,2);    
    $T->SetClass(3,4,'explain');
    $Techs=tech_get_player_names($sql, $GET['id']);
    $cnt=0;
    foreach ($Techs as $Tech)
    {
	if ($cnt>0)
	    $T->Insert(3,4,',');
	if ($cnt==6 or $cnt==12)
	    $T->Insert(3,4,new Br());
	$T->Insert(3,4,$Tech.' ');
    }
}

if (($bio>=$player['Sensory']+8 and tech_check($PlayerTechnology,3) and starsystem_in_bio_range($sql,$player['HomeSID'],$_SESSION['PID'])) or $player['PID']==$_SESSION['PID'])
{
    $TableWidth=4;
    if ($GET['tf']==1)
    {
	$space=0;
	$I->Insert("(");
    }
    $T->Insert(3,6,"Intelligence Report");
    $T->SetClass(3,6,"legend scan");
    foreach ($sciences as $u => $science)
        {
        $DO=new Div();
	$DO->sClass="hinted" . $_SESSION['Hint'];
	if ($_SESSION['Hint']>0)
	{
        $D=new Div();
        switch ($science)
	{
	    case "Sensory":
		$D->Insert("Increases your view and operation range (the green area on your map)");
	        $D->Br();
	        $D->Insert("If your sensory level is at least 6 levels higher than your opponent, you may learn about his race and current science levels");
		break;
	    case "Engineering":
		$D->Insert("Decreases ship price, 2.5%% of inicial price every 2 levels");
		break;
	    case "Warp":
		$D->Insert("Increases speed of your ships");
		break;
	    case "Physics":
		$D->Insert("Increases chances of winning a battle.");
		break;
	    case "Mathematics":
		$D->Insert("Increases number of survivours after a won battle");
		break;
	    case "Urban":
		$D->Insert("Defines your maximum population on a planet, and reduces toxin emmisions");
		break;
        }
	$DO->Insert($D);
	}
	$DO->Insert($science);

	    $T->Insert(3,$u+7,$DO);
	    $T->Insert(4,7+$u,"{$player[$science]}");
	    if ($GET['tf']==1)
	    {
		if ($space)
		    $I->Insert(" ");
		$I->Insert(sprintf("%2d",$player[$science]));
		$space=1;
	    }
	    $T->SetClass(3,7+$u,'sublegend');
	}
    $T->SetClass(4,7,"number");
    $T->Join(3,6,2,1);

    if ($GET['tf']==1)
	$I->Insert(")");
    }
elseif ($TableWidth=4)
{
    $T->Join(3,6,2,7);
}
    
if (($bio>=$player['Sensory']+12 and tech_check($PlayerTechnology,4) and starsystem_in_bio_range($sql,$player['HomeSID'],$_SESSION['PID'])) or $player['PID']==$_SESSION['PID'])
    {
	$TableWidth=6;
	if ($GET['tf']==1)
	    $I->Insert("(");
	$T->Insert(5,3,"Race");
	$T->SetClass(5,3,"scan");
        $T->Insert(5,4,"Growth"); $T->Insert(6,4,"{$player['Growth']}"); if($GET['tf']==1) $I->Insert(sprintf("%+2d",$player['Growth']));
        $T->SetClass(6,4,"number");
        $T->Insert(5,5,"Science"); $T->Insert(6,5,"{$player['Science']}"); if($GET['tf']==1) $I->Insert(sprintf("%+3d",$player['Science']));
        $T->Insert(5,6,"Culture"); $T->Insert(6,6,"{$player['Culture']}"); if($GET['tf']==1) $I->Insert(sprintf("%+3d",$player['Culture']));
        $T->Insert(5,7,"Production"); $T->Insert(6,7,"{$player['Production']}"); if($GET['tf']==1) $I->Insert(sprintf("%+3d",$player['Production']));
        $T->Insert(5,8,"Speed"); $T->Insert(6,8,"{$player['Speed']}"); if($GET['tf']==1) $I->Insert(sprintf("%+3d",$player['Speed']));
        $T->Insert(5,9,"Attack"); $T->Insert(6,9,"{$player['Attack']}"); if($GET['tf']==1) $I->Insert(sprintf("%+3d",$player['Attack']));
        $T->Insert(5,10,"Defence"); $T->Insert(6,10,"{$player['Defence']}"); if($GET['tf']==1) $I->Insert(sprintf("%+3d",$player['Defence']));
        for ($i=4; $i<=10; ++$i)
	    $T->SetClass(5,$i,"sublegend");
        $T->Join(5,3,2,1);

	if ($GET['tf']==1)
	    $I->Insert(")");
    
    $T->Join(5,11,2,2); //clear it
}
$T->Join(1,1,$TableWidth,1);
$T->Join(1,2,$TableWidth,1);
$T->Set(1,2,$I);
$T->aRowClass[1]='title';

$T->Insert(1,13,new Link("post.php?pm={$player['AID']}","Send private message"));
$T->Join(1,13,9,1);
$T->aRowClass[13]='legend';

$Mds=awards_list($sql, $player['AID']);

$Awards=new Table();
$Awxpos=0;
$Awypos=1;

foreach($Mds as $Md)
{
if ((++$Awxpos)>4) {$Awxpos=1; $Awypos+=2; }
$MainName=awards_get_name($Md['Round'], $Md['Rank'], $Md['Type']);
$MainMedal=awards_get_medal($Md['Rank']);
$Awards->Insert($Awxpos,$Awypos,new Image("IMG/medal/{$MainName}.jpg"));
$Awards->Insert($Awxpos,$Awypos+1,new Image("IMG/medal/{$MainMedal}.jpg"));
$Awards->aRowClass[$Awypos]='nullspace';
$Awards->aRowClass[$Awypos+1]='nullspace';
}

$T->Insert(1,14,$Awards);
$T->Join(1,14,9,1);



$H->Insert($T);

if (($bio>=$player['Sensory']+16 and tech_check($PlayerTechnology,5) and starsystem_in_bio_range($sql,$player['HomeSID'],$_SESSION['PID'])))
{
    $Ls=planet_list($sql, $GET['id']);
    $T=new Table();
    $T->sClass='block';
    $T->aRowClass[1]='legend';
    $T->Insert(1,1,"Name");
    $T->Insert(2,1,"Pop");
    $T->Insert(3,1,"Tx");
    $T->Insert(4,1,"Frm");
    $T->Insert(5,1,"Fct");
    $T->Insert(6,1,"Cyb");
    $T->Insert(7,1,"Lab");
    $T->Insert(8,1,"Ref");
    $T->Insert(9,1,"PP");
    $T->Insert(10,1,"St");
    $T->Insert(11,1,"Emb");
    $T->Insert(12,1,"Gtw");
    $i=1;
    foreach ($Ls as $L)
    {
	++$i;
        $T->Insert(1,$i,"{$L['Name']} {$L['Ring']}");
	if ($L['CustomName']!="")
	    {
	    $T->Insert(1,$i,new Br());
	    $T->Insert(1,$i,$L['CustomName']);
	    }
	$T->Insert(2,$i,''.$L['Population']);
	$T->Insert(3,$i,''.floor($L['Toxic']/1000));
	$T->Insert(4,$i,''.$L['Farm']);
	$T->Insert(5,$i,''.$L['Factory']);
	$T->Insert(6,$i,''.$L['Cybernet']);
	$T->Insert(7,$i,''.$L['Lab']);
	$T->Insert(8,$i,''.$L['Refinery']);
	$T->Insert(9,$i,''.floor($L['PP']));
	$T->Insert(10,$i,''.($L['SpaceStation']>0?'+':''));
	$T->Insert(11,$i,''.($L['Embassy']>0?'+':''));
	$T->Insert(12,$i,''.($L['Gateway']!=''?'+':''));
    }
    $H->Insert($T);
}

get("b","integer");
if (isset($GET['b']))
    $H->Insert(new Link("detail.php?id={$GET['b']}","Back to system detail"));

if ($_SESSION['IsAdmin'])
{
    $H->Br();
    $H->Insert("current multi level: " . $player['Multi']);
    $H->Br();
    $H->Insert(new Link("pinfo.php?id={$GET['id']}&m=1","Mark multi"));
    $H->Br();
    $H->Insert(new Link("pinfo.php?id={$GET['id']}&m=0","Unmark multi"));
}

$skipInput=true;
include("part/race.php");

include("part/mainsubmenu.php");
$H->Draw();
CloseSQL($sql);
?>
