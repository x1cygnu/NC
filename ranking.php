<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");
include_once("internal/ranking.php");
include_once("internal/building.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");

$H->sTitle="Northern Cross - Ranking";

if (CheckPlayer($sql))
{
    include("part/mainmenu.php");
}

$T=new Table();
$T->Insert(1,1,"Ranking type");
$T->Insert(1,2,new Link("ranking.php","Player"));
$T->Insert(2,2,new Link("ranking.php?type=1","Alliance"));
$T->sClass='menu';
$T->SetClass(1,1,'title');
$T->Join(1,1,2,1);
$H->Insert($T);

$sql=&OpenSQL($H);

get("from","integer");
get("to","integer");
get("ord","integer");
get("type","integer");
if (!isset($GET['from'])) $GET['from']=1;
if (!isset($GET['to'])) $GET['to']=100;
if (!isset($GET['ord'])) $GET['ord']=0;
if (!isset($GET['type'])) $GET['type']=0;

if ($GET['type']==0)
{
switch ($GET['ord'])
{
case 0: $order="Rank"; $R=ranking_players_rank(&$sql, $GET['from'], $GET['to']); break;
case 1: $order="Points"; $R=ranking_players_points(&$sql, $GET['from'], $GET['to']); break;
case 2: $order="Science"; $R=ranking_players_science(&$sql, $GET['from'], $GET['to']); break;
case 3: $order="Culture"; $R=ranking_players_culture(&$sql, $GET['from'], $GET['to']); break;
case 4: $order="Player Level"; $R=ranking_players_pl(&$sql, $GET['from'], $GET['to']); break;
case 5: $order="Violence Level"; $R=ranking_players_vl(&$sql, $GET['from'], $GET['to']); break;
case 6: $order="Trade Revenue"; $R=ranking_players_ta(&$sql, $GET['from'], $GET['to']); break;
}


$T=new Table();
$T->Insert(1,1,"Player ranking (by $order)");
$Max=ranking_player_count($sql);
if ($Max>1000)
    $Max=1000;
for ($i=0; $i<$Max; $i+=100)
{
    $from=$i+1;
    $to=$i+100;
    $T->Insert(1,2,new Link("ranking.php?from=$from&to=$to&ord={$GET['ord']}","$from"));
    $T->Insert(1,2," ");
}
$T->Insert(1,3,"#");
$T->Insert(2,3,"Tag");
$T->Insert(3,3,"Name");
$T->Insert(4,3,new Link("ranking.php?from={$GET['from']}&to=${GET['to']}&ord=0","Rank"));
$T->Insert(5,3,new Link("ranking.php?from={$GET['from']}&to=${GET['to']}&ord=1","Points"));
$T->Insert(6,3,"Cntd");
$T->Insert(7,3,new Link("ranking.php?from={$GET['from']}&to=${GET['to']}&ord=2","Science"));
$T->Insert(8,3,new Link("ranking.php?from={$GET['from']}&to=${GET['to']}&ord=3","Culture"));
$T->Insert(9,3,new Link("ranking.php?from={$GET['from']}&to=${GET['to']}&ord=4","PL"));
$T->Insert(10,3,new Link("ranking.php?from={$GET['from']}&to=${GET['to']}&ord=5","VL"));
$T->Insert(11,3,new Link("ranking.php?from={$GET['from']}&to=${GET['to']}&ord=6","TR"));

$i=3;
$Pos=$GET['from'];
foreach ($R as $P)
{
    foreach ($P as $K => $EL)
	$P[$K]=htmlentities($EL);
	
    ++$i;
    $T->Insert(1,$i,"$Pos");
    ++$Pos;
    if ($P['TAG']!="")
        $T->Insert(2,$i,new Link("alliance.php?tag={$P['TAG']}","{$P['TAG']}"));
    if (CheckActivePlayer())
        $T->Insert(3,$i,new Link("pinfo.php?id={$P['PID']}","{$P['Nick']}"));
    else
	$T->Insert(3,$i,$P['Nick']);
    if ($P['Rank']<999999)
        $T->Insert(4,$i,$P['Rank']);
    $T->Insert(5,$i,$P['Points']);
    if ($P['Countdown']<15)
        $T->Insert(6,$i,$P['Countdown']);

    $Max=max($P['Sensory'], $P['Engineering'], $P['Warp'], $P['Physics'], $P['Mathematics'], $P['Urban']);
    $T->Insert(7,$i,"$Max");
    $T->Insert(8,$i,$P['CultureLvl']);
    
    $prc=floor((1-$P['PLRemain']/pl_points_for_lvl($P['PL']+1))*100);
    $T->Insert(9,$i,"{$P['PL']}-{$prc}%");
    $prc=floor((1-$P['VLRemain']/vl_points_for_lvl($P['VL']+1))*100);
    $T->Insert(10,$i,"{$P['VL']}-{$prc}%");
    $T->Insert(11,$i,"{$P['TA']}");
}

$T->Join(1,1,11,1);
$T->Join(1,2,11,1);

} // end of $GET['type']==0

