<?php

chdir('..');
include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");
include_once("internal/planet.php");
include_once("internal/progress.php");
include_once("internal/player.php");
include_once("internal/hint.php");

session_start();

global $GET;
$GET=array();


$H = new HTML();
$H->AddStyle("default.css");


$sql=&OpenSQL($H);

ForceActivePlayer($sql, $H, "planet.php");

ForceFrozen($sql, $H);


$H->AddStyle("detail.css");
$H->AddStyle("planet.css");
$H->AddStyle("hint.css");

$menuselected="Planets";
include("mobile/part/mainmenu.php");


//player_update_all($sql,$_SESSION['PID']);

$eco=player_get_science($sql,$_SESSION['PID'],"Engineering");

get("g","integer");
get("emb","integer");
if ($GET['g']>0)
    $GET['id']=$GET['g']-1;
elseif ($GET['emb']>0)
    $GET['id']=$GET['emb']-1;
else
    get("id","integer");

    
$Index=planet_index($sql,$_SESSION['PID'],$GET['id']);


$P=planet_get_all($sql, $Index['here']);
if ($P['Owner']!=$_SESSION['PID'])
    {
	$H->Insert(new Error("You have no control over chosen planet"));
	$H->Draw();
	die;
    }

$buy=false;


if ($GET['emb']>0)	//build embassy
{
    $R=planet_construction_build($sql, $_SESSION['PID'], $P['PLID'], "Embassy", 1024);
    if ($R!="")
	$H->Insert(new Error($R));
    else
	$H->Insert(new Info("Embassy constructed"));
    $buy=true;
}

function B($thing,$alt)
{
global $sql; global $buy; global $POST; global $H; global $GET; global $Error;
global $Index;
    post($thing . 'v','integer');
    if (exists($POST[$thing . 'v']) and $POST[$thing . 'v']>0)
	{
	if (planet_spend_pp($sql, $Index['here'], $POST[$thing . 'v']))
	    {
	    if (planet_building_build($sql, $_SESSION['PID'], $Index['here'], $alt, $POST[$thing . 'v']))
	        $buy=true;
	    else
		$H->Insert($Error->Report());
	    }
	}
}

function BRP($thing,$alt)
{
global $sql; global $buy; global $POST; global $H; global $GET; global $Error;
global $Index;
    post($thing . 'v','integer');
    if (exists($POST[$thing . 'v']) and $POST[$thing . 'v']>0)
	{
	    planet_build_RP($sql, $_SESSION['PID'], $Index['here'], $alt, $POST[$thing . 'v']);
	    $buy=true;    
	}
}


function S($thing,$alt)
{
global $sql; global $buy; global $POST; global $H; global $GET; global $Error;
global $Index;
    post($thing . 'v','integer');
    if (exists($POST[$thing . 'v']) and $POST[$thing . 'v']>0)
	{
	if (planet_spend_pp($sql, $Index['here'], $POST[$thing . 'v']))
	    {
	    if (planet_build_ship($sql, $_SESSION['PID'], $Index['here'], $alt, $POST[$thing . 'v']))
		    $buy=true;
	    else
		$H->Insert($Error->Report());
	    }
	}
}

$Race=player_get_full_race($sql, $_SESSION['PID']);

//apply HTx/STx modifications

if ($_POST['build']=="Spend PP")
{
B('farm','Farm');
B('factory','Factory');
B('cybernet','Cybernet');
B('lab','Lab');
B('refinery','Refinery');
B('sb','Starbase');

S('int','Int');
S('fr','Fr');
S('bs','Bs');
S('tr','Tr');
S('cs','CS');
}
elseif ($_POST['build']=="Spend RP")
{
BRP('farm','Farm');
BRP('factory','Factory');
BRP('cybernet','Cybernet');
BRP('lab','Lab');
BRP('refinery','Refinery');
}

if ($buy)
    $P=planet_get_all($sql, $Index['here']);

$F=new Form("planet.php?id={$GET['id']}",true);

$T=new Table();
$T->SetCols(4);
$T->Insert(1,1,"{$P['Name']} {$P['Ring']}");
$T->Join(1,1,4,1);
$T->aRowClass[1]='t';
$prvid=$GET['id']-1;
$nxtid=$GET['id']+1;
if ($Index['prev']>0)
    $H->Insert(new Link("planet.php?id=$prvid","<< Previous"));
if ($Index['next']>0)
    $H->Insert(new Link("planet.php?id=$nxtid","Next >>"));


$r=2;
$T->Insert(1,$r,"Pop");
$T->Insert(2,$r,"{$P['Population']}");
$max=growth_points_for_lvl($P['Population']+1);
$T->Insert(3,$r,floor($max-$P['PopulationRemain']) . "/" . $max);

++$r;
$T->Insert(1,$r,"Tx");
$STxl=floor($P['STx']/1000);
$T->Insert(2,$r,"" . $STxl);
$STxr=floor($P['STx']%1000);
$T->Insert(3,$r,$STxr . "/" . 1000);

++$r;
$PP=$P['PP'];
$T->Insert(1,$r,"PP");
$T->Insert(2,$r,"" . floor($PP));

++$r;
$T->Insert(1,$r,"RP");
$RP=player_get_RP($sql,$_SESSION['PID']);
$T->Insert(2,$r,"$RP");

++$r;
$T->Insert(1,$r,"Frm");
$T->Insert(2,$r,"{$P['Farm']}");
$max=building_points_for_lvl($P['Farm']+1);
$T->Insert(3,$r,floor($max - $P['FarmRemain']) . "/" . $max);
if (!$Siege)
{
$T->Insert(4,$r,new Input("text","farmv","","sh"));
}

