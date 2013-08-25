<?php

chdir('..');

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");
include_once("internal/starsystem.php");
include_once("internal/hint.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->AddStyle("default.css");

$sql=&OpenSQL($H);

ForceActivePlayer($sql, $H, "mobile/detail.php");


ForceFrozen($sql, $H);

get("id","integer");

include("mobile/part/mainmenu.php");


$info=starsystem_get_all($sql,$GET['id']);

$H->Insert("<b>" . $info['Name'] . "</b>");
$H->Br();
$H->Insert(" (" . $info['X'] . "/" . $info['Y'] . ")");
$H->Insert(" SID={$info['SID']}");
$H->Insert(" lvl={$info['Level']}");

if (!starsystem_in_bio_range($sql, $GET['id'], $_SESSION['PID']))
{
    $H->Insert(new Error("Selected starsystem is outside your sensory range"));
    $H->Draw();
    die;
}


$T=new Table();

$T->Insert(1,1,"#");
$T->Insert(2,1,"Pop");
$T->Insert(3,1,"Tx");
$T->Insert(4,1,"SB");
$T->Insert(5,1,"Owner");
$T->aRowClass[1]='t';

$detail=starsystem_detail($sql,$GET['id']);

$i=1;
foreach ($detail as $planet)
{
    ++$i;
    $T->Insert(1,$i,$planet['Ring']);
    $T->Insert(2,$i,$planet['Population']);
    $Tx=floor($planet['STx']/1000);
    $T->Insert(3,$i,"$Tx");
    $T->Insert(4,$i,$planet['Starbase']);
    
    if ($planet['Owner']==0)
    {
	if ($planet['Population']==0)
            $T->Insert(5,$i,"--free--");
        else
            $T->Insert(5,$i,"--unkn--");
    }
    else
    {
	if (!isset($planet['Nick']))
	{
            $T->Insert(5,$i,"--unkn--");
	}
	else
	{
	$S="";
	if (exists($planet['TAG']))
	    {
	    $T->Insert(5,$i,new Link("alliance.php?tag={$planet['TAG']}","[{$planet['TAG']}]"));
	    $S.=" ";
	    }
	$S.=$planet['Nick'];
        $T->Insert(5,$i,new Link("pinfo.php?id={$planet['Owner']}",$S));
	}
    }

    if ($planet['FleetOwner']!=0 and $planet['FleetOwner']!=$planet['Owner'])
	$T->aRowClass[$i]="siegedplanet";
}

$H->Insert($T);
$H->Draw();
CloseSQL($sql);
?>
