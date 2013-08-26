<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/validator.php");
include_once("internal/starsystem.php");
include_once("internal/hint.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");
$H->AddStyle("detail.css");
$H->AddStyle("hint.css");

$H->sTitle="Northern Cross - System detail";

$sql=&OpenSQL($H);

ForceActivePlayer($sql, $H, "detail.php");


ForceFrozen($sql, $H);

get("id","integer");

$menuselected="Map";
include("./part/mainmenu.php");


$info=starsystem_get_all($sql,$GET['id']);
$I=new Table();
$I->sClass='block standard';

$I->Insert(1,1,"Star <b>" . $info['Name'] . "</b>");
$I->aRowClass[1]='title';
$I->Insert(1,2,MakeHint("Position","coordinates relative to center of Galaxy"));
$I->Insert(1,3,"ID");
$I->Insert(1,4,MakeHint("Level","average population within the system, without the lowest and highest"));
get("tf","integer");
$I->Insert(1,5,"text form");
if ($GET['tf']!=1)
    $I->Insert(2,5,new Link("detail.php?id={$GET['id']}&tf=1","click to open"));
else
{
    $I->Insert(2,5,"<pre>");
    $I->Insert(2,5,DecodeTime(EncodeNow()) . sprintf("(GMT%+d)",$_SESSION['TimeZone']));
//    $I->Insert(2,5,new Br());
    $I->Insert(2,5,"\nStar <b>" . $info['Name'] . "</b> (" . $info['X'] . "/" . $info['Y'] .") [" . $info['SID'] . "]");
    $I->Get(2,5)->sStyle="text-align : left;";
}

$I->SetClass(1,2,"legend");
$I->SetClass(1,3,"legend");
$I->SetClass(1,4,"legend");
$I->SetClass(1,5,"legend");

$I->Insert(2,2,$info['X'] . "/" . $info['Y']);
$I->Insert(2,3,"{$info['SID']}");
$I->Insert(2,4,"{$info['Level']}");

$I->Join(1,1,3,1);


if (!starsystem_in_bio_range($sql, $GET['id'], $_SESSION['PID']))
{
    $H->Insert($I);
    $H->Insert(new Error("Selected starsystem is outside your sensory range"));
    $H->Draw();
    die;
}


$T=new Table();
$T->sClass="standard";
$T->Insert(1,1,"Starsystem detail");
$T->aRowClass[1]="title";

$T->Insert(1,2,MakeHint("Orbit","lowest is the nearest to the star. Distance is a square of the orbit number"));
$T->SetClass(1,2,'orbitcolumn');
$T->Insert(2,2,MakeHint("Pop","Gives production points and science points for the planet owner"));
$T->SetClass(2,2,'numbercolumn');
$T->Insert(3,2,MakeHint("Toxic","Reduces planet production and growth efficiency"));
$T->SetClass(3,2,'numbercolumn');
$T->Insert(4,2,MakeHint("SB","Cheap but stationary defense"));
$T->SetClass(4,2,'numbercolumn');
$T->Insert(5,2,MakeHint("Owner","Name of a player owner. 'unknown' is a planet whose owner resigned from the game"));
$T->aRowClass[2]="legend";
$T->Insert(6,2,"Name");
$T->aRowClass[2]="legend";

$T->Join(1,1,5,1);


$detail=starsystem_detail($sql,$GET['id']);

$i=2;
foreach ($detail as $planet)
{
    ++$i;
    $T->Insert(1,$i,$planet['Ring']);
    $T->Insert(2,$i,$planet['Population']);
    $Tx=floor($planet['STx']/1000);
    $T->Insert(3,$i,"$Tx");
    $T->Insert(4,$i,$planet['Starbase']);
    $T->Insert(6,$i,"{$planet['CustomName']}");
    
    if ($GET['tf']==1)
    {
//	$I->Insert(2,5,new Br());
	$I->Insert(2,5,"\n#" . sprintf("%2d", $planet['Ring']) . " "
		       . sprintf("%2d", $planet['Population']) . "p "
		       . sprintf("%2d", $Tx) . "tx "
		       . sprintf("%2d", $planet['Starbase']) . "sb ");
    }
    
    $T->SetClass(1,$i,"sublegend");
    if ($planet['Owner']==0)
    {
	if ($planet['Population']==0)
	{
	    $T->aRowClass[$i]="freeplanet";
            $T->Insert(5,$i,"Free Planet");
	    if ($GET['tf']==1)
	    $I->Insert(2,5,"--- free ---");
	}
        else
	{
	    $T->aRowClass[$i]="unknownplanet";
            $T->Insert(5,$i,"Unknown");
	    if ($GET['tf']==1)
	    $I->Insert(2,5,"-- unknown --");
	}
    }
    else
    {
	if (!isset($planet['Nick']))
	{
	    $T->aRowClass[$i]="unknownplanet";
            $T->Insert(5,$i,"Unknown");
	    if ($GET['tf']==1)
	    $I->Insert(2,5,"--- unknown ---");
	}
	else
	{
        $T->aRowClass[$i]="takenplanet";
	$S="";
	if (exists($planet['TAG']))
	    {
	    $T->Insert(5,$i,new Link("alliance.php?tag={$planet['TAG']}","[{$planet['TAG']}]"));
	    if ($GET['tf']==1)
		$I->Insert(2,5,sprintf("%8s","[" . $planet['TAG'] . "] "));
	    $S.=" ";
	    }
	$S.=$planet['Nick'];
        if ($GET['tf']==1)
	    {
	    if ($planet['Nick']!="The Consortium")
		$I->Insert(2,5,"" . $planet['Nick']);
	    else
		$I->Insert(2,5,"--- consortium ---");
	    }
        $T->Insert(5,$i,new Link("pinfo.php?id={$planet['Owner']}",$S));
	}
    }

    if ($planet['FleetOwner']!=0 and $planet['FleetOwner']!=$planet['Owner'])
	$T->aRowClass[$i]="siegedplanet";
}

if ($GET['tf']==1)
{
	$I->Insert(2,5,"</pre>");
    }

$Fst=new Table();
$Fst->sClass='block standard';
$Fst->Insert(1,1,"Jump to");
$Fst->SetClass(1,1,'legend');

$Lst=starsystem_bio($sql, $_SESSION['PID'], true);
$Sel = new Select();
foreach ($Lst as $Star)
    $Sel->AddOption($Star['SID'],($Star['YPC']>0?"*":"") . $Star['Name'] . " (" . $Star['X'] . "/" . $Star['Y'] . ")");
//    $Sel->AddOption($Star['SID'],$Star['YPC'] . $Star['Name'] . " (" . $Star['X'] . "/" . $Star['Y'] . ")");
$Sel->sDefault=$GET['id'];
$Sel->sName="id";

$Fst->Insert(2,1,$Sel);
$Fst->insert(3,1,new Input("submit","","Jump","smbutton"));

$F=new Form("detail.php",false);
$F->Insert($Fst);

$H->Insert($F);

$H->Insert($I);
$H->Insert($T);
include("part/mainsubmenu.php");
$H->Draw();
CloseSQL($sql);
?>
