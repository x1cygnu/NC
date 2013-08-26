<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/validator.php");
include_once("internal/player.php");
include_once("internal/alliance.php");
include_once("internal/forumfunc.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");
$H->AddStyle("alliance.css");

$H->sTitle="Northern Cross - Group";

$sql=&OpenSQL($H);

$menuselected="Forum";
$forummenuselected="group";

ForcePlayer($sql, $H, "group.php");


include("part/mainmenu.php");
include("part/forummenu.php");


post("tag","string");
post("Create","string");
if ($POST['Create']=='Create')
{
    if ($tag!="")
	$H->Insert(new Error("You are already in a group"));
    else
    {
	$W=alliance_perm_create($sql, $POST['tag'], $_SESSION['AID']);
	if ($W!="")
	    $H->Insert(new Error($W));
	else
	    $H->Insert(new Info("Group succesfully created.<br/>You might want to fill in additional data"));
    }
}

$tag=account_get_perm_tag($sql, $_SESSION['AID']);

if ($tag=="") //no alliance
{
    $H->Insert(new Info("You are not assigned to any group yet"));
    $F=new Form("group.php",true);
    $T=new Table();
    $T->Insert(1,1,"Form new group");
    $T->SetClass(1,1,'title');
    $T->Insert(2,1,"Tag");
    $T->SetClass(2,1,'legend');
    $T->sClass='block';
    $T->iWidth=500;
    $T->Insert(3,1,new Input("text","tag","","text number"));
    $T->Insert(4,1,new Input("submit","Create","Create","smbutton"));
    $F->Insert($T);
    $H->Insert($F);
    $H->Draw();
    include("part/mainsubmenu.php");
    CloseSQL($sql);
    die;
}

$Founder=alliance_get_founder($sql,$tag);
get("act","string");
if ($GET['act']=='leave') {
  if ($_SESSION['AID']==$Founder)
    $H->Insert(new Info(new Link("group.php?act=reallyleave","Click here if you are sure you want to destroy whole group")));
  else
    $H->Insert(new Info(new Link("group.php?act=reallyleave","Click here if you are sure you want to leave this group")));
}
if ($GET['act']=='reallyleave') {
  if ($_SESSION['AID']==$Founder) {
    $ingametag=player_get_tag($sql, $_SESSION['PID']);
    if ($ingametag=='') {
      alliance_perm_disband($sql, $tag);
      $H->Insert(new Info("Group disbanded"));
      $H->Draw();
      include("part/mainsubmenu.php");
      CloseSQL($sql);
      die;
    } else
      $H->Insert(new Error("You cannot disband the group since you are already in an allliance"));
  } else {
    $ans=alliance_remove_perm_member($sql, $_SESSION['AID'], $_SESSION['AID']);
    if ($ans!='')
      $H->Insert(new Error($ans));
    else {
      $H->Insert(new Info('You left the group'));
      $H->Draw();
      include("part/mainsubmenu.php");
      CloseSQL($sql);
      die;
    }
  }
}

post("invite","string");
if (alliance_get_founder($sql,$tag)==$_SESSION['AID'] and $POST['invite']=='Add')
{
    post("nick","string");
    $ans=alliance_add_perm_member($sql,$tag,account_get_id($sql, $POST['nick']));
    if ($ans!="")
      $H->Insert(new Error($ans));
    else
      $H->Insert(new Info("Player added to the group"));
}

//$H->Insert("Founder==" . $Founder);
post("Update","string");
if ($Founder==$_SESSION['AID'] and $POST['Update']=='Update')
{
	post("name","string");
	post("desc","string");
	post("URL","string");
	post("founder","string");
	$aid=account_get_id($sql,$POST['founder']);
	alliance_update($sql,$tag,$POST['name'],$POST['desc'],$POST['URL']);
	alliance_set_founder($sql,$tag,$aid);
}

if ($_SESSION['AID']==$Founder) {
  get("k","integer");
  if (exists($GET['k'])) {
    $ans=alliance_remove_perm_member($sql, $GET['k'], $_SESSION['AID']);
    if ($ans!="")
      $H->Insert(new Error($ans));
    else
      $H->Insert(new Info("Player removed from the group"));
  }
}


