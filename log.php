<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/validator.php");
include_once("internal/log.php");
include_once("internal/account.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->AddStyle("default.css");

$sql=&OpenSQL($H);

$H->sTitle="Northern Cross - Main logs";

ForcePlayer($sql, $H, "log.php");

$menuselected="Log";

if (!$_SESSION['IsAdmin'])
{
    $H->Insert(new Error("Must be admin in order to see this page"));
    $H->Draw();
    die;
}

include("part/mainmenu.php");

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

$sql->debug=true;


$Q="";
post("send","string");
if ($POST['send']!="Send")
{
    $POST['tfrom']=FullDecodeTime(EncodeNow()-24*3600);
    $POST['tto']=FullDecodeTime(EncodeNow());
}
post("tfrom","string"); if (exists($POST['tfrom']))
    {
	$STL=EncodeTime($POST['tfrom']);
	AddParamAND($Q,"Time>$STL");
    }
post("tto","string"); if (exists($POST['tto']))
    {
	$ETL=EncodeTime($POST['tto']);
	AddParamAND($Q,"Time<$ETL");
    }

$P="";
post("p1","string"); if (exists($POST['p1'])) AddParamOR($P,"Arg4=\"{$POST['p1']}\" OR AID=" . account_get_id($sql,$POST['p1']));
post("p2","string"); if (exists($POST['p2'])) AddParamOR($P,"Arg4=\"{$POST['p2']}\" OR AID=" . account_get_id($sql,$POST['p2']));
post("p3","string"); if (exists($POST['p3'])) AddParamOR($P,"Arg4=\"{$POST['p3']}\" OR AID=" . account_get_id($sql,$POST['p3']));
post("p4","string"); if (exists($POST['p4'])) AddParamOR($P,"Arg4=\"{$POST['p4']}\" OR AID=" . account_get_id($sql,$POST['p4']));
if ($P!="")
AddParamAND($Q,"($P)");

$P="";
post("comm1","string"); if (exists($POST['comm1']) and $POST['comm1']>-1) AddParamOR($P,"Command=" . makeinteger($POST['comm1']));
post("comm2","string"); if (exists($POST['comm2']) and $POST['comm2']>-1) AddParamOR($P,"Command=" . makeinteger($POST['comm2']));
post("comm3","string"); if (exists($POST['comm3']) and $POST['comm3']>-1) AddParamOR($P,"Command=" . makeinteger($POST['comm3']));
post("comm4","string"); if (exists($POST['comm4']) and $POST['comm4']>-1) AddParamOR($P,"Command=" . makeinteger($POST['comm4']));
if ($P!="")
AddParamAND($Q,"($P)");

$P="";
post("res1","string"); if (exists($POST['res1']) and $POST['res1']>-1) AddParamOR($P,"Result=" . makeinteger($POST['res1']));
post("res2","string"); if (exists($POST['res2']) and $POST['res2']>-1) AddParamOR($P,"Result=" . makeinteger($POST['res2']));
post("res3","string"); if (exists($POST['res3']) and $POST['res3']>-1) AddParamOR($P,"Result=" . makeinteger($POST['res3']));
post("res4","string"); if (exists($POST['res4']) and $POST['res4']>-1) AddParamOR($P,"Result=" . makeinteger($POST['res4']));
if ($P!="")
AddParamAND($Q,"($P)");

echo $Q;

$T=new Table();
$T->sClass='block';
$T->Insert(1,1,"Northern Cross Logs");
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

$SC=new Select;
$SC->AddOption(-1,"");
$SC->sDefault=-1;
foreach ($Log_Commands as $key => $value)
    $SC->AddOption($key,$value);
$SR=new Select;
$SR->AddOption(-1,"");
$SR->sDefault=-1;
foreach ($Log_Results as $key => $value)
    $SR->AddOption($key,$value);
    
$T->Insert(4,2,"Command");
$T->Insert(5,2,"Result");
for ($i=1; $i<=4; ++$i)
{
    $SC->sName="comm" . $i;
    $SR->sName="res" . $i;
    if ($POST['send']=="Send")
    {
    $SC->sDefault=$POST['comm' . $i];
    $SR->sDefault=$POST['res' . $i];
    }
    else
    {
    $SC->sDefault=$POST['comm' . $i]=-1;
    $SR->sDefault=$POST['res' . $i]=-1;
    }
    $T->Insert(4,$i+2,$SC);
    $T->Insert(5,$i+2,$SR);
}

$T->Join(1,1,5,1);

$F = new Form("log.php",true);
$F->Insert($T);
$F->Insert(new Input("submit","send","Send","smbutton"));
$H->Insert($F);

if ($Q!='')
    $Q=" WHERE " . $Q;

$Logs=$sql->query("SELECT * FROM NC_Log $Q ORDER BY Time DESC LIMIT 0, 512");

$T=new Table();
$T->sClass='block';
$T->aRowClass[1]='legend';
$T->Insert(1,1,"Time");
$T->Insert(2,1,"Player");
$T->Insert(3,1,"Command");
$T->Insert(4,1,"Result");
$T->Insert(5,1,"Arg1");
$T->Insert(6,1,"Arg2");
$T->Insert(7,1,"Arg3");
$T->Insert(8,1,"Arg4");
$T->Insert(9,1,"Arg5");
$T->Insert(10,1,"Arg6");

$row=1;
foreach($Logs as $Log)
{
    ++$row;
    $T->Insert(1,$row,"" . DecodeTIme($Log['Time']));
//    $T->Insert(2,$row,"" . $Log['AID']);
    $T->Insert(2,$row,"" . account_get_name($sql,$Log['AID']));
    $T->Insert(3,$row,"" . $Log_Commands[$Log['Command']]);
    $T->Insert(4,$row,"" . $Log_Results[$Log['Result']]);
    $T->Insert(5,$row,"" . $Log['Arg1']);
    $T->Insert(6,$row,"" . $Log['Arg2']);
    $T->Insert(7,$row,"" . $Log['Arg3']);
    $T->Insert(8,$row,"" . $Log['Arg4']);
    $T->Insert(9,$row,"" . $Log['Arg5']);
    $T->Insert(10,$row,"" . $Log['Arg6']);
}
$H->Insert($T);
$H->Draw();
CloseSQL($sql);
?>
