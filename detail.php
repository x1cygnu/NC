<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/validator.php");
include_once("internal/starsystem.php");
include_once("internal/hint.php");

session_start();

$_SESSION['Ajax']=1;

global $GET;
$GET=array();

$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");
$H->AddStyle("detail.css");
$H->AddStyle("hint.css");
$H->AddStyle("rb2.css");

$H->sTitle="Northern Cross - System detail";

$sql=&OpenSQL($H);

ForceActivePlayer($sql, $H, "detail.php");


ForceFrozen($sql, $H);

get("id","integer");

$menuselected="Map";
include("./part/mainmenu.php");


if ($_SESSION['Ajax'])
{
    $H->AddJavascriptFile('js/ajax.js');
    $H->AddJavascriptFile('js/common.js');
    $H->AddJavascriptFile('js/detail.js');    
}

$info=starsystem_get_all($sql,$GET['id']);
$I=new Table();
if ($_SESSION['Ajax'])
    $I->sId='star';
$I->sClass='block standard';

$I->Insert(1,1,"Star <b>" . $info['Name'] . "</b>");
$I->aRowClass[1]='title';
$I->Insert(1,2,MakeHint("Position","coordinates relative to center of Galaxy"));
$I->Insert(1,3,"ID");
$I->Insert(1,4,MakeHint("Level","average population within the system, without the lowest and highest"));
get("tf","integer");
if ($GET['tf']!=1)
{
    if ($_SESSION['Ajax']==1)
    {
	$L=new Link("","text form");
	$L->onClick("ajaxRequest('xml/detail.php','id={$GET['id']}',textStarsystem,false);return false;");
	$I->Insert(1,5,$L);    
    }
    else
	$I->Insert(1,5,new Link("detail.php?id={$GET['id']}&tf=1","text form"));
}
else
{
    $I->Insert(1,5,"<pre>");
    $I->Insert(1,5,DecodeTime(EncodeNow()) . sprintf("(GMT%+d)",$_SESSION['TimeZone']));
    $I->Insert(1,5,"\nStar <b>" . $info['Name'] . "</b> (" . $info['X'] . "/" . $info['Y'] .") [" . $info['SID'] . "]");
    $I->Get(1,5)->sStyle="text-align : left;";
}

$I->Get(1,5)->sId='textsystem';
$I->SetClass(1,2,"legend");
$I->SetClass(1,3,"legend");
$I->SetClass(1,4,"legend");
$I->SetClass(1,5,"legend");

$I->Insert(2,2,$info['X'] . "/" . $info['Y']);
$I->Insert(2,3,"{$info['SID']}");
$I->Insert(2,4,"{$info['Level']}");

$I->Join(1,1,3,1);




$T=new Table();
if ($_SESSION['Ajax'])
    $T->sId='planets';
$T->sClass="standard";
$T->Insert(1,1,"Starsystem detail");
$T->aRowClass[1]="title";

$T->Insert(1,2,MakeHint("Orbit","lowest is the nearest to the star. Distance is a square of the orbit number"));
$T->SetClass(1,2,'orbitcolumn');
$T->Insert(2,2,MakeHint("Type","Planets are classified into types depending on their size, atmosphere, temperature etc.<br>These have impact on efectiveness of structures on the planet"));
$T->Insert(3,2,MakeHint("Pop","Gives production points and science points for the planet owner"));
$T->SetClass(3,2,'numbercolumn');
$T->Insert(4,2,MakeHint("Toxic","Reduces planet production and growth efficiency"));
$T->SetClass(4,2,'numbercolumn');
$T->Insert(5,2,MakeHint("SB","Cheap but stationary defense"));
$T->SetClass(5,2,'numbercolumn');
$T->Insert(6,2,MakeHint("Owner","Name of a player owner. 'unknown' is a planet whose owner resigned from the game"));
$T->aRowClass[2]="legend";
$T->Insert(7,2,"Name");

$T->Join(1,1,7,1);

if (!starsystem_in_bio_range($sql, $GET['id'], $_SESSION['PID']))
{
    $H->Insert($I);
    $H->Insert($T);
    $H->Insert(new Error("Selected starsystem is outside your sensory range"));
    $H->Draw();
    die;
}



$detail=starsystem_detail($sql,$GET['id']);

