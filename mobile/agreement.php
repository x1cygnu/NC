<?php
chdir('..');
include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");
include_once("internal/agreement.php");
include_once("internal/account.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->AddStyle("default.css");


$sql=&OpenSQL($H);

ForceActivePlayer($sql, $H, "mobile/agreement.php");


ForceFrozen($sql, $H);


include("mobile/part/mainmenu.php");

get("a","integer");
get("d","integer");
get("t","integer");
if (exists($GET['a']))
{
    $U=ta_accept($sql, $GET['a'], $_SESSION['PID'],$GET['t']);
    if ($U=="")
	$H->Insert(new Info("Trade Agreement signed"));
    else
	$H->Insert(new Error($U));
}
elseif (exists($GET['d']))
{
    $U=ta_decline($sql, $GET['d'], $_SESSION['PID'],$GET['t']);
    if ($U=="")
	$H->Insert(new Info("Trade Agreement rejected"));
    else
	$H->Insert(new Error($U));
}

post("TA","string");
post("TAName","string");
if ($POST['TA']=="Send offer")
{
    $pid=account_get_pid_from_nick($sql,$POST['TAName']);
    if ($pid==0)
	$H->Insert(new Error("Player not found"));
    else
	{
        $U=ta_invite($sql, $_SESSION['PID'], $pid);
	if ($U=="")
	    $H->Insert(new Info("Trade Agreement offer has been sent"));
	else
	    $H->Insert(new Error($U));
	}
}

post("SA","string");
post("SAName","string");
if ($POST['SA']=="Send offer")
{
    $pid=account_get_pid_from_nick($sql,$POST['SAName']);
    if ($pid==0)
	$H->Insert(new Error("Player not found"));
    elseif (!starsystem_in_bio_range($sql,player_get_home($sql,$pid),$_SESSION['PID']))
	$H->Insert(new Error("Player outside of your sensory range"));
    else
	{
        $U=ta_invite($sql, $_SESSION['PID'], $pid, 2);
	if ($U=="")
	    $H->Insert(new Info("Sensory Agreement offer has been sent"));
	else
	    $H->Insert(new Error($U));
	}
}

$A=agreements_get($sql, $_SESSION['PID']);
$T=new Table();
$T->SetCols(4);
$i=0;
foreach ($A as $Agr)
{
    ++$i;
    $Nam=account_get_name_from_pid($sql, $Agr['PID']);
    $T->Insert(1, $i, "$Nam");
    $T->Insert(2, $i, $AgreementType[$Agr['Type']]);
    if ($Agr['Status']==2)
    {
	if ($Agr['Type']==1)
	{
	    $V=player_get_given_ta($sql, $Agr['PID']);
	    $T->Insert(3, $i, sprintf("%.1f%%",$V));
	}
	elseif ($Agr['Type']==2)
	    $T->Insert(3, $i, player_get_science($sql,$Agr['PID'],"Sensory"));
    }
    else
    {
	if ($Agr['Initiator']==1)
	    $T->Insert(3,$i,"Pending");
	else
	{
	    $T->Insert(3,$i,new Link("agreement.php?a={$Agr['PID']}&t={$Agr['Type']}","Accept"));
	    $T->Insert(3,$i,new Link("agreement.php?d={$Agr['PID']}&t={$Agr['Type']}","Decline"));
	    $TC=ta_cost(ta_count($sql, $_SESSION['PID'],$Agr['Type']),$Agr['Type']);
	    $T->Insert(4,$i,"{$TC}@");	    
	}
    }	
}

++$i;
$TA=player_get_ta($sql, $_SESSION['PID']);
$T->Insert(1,$i,"TR: {$TA}%");
$T->Join(1,$i,4,1);
$T->aRowClass[$i]='t';

++$i;
$T->Insert(1,$i,new Input("text","TAName"));
$T->Insert(2,$i,"Trd");
$T->Insert(3,$i,new Input("submit","TA","Snd"));
$TC=ta_cost(ta_pending_count($sql, $_SESSION['PID']));
$T->Insert(4,$i,"{$TC}");

++$i;
$T->Insert(1,$i,new Input("text","SAName","","text"));
$T->Insert(2,$i,"Sen");
$T->Insert(3,$i,new Input("submit","SA","Snd"));
$TC=ta_cost(ta_pending_count($sql, $_SESSION['PID'],2),2);
$T->Insert(4,$i,"{$TC}");


$F=new Form("agreement.php",true);
$F->Insert($T);

$H->Insert($F);

$H->Draw();
CloseSQL($sql);
?>
