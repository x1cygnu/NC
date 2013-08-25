<?php

chdir('..');
include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");
include_once("internal/player.php");
include_once("internal/planet.php");
include_once("internal/alliance.php");
include_once("internal/fleet.php");

session_start();

get("id","integer");

$H = new HTML();
$H->AddStyle("default.css");
$H->AddStyle("news.css");


$sql=&OpenSQL($H);
ForceActivePlayer($sql, $H, "member.php");
ForceFrozen($sql, $H);

include("mobile/part/mainmenu.php");

$htag=player_get_tag($sql,$_SESSION['PID']);
$ptag=player_get_tag($sql,$GET['id']);
$admin=account_get_admin_from_pid($sql, $GET['id']);
if ($htag=="" or $ptag!=$htag or $admin==1)
{
    $H->Insert(new Info("Player information unavailable"));
    $H->Draw();
    die;
}

$Ps=planet_list($sql, $GET['id']);

$Sf=alliance_sieging_fleets($sql, $GET['id']);
$Tf=fleet_get_intransit($sql, $GET['id']);

$T=new Table();
$T->SetCols(3);
$PName=account_get_name_from_pid($sql,$GET['id']);
$T->Insert(1,1,"Planets of $PName");
$T->SetCols(3);
$T->Join(1,1,3,1);
$T->aRowClass[1]='t';
foreach($Ps as $n => $P)
{
    $i=$n+2;
    $T->Insert(1,$i,substr($P['Name'],0,12) . "{$P['Ring']}");
    $T->Insert(2,$i,"{$P['Population']}");
    if ($P['Starbase']>0)
        $T->Insert(3,$i,"{$P['Starbase']}SB ");
    if ($P['Int']>0)
        $T->Insert(3,$i,"{$P['Int']}I ");
    if ($P['Fr']>0)
        $T->Insert(3,$i,"{$P['Fr']}F ");
    if ($P['Bs']>0)
	$T->Insert(3,$i,"{$P['Bs']}B ");
    if ($P['CS']>0)
	$T->Insert(3,$i,"{$P['CS']}C ");
    if ($P['Tr']>0)
	$T->Insert(3,$i,"{$P['Tr']}T");    

    if ($P['FleetOwner']!=0 and $P['FleetOwner']!=$P['Owner'])
        $T->aRowClass[$i]='r';
    
}
++$i;
$T->Insert(1,$i,"Sieging fleets");
$T->aRowClass[$i]='t';
$T->Join(1,$i,3,1);

foreach ($Sf as $P)
{
    ++$i;
    $T->aRowClass[$i]='siegedplanet';
    $T->Insert(1,$i,substr($P['Name'],0,12) . "{$P['Ring']}");
    $T->Insert(2,$i,"{$P['Population']}");
    if ($P['Starbase']>0)
        $T->Insert(3,$i,"{$P['Starbase']}SB ");
    if ($P['Int']>0)
        $T->Insert(3,$i,"{$P['Int']}I ");
    if ($P['Fr']>0)
        $T->Insert(3,$i,"{$P['Fr']}F ");
    if ($P['Bs']>0)
	$T->Insert(3,$i,"{$P['Bs']}B ");
    if ($P['CS']>0)
	$T->Insert(3,$i,"{$P['CS']}C ");
    if ($P['Tr']>0)
	$T->Insert(3,$i,"{$P['Tr']}T");    
}

++$i;
$T->Insert(1,$i,"Fleets in transit");
$T->aRowClass[$i]='t';
$T->Join(1,$i,3,1);

foreach ($Tf as $P)
{
    ++$i;
    $T->Insert(1,$i,substr($P['Name'],0,12) . "{$P['Ring']}");
    $T->Insert(3,$i,DecodeTime($P['ETA']));    
    ++$i;
    if ($P['Int']>0)
        $T->Insert(3,$i,"{$P['Int']}I ");
    if ($P['Fr']>0)
        $T->Insert(3,$i,"{$P['Fr']}F ");
    if ($P['Bs']>0)
	$T->Insert(3,$i,"{$P['Bs']}B ");
    if ($P['CS']>0)
	$T->Insert(3,$i,"{$P['CS']}C ");
    if ($P['Tr']>0)
	$T->Insert(3,$i,"{$P['Tr']}T");    
}

$H->Insert($T);

$Inc=news_list_incommings($sql,$GET['id']);

$T=new Table();

$i=1;
foreach ($Inc as $entry)
{
    $T->Insert(1,$i,"<b>" . DecodeTime($entry['Time']) . "</b>");
    $T->SetClass(1,$i,"n2");
    $T->Insert(1,$i+1,$entry['Text']);
    $T->SetClass(1,$i+1,"nrm");
    $i+=2;
}
$H->Insert($T);


$H->Insert(new Link("pinfo.php?id={$GET['id']}","Public info"));

$H->Draw();
CloseSQL($sql);
?>
