<?php
//ob_start("ob_gzhandler");

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/validator.php");
include_once("internal/background.php");

session_start();



$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");

$H->sTitle="Northern Cross - Background images";

$sql=&OpenSQL($H);

ForcePlayer($sql, $H, "bgimg.php");

if ($_SESSION['IsAdmin']!=1)
    {
	$H->Draw();
	die;
    }

$menuselected="Bgmap";
include("part/mainmenu.php");


get("e","integer");
if ($_GET['e']!="")
{
    $sql->query("DELETE FROM NC_BackgroundList WHERE BaseID={$GET['e']}");
}

get("ep","integer");
if ($_GET['ep']!="")
{
    get("x","integer");
    get("y","integer");
    $sql->query("DELETE FROM NC_BackgroundList WHERE BaseID={$GET['ep']} AND X={$GET['x']} AND Y={$GET['y']}");
    background_correct($sql, $GET['ep']);
}


get("cor","integer");
if ($_GET['cor']!="")
{
    background_correct($sql, $GET['cor']);
}

get("o","integer");

post("file","string");
if (exists($POST['file']))
{
    post("width","integer");
    post("height","integer");
    $BGArr=$sql->query("SELECT MAX(BaseID)+1 AS M FROM NC_BackgroundList");
    $BgID=makeinteger($BGArr[0]['M']);
    echo "BgID=$BgID";
    if ($POST['width']==0 or $POST['height']==0)
    {
	$sql->query("INSERT INTO NC_BackgroundList VALUES(NULL, $BgID,0,0," . makequotedstring($POST['file']) . ")");
    }
    else
    {
	for ($i=0; $i<$POST['width']; $i+=27)
	for ($j=0; $j<$POST['height']; $j+=27)
	{
	    $x=floor($i/27);
	    $y=floor($j/27);
	    $sql->query("INSERT INTO NC_BackgroundList VALUES(NULL, $BgID, $x, $y, " . makequotedstring("{$POST['file']}.$x.$y") . ")");
	    }
    }
}

$F=new Form("bgimg.php","true");
$F->Insert("Base file name: [png] ");
$F->Insert(new Input("text","file","","text"));
$F->Insert(new Br());
$F->Insert("Width: ");
$F->Insert(new Input("text","width","","text"));
$F->Insert(new Br());
$F->Insert("Height: ");
$F->Insert(new Input("text","height","","text"));
$F->Insert(new Br());
$F->Insert(new Input("submit","submit","submit","smbutton"));
$F->Insert(new Br());
$H->Insert($F);

$T=new Table();
$T->sClass='block';
$T->Insert(1,1,"Background image manager");
$T->Insert(1,2,"BaseID");
$T->Insert(2,2,"Sky");
$T->Insert(3,2,"In range sky");
$T->Insert(4,2,"Delete");
if (exists($GET['o']))
    $S="WHERE BaseID={$GET['o']}";
else
    $S="";
$A=$sql->query("SELECT * FROM NC_BackgroundList $S ORDER BY BaseID, Y, X");
$L=array();
$LD=array();

foreach ($A as $Pic)
{
    $X=$Pic['BaseID'];
    if (!isset($L[$X]))
    {
	$L[$X]=new Table();
	$LD[$X]=new Table();
        $T->Insert(1,$X+3,"$X");
	$T->SetClass(1,$X+3,'sublegend');
	$T->Insert(4,$X+3,new Link("bgimg.php?e=$X","X"));
	$T->Insert(4,$X+3,new Link("bgimg.php?cor=$X","C"));
	}
    $L[$X]->Insert($Pic['X']+1,$Pic['Y']+1,new Link("bgimg.php?o=$X&ep=$X&x={$Pic['X']}&y={$Pic['Y']}",new Image("IMG/map/" . $Pic['File'] . ".png")));
    $LD[$X]->Insert($Pic['X']+1,$Pic['Y']+1,new Link("bgimg.php?o=$X&ep=$X&x={$Pic['X']}&y={$Pic['Y']}",new Image("IMG/map/_" . $Pic['File'] . ".png")));
}
foreach ($L as $ID => $Pic)
{
    $T->Insert(2,$ID+3,$Pic);
    $T->Insert(3,$ID+3,$LD[$ID]);
}
$T->Join(1,1,4,1);
$T->aRowClass[1]='title';
$T->aRowClass[2]='legend';
$H->Insert($T);
$H->Draw();
CloseSQL($sql);
?>
