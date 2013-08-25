<?php
ob_start("ob_gzhandler");

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");
include_once("internal/background.php");

session_start();


$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");

$H->sTitle="Northern Cross - Background map";

$sql=&OpenSQL($H);

ForcePlayer($sql, $H, "bgmap.php");

$menuselected="Bgmap";
include("part/mainmenu.php");

if (!$_SESSION['IsAdmin'])
{
    include("part/mainsubmenu.php");
    $H->Draw();
    die;
}

$H->AddStyle("map.css");

$H->Insert(new Link("bgimg.php","Background Image Manager"));


get("r","integer");
def($GET['r'],10);
$bio=$GET['r'];
if ($bio>200)
    $bio=200;
if ($bio<1)
    $bio=1;

get("id","integer");
get("x","integer");
get("y","integer");
get("imgid","integer");
get("comm","string");
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

if ($GET['comm']=="Put")
{
    background_put($sql, $x, $y, $GET['imgid']);
}
$UseImage=false;
if ($_GET['imgid']!="")
{
    $Imgs=background_view_image($sql,$GET['imgid']);
    $I=new Table();
    $I->sClass='block';
    for ($i=1; $i<10; ++$i)
    {
	$I->Insert(1,$i+1,"$i");
	$I->SetClass(1,$i+1,'legend');
	$I->Insert($i+1,1,"$i");
	$I->SetClass($i+1,1,'legend');
    }
    foreach ($Imgs as $Img)
    {
	$UseImage=true;
	$I->Insert($Img['X']+2,$Img['Y']+2,new Image("IMG/map/{$Img['File']}.png"));
    }
    $H->Insert($I);
}
$F = new Form("bgmap.php",false);
$T = new Table();
$T->Insert(1,1,"System ID");
$T->Insert(2,1,"X");
$T->Insert(3,1,"Y");
$T->Insert(4,1,"Range");
$T->Insert(5,1,"Image");
$T->Insert(1,2,new Input("text","id",$GET['id'],"text number"));
$T->Insert(2,2,new Input("text","x",$GET['x'],"text number"));
$T->Insert(3,2,new Input("text","y",$GET['y'],"text number"));
$T->Insert(4,2,new Input("text","r",$GET['r'],"text number"));
$T->Insert(5,2,new Input("IMG ID","imgid",$GET['imgid'],"text number"));
$T->Insert(1,3,new Input("submit","comm","Center","smbutton"));
$T->Insert(1,3,new Input("submit","comm","Put","smbutton"));
$T->Join(1,3,5,1);


$F->Insert($T);
$H->Insert($F);

$hbio=(int)($bio/2);
$newl=$x-$hbio;
$newt=$y-$hbio;
$newr=$x+$hbio;
$newb=$y+$hbio;
$xfrom=$x-$bio;
$xto=$x+$bio;
$yfrom=$y-$bio;
$yto=$y+$bio;

$BGSQL=background_show_range($sql, 0, $x, $y, $bio);
foreach ($BGSQL as $BGE)
{
    $putx=$BGE['X']-$xfrom+2;
    $puty=$BGE['Y']-$yfrom+2;
    $BG[$putx][$puty]=$BGE;
}

$M=new Table();
$M->sClass="map";

$M->Insert(1,1,new Link("bgmap.php?x=$newl&y=$newt&r=$bio&imgid={$_GET['imgid']}",
			new Image('IMG/tlb.png',"TL")));
$M->Insert(1,3+2*$bio,new Link("bgmap.php?x=$newl&y=$newb&r=$bio&imgid={$_GET['imgid']}",
			new Image('IMG/blb.png',"BL")));

$M->Insert(3+2*$bio,1,new Link("bgmap.php?x=$newr&y=$newt&r=$bio&imgid={$_GET['imgid']}",
			new Image('IMG/trb.png',"TR")));
$M->Insert(3+2*$bio,3+2*$bio,new Link("bgmap.php?x=$newr&y=$newb&r=$bio&imgid={$_GET['imgid']}",
			new Image('IMG/brb.png',"BR")));

$M->aRowClass[1]="legend";
$M->aRowClass[$bio*2+3]="legend";
for ($tx = 1; $tx <= $bio*2+1; $tx++)
{
    if ($tx==$bio+1)
    {
	$M->Insert(1+$tx,1,new Link("bgmap.php?x=$x&y=$newt&r=$bio&imgid={$_GET['imgid']}",
				new Image('IMG/tb.png',"up")));
	$M->Insert(1+$tx,3+$bio*2,new Link("bgmap.php?x=$x&y=$newb&r=$bio&imgid={$_GET['imgid']}",
				new Image('IMG/bb.png',"down")));
	$M->Insert(1,$tx+1,new Link("bgmap.php?x=$newl&y=$y&r=$bio&imgid={$_GET['imgid']}",
				new Image('IMG/lb.png',"left")));
	$M->Insert(3+$bio*2,$tx+1,new Link("bgmap.php?x=$newr&y=$y&r=$bio&imgid={$_GET['imgid']}",
				new Image('IMG/rb.png',"right")));
    }
    else
    {
        $M->Insert(1+$tx,1,$xfrom+$tx-1 . "");
        $M->Insert(1,1+$tx,$yfrom+$tx-1 . "");
        $M->Insert($bio*2+3,1+$tx,$yfrom+$tx-1 . "");
        $M->Insert(1+$tx,$bio*2+3,$xfrom+$tx-1 . "");
    }
	$M->SetClass(1,1+$tx,"legend");
	$M->SetClass($bio*2+3,1+$tx,"legend");
}

$Blank=new Image("IMG/b.gif",".");


for ($y=2; $y<=2*$bio+2; ++$y)
for ($x=2; $x<=2*$bio+2; ++$x)
{
    if ($BG[$x][$y]!="")
    {
    $M->SetStyle($x,$y,"background-image : url(IMG/map/"
	. ($BG[$x][$y]['InRange']?"_":"")
	. $BG[$x][$y]['File']
    . ".png)");
    }
    if ($UseImage)
        {
	$rx=$x+$xfrom-2;
	$ry=$y+$yfrom-2;
        $M->Insert($x,$y,new Link("bgmap.php?x=$rx&y=$ry&comm=Put&imgid={$GET['imgid']}",$Blank));
	}
    else
        $M->Insert($x,$y,$Blank);

}

$H->Insert($M);


include_once("part/mainsubmenu.php");
$H->Draw();
CloseSQL($sql);
?>