$i=2;
$r=-1;
foreach ($detail as $planet)
{
	++$i;
	++$r;
	while ($r<$planet['Ring']) {
		$T->Insert(1,$i,'' . $r);
		$T->setClass(1,$i,'sublegend');
		$T->aRowClass[$i]='lackofplanet';
		++$i;
		++$r;
	}
	$T->Insert(1,$i,$planet['Ring']);
	$T->Insert(2,$i,MakeHint($planet['TypeName'],$planet['Description'] . "<br>" .
				"<b>Growth: " . $planet['Growth'] . "%, Science: " . $planet['Science'] .
				"%<br>Culture: " . $planet['Culture'] . "%, Production: " . $planet['Production'] .
				"%<br>Toxic vulnerability: " . $planet['ToxicStability'] . "%<br>" .
				" Starbase: OV: " . $planet['Attack'] . "%, DV: " . $planet['Defense'] . "%<br>Building base cost: " . $planet['BaseCost'] .
				"<br>Culture requirement: " . ($planet['CultureSlot']>0?"Yes":"No") . "<br>Technology requirement: " . ($planet['TechReq']>0?$planet['TechName']:'none') . '</b>'));
	$T->Insert(3,$i,$planet['Population']);
	$Tx=floor($planet['STx']/1000);
	$T->Insert(4,$i,"$Tx");
	$T->Insert(5,$i,$planet['Starbase']);
	$T->Insert(7,$i,"{$planet['CustomName']}");

	if ($GET['tf']==1)
	{
		$I->Insert(1,5,"\n#" . sprintf("%2d", $planet['Ring']) . " "
				. sprintf("%-10s", $planet['TypeName']) . " "
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
			if ($planet['PTID']<21)
				$T->Insert(6,$i,"Free Planet");
			if ($GET['tf']==1)
				$I->Insert(1,5,"--- free ---");
		}
		else
		{
			$T->aRowClass[$i]="unknownplanet";
			if ($planet['Ring']>0)
				$T->Insert(6,$i,"Unknown");
			if ($GET['tf']==1)
				$I->Insert(1,5,"-- unknown --");
		}
	}
	else
	{
		if (!isset($planet['Nick']))
		{
			$T->aRowClass[$i]="unknownplanet";
			if ($planet['Ring']>0)
				$T->Insert(6,$i,"Unknown");
			if ($GET['tf']==1)
				$I->Insert(1,5,"--- unknown ---");
		}
		else
		{
			$T->aRowClass[$i]="takenplanet";
			$S="";
			$colorStyle="independent";
			if ($planet['TAG']=="RED")
				$colorStyle="red";
			if ($planet['TAG']=="BLUE")
				$colorStyle="blue";
			if (exists($planet['TAG']))
			{
				$L = new Link("alliance.php?tag={$planet['TAG']}&b={$GET['id']}","[{$planet['TAG']}]");
				$L->sClass = $colorStyle;
				$T->Insert(6,$i,$L);
				if ($GET['tf']==1)
					$I->Insert(1,5,sprintf("%8s","[" . $planet['TAG'] . "] "));
				$S.=" ";
			}
			$S.=$planet['Nick'];
			if ($GET['tf']==1)
			{
				if ($planet['Nick']!="The Consortium")
					$I->Insert(1,5,"" . $planet['Nick']);
				else
					$I->Insert(1,5,"--- consortium ---");
			}
			$L=new Link("pinfo.php?id={$planet['Owner']}&b={$GET['id']}",$S);
			$L->sClass = $colorStyle;
			$T->Insert(6,$i,$L);
		}
	}

	if ($planet['FleetOwner']!=0 and $planet['FleetOwner']!=$planet['Owner'])
		$T->aRowClass[$i]="siegedplanet";

	if ($planet['CultureSlot']==0)
		$T->aRowClass[$i].='nocul';
}

if ($GET['tf']==1)
{
	$I->Insert(1,5,"</pre>");
    }
$I->Join(1,5,2,1);
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
if ($_SESSION['Ajax'])
{
    $Sel->onChange("ajaxRequest('xml/detail.php','id='+options[selectedIndex].value,drawStarsystem,false)");
}
else
{
    $Sel->sName="id";
    $Fst->Insert(3,1,new Input("submit","","Jump","smbutton"));
}
$Fst->Insert(2,1,$Sel);

$F=new Form("detail.php",false);
$F->Insert($Fst);

$H->Insert($F);

$H->Insert($I);
$H->Insert($T);
include("part/mainsubmenu.php");
$H->Br();
$H->Br();
$H->Br();
$H->Br();
$H->Br();
$H->Br();
$H->Br();
$H->Br();
$H->Draw();
CloseSQL($sql);
?>
