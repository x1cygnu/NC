<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");
include_once("internal/log.php");
include_once("internal/account.php");
include_once("internal/multi.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");

$H->sTitle="Northern Cross - Login logs";

$sql=&OpenSQL($H);

if (!$_SESSION['IsAdmin'])
{
    $H->Insert(new Error("Must be admin in order to see this page"));
    $H->Draw();
    die;
}

$menuselected="Multi";
include("part/mainmenu.php");
include("part/multi.php");


function AddParamAND(&$Q, $V)
{
    if ($Q!="")
	$Q.=" AND $V";
    else
	$Q.="$V";
}

function AddParamOR(&$Q, $V)
{
    if ($Q!="")
	$Q.=" OR $V";
    else
	$Q.="$V";
}

$Q="";
post("tfrom","string"); if (exists($POST['tfrom']))
    {
	$STL=EncodeTime($POST['tfrom']);
	AddParamAND($Q,"Time>$STL");
    }
else
    {
      $POST['tfrom']=0;
/*	if (!isset($GET['ip']))
	    $POST['tfrom']=FullDecodeTime(EncodeNow()-24*3600);
	else
	    $POST['tfrom']=0;*/
    }
post("tto","string"); if (exists($POST['tto']))
    {
	$ETL=EncodeTime($POST['tto']);
	AddParamAND($Q,"Time<$ETL");
    }
else
$POST['tto']=0;
//    $POST['tto']=FullDecodeTime(EncodeNow());

$P="";
for ($i=1; $i<=4; ++$i) {
  post("p".$i,"string");
  if (exists($POST['p'.$i])) {
    $V=account_get_sit_from_nick($sql, $POST['p'.$i]);
    if ($V['AID']>0) {
      AddParamOR($P,"NewAID=" . makeinteger($V['AID']));
      AddParamOR($P,"PrevAID=" . makeinteger($V['AID']));
      AddParamOR($P,"SecretAID=" . makeinteger($V['AID']));
    }
  }
}
if ($P!="")
    AddParamAND($Q,'(' . $P . ')');

$IPs="";
for ($i=1; $i<=4; ++$i) {
post("ip".$i ,"string");
get("ip".$i ,"string");
if (!exists($POST['ip'.$i]) && exists($GET['ip'.$i]))
	$POST['ip'.$i]=$GET['ip'.$i];
if (exists($POST['ip'.$i])) {
  $V=ip_separate($POST['ip'.$i]);
  AddParamOR($IPs,"( IP0={$V[0]} AND IP1={$V[1]} " .
      " AND IP2={$V[2]} AND IP3={$V[3]})");
  AddParamOR($IPs,"( FIP0={$V[0]} AND FIP1={$V[1]} " .
      " AND FIP2={$V[2]} AND FIP3={$V[3]})");
  }
}
if ($IPs!="")
    AddParamAND($Q,'(' . $IPs . ')');

$CTs="";
for ($i=1; $i<=4; ++$i) {
post("ct".$i ,"integer"); if (exists($POST['ct'.$i])) {
  AddParamOR($CTs,'CookieID=' . $POST['ct'.$i]);
  }
}
if ($CTs!="")
    AddParamAND($Q,'(' . $CTs . ')');

$T=new Table();
$T->sClass='block';
$T->Insert(1,1,"Northern Cross Logins");
$T->aRowClass[1]='title';
$T->aRowClass[2]='legend';

$T->Insert(1,2,"Time period<br/>(i.e. 10 Nov 2006 10:00:00)");
$T->Insert(1,3,"From");
$T->Insert(1,4,"To");
$T->Insert(2,3,new Input("text","tfrom",$POST['tfrom'],"text"));
$T->Insert(2,4,new Input("text","tto",$POST['tto'],"text"));
$T->Join(1,2,2,1);