elseif ($GET['type']==1)
{
switch ($GET['ord'])
{
case 2: $order="Points"; $R=ranking_alliance_points(&$sql, $GET['from'], $GET['to']); break;
case 1: $order="Members"; $R=ranking_alliance_members(&$sql, $GET['from'], $GET['to']); break;
case 0: $order="TCP"; $R=ranking_alliance_TCP(&$sql, $GET['from'], $GET['to']); break;
}


$T=new Table();
$T->Insert(1,1,"Alliance ranking (by $order)");
$Max=ranking_alliance_count($sql);
if ($Max>1000)
    $Max=1000;
for ($i=0; $i<$Max; $i+=100)
{
    $from=$i+1;
    $to=$i+100;
    $T->Insert(1,2,new Link("ranking.php?type=1&from=$from&to=$to&ord={$GET['ord']}","$from"));
    $T->Insert(1,2," ");
}
$T->Insert(1,3,"#");
$T->Insert(2,3,"Tag");
$T->Insert(3,3,"Name");
$T->Insert(4,3,new Link("ranking.php?type=1&from={$GET['from']}&to=${GET['to']}&ord=2","Points"));
$T->Insert(5,3,new Link("ranking.php?type=1&from={$GET['from']}&to=${GET['to']}&ord=0","TCP"));
$T->Insert(6,3,"Cntd");
$T->Insert(7,3,new Link("ranking.php?type=1&from={$GET['from']}&to=${GET['to']}&ord=1","Members"));
$T->Insert(8,3,"URL");

$i=3;
$Pos=$GET['from'];
foreach ($R as $P)
{
    foreach ($P as $K => $EL)
	$P[$K]=htmlentities($EL);
    ++$i;
    $T->Insert(1,$i,"$Pos");
    ++$Pos;
    $T->Insert(2,$i,$P['TAG']);
    $T->Insert(3,$i,new Link("alliance.php?tag={$P['TAG']}","{$P['Name']}"));
    $T->Insert(4,$i,$P['Points']);
    $T->Insert(5,$i,$P['TCP']);
    if ($P['Countdown']<21)
        $T->Insert(6,$i,$P['Countdown']);
    $T->Insert(7,$i,$P['NoMembers']);
    if ($P['URL']!="")
    $T->Insert(8,$i,new Link($P['URL'],$P['URL']));
}

$T->Join(1,1,8,1);
$T->Join(1,2,8,1);

}//end of $GET['type']==1

$T->sClass='block';
$T->aRowClass[1]='title';
$T->aRowClass[2]='legend';
$T->aRowClass[3]='legend';

$H->Insert($T);

include("part/mainsubmenu.php");

$H->Draw();
CloseSQL($sql);
?>
