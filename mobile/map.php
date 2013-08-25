<?php
chdir('..');

include_once("./internal/common.php");
include_once("./internal/security/validator.php");
include_once("./internal/starsystem.php");
include_once("./internal/player.php");
include_once("./internal/background.php");

session_start();

$H=new HTML();
$H->AddStyle('default.css');

$sql = &OpenSQL($H);

ForceActivePlayer($sql, $H, "map.php");
ForceFrozen($sql, $H);

get("r","integer");
def($GET['r'],10);
$bio=$GET['r'];
if ($bio>20)
    $bio=20;
if ($bio<1)
    $bio=1;

get("id","integer");
get("x","integer");
get("y","integer");
if (exists($GET['id']))    
{
    $iId=$GET['id'];
    $coords=$sql->query("SELECT X, Y FROM NC_Map WHERE SID=$iId");
    $x=$coords[0]['X'];
    $y=$coords[0]['Y'];
}
else
{
    if (exists($GET['x']) and exists($GET['y']))
	{$x=$GET['x']; $y=$GET['y'];}
    else
    {
	$coords=$sql->query("SELECT * FROM NC_Map WHERE SID=(SELECT HomeSID FROM NC_Player WHERE PID={$_SESSION['PID']})");
	$x=$coords[0]['X'];
	$y=$coords[0]['Y'];
    }
}


include("mobile/part/mainmenu.php");


$Rs=starsystem_bio($sql,$_SESSION['PID']);

$Present=new Table();
$Present->Insert(1,1,"with your planets");
$Present->aRowClass[1]='t';
$Rest=new Table();
$Rest->aRowClass[1]='t';
$Rest->Insert(1,1,"rest");
$pr=1;
$rst=1;
foreach ($Rs as $Star)
{
    if ($Star['YPC']>0)
    {
	++$pr;
        $Present->Insert(1,$pr,new Link("detail.php?id={$Star['SID']}","({$Star['X']}/{$Star['Y']}) {$Star['Name']}"));
    }
    else
    {
	++$rst;
        $Rest->Insert(1,$rst,new Link("detail.php?id={$Star['SID']}","({$Star['X']}/{$Star['Y']}) {$Star['Name']}"));
    }
    
}

    
    
$H->Insert($Present);
$H->Insert($Rest);


$H->Draw();
CloseSQL($sql);
?>