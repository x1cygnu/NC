<?php
ob_start("ob_gzhandler");
include_once("./internal/common.php");
include_once("./internal/security/validator.php");
include_once("./internal/starsystem.php");
include_once("./internal/player.php");
include_once("./internal/background.php");

session_start();

$H=new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle('default.css');
$H->AddStyle('map.css');

$H->sTitle="Northern Cross - Map";

$sql = &OpenSQL($H);

ForceActivePlayer($sql, $H, "map.php");
ForceFrozen($sql, $H);

$H->addJavascriptFile('js/common.js');
$H->addJavascriptFile('js/fading.js');
$H->addJavascriptFile('js/map.js');

$menuselected="Map";

get("r","integer");
def($GET['r'],$_SESSION['DefMapR']);
$bio=$GET['r'];
if ($bio>30)
    $bio=30;
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
	$x=$_SESSION['DefMapX'];
	$y=$_SESSION['DefMapY'];
/*	$coords=$sql->query("SELECT * FROM NC_Map WHERE SID=(SELECT HomeSID FROM NC_Player WHERE PID={$_SESSION['PID']})");
	$x=$coords[0]['X'];
	$y=$coords[0]['Y'];*/
    }
}


include("part/mainmenu.php");

$F = new Form("map.php",false);
$T = new Table();
$T->Insert(1,1,"System ID");
$T->Insert(2,1,"X");
$T->Insert(3,1,"Y");
$T->Insert(4,1,"Range");
$T->Insert(1,2,new Input("text","id","","text number"));
$T->Insert(2,2,new Input("text","x","","text number"));
$T->Insert(3,2,new Input("text","y","","text number"));
$T->Insert(4,2,new Input("text","r","","text number"));
$T->Insert(1,3,new Input("submit","","Center","smbutton"));
$T->Join(1,3,4,1);

$F->Insert($T);
$H->Insert($F);

include_once("mapgen.php");

$M=map_gen($sql, $_SESSION['PID'], $x, $y, $GET['r'], "map.php");

$MainDiv=map_gen_get($M);


/*
$D=new Div();
$D->sId='starmsg';
$D->sStyle="position : absolute; z-index : 9; display : none";
$MainDiv->Insert($D);
$D->sId='starcursor';
$D->sStyle="position : absolute; z-index : 9; display : none";
$MainDiv->Insert($D);
*/

$H->onLoad("initmap($x,$y,{$GET['r']})");
$H->Insert($M);
include("part/mainsubmenu.php");
$H->Draw();
CloseSQL($sql);
?>