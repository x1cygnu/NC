<?php

chdir('..');
include_once("internal/xml.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");

include_once("internal/starsystem.php");


session_start();

global $GET;
$GET=array();

$X = new XML("NC");

$sql=&OpenSQL();

if (!CheckActivePlayerXML($sql, $X)) {$X->Draw(); die;}

if (!CheckFrozenXML($sql, $X)) {$X->Draw(); die;}

get("id","integer");
$X->Insert(new XMLEntity("time",DecodeTime(EncodeNow()) . "(GMT+0)"));

$S=new XMLEntity("starsystem");
$info=starsystem_get_all($sql,$GET['id']);
$S->AddAttribute("name",$info['Name']);
$S->AddAttribute("x",$info['X']);
$S->AddAttribute("y",$info['Y']);
$S->AddAttribute("lvl",$info['Level']);
$S->AddAttribute("sid",$info['SID']);


if (!starsystem_in_bio_range($sql, $GET['id'], $_SESSION['PID']))
    $S->Insert(new XMLEntity("error","Starsystem outside your sensory range"));
else
{
    $detail = starsystem_detail($sql, $GET['id']);
    foreach ($detail as $P)
    {
	$planet=new XMLEntity("planet");
	$planet->AddAttribute("ring","" . $P['Ring']);
	$planet->AddAttribute("type","" . $P['TypeName']);	
	$planet->AddAttribute("name","" . $P['CustomName']);
	$planet->AddAttribute("slot","" . $P['CultureSlot']);
	$planet->Insert(new XMLEntity("pop","" . $P['Population']));
        $Tx=floor($P['STx']/1000);	
	$planet->Insert(new XMLEntity("tx","{$Tx}"));
	$planet->Insert(new XMLEntity("SB","" . $P['Starbase']));
	$planet->Insert(new XMLEntity("description",$P['Description']));
	$owner=new XMLEntity("player");
        if ($P['Owner']==0)
        {
	    if ($P['Population']==0) {
	        $planet->AddAttribute("conq","0");
		}
            else {
	        $planet->AddAttribute("conq","1");
	    }
	} else {
	    $planet->AddAttribute("conq","1");
	    if (isset($P['Nick'])) {
		if (exists($P['TAG']))
		    $owner->Insert(new XMLEntity("tag","" . $P['TAG']));
		$owner->Insert(new XMLEntity("name","" . $P['Nick']));
		$owner->Insert(new XMLEntity("pid","" . $P['Owner']));
		$planet->Insert($owner);
	    }
	}
	
    if ($P['FleetOwner']!=0 and $P['FleetOwner']!=$P['Owner'])
	$planet->Insert(new XMLEntity("siege"));
    $S->Insert($planet);
    }
}

$X->Insert($S);
$X->Draw();
CloseSQL($sql);
?>
