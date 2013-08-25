<?php
ob_start("ob_gzhandler");
include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");
include_once("internal/player.php");
include_once("internal/artefact.php");
include_once("internal/tech.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");

$H->sTitle="Northern Cross - Trade";


$sql=&OpenSQL($H);

ForceActivePlayer($sql, $H, "trade.php");
ForceFrozen($sql, $H);
ForceNoSitting($sql, $H, $_SESSION['PID']);

include("part/sitpid.php");

$H->AddStyle("trade.css");

$menuselected="Trade";
include("part/mainmenu.php");


$changeIsValid=PostControl(false);

if ($changeIsValid)
    get("sa","integer");
if ($GET['sa']==1)
{
    player_spend_all($sql, $MainPID);
}

if ($changeIsValid)
    get("use","integer");
if (exists($GET['use']))
    artefact_use($sql, $MainPID, $GET['use']);


$Art=$sql->query("SELECT * FROM NC_ArtefactList");
$Pl=player_get_all($sql, $MainPID);

$eco=player_get_science($sql,$MainPID,"Engineering");
$CapitalAvailable=player_capital_available($sql, $MainPID);
if ($CapitalAvailable)
{
    $VprCost=Vpr_points($eco);
    $IntCost=Int_points($eco);
    $FrCost=Fr_points($eco);
    $BsCost=Bs_points($eco);
    $DrnCost=Drn_points($eco);
}
else
{
$VprCost=Vpr_points($eco-6);
$IntCost=Int_points($eco-6);
$FrCost=Fr_points($eco-6);
$BsCost=Bs_points($eco-6);
$DrnCost=Drn_points($eco-6);
}

$U=artefact_get_own($sql, $MainPID);
//print_r($U);

$orderIsValid=PostControl(true);

$MaxRP=$Pl['PL']*($Pl['PL']+1)/2;
if ($orderIsValid)
    post("buypp","string");
if ($POST['buypp']=="Buy")
{
    post("amt","integer");
    if ($POST['amt']>$Pl['AT'])
	$H->Insert(new Error("Not enough money"));
    else
	{
        $Uhh=pp_buy($sql, $MainPID, $POST['amt']);
	if ($Uhh!="")
	    $H->Insert(new Error($Uhh));
	}
    $Pl=player_get_all($sql, $MainPID);
}

$Techs=tech_get_player_names($sql, $MainPID);

if ($orderIsValid)
    post("buyvpr","string");
    if ($POST['buyvpr']=="Buy" and tech_check_name($Techs,'Vpr'))
    {	post("amt","integer");
	if ($POST['amt']*$VprCost>$Pl['AT']) $H->Insert(new Error("Not enough money"));
	else {$Uhh=ship_buy($sql, $MainPID, $POST['amt'], "Vpr", $VprCost);
	if ($Uhh!="") $H->Insert(new Error($Uhh));}
        $Pl=player_get_all($sql, $MainPID);
    }


if ($orderIsValid)
    post("buyint","string");
    if ($POST['buyint']=="Buy")
    {	post("amt","integer");
	if ($POST['amt']*$IntCost>$Pl['AT']) $H->Insert(new Error("Not enough money"));
	else {$Uhh=ship_buy($sql, $MainPID, $POST['amt'], "Int", $IntCost);
	if ($Uhh!="") $H->Insert(new Error($Uhh));}
        $Pl=player_get_all($sql, $MainPID);
    }


if ($orderIsValid)
    post("buyfr","string");
    if ($POST['buyfr']=="Buy" and tech_check_name($Techs,'Fr'))
    {	post("amt","integer");
	if ($POST['amt']*$FrCost>$Pl['AT']) $H->Insert(new Error("Not enough money"));
	else {$Uhh=ship_buy($sql, $MainPID, $POST['amt'], "Fr", $FrCost);
	if ($Uhh!="") $H->Insert(new Error($Uhh));}
        $Pl=player_get_all($sql, $MainPID);
    }

if ($orderIsValid)
    post("buybs","string");
    if ($POST['buybs']=="Buy" and tech_check_name($Techs,'Bs'))
    {	post("amt","integer");
	if ($POST['amt']*$BsCost>$Pl['AT']) $H->Insert(new Error("Not enough money"));
	else {$Uhh=ship_buy($sql, $MainPID, $POST['amt'], "Bs", $BsCost);
	if ($Uhh!="") $H->Insert(new Error($Uhh));}
        $Pl=player_get_all($sql, $MainPID);
    }

