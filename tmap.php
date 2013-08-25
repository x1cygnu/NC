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

$menuselected="Map";

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

$hbio=(int)($bio/2);
$newl=$x-$hbio;
$newt=$y-$hbio;
$newr=$x+$hbio;
$newb=$y+$hbio;
$xfrom=$x-$bio;
$xto=$x+$bio;
$yfrom=$y-$bio;
$yto=$y+$bio;

if ($_SESSION['MapBackground'])
    $BGSQL=background_show_range($sql, $_SESSION['PID'], $x, $y, $bio);


$M=new Table();
$M->sClass="map";

function applybio($x,$y,$range)
{
global $xfrom; global $xto; global $yfrom; global $yto; global $M;
$xrfrom=$x-$range;
$xrto=$x+$range;
$yrfrom=$y-$range;
$yrto=$y+$range;
TrimDown($xrfrom,$xfrom);
TrimUp($xrto,$xto);
TrimDown($yrfrom,$yfrom);
TrimUp($yrto,$yto);

for ($wy=$yrfrom; $wy<=$yrto; ++$wy)
for ($wx=$xrfrom; $wx<=$xrto; ++$wx)
    {
    $PXxx=$wx-$xfrom+2;
    $PYyy=$wy-$yfrom+2;
    $M->SetClass($wx-$xfrom+2,$wy-$yfrom+2,"b");
    }

}

function applymapbio($x,$y,$range,&$BG)
{
global $xfrom; global $xto; global $yfrom; global $yto; global $M;
$xrfrom=$x-$range;
$xrto=$x+$range;
$yrfrom=$y-$range;
$yrto=$y+$range;
TrimDown($xrfrom,$xfrom);
TrimUp($xrto,$xto);
TrimDown($yrfrom,$yfrom);
TrimUp($yrto,$yto);

for ($wy=$yrfrom; $wy<=$yrto; ++$wy)
for ($wx=$xrfrom; $wx<=$xrto; ++$wx)
    {
    $BG[$wx-$xfrom+2][$wy-$yfrom+2]['InRange']=true;
    }
}


$M->Insert(1,1,new Link("map.php?x=$newl&y=$newt&r=$bio",
			new Image('IMG/tlb.png',"TL")));
$M->Insert(1,3+2*$bio,new Link("map.php?x=$newl&y=$newb&r=$bio",
			new Image('IMG/blb.png',"BL")));

$M->Insert(3+2*$bio,1,new Link("map.php?x=$newr&y=$newt&r=$bio",
			new Image('IMG/trb.png',"TR")));
$M->Insert(3+2*$bio,3+2*$bio,new Link("map.php?x=$newr&y=$newb&r=$bio",
			new Image('IMG/brb.png',"BR")));

$M->aRowClass[1]="legend";
$M->aRowClass[$bio*2+3]="legend";
for ($tx = 1; $tx <= $bio*2+1; $tx++)
{
    if ($tx==$bio+1)
    {
	$M->Insert(1+$tx,1,new Link("map.php?x=$x&y=$newt&r=$bio",
				new Image('IMG/tb.png',"up")));
	$M->Insert(1+$tx,3+$bio*2,new Link("map.php?x=$x&y=$newb&r=$bio",
				new Image('IMG/bb.png',"down")));
	$M->Insert(1,$tx+1,new Link("map.php?x=$newl&y=$y&r=$bio",
				new Image('IMG/lb.png',"left")));
	$M->Insert(3+$bio*2,$tx+1,new Link("map.php?x=$newr&y=$y&r=$bio",
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


$stars=starsystem_show_range_advanced($sql, $_SESSION['PID'], $x, $y, $bio);
echo "<pre>";
print_r($stars);
echo "</pre>";

$SS=array();

foreach ($stars as $star)
{
    $putx=$star['X']-$xfrom+2;
    $puty=$star['Y']-$yfrom+2;
    $SS[$puty][$putx]=$star;
}

$Blank=new Image("IMG/b.gif",".");



$R=player_get_bio_ranges($sql,$_SESSION['PID']);
$InRC=0;

//background    
if ($_SESSION['MapBackground'])
{
    foreach ($BGSQL as $BGE)
    {
	$putx=$BGE['X']-$xfrom+2;
        $puty=$BGE['Y']-$yfrom+2;
        $BG[$putx][$puty]=$BGE;
    }

    foreach ($R as $ran)
    {
	++$InRC;
        applymapbio($ran['X'],$ran['Y'],$ran['Range'],$BG);
    }

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
    }
}    

foreach ($R as $ran)
{
    ++$InRC;
    applybio($ran['X'],$ran['Y'],$ran['Range']);
}
//$H->Insert("$InRC players used<br/>");


for ($y=2; $y<=2*$bio+2; ++$y)
for ($x=2; $x<=2*$bio+2; ++$x)
{
    if (isset($SS[$y][$x]))
	{
	if ($SS[$y][$x]['Special']==1)
	    $TGIF="IMG/ssp.gif";
	else
	    $TGIF="IMG/s{$SS[$y][$x]['Level']}.gif";
//	if (true)
	    if ($M->GetClass($x,$y)=='b')
	    {
    	    $M->Insert($x,$y,
		new Link("detail.php?id={$SS[$y][$x]['SID']}",
		    new Image($TGIF,"{$SS[$y][$x]['Name']}","{$SS[$y][$x]['Name']}")));
	    }
        else
	    $M->Insert($x,$y,
	        new Image($TGIF,"{$SS[$y][$x]['Name']}"));
	    }
    else
	$M->Insert($x,$y,$Blank);
}

    
    
$H->Insert($M);
include("part/mainsubmenu.php");
$H->Draw();
CloseSQL($sql);
?>