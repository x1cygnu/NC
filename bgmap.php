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

if (!$_SESSION['IsAdmin'])
{
    $H->Draw();
    die;
}

$menuselected="Map";

get("r","integer");
def($GET['r'],10);
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
	$coords=$sql->query("SELECT * FROM NC_Map WHERE SID=(SELECT HomeSID FROM NC_Player WHERE PID={$_SESSION['PID']})");
	$x=$coords[0]['X'];
	$y=$coords[0]['Y'];
    }
}


include("part/mainmenu.php");

$F = new Form("bgmap.php",false);
$T = new Table();
$T->Insert(1,1,"System ID");
$T->Insert(2,1,"X");
$T->Insert(3,1,"Y");
$T->Insert(4,1,"Range");
$T->Insert(1,2,new Input("text","id","","text number"));
$T->Insert(2,2,new Input("text","x",$x,"text number"));
$T->Insert(3,2,new Input("text","y",$y,"text number"));
$T->Insert(4,2,new Input("text","r",$GET['r'],"text number"));
$T->Insert(1,3,"Image Index");
$T->Insert(2,3,"X1");
$T->Insert(3,3,"Y1");
$T->Insert(4,3,"X2");
$T->Insert(5,3,"Y2");
$T->Insert(6,3,"Z");
$T->Insert(7,3,"Transp");
$T->Insert(1,4,new Input("text","bgidx",'',"text number"));
$IX1=new Input("text","x1","","text number"); $IX1->sId="x1"; $IX1->onChange("document.getElementById('x2').value=15+parseInt(value)");
$T->Insert(2,4,$IX1);
$IY1=new Input("text","y1","","text number"); $IY1->sId="y1"; $IY1->onChange("document.getElementById('y2').value=15+parseInt(value)");
$T->Insert(3,4,$IY1);
$IX2=new Input("text","x2","","text number"); $IX2->sId='x2';
$T->Insert(4,4,$IX2);
$IY2=new Input("text","y2","","text number"); $IY2->sId='y2';
$T->Insert(5,4,$IY2);
$T->Insert(6,4,new Input("text","z","1","text number"));
$T->Insert(7,4,new Input("text","transp","0","text number"));
$T->Insert(1,5,new Input("submit","","Center","smbutton"));
$T->Join(1,5,5,1);
$F->Insert($T);
$H->Insert($F);

include_once("mapgen.php");

get("bgidx","integer");
if ($GET['bgidx']>0) {
    get("x1","integer");
    get("x2","integer");
    get("y1","integer");
    get("y2","integer");
    get("z","integer");
    get("transp","integer");
    $sql->query("INSERT INTO NC_NewBackground VALUES ({$GET['x1']},{$GET['y1']},{$GET['x2']},{$GET['y2']},{$GET['bgidx']},{$GET['z']},{$GET['transp']})");    
}

$M=map_gen($sql, $_SESSION['PID'], $x, $y, $GET['r'], "bgmap.php");
    
$H->Insert($M);
include("part/mainsubmenu.php");
$H->Draw();
CloseSQL($sql);
?>
