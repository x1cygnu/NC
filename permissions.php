<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");

$H->sTitle="Northern Cross - Forum permissions";

ForceAdmin($H);

$H->AddStyle("forum.css");

$menuselected="Groups";
include_once("internal/group.php");
include_once("internal/permissions.php");
include("part/mainmenu.php");

$sql=&OpenSQL($H);

$T=new Table();
$T->aRowClass[1]='title';
$T->aRowClass[2]='legend';
$T->sClass='block';

get("gid","integer");
if (!exists($GET['gid']))
{
    $H->Insert(new Error("Group not specified"));
    $H->Draw();
    die;
}

get("s","integer");
get("p","string");
if (exists($GET['s']) and exists($GET['p']))
{
    if ($GET['p']=='r') set_permission($sql, $GET['gid'], $GET['s'], "Read");
    elseif ($GET['p']=='w') set_permission($sql, $GET['gid'], $GET['s'], "Write");
    elseif ($GET['p']=='n') set_permission($sql, $GET['gid'], $GET['s'], "New");
    elseif ($GET['p']=='m') set_permission($sql, $GET['gid'], $GET['s'], "Modify");
    elseif ($GET['p']=='d') set_permission($sql, $GET['gid'], $GET['s'], "Delete");
    elseif ($GET['p']=='l') set_permission($sql, $GET['gid'], $GET['s'], "Lock");
    else
	$H->Insert(new Error("Unknown permission parameter"));
}

$Ps=get_permission_for_group($sql, $GET['gid']);

$T=new Table();
$T->Insert(1,1,"Permissions for group " . get_group_name($sql, $GET['gid']));
$T->aRowClass[1]='title';

$T->Insert(1,2,"Section");
$T->Insert(2,2,"Read");
$T->Insert(3,2,"Write");
$T->Insert(4,2,"New");
$T->Insert(5,2,"Modify");
$T->Insert(6,2,"Delete");
$T->Insert(7,2,"Lock");
$T->aRowClass[2]='legend';
$T->Join(1,1,6,1);

$i=2;
foreach($Ps as $V => $P)
{
    ++$i;
    $T->Insert(1,$i,new Link("forum.php?s=$V","{$P['Name']}"));
    $T->SetClass(1,$i,'sublegend');
    $T->Insert(2,$i,new Link("permissions.php?gid={$GET['gid']}&s=$V&p=r",($P['Read']?"Yes":"No")));
    $T->SetClass(2,$i,($P['Read']?"permYes":"permNo"));
    $T->Insert(3,$i,new Link("permissions.php?gid={$GET['gid']}&s=$V&p=w",($P['Write']?"Yes":"No")));
    $T->SetClass(3,$i,($P['Write']?"permYes":"permNo"));
    $T->Insert(4,$i,new Link("permissions.php?gid={$GET['gid']}&s=$V&p=n",($P['New']?"Yes":"No")));
    $T->SetClass(4,$i,($P['New']?"permYes":"permNo"));
    $T->Insert(5,$i,new Link("permissions.php?gid={$GET['gid']}&s=$V&p=m",($P['Modify']?"Yes":"No")));
    $T->SetClass(5,$i,($P['Modify']?"permYes":"permNo"));
    $T->Insert(6,$i,new Link("permissions.php?gid={$GET['gid']}&s=$V&p=d",($P['Delete']?"Yes":"No")));
    $T->SetClass(6,$i,($P['Delete']?"permYes":"permNo"));
    $T->Insert(7,$i,new Link("permissions.php?gid={$GET['gid']}&s=$V&p=l",($P['Lock']?"Yes":"No")));
    $T->SetClass(7,$i,($P['Lock']?"permYes":"permNo"));
}

$H->Insert($T);

$H->Draw();
CloseSQL($sql);
?>
