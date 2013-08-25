<?php
chdir('..');

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");
include_once("internal/tech.php");
include_once("internal/player.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");

$H->sTitle="Northern Cross - Technology";

$sql=&OpenSQL($H);

ForceActivePlayer($sql, $H, "tech.php");


ForceFrozen($sql, $H);

$H->AddStyle("tech.css");
$H->AddStyle("science.css");

$menuselected="Tech";
include("part/mainmenu.php");

$Techs=tech_get_list($sql, $_SESSION['PID']);

$T=new Table();
$T->Insert(1,1,"Technologies");
$T->aRowClass[1]='title';
$T->Insert(1,2,"Code");
$T->Insert(2,2,"Name");
$T->Insert(3,2,"Sci");
$T->Insert(4,2,"@");
$T->Insert(5,2,"Prerequisities");
$T->Insert(6,2,"Research");

$P=player_get_sciences($sql, $_SESSION['PID']);

get("h","integer");
if ($GET['h']>0 and $P['TechSelected']==$GET['h'])
{
    tech_halt($sql, $_SESSION['PID']);
    $P=player_get_sciences($sql, $_SESSION['PID']);
}

get("c","integer");
if ($GET['c']>0 and $P['TechSelected']==$GET['c'])
{
    tech_continue($sql, $_SESSION['PID']);
    $P=player_get_sciences($sql, $_SESSION['PID']);
}


get("e","integer");
if ($GET['e']>0 and $P['TechSelected']==$GET['e'])
{
    $H->Insert(new Link("tech.php?ee={$GET['e']}","Click here to confirm cancellation"));
    $H->Br();
    $H->Insert("Warning, time and money spent on this technology will be lost");
}

get("ee","integer");
if ($GET['ee']>0 and $P['TechSelected']==$GET['ee'])
{
    tech_cancel($sql, $_SESSION['PID']);
    $P=player_get_sciences($sql, $_SESSION['PID']);
}

get("t","integer");
if ($GET['t']>0)
{
    if ($P['TechSelected']!=0)
	$H->Insert(new Error("You may develop only one technology at once"));
    $TechIDL=$GET['t']-1;
    if ($Techs[$TechIDL]['Status']==0)
	$H->Insert(new Error("You may not develop this technology yet"));
    elseif ($Techs[$TechIDL]['Status']==2)
	$H->Insert(new Error("This technology is already developped"));
    else
    {
	$R=tech_select($sql, $_SESSION['PID'], $GET['t'], $Techs[$TechIDL]['ScienceCost'], $Techs[$TechIDL]['ATCost']);
	if ($R!="")
	    $H->Insert(new Error($R));
	else
	{
	    $H->Insert(new Info("Now researching {$Techs[$TechIDL]['Name']}"));
	    $P=player_get_sciences($sql, $_SESSION['PID']);
        }
    }
}


$row=2;
foreach ($Techs as $Tech)
{
++$row;
$comma=false;
$T->Insert(1,$row,$Tech['Help']);
$T->Insert(2,$row,$Tech['Name']);
$T->Insert(3,$row,$Tech['ScienceCost']);
$T->Insert(4,$row,$Tech['ATCost']);
if ($Tech['Sensory']>0)
{
    if ($Tech['Error'][1]) $T->Insert(5,$row,"<b>Sen: {$Tech['Sensory']}</b>");
    else $T->Insert(5,$row,"Sen: {$Tech['Sensory']}");
    $comma=true;
}
if ($Tech['Engineering']>0)
{
    if ($comma) $T->Insert(5,$row,",");
    if ($Tech['Error'][2]) $T->Insert(5,$row,"<b>Eng: {$Tech['Engineering']}</b>");
    else $T->Insert(5,$row,"Eng: {$Tech['Engineering']}");
    $comma=true;
}
if ($Tech['Warp']>0)
{
    if ($comma) $T->Insert(5,$row,",");
    if ($Tech['Error'][3]) $T->Insert(5,$row,"<b>Wrp: {$Tech['Warp']}</b>");
    else $T->Insert(5,$row,"Wrp: {$Tech['Warp']}");
    $comma=true;
}
if ($Tech['Physics']>0)
{
    if ($comma) $T->Insert(5,$row,",");
    if ($Tech['Error'][4]) $T->Insert(5,$row,"<b>Phy: {$Tech['Physics']}</b>");
    else $T->Insert(5,$row,"Phy: {$Tech['Physics']}");
    $comma=true;
}
if ($Tech['Mathematics']>0)
{
    if ($comma) $T->Insert(5,$row,",");
    if ($Tech['Error'][5]) $T->Insert(5,$row,"<b>Mat: {$Tech['Mathematics']}</b>");
    else $T->Insert(5,$row,"Mat: {$Tech['Mathematics']}");
    $comma=true;
}
if ($Tech['Urban']>0)
{
    if ($comma) $T->Insert(5,$row,",");
    if ($Tech['Error'][6]) $T->Insert(5,$row,"<b>Urb: {$Tech['Urban']}</b>");
    else $T->Insert(5,$row,"Urb: {$Tech['Urban']}");
    $comma=true;
}
if ($Tech['Tech1']>0)
{
    if ($comma) $T->Insert(5,$row,",");
    if ($Tech['Error'][7]) $T->Insert(5,$row,"<b>" . $Techs[$Tech['Tech1']-1]['Help'] . "</b>");
    else $T->Insert(5,$row,"" . $Techs[$Tech['Tech1']-1]['Help']);
    $comma=true;
}
if ($Tech['Tech2']>0)
{
    if ($comma) $T->Insert(5,$row,",");
    if ($Tech['Error'][8]) $T->Insert(5,$row,"<b>" . $Techs[$Tech['Tech2']-1]['Help'] . "</b>");
    else $T->Insert(5,$row,"" . $Techs[$Tech['Tech2']-1]['Help']);
    $comma=true;
}

if ($Tech['Status']==2)
{
    $T->aRowClass[$row]='compl';
    $T->Insert(6,$row,'Done');
    }
elseif ($Tech['Status']==1)
{
    $T->aRowClass[$row]='av';
    if ($Tech['TechID']==$P['TechSelected'])
    {
	if ($P['TechDevelop']==1)
	    {
	    $T->aRowClass[$row]='selectedscience';
	    $T->Insert(6,$row,new Link("tech.php?h={$Tech['TechID']}","Halt"));
	    }
	else
	    {
	    $T->Insert(6,$row,new Link("tech.php?c={$Tech['TechID']}","Continue "));
	    $T->Insert(6,$row,new Link("tech.php?e={$Tech['TechID']}","Cancel"));
	    }
    }
    else
    {
	if ($P['TechSelected']==0)
	    $T->Insert(6,$row,new Link("tech.php?t={$Tech['TechID']}","Research"));
    }
}
else
{
    $T->aRowClass[$row]='unav';
    $T->Insert(6,$row,'Unavailable');
}

}

$T->aRowClass[2]='legend';
$T->sClass='block';
$T->Join(1,1,5,1);
$H->Insert($T);
include("part/mainsubmenu.php");
$H->Draw();
CloseSQL($sql);
?>