if ($orderIsValid)
    post("buydrn","string");
    if ($POST['buydrn']=="Buy" and tech_check_name($Techs,'Drn'))
    {	post("amt","integer");
	if ($POST['amt']*$DrnCost>$Pl['AT']) $H->Insert(new Error("Not enough money"));
	else {$Uhh=ship_buy($sql, $MainPID, $POST['amt'], "Drn", $DrnCost);
	if ($Uhh!="") $H->Insert(new Error($Uhh));}
        $Pl=player_get_all($sql, $MainPID);
    }


//print_r($_POST);
//echo "<br/>";
post("art","integer");
//print_r($_POST);
//echo "<br/>";
post("amt","integer");
if ($orderIsValid)
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
	artefact_buy($sql, $MainPID, $POST['art'], $Art[$POST['art']]['Cost'], $POST['amt']);
        $Pl=player_get_all($sql, $MainPID);
	$U=artefact_get_own($sql, $MainPID);
    }

}


if ($orderIsValid)
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
	artefact_sell($sql, $MainPID, $POST['art'], $Art[$POST['art']]['Cost'], $POST['amt']);
        $Pl=player_get_all($sql, $MainPID);
	$U=artefact_get_own($sql, $MainPID);
    }
}
//echo "MaxRP=$MaxRP";

$T=new Table();
$T->SetCols(5);
$T->Insert(1,1,"Antimatter Tantamount");
$T->SetClass(1,1,'legend');
$pr=sprintf("%.2f",$Pl['AT']);
$T->Insert(2,1,$pr);
$T->Insert(1,2,"Items in warehouse");
$T->Join(1,2,5,1);
$T->aRowClass[2]='title';

$T->Insert(1,3,"Item");
$T->Insert(2,3,"Value");
$T->Insert(3,3,"Amount");
$T->Insert(4,3,"Order");
$T->Insert(5,3,"Usage");
$T->aRowClass[3]='legend';

$T->sClass='block';
$row=3;
/*
if ($Pl['RP']>0)
{
    ++$row;
    $T->Insert(1,$row,$Art[0]['Name']);
    $T->Insert(2,$row,"{$Art[$i]['Cost']}@");
    $F=new Form("trade.php",true);
    $F->Insert(new Input("text","amt","","text number"));
    $F->Insert("/{$Pl['RP']}");
    $F->Insert(new Input("submit","sell","Selllll","smbutton"));
    $F->Insert(new Input("hidden","art","0"));
    $T->Insert(3,$row,$F);
}
*/
foreach ($U as $Nam => $A)
{
    ++$row;
    $T->Insert(1,$row,$Art[$Nam]['Name']);
    $T->SetClass(1,$row,'sublegend');
    $T->Insert(2,$row,"{$Art[$Nam]['Cost']}@");
    $T->SetClass(2,$row,'pr');
    $T->Insert(3,$row,"{$A['Amount']}");
    if ($Nam==0)
	$T->Insert(3,$row,"/$MaxRP");
    $uk=abs($A['Sell']);
    if ($A['Sell']==0)
    {
	if ($_SESSION['PID']==$MainPID)
    	    $F=new Form("trade.php",true);
	else
	    $F=new Form("trade.php?sit=1",true);
	$F->Insert(new Input("text","amt","","text number"));
	$F->Insert("/{$A['Amount']}");
	$F->Insert(new Input("submit","sell","Sell","smbutton"));
	$F->Insert(new Input("hidden","art","$Nam"));
	$F->Insert(new Input("hidden","orderid",$_SESSION['PostCode']+1));
        $T->Insert(4,$row,$F);
    }
    elseif ($A['Sell']>0)
	$T->Insert(4,$row,"Pending (-{$uk})");
    else
	$T->Insert(4,$row,"Pending (+{$uk})");
    if ($A['Amount']>0 and $Nam>0)
    {
        if ($A['InUse']==1)
        $T->Insert(5,$row,"Active");
	else
        $T->Insert(5,$row,new Link("trade.php?use={$Nam}&orderid=".($_SESSION['PostCode']+1) . $sitAddition,"Use"));
    }
    if ($A['InUse']==1)
	$T->aRowClass[$row]='use';
}


