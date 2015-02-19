<?php

print("We are temporarily unavailable. Be right back! A frozen time will be scheduled so you don't crash your fleets");
die;
include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/validator.php");
include_once("internal/armageddon.php");
include_once("internal/account.php");
include_once("internal/alliance.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");
$H->AddStyle("title.css");
$H->sTitle="Northern Cross - Free Online Somewhat-Massive Multiplayer Game";

$H->Insert("Free Online Somewhat-Massive Multiplayer Game");

//$H->Insert(new Error("There seems to be a problem with the server. Please be patient..."));

$T=new Table();
$T->sClass="nullspace";
$T->iWidth=322;
$T->Insert(1,1,new Image("./IMG/NCTitle.png","Northern Cross"));
$T->aRowClass[1]="title";
$T->Insert(1,2,new Image("./IMG/Cygnus.jpg","Northern Cross constellation"));
$T->Insert(1,3,"Login:");
$T->Insert(1,3,new Input("text","login",$_COOKIE['pname'],"text name",1));
$T->Insert(1,4,"Password:");
$T->Insert(1,4,new Input("password","password","","text name",2));

if ($_COOKIE['pname']=="")
    $H->onLoad("document.getElementsByName('login')[0].focus()");
else
    $H->onLoad("document.getElementsByName('password')[0].focus()");

$T->Insert(2,3,new Input("submit","subtmit","Enter","smbutton",3));

$sql=&OpenSQL();
if (isset($sql))
{
  $R=round_get_info($sql);
  $T->Insert(1,5,"Version: {$R['Version']}");
  $T->Insert(1,6,"Round: {$R['RoundName']}");
  $U=DecodeTime($R['Start']);
  $T->Insert(1,7,"Start: $U");
  if ($_SESSION['TimeZone']-floor($_SESSION['TimeZone'])>0)
    $T->Insert(1,7,sprintf("(GMT%+.1f)",$_SESSION['TimeZone']));
  else
    $T->Insert(1,7,sprintf("(GMT%+d)",$_SESSION['TimeZone']));
  $T->Join(1,5,2,1);
  $T->Join(1,6,2,1);
  $T->Join(1,7,2,1);

  if (CheckFrozen($sql))
    $H->Insert(new Error("Game is frozen at the moment"));
  //$H->Insert(new Info("New starcluster has been opened"));

  $U=round_get_frozen($sql);
  $Now=EncodeNow();
  if ($Now<$U['FrozenTo'])
  {
    $T->Insert(1,8,"Scheduled frozen time (GMT)");
    $T->Insert(1,8,new Br());
    $T->Insert(1,8,"From: " . DecodeTime($U['FrozenFrom'],0));
    $T->Insert(1,8,new Br());
    $T->Insert(1,8,"To: " . DecodeTime($U['FrozenTo'],0));
    $T->SetClass(1,8,"error");
    $T->Join(1,8,2,1);
  }


  $W=round_get_winner($sql);
  if ($W['SingleWon']>0)
  {
    $Nick=account_get_name($sql, $W['SingleWon']);
    $T->Insert(1,9,"<i>{$R['RoundName']}</i> SP winner: {$Nick}<br/>");
  }
  if ($W['AllianceWon']!="")
  {
    $Aln=alliance_get_name($sql, $W['AllianceWon']);
    $T->Insert(1,9,"<i>{$R['RoundName']}</i> alliance winner: [{$W['AllianceWon']}] $Aln<br/>");
  }
  CloseSQL($sql);
}
else
{
    $T->Insert(1,5,"Database locked out");
    $T->Join(1,5,2,1);
}
$T->Join(1,1,2,1);
$T->Join(1,2,2,1);
$T->Join(2,3,1,2);
$T->aRowClass[3]="block";
$T->aRowClass[4]="block";
$T->aRowClass[5]="legend";
$T->aRowClass[6]="legend";

$F=new Form("login.php");
$F->Insert($T);
$H->Insert($F);

/*
$H->Insert(new Info("This is RED vs BLUE round"));
$H->Insert("Make sure you recruit yourself to one of the teams");
$H->Br();
$H->Insert("check the forum for details...");
*/
include("part/mainsubmenu.php");
$H->Draw();
?>
