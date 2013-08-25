<?php
chdir('..');

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");
include_once("internal/player.php");
include_once("internal/artefact.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->AddStyle("default.css");



$sql=&OpenSQL($H);

ForceActivePlayer($sql, $H, "mobile/trade.php");

ForceFrozen($sql, $H);


$menuselected="Trade";
include("mobile/part/mainmenu.php");




get("sa","integer");
if ($GET['sa']==1)
{
    player_spend_all($sql, $_SESSION['PID']);
}

get("use","integer");
if (exists($GET['use']))
    artefact_use($sql, $_SESSION['PID'], $GET['use']);


$Art=$sql->query("SELECT * FROM NC_ArtefactList");
$Pl=player_get_all($sql, $_SESSION['PID']);

$eco=player_get_science($sql,$_SESSION['PID'],"Engineering");
$CapitalAvailable=player_capital_available($sql, $_SESSION['PID']);
if ($CapitalAvailable)
{
    $IntCost=Int_points($eco);
    $FrCost=Fr_points($eco);
    $BsCost=Bs_points($eco);
}
else
{
$IntCost=Int_points($eco-6);
$FrCost=Fr_points($eco-6);
$BsCost=Bs_points($eco-6);
}

$U=artefact_get_own($sql, $_SESSION['PID']);
//print_r($U);

$MaxRP=$Pl['PL']*($Pl['PL']+1)/2;
post("buypp","string");
if ($POST['buypp']=="Buy")
{
    post("amt","integer");
    if ($POST['amt']>$Pl['AT'])
	$H->Insert(new Error("Not enough money"));
    else
	{
        $Uhh=pp_buy($sql, $_SESSION['PID'], $POST['amt']);
	if ($Uhh!="")
	    $H->Insert(new Error($Uhh));
	}
    $Pl=player_get_all($sql, $_SESSION['PID']);
}

post("buyint","string");
    if ($POST['buyint']=="Buy")
    {	post("amt","integer");
	if ($POST['amt']*$IntCost>$Pl['AT']) $H->Insert(new Error("Not enough money"));
	else {$Uhh=ship_buy($sql, $_SESSION['PID'], $POST['amt'], "Int", $IntCost);
	if ($Uhh!="") $H->Insert(new Error($Uhh));}
        $Pl=player_get_all($sql, $_SESSION['PID']);
    }


post("buyfr","string");
    if ($POST['buyfr']=="Buy")
    {	post("amt","integer");
	if ($POST['amt']*$FrCost>$Pl['AT']) $H->Insert(new Error("Not enough money"));
	else {$Uhh=ship_buy($sql, $_SESSION['PID'], $POST['amt'], "Fr", $FrCost);
	if ($Uhh!="") $H->Insert(new Error($Uhh));}
        $Pl=player_get_all($sql, $_SESSION['PID']);}

post("buydrn","string");
    if ($POST['buydrn']=="Buy")
    {	post("amt","integer");
	if ($POST['amt']*$BsCost>$Pl['AT']) $H->Insert(new Error("Not enough money"));
	else {$Uhh=ship_buy($sql, $_SESSION['PID'], $POST['amt'], "Bs", $BsCost);
	if ($Uhh!="") $H->Insert(new Error($Uhh));}
        $Pl=player_get_all($sql, $_SESSION['PID']);}


//print_r($_POST);
//echo "<br>";
post("art","integer");
//print_r($_POST);
//echo "<br>";
post("amt","integer");
post("buy","string");
if (exists($POST['buy']))
{
    if ($U[$POST['art']]['Sell']!=0)
	$H->Insert(new Error("Selected artefact is already in order"));
    elseif ($POST['amt']*$Art[$POST['art']]['Cost']>$Pl['AT'])
	$H->Insert(new Error("You need more money"));
    elseif ($POST['amt']<0)
	$H->Insert(new Error("You cannot buy negative amount of items"));
    elseif ($POST['art']==0 and $POST['amt']+$U[0]['Amount']>$MaxRP)
	$H->Insert(new Error("You cannot have more than $MaxRP Rough Products at once"));
    else
    {
	artefact_buy($sql, $_SESSION['PID'], $POST['art'], $Art[$POST['art']]['Cost'], $POST['amt']);
        $Pl=player_get_all($sql, $_SESSION['PID']);
	$U=artefact_get_own($sql, $_SESSION['PID']);
    }

}


post("sell","string");
if (exists($POST['sell']))
{
    if ($U[$POST['art']]['Sell']!=0)
	$H->Insert(new Error("Selected artefact is already in order"));
    elseif ($POST['amt']>$U[$POST['art']]['Amount'])
	$H->Insert(new Error("You cannot sell more items than you have ({$POST['amt']}/{$U[$POST['art']]['Amount']})"));
    elseif ($POST['amt']<0)
	$H->Insert(new Error("You cannot sell negative amount of items"));
    else
    {
	artefact_sell($sql, $_SESSION['PID'], $POST['art'], $Art[$POST['art']]['Cost'], $POST['amt']);
        $Pl=player_get_all($sql, $_SESSION['PID']);
	$U=artefact_get_own($sql, $_SESSION['PID']);
    }
}

$T=new Table();
$T->SetCols(4);
$T->Insert(1,1,"@");
$pr=sprintf("%.2f",$Pl['AT']);
$T->Insert(2,1,$pr);
$T->Join(2,1,3,1);

$row=1;
foreach ($U as $Nam => $A)
{
    ++$row;
    $T->Insert(1,$row,$Art[$Nam]['Short']);

    $T->Insert(3,$row,"{$A['Amount']}");
    if ($Nam==0)
	$T->Insert(3,$row,"/$MaxRP");
    $uk=abs($A['Sell']);
    if ($A['Sell']==0)
    {
        $F=new Form("trade.php",true);
	$F->Insert(new Input("text","amt","","sh"));
	$F->Insert("/{$A['Amount']}");
	$F->Insert(new Input("submit","sell","Sell","sh"));
	$F->Insert(new Input("hidden","art","$Nam"));
        $T->Insert(4,$row,$F);
    }
    elseif ($A['Sell']>0)
	$T->Insert(4,$row,"Pending (-{$uk})");
    else
	$T->Insert(4,$row,"Pending (+{$uk})");
    if ($A['Amount']>0 and $Nam>0)
    if ($A['InUse']==1)
        $T->Insert(2,$row,"In use");
    else
        $T->Insert(2,$row,new Link("trade.php?use={$Nam}","Use"));
}


++$row;
if ($CapitalAvailable)
    $T->Insert(1,$row,"Buy ships");
else
    $T->Insert(1,$row,"Buy ships (price is higher!)");
$T->Join(1,$row,4,1);
$T->aRowClass[$row]='t';

++$row;
$T->Insert(1,$row,"Int");
$T->Insert(2,$row,"{$IntCost}@");
$max=floor($Pl['AT']/$IntCost);
$T->Insert(3,$row,"{$max}");
$F=new Form("trade.php",true);
$F->Insert(new Input("text","amt","{$max}","sh"));
$F->Insert(new Input("submit","buyint","Buy","sh"));
$T->Insert(4,$row,$F);

++$row;
$T->Insert(1,$row,"Fr");
$T->Insert(2,$row,"{$FrCost}@");
$max=floor($Pl['AT']/$FrCost);
$T->Insert(3,$row,"{$max}");
$F=new Form("trade.php",true);
$F->Insert(new Input("text","amt","{$max}","sh"));
$F->Insert(new Input("submit","buyfr","Buy","sh"));
$T->Insert(4,$row,$F);

++$row;
$T->Insert(1,$row,"Bs");
$T->Insert(2,$row,"{$BsCost}@");
$max=floor($Pl['AT']/$BsCost);
$T->Insert(3,$row,"{$max}");
$F=new Form("trade.php",true);
$F->Insert(new Input("text","amt","{$max}","sh"));
$F->Insert(new Input("submit","buydrn","Buy","sh"));
$T->Insert(4,$row,$F);


++$row;
$T->Insert(1,$row,"Price list");
$T->Join(1,$row,4,1);
$T->aRowClass[$row]='t';

++$row;
$T->Insert(1,$row,"PP");
$T->Insert(2,$row,"1@");
$max=floor($Pl['AT']);
$T->Insert(3,$row,"{$max}");
$F=new Form("trade.php",true);
$F->Insert(new Input("text","amt","{$max}","sh"));
$F->Insert(new Input("submit","buypp","Buy","sh"));
$T->Insert(4,$row,$F);





for ($i=0; $i<=$Pl['PL']; ++$i)
{
    ++$row;
    $T->Insert(1,$row,$Art[$i]['Short']);
    $T->Insert(2,$row,"{$Art[$i]['Cost']}@");
    $max=floor($Pl['AT']/$Art[$i]['Cost']);
    if ($i==0 and $max>$MaxRP)
	$max=$MaxRP;
    $T->Insert(3,$row,"{$max}");
    if ($Art[$i]['Cost']<=$Pl['AT'])
    {
        $F=new Form("trade.php",true);
	$F->Insert(new Input("text","amt","1","sh"));
	$F->Insert(new Input("submit","buy","Buy","sh"));
	$F->Insert(new Input("hidden","art","$i"));
        $T->Insert(4,$row,$F);
    }
}
$H->Insert($T);
$H->Draw();
CloseSQL($sql);
?>