if ($CapitalAvailable)
$BuyEndingString=" on capital";
else
$BuyEndingString=" on best unsieged";



++$row;
if ($CapitalAvailable)
    $T->Insert(1,$row,"Buy ships");
else
    $T->Insert(1,$row,"Buy ships (price is higher!)");
$T->Join(1,$row,5,1);
$T->aRowClass[$row]='title';
++$row;
$T->Insert(1,$row,"Type");
$T->Insert(2,$row,"Value");
$T->Insert(3,$row,"Max");
$T->Insert(4,$row,"Order");
$T->Insert(5,$row,"Usage");
$T->aRowClass[$row]='legend';

if (tech_check_name($Techs,'Vpr'))
{
++$row;
$T->Insert(1,$row,"Vipers");
$T->SetClass(1,$row,'sublegend');
$T->Insert(2,$row,"{$VprCost}@");
$T->SetClass(2,$row,'pr');
$max=floor($Pl['AT']/$VprCost);
$T->Insert(3,$row,"{$max}");
if ($MainPID==$_SESSION['PID'])
    $F=new Form("trade.php",true);
else
    $F=new Form("trade.php?sit=1",true);
$F->Insert(new Input("text","amt","{$max}","text number"));
$F->Insert(new Input("submit","buyvpr","Buy","smbutton"));
$F->Insert(new Input("hidden","orderid",$_SESSION['PostCode']+1));
$T->Insert(4,$row,$F);
$T->Insert(5,$row,'1Vpr' . $BuyEndingString);
}

++$row;
$T->Insert(1,$row,"Interceptors");
$T->SetClass(1,$row,'sublegend');
$T->Insert(2,$row,"{$IntCost}@");
$T->SetClass(2,$row,'pr');
$max=floor($Pl['AT']/$IntCost);
$T->Insert(3,$row,"{$max}");
if ($MainPID==$_SESSION['PID'])
    $F=new Form("trade.php",true);
else
    $F=new Form("trade.php?sit=1",true);
$F->Insert(new Input("text","amt","{$max}","text number"));
$F->Insert(new Input("submit","buyint","Buy","smbutton"));
$F->Insert(new Input("hidden","orderid",$_SESSION['PostCode']+1));
$T->Insert(4,$row,$F);
$T->Insert(5,$row,'1Int' . $BuyEndingString);


if (tech_check_name($Techs,'Fr'))
{
++$row;
$T->Insert(1,$row,"Frigates");
$T->SetClass(1,$row,'sublegend');
$T->Insert(2,$row,"{$FrCost}@");
$T->SetClass(2,$row,'pr');
$max=floor($Pl['AT']/$FrCost);
$T->Insert(3,$row,"{$max}");
if ($MainPID==$_SESSION['PID'])
    $F=new Form("trade.php",true);
else
    $F=new Form("trade.php?sit=1",true);
$F->Insert(new Input("text","amt","{$max}","text number"));
$F->Insert(new Input("submit","buyfr","Buy","smbutton"));
$F->Insert(new Input("hidden","orderid",$_SESSION['PostCode']+1));
$T->Insert(4,$row,$F);
$T->Insert(5,$row,'1Fr' . $BuyEndingString);
}

if (tech_check_name($Techs,'Bs'))
{
++$row;
$T->Insert(1,$row,"Battleships");
$T->SetClass(1,$row,'sublegend');
$T->Insert(2,$row,"{$BsCost}@");
$T->SetClass(2,$row,'pr');
$max=floor($Pl['AT']/$BsCost);
$T->Insert(3,$row,"{$max}");
if ($MainPID==$_SESSION['PID'])
    $F=new Form("trade.php",true);
else
    $F=new Form("trade.php?sit=1",true);
$F->Insert(new Input("text","amt","{$max}","text number"));
$F->Insert(new Input("submit","buybs","Buy","smbutton"));
$F->Insert(new Input("hidden","orderid",$_SESSION['PostCode']+1));
$T->Insert(4,$row,$F);
$T->Insert(5,$row,'1Bs' . $BuyEndingString);
}