++$r;
$T->Insert(1,$r,"Fct");
$T->Insert(2,$r,"{$P['Factory']}");
$max=building_points_for_lvl($P['Factory']+1);
$T->Insert(3,$r,floor($max - $P['FactoryRemain']) . "/" . $max);
if (!$Siege)
{
$T->Insert(4,$r,new Input("text","factoryv","","sh"));
}

++$r;
$T->Insert(1,$r,"Cyb");
$T->Insert(2,$r,"{$P['Cybernet']}");
$max=building_points_for_lvl($P['Cybernet']+1);
$T->Insert(3,$r,floor($max - $P['CybernetRemain']) . "/" . $max);
if (!$Siege)
{
$T->Insert(4,$r,new Input("text","cybernetv","","sh"));
}

++$r;
$T->Insert(1,$r,"Lab");
$T->Insert(2,$r,"{$P['Lab']}");
$max=building_points_for_lvl($P['Lab']+1);
$T->Insert(3,$r,floor($max - $P['LabRemain']) . "/" . $max);
if (!$Siege)
{
$T->Insert(4,$r,new Input("text","labv","","sh"));
}

++$r;
$T->Insert(1,$r,"Ref");
$T->Insert(2,$r,"{$P['Refinery']}");
$max=building_points_for_lvl($P['Refinery']+1);
$T->Insert(3,$r,floor($max - $P['RefineryRemain']) . "/" . $max);
if (!$Siege)
{
$T->Insert(4,$r,new Input("text","refineryv","","sh"));
}

++$r;
$T->Insert(1,$r,"SB");
$T->Insert(2,$r,"{$P['Starbase']}");
$max=building_points_for_lvl($P['Starbase']+1);
$T->Insert(3,$r,floor($max - $P['StarbaseRemain']) . "/" . $max);
if (!$Siege)
{
$T->Insert(4,$r,new Input("text","sbv","","sh"));
}


    
if ($Siege)
{
    ++$r;
    $Enemy=account_get_name_from_pid($sql, $P['FleetOwner']);
    $T->Insert(1,$r,"Under siege ($Enemy)");
    $T->Join(1,$r,4,1);
    $T->SetClass(1,$r,"r");
}

++$r;
$T->Insert(1,$r,"Int");
$T->Insert(2,$r,"{$P['Int']}");
if (!$Siege)
{
$max=Int_points($eco);
$T->Insert(3,$r,floor($P['IntRemain']) . "/" . $max);
$T->Insert(4,$r,new Input("text","intv","","sh"));
}
else
$T->aRowClass[$r]='r';

++$r;
$T->Insert(1,$r,"Fr");
$T->Insert(2,$r,"{$P['Fr']}");
if (!$Siege)
{
$max=Fr_points($eco);
$T->Insert(3,$r,floor($P['FrRemain']) . "/" . $max);
$T->Insert(4,$r,new Input("text","frv","","sh"));
}
else
$T->aRowClass[$r]='r';

++$r;
$T->Insert(1,$r,"Bs");
$T->Insert(2,$r,"{$P['Bs']}");
if (!$Siege)
{
$max=Bs_points($eco);
$T->Insert(3,$r,floor($P['BsRemain']) . "/" . $max);
$T->Insert(4,$r,new Input("text","bsv","","sh"));
}
else
$T->aRowClass[$r]='r';

++$r;
$T->Insert(1,$r,"Tr");
$T->Insert(2,$r,"{$P['Tr']}");
if (!$Siege)
{
$max=Tr_points($eco);
$T->Insert(3,$r,floor($P['TrRemain']) . "/" . $max);
$T->Insert(4,$r,new Input("text","trv","","sh"));
}
else
$T->aRowClass[$r]='r';

++$r;
$T->Insert(1,$r,"CS");
$T->Insert(2,$r,"{$P['CS']}");
if (!$Siege)
{
$max=CS_points($eco);
$T->Insert(3,$r,floor($P['CSRemain']) . "/" . $max);
$T->Insert(4,$r,new Input("text","csv","","sh"));
}
else
$T->aRowClass[$r]='r';



///////////////////////////////////////////
// CONSTRUCTIONS
///////////////////////////////////////////



/////////////////////
// EMBASSY
/////////////////////
++$r;
$T->Insert(1,$r,"Emb");
$T->Insert(2,$r,"" . $P['Embassy']);
if ($P['Embassy']==0)
{
    $T->Insert(3,$r,'1024');
    if ($PP>=1024)
	{
	$GNv=$GET['id']+1;
        $T->Insert(4,$r,new Link("planet.php?emb={$GNv}","Build"));
	}
}

/////////////////////
// GATEWAY
/////////////////////
++$r;
$T->Insert(1,$r,"Gtw");
if ($P['Gateway']!="")
{
    $T->Insert(2,$r,"" . $P['Gateway']);
    $T->Join(2,$r,3,1);
}
else
{
$T->Insert(2,$r,'0');
$T->Insert(3,$r,'12288');
    if ($PP>=12288)
	{
	$GNv=$GET['id']+1;
        $T->Insert(4,$r,new Link("planet.php?g={$GNv}","Build"));
	}
}

if (!$Siege)
{
$F->Insert(new Input("submit","build","Spend PP","smbutton"));
$F->Insert(new Input("submit","build","Spend RP","smbutton"));
}


$F->Insert($T);

if (!$Siege)
{
$F->Insert(new Input("submit","build","Spend PP","smbutton"));
$F->Insert(new Input("submit","build","Spend RP","smbutton"));
}


$H->Insert($F);

$H->Draw();
CloseSQL($sql);
?>