$T->Insert(3,2,"Players");
$T->Insert(3,3,new Input("text","p1",$POST['p1'],"text"));
$T->Insert(3,4,new Input("text","p2",$POST['p2'],"text"));
$T->Insert(3,5,new Input("text","p3",$POST['p3'],"text"));
$T->Insert(3,6,new Input("text","p4",$POST['p4'],"text"));

$T->Insert(4,2,"IPs");
$T->Insert(4,3,new Input("text","ip1",$POST['ip1'],"text"));
$T->Insert(4,4,new Input("text","ip2",$POST['ip2'],"text"));
$T->Insert(4,5,new Input("text","ip3",$POST['ip3'],"text"));
$T->Insert(4,6,new Input("text","ip4",$POST['ip4'],"text"));

$T->Insert(5,2,"CTs");
$T->Insert(5,3,new Input("text","ct1",$POST['ct1'],"text number"));
$T->Insert(5,4,new Input("text","ct2",$POST['ct2'],"text number"));
$T->Insert(5,5,new Input("text","ct3",$POST['ct3'],"text number"));
$T->Insert(5,6,new Input("text","ct4",$POST['ct4'],"text number"));

$T->Join(1,1,3,1);

$F = new Form("logins.php",true);
$F->Insert($T);
$F->Insert(new Input("submit","","Send","smbutton"));
$H->Insert($F);

if ($Q!='')
    $Q=" WHERE " . $Q;

$Logs=$sql->query("SELECT * FROM NC_LogLogin $Q ORDER BY Time DESC LIMIT 0, 100");

$T=new Table();
$T->sClass='block';
$T->aRowClass[1]='legend';
$T->aRowClass[2]='legend';
$T->Insert(1,1,"Time");
$T->Insert(2,1,"Account");
$T->Insert(2,2,"SAID");
$T->Insert(3,2,"pAID");
$T->Insert(4,2,"AID");
$T->Join(2,1,3,1);
$T->Join(1,1,1,2);
$T->Insert(5,1,"In");
$T->Insert(6,1,"OK");
$T->Insert(7,1,"IP");
$T->Insert(8,1,"Forward");
$T->Insert(9,1,"Cookie thread");
$T->Insert(9,2,"New");
$T->Insert(10,2,"ID");
$T->Join(9,1,2,1);
$T->Join(5,1,1,2);
$T->Join(6,1,1,2);
$T->Join(7,1,1,2);
$T->Join(8,1,1,2);

$row=2;
$ToLogNames=array();
foreach($Logs as $Log)
{
    ++$row;
    $T->Insert(1,$row,"" . DecodeTime($Log['Time']));
    $T->Insert(2,$row,"" . account_get_name($sql,$Log['SecretAID']));
    $T->Insert(3,$row,"" . account_get_name($sql,$Log['PrevAID']));
    $T->Insert(4,$row,"" . account_get_name($sql,$Log['NewAID']));
    $T->Insert(5,$row,($Log['In']==1?"In":"Out"));
    $T->Insert(6,$row,($Log['Succesfull']==1?"OK":"Fail"));
    $T->Insert(7,$row,sprintf("%3d.%3d.%3d.%3d",
	  $Log['IP0'],$Log['IP1'],$Log['IP2'],$Log['IP3']));
    $T->Insert(8,$row,sprintf("%3d.%3d.%3d.%3d",
	  $Log['FIP0'],$Log['FIP1'],$Log['FIP2'],$Log['FIP3']));
    $T->Insert(9,$row,($Log['CookieNew']==1?"+":"-"));
    $T->Insert(10,$row,"" . $Log['CookieID']);
}
$H->Insert($T);

if ($IPisSet)
{
    $F=new Form("log.php",true);
    $FC=0;
    foreach ($ToLogNames as $Name => $Dumb)
    {
	++$FC;
	$F->Insert(new Input("hidden",'p'.$FC,$Name));
    }
    $F->Insert(new Input("submit","","Show full log","smbutton"));
    $H->Insert($F);
}
$H->Draw();
CloseSQL($sql);
?>
