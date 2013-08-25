<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");
include_once("internal/group.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->AddStyle("default.css");

$H->sTitle="Northern Cross - Group management";

ForceAdmin($H);

$H->AddStyle("forum.css");

$menuselected="Group";
include("part/mainmenu.php");
$sql=&OpenSQL($H);

get("gid","integer");

$T=new Table();
$T->aRowClass[1]='title';
$T->aRowClass[2]='legend';
$T->sClass='block';
if (exists($GET['gid']))
{

    get("per","integer");
    if (exists($GET['per']))
	remove_member($sql, $GET['gid'], $GET['per']);

    get("Nick","string");
    if (exists($GET['Nick']))
	add_member($sql, $GET['gid'], $GET['Nick']);


    $Ss=get_members($sql, $GET['gid']);
    $name=get_group_name($sql, $GET['gid']);
    $T->Insert(1,1,"Members of group " . $name);
    $T->Insert(1,2,"AID");
    $T->Insert(2,2,"Nick");
    $T->Insert(3,2,"Remove");
    $i=2;
    foreach ($Ss as $S)
    {
	++$i;
	$T->Insert(1,$i,"{$S['AID']}");
	$T->Insert(2,$i,"{$S['Nick']}");
	$T->Insert(3,$i,new Link("group.php?gid={$GET['gid']}&per={$S['AID']}","X"));
    }
    
    $F=new Form("group.php",false);
    ++$i;
    $T->aRowClass[$i]='sublegend';
    $T->Insert(2,$i,new Input("text","Nick","","text"));
    $T->Insert(3,$i,new Input("submit","","Add","smbutton"));
    
    $T->Join(1,1,3,1);
    
    $F->Insert(new Input("hidden","gid",$GET['gid']));
    $F->Insert($T);
    $H->Insert($F);
}
else
{
    get("ger","integer");
    if (exists($GET['ger']))
    {
	get("confirm","string");
	if (exists($GET['confirm']) && $GET['confirm']=="Confirm")
		remove_group($sql, $GET['ger']);
	else
	    {
		$F=new Form("group.php",false);
		$F->Insert(new Input("hidden","ger",$GET['ger']));
		$F->Insert("Really disconnect all members and remove group number {$GET['ger']}?");
		$F->Insert(new Br());
		$F->Insert(new Input("submit","confirm","Confirm","smbutton"));
		$H->Insert($F);
	    }
    }

    get("Name","string");
    if (exists($GET['Name']))
    {
	get("Desc","string");
	add_group($sql, $GET['Name'], $GET['Desc']);
	}


    $Ss=get_groups($sql);
    $T->Insert(1,1,"Groups of Northern Cross Forum");
    $T->Insert(1,2,"GID");
    $T->Insert(2,2,"Name");
    $T->Insert(3,2,"Members");
    $T->Insert(4,2,"Perm.");
    $T->Insert(5,2,"Remove");
    $i=2;
    foreach ($Ss as $S)
    {
	++$i;
	$T->Insert(1,$i,"{$S['GID']}");
	if ($S['GID']==$GET['ger'])
	    $T->aRowClass[$i]='warningmark';
	$T->Insert(2,$i,new Link("group.php?gid={$S['GID']}","{$S['Name']}"));
	$T->Insert(3,$i,"{$S['Size']}");
	$T->Insert(4,$i,new Link("permissions.php?gid={$S['GID']}","Edit"));
	$T->Insert(5,$i,new Link("group.php?ger={$S['GID']}","X"));
    }
    
    $F=new Form("group.php",false);
    ++$i;
    $T->aRowClass[$i]='sublegend';
    $T->aRowClass[$i+1]='sublegend';
    $T->Insert(1,$i,"Name");
    $T->Insert(1,$i+1,"Desc");
    $T->Insert(2,$i,new Input("text","Name","","text"));
    $T->Insert(2,$i+1,new Input("text","Desc","","text"));
    $T->Insert(3,$i,new Input("submit","","Add","smbutton"));
    $T->Join(3,$i,3,2);

    $T->Join(1,1,5,1);

    $F->Insert($T);
    $H->Insert($F);
}

$H->Draw();
CloseSQL($sql);
?>
