<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");
include_once("internal/planet.php");
include_once("internal/progress.php");
include_once("internal/player.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");

$H->sTitle="Northern Cross - Planets";

$sql=&OpenSQL($H);

ForceActivePlayer($sql, $H, "planets.php");
ForceFrozen($sql, $H);
include("part/sitpid.php");

$H->AddStyle("detail.css");
$H->AddStyle("progress.css");
$H->AddStyle("planets.css");

$menuselected="Planets";
include("part/mainmenu.php");

$L=planet_list($sql, $MainPID);

$T=new Table();
$T->sClass="block";
$T->SetCols(15);
$T->Insert(1,1,"System");
$T->Insert(2,1,"Type");
$T->Insert(3,1,"Name");
$T->Insert(4,1,"Population");
$T->Join(4,1,3,1);
$T->Insert(7,1,"Tx");
$T->Insert(8,1,"Frm");
$T->Insert(9,1,"Fct");
$T->Insert(10,1,"Cyb");
$T->Insert(11,1,"Lab");
$T->Insert(12,1,"Ref");
$T->Insert(13,1,"SB");

$T->Insert(14,1,"PP");
if ($MainPID==$_SESSION['PID'])
    $T->Insert(14,1,new Link("spendall.php"," [spend all] "));
else
    $T->Insert(14,1,new Link("spendall.php?sit=1"," [spend all] "));
$T->Join(14,1,2,1);
$T->aRowClass[1]='title';

$T->sDefaultRowClass='takenplanet';

$i=1;
$row=0;
$Poptotal=0;
$Frmtotal=0;
$Fcttotal=0;
$Cybtotal=0;
$Labtotal=0;
$Reftotal=0;
$SBtotal=0;
$PPtotal=0;
$Prodtotal=0;
$TakenPlanettotal=0;
$CulPlanettotal=0;
foreach ($L as $planet)
{
    $TakenPlanettotal++;
    if ($planet['CultureSlot'])
	$CulPlanettotal++;
    $Poptotal+=$planet['Population'];
    $Frmtotal+=$planet['Farm'];
    $Fcttotal+=$planet['Factory'];
    $Cybtotal+=$planet['Cybernet'];
    $Labtotal+=$planet['Lab'];
    $Reftotal+=$planet['Refinery'];
    $SBtotal+=$planet['Starbase'];
    ++$i;
    $T->Insert(1,$i,new Link("detail.php?id={$planet['SID']}",$planet['SID']));
    $T->Insert(2,$i,$planet['TypeName']);
    $T->Insert(3,$i,new Link("planet.php?id=$row" . $sitAddition,$planet['CustomName']!=""?"{$planet['CustomName']}":"{$planet['Name']} {$planet['Ring']}"));
    $T->SetClass(3,$i,"planetname");
    $T->Insert(4,$i,"{$planet['Population']}");
    $T->SetClass(4,$i,"population");
    Progress($T->Get(5,$i),140,$planet['PopulationRemain']+1,growth_points_for_lvl($planet['Population']+1)+2,true);
    $u=$planet['PopProd'];
    $T->Insert(6,$i,sprintf("%+.1f",$u));
    $T->SetClass(6,$i,"populationh");
    $PP=floor($planet['PP']);
    $PPtotal+=$PP;
    $PPh=$planet['PPProd'];
    $Prodtotal+=$PPh;
    $STx=floor($planet['STx']/1000);
    $T->Insert(7,$i,"$STx");
    $T->SetClass(7,$i,"tx");
    $T->Insert(8,$i,"{$planet['Farm']}");
    $T->Insert(9,$i,"{$planet['Factory']}");
    $T->Insert(10,$i,"{$planet['Cybernet']}");
    $T->Insert(11,$i,"{$planet['Lab']}");
    $T->Insert(12,$i,"{$planet['Refinery']}");
    $T->Insert(13,$i,"{$planet['Starbase']}");
    $T->SetClass(13,$i,"tx");
    $T->Insert(14,$i,"$PP");
    $T->SetClass(14,$i,"pp");
    $T->Insert(15,$i,sprintf("%+.1f",$PPh));
    $T->SetClass(15,$i,"pph");
    
    if ($planet['Owner']!=$planet['FleetOwner'] and $planet['FleetOwner']!=0)
    {
	$T->aRowClass[$i]="siegedplanet";
        $T->onRowMouseOver($i,"this.className='siegedplanetover'");
        $T->onRowMouseOut($i,"this.className='siegedplanet'");
    }
    else
    {
        $T->onRowMouseOver($i,"this.className='takenplanetover'");
        $T->onRowMouseOut($i,"this.className='takenplanet'");
    }	
    $T->SetRowLink($i,"planet.php?id=$row" . $sitAddition);
    ++$row;

}
++$i;
$T->Insert(1,$i,"Total");
$Info=player_get_rank($sql, $MainPID);
$Pts=player_count_points($sql, $MainPID);
$dif=$Pts-$Info['Points'];
$T->Insert(3,$i,"#{$Info['Rank']}: {$Pts} ($dif)");
if ($Info['Countdown']<15)
    $T->Insert(2,$i," Cntd: {$Info['Countdown']}");
//$T->Join(1,$i,2,1);
$T->Insert(4,$i,"" . $Poptotal);
$T->Insert(5,$i,sprintf("TR output %.1f%%",player_get_given_ta(&$sql, $MainPID)));
$T->Insert(8,$i,"" . $Frmtotal);
$T->Insert(9,$i,"" . $Fcttotal);
$T->Insert(10,$i,"" . $Cybtotal);
$T->Insert(11,$i,"" . $Labtotal);
$T->Insert(12,$i,"" . $Reftotal);
$T->Insert(13,$i,"" . $SBtotal);
$T->Insert(14,$i,"" . $PPtotal);
$T->Insert(15,$i,sprintf("%+.1f",$Prodtotal));
$T->aRowClass[$i]='block';


$H->Insert($T);

$T=new Table();
$T->sClass='block';
$T->Insert(1,1,"Planets possessed");
$T->Insert(1,2,"Culture used");
$T->Insert(1,3,"Culture available");
$T->Insert(2,1,"" . $TakenPlanettotal);
$T->Insert(2,2,"" . $CulPlanettotal);
$T->Insert(2,3,"" . player_get_science($sql, $_SESSION['PID'], "CultureLvl"));
$T->SetClass(1,1,'legend');
$T->SetClass(1,2,'legend');
$T->SetClass(1,3,'legend');
$T->SetClass(2,1,'number');
$H->Insert($T);


include("part/mainsubmenu.php");
$H->Draw();
CloseSQL($sql);
?>