if (tech_check_name($Techs,'Drn'))
{
++$row;
$T->Insert(1,$row,"Dreadnoughts");
$T->SetClass(1,$row,'sublegend');
$T->Insert(2,$row,"{$DrnCost}@");
$T->SetClass(2,$row,'pr');
$max=floor($Pl['AT']/$DrnCost);
$T->Insert(3,$row,"{$max}");
if ($MainPID==$_SESSION['PID'])
    $F=new Form("trade.php",true);
else
    $F=new Form("trade.php?sit=1",true);
$F->Insert(new Input("text","amt","{$max}","text number"));
$F->Insert(new Input("submit","buydrn","Buy","smbutton"));
$F->Insert(new Input("hidden","orderid",$_SESSION['PostCode']+1));
$T->Insert(4,$row,$F);
$T->Insert(5,$row,'1Drn' . $BuyEndingString);
}


++$row;
$T->Insert(1,$row,"Price list");
$T->Join(1,$row,5,1);
$T->aRowClass[$row]='title';
++$row;
$T->Insert(1,$row,"Item");
$T->Insert(2,$row,"Value");
$T->Insert(3,$row,"Max");
$T->Insert(4,$row,"Order");
$T->Insert(5,$row,"Usage");
$T->aRowClass[$row]='legend';

++$row;
$T->Insert(1,$row,"Production Points");
$T->SetClass(1,$row,'sublegend');
$T->Insert(2,$row,"1@");
$T->SetClass(2,$row,'pr');
$max=floor($Pl['AT']);
$T->Insert(3,$row,"{$max}");
if ($MainPID==$_SESSION['PID'])
    $F=new Form("trade.php",true);
else
    $F=new Form("trade.php?sit=1",true);
$F->Insert(new Input("text","amt","{$max}","text number"));
$F->Insert(new Input("submit","buypp","Buy","smbutton"));
$F->Insert(new Input("hidden","orderid",$_SESSION['PostCode']+1));
$T->Insert(4,$row,$F);
$T->Insert(5,$row,'1PP on capital');



$maxRowC=makeinteger($Pl['PL']);
TrimUp($maxRowC,50);


for ($i=0; $i<=$maxRowC; ++$i)
{
    ++$row;
    $T->Insert(1,$row,$Art[$i]['Name']);
    $T->SetClass(1,$row,'sublegend');
    $T->Insert(2,$row,"{$Art[$i]['Cost']}@");
    $T->SetClass(2,$row,'pr');
    $max=floor($Pl['AT']/$Art[$i]['Cost']);
    if ($i==0 and $max>$MaxRP)
	$max=$MaxRP;
    $T->Insert(3,$row,"{$max}");
//    $T->SetClass(3,$row,'pr');
    // Antimater Tantamount
    if ($Art[$i]['Cost']<=$Pl['AT'])
    {
	if ($MainPID==$_SESSION['PID'])
	    $F=new Form("trade.php",true);
	else
	    $F=new Form("trade.php?sit=1",true);
	$F->Insert(new Input("text","amt","1","text number"));
	$F->Insert(new Input("submit","buy","Buy","smbutton"));
	$F->Insert(new Input("hidden","art","$i"));
	$F->Insert(new Input("hidden","orderid",$_SESSION['PostCode']+1));
        $T->Insert(4,$row,$F);
    }
    if ($i==0)
	$T->Insert(5,$row,'10% of building');
    if ($Art[$i]['Growth']!=0) {$v=sprintf("Gr:%+d ",$Art[$i]['Growth']); $T->Insert(5,$row,$v);}
    if ($Art[$i]['Science']!=0) {$v=sprintf("Sc:%+d ",$Art[$i]['Science']); $T->Insert(5,$row,$v);}
    if ($Art[$i]['Culture']!=0) {$v=sprintf("Cul:%+d ",$Art[$i]['Culture']); $T->Insert(5,$row,$v);}
    if ($Art[$i]['Production']!=0) {$v=sprintf("Prd:%+d ",$Art[$i]['Production']); $T->Insert(5,$row,$v);}
    if ($Art[$i]['Speed']!=0) {$v=sprintf("Sp:%+d ",$Art[$i]['Speed']); $T->Insert(5,$row,$v);}
    if ($Art[$i]['Attack']!=0) {$v=sprintf("At:%+d ",$Art[$i]['Attack']); $T->Insert(5,$row,$v);}
    if ($Art[$i]['Defence']!=0) {$v=sprintf("Def:%+d ",$Art[$i]['Defence']); $T->Insert(5,$row,$v);}
	
}
$H->Insert($T);
include("part/mainsubmenu.php");
$H->Draw();
CloseSQL($sql);
?>