$Alliance=alliance_get_permanent($sql,$tag);
$T=new Table();
$T->sClass='block';
$Name=htmlstring($Alliance['Name']);
$T->Insert(1,1,"[{$Alliance['TAG']}] $Name");
$T->aRowClass[1]='title';
$T->Insert(1,2,"Additional info"); $T->Insert(2,2,htmlstring($Alliance['Description']));
$T->SetClass(1,2,'legend');
$T->Insert(1,3,"URL"); $T->Insert(2,3,new Link($Alliance['URL'],htmlstring($Alliance['URL']),"_blank"));
$T->SetClass(1,3,'legend');
$T->SetStyle(2,3,'width : 200pt;');
$T->Insert(1,4,"Founder"); $name=account_get_name($sql,$Alliance['Founder']);
$T->SetClass(1,4,'legend');
if ($name!="")
    $T->Insert(2,4,$name);
else
    $T->Insert(2,4,"[[ deleted from the game ]]");

$T->Join(1,1,2,1);

if ($Alliance['Founder']==$_SESSION['AID'])
{
    $F=new Form("group.php",true);
    $T->Insert(3,1,new Input("text","name","","text"));
    $T->Insert(3,2,new Input("text","desc","","text"));
    $T->Insert(3,3,new Input("text","URL","","text"));
    $T->Insert(3,4,new Input("text","founder","","text"));
    $T->Insert(3,5,new Input("submit","Update","Update","smbutton"));
    $F->Insert($T);
    $H->Insert($F);
}
else
    $H->Insert($T);

$T=new Table();
$T->Insert(1,1,"Group Members");
$T->aRowClass[1]='title';
$T->aRowClass[2]='legend';

if ($_SESSION['AID']==$Alliance['Founder'])
  $T->Insert(4,2,"Kick");
else
  $T->SetCols(3);
$T->Join(1,1,$T->iCols,1);

$T->Insert(1,2,"Name");
$T->Insert(2,2,"Last login");
$T->Insert(3,2,"Ingame");


$Ms=forum_group_get_members($sql, $tag);
$i=2;
foreach ($Ms as $M)
{
    ++$i;
    $T->Insert(1,$i,new Link("member.php?pm={$M['AID']}","{$M['Nick']}"));
    $T->SetClass(1,$i,'sublegend');

    if ($M['PID']==0) {
      $IdleT=EncodeNow()-$M['LastLogin'];
      if ($IdleT<60) $Idle="{$IdleT}s";
      elseif ($IdleT<3600) {$IdleT=floor($IdleT/60); $Idle="{$IdleT}min";}
      elseif ($IdleT<3600*24) {$IdleT=floor($IdleT/3600); $Idle="{$IdleT}h";}
      else {$IdleT=floor($IdleT/(3600*24)); $Idle="{$IdleT}d";}
      $T->Insert(2,$i,$Idle);
    } else
      $T->Insert(2,$i,'hidden');

    $MayKick=true;
    if ($M['PID']==0)
      $T->Insert(3,$i,"no");
    else {
      if ($M['TAG']!="") {
	$T->Insert(3,$i,"tagged");
	$MayKick=false;
      }
      else
	$T->Insert(3,$i,"yes");
    }
    if ($_SESSION['AID']==$Alliance['Founder']) {
      if ($M['AID']==$Alliance['Founder'])
	$MayKick=false;
      if ($MayKick)
	$T->Insert(4,$i,new Link("group.php?k=" . $M['AID'],"kick"));
      else
	$T->Insert(4,$i,'-');
    }
}
$H->Insert($T);

if ($_SESSION['AID']==$Founder) {
$T=new Table();
$T->Insert(1,1,'Include new player into your group');
$T->Insert(2,1,new Input("text","nick","","text"));
$T->Insert(3,1,new Input("submit","invite","Add","smbutton"));
$T->SetClass(1,1,'title');
$T->sClass='block';

$F=new Form("group.php","true");
$F->Insert($T);
$H->Insert($F);
}

if ($_SESSION['TAG']=="") {
  if ($_SESSION['AID']==$Founder)
    $H->Insert(new Link("group.php?act=leave","Disband this group"));
  else
    $H->Insert(new Link("group.php?act=leave","Leave this group"));
}
include("part/mainsubmenu.php");
$H->Draw();
CloseSQL($sql);
?>
