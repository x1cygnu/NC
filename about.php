<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/validator.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");

$H->sTitle="Northern Cross - About";

$sql=&OpenSQL($H);

if (CheckPlayer($sql))
{
    include("part/mainmenu.php");
}

$T = new Table();
$T->Insert(1,1,"Game stats");
$T->Insert(1,2,new Link("about.php?show=race","Race"));
$T->Insert(2,2,new Link("about.php?show=science","Science"));
$T->Insert(3,2,new Link("about.php?show=dev","Development"));
$T->Insert(4,2,new Link("about.php?show=ptypes","Planet types"));
$T->aRowClass[1]='title';
$T->Join(1,1,4,1);
$T->sClass='block standard';
$H->Insert($T);

get("show","string");

///////////////////////
// RACE
///////////////////////

if ($GET['show']=="race")
{
$T = new Table();
$Rs=$sql->query("SELECT AVG(Growth) Gr, AVG(Science) Sc, AVG(Culture) Cul, AVG(Production) Prod, AVG(Speed) Spd, AVG(Attack) Attk, AVG(Defence) Def FROM NC_Player WHERE Points>0 AND Sensory<127 AND PID>1");
$R=$Rs[0];
$T->Insert(1,1,"Race statistics");
$T->aRowClass[1]='title';
$T->Insert(1,2,"Attribute"); $T->Insert(2,2,"Average");
$T->aRowClass[2]='legend';
$T->Join(1,1,3,1);
$T->Insert(1,3,"Growth"); $T->Insert(2,3,sprintf("%+.1f",$R['Gr']));
$T->Insert(1,4,"Science"); $T->Insert(2,4,sprintf("%+.1f",$R['Sc']));
$T->Insert(1,5,"Culture"); $T->Insert(2,5,sprintf("%+.1f",$R['Cul']));
$T->Insert(1,6,"Production"); $T->Insert(2,6,sprintf("%+.1f",$R['Prod']));
$T->Insert(1,7,"Speed"); $T->Insert(2,7,sprintf("%+.1f",$R['Spd']));
$T->Insert(1,8,"Attack"); $T->Insert(2,8,sprintf("%+.1f",$R['Attk']));
$T->Insert(1,9,"Defense"); $T->Insert(2,9,sprintf("%+.1f",$R['Def']));
$T->sClass='block';
$H->Insert($T);
}

///////////////////////
// SCIENCES
///////////////////////

if ($GET['show']=="science")
{
$T = new Table();
$Rs=$sql->query("SELECT AVG(Sensory) AvgSen, AVG(Engineering) AvgEng, AVG(Warp) AvgWrp, AVG(Mathematics) AvgMath, AVG(Physics) AvgPhy, AVG(Urban) AvgUrb, " .
		" MAX(Sensory) MaxSen, MAX(Engineering) MaxEng, MAX(Warp) MaxWrp, MAX(Mathematics) MaxMath, MAX(Physics) MaxPhy, MAX(Urban) MaxUrb " .
		"FROM NC_Player WHERE Points>0 AND Sensory<127 AND PID>1");
$R=$Rs[0];
$T->Insert(1,1,"Science statistics");
$T->aRowClass[1]='title';
$T->Insert(1,2,"Attribute"); $T->Insert(2,2,"Average"); $T->Insert(3,2,"Max");
$T->aRowClass[2]='legend';
$T->Join(1,1,3,1);
$T->Insert(1,3,"Sensory"); $T->Insert(2,3,sprintf("%.1f",$R['AvgSen'])); $T->Insert(3,3,sprintf("%d",$R['MaxSen']));
$T->Insert(1,4,"Engineering"); $T->Insert(2,4,sprintf("%.1f",$R['AvgEng'])); $T->Insert(3,4,sprintf("%d",$R['MaxEng']));
$T->Insert(1,5,"Warp"); $T->Insert(2,5,sprintf("%.1f",$R['AvgWrp'])); $T->Insert(3,5,sprintf("%d",$R['MaxWrp']));
$T->Insert(1,6,"Physics"); $T->Insert(2,6,sprintf("%.1f",$R['AvgPhy'])); $T->Insert(3,6,sprintf("%d",$R['MaxPhy']));
$T->Insert(1,7,"Mathematics"); $T->Insert(2,7,sprintf("%.1f",$R['AvgMath'])); $T->Insert(3,7,sprintf("%d",$R['MaxMath']));
$T->Insert(1,8,"Urban"); $T->Insert(2,8,sprintf("%.1f",$R['AvgUrb'])); $T->Insert(3,8,sprintf("%d",$R['MaxUrb']));
$T->sClass='block';
$H->Insert($T);
}

if ($GET['show']=="dev")
{
$T = new Table();
$Rs=$sql->query("SELECT AVG(PL) AvgPL, AVG(CultureLvl) AvgCul, AVG(TA) AvgTA, AVG(VL) AvgVL, AVG(Points) AvgPoints, " .
		" MAX(PL) MaxPL, MAX(CultureLvl) MaxCul, MAX(TA) MaxTA, MAX(VL) MaxVL, MAX(Points) MaxPoints " .
		"FROM NC_Player WHERE Points>0 AND Sensory<127 AND PID>1");
$R=$Rs[0];
$T->Insert(1,1,"Developement statistics");
$T->aRowClass[1]='title';
$T->Insert(1,2,"Attribute"); $T->Insert(2,2,"Average"); $T->Insert(3,2,"Max");
$T->aRowClass[2]='legend';
$T->Join(1,1,3,1);
$T->Insert(1,3,"Culture"); $T->Insert(2,3,sprintf("%.1f",$R['AvgCul'])); $T->Insert(3,3,sprintf("%d",$R['MaxCul']));
$T->Insert(1,4,"Player Level"); $T->Insert(2,4,sprintf("%.1f",$R['AvgPL'])); $T->Insert(3,4,sprintf("%d",$R['MaxPL']));
$T->Insert(1,5,"Violence Level"); $T->Insert(2,5,sprintf("%.1f",$R['AvgVL'])); $T->Insert(3,5,sprintf("%d",$R['MaxVL']));
$T->Insert(1,6,"Trade Revenue"); $T->Insert(2,6,sprintf("%.1f",$R['AvgTA'])); $T->Insert(3,6,sprintf("%d",$R['MaxTA']));
$T->Insert(1,7,"Points"); $T->Insert(2,7,sprintf("%.1f",$R['AvgPoints'])); $T->Insert(3,7,sprintf("%d",$R['MaxPoints']));
$T->sClass='block';
$H->Insert($T);
}

if ($GET['show']=="ptypes")
{
$T = new Table();
$T->Insert(1,1,"Planet type statistics");
$T->aRowClass[1]='title';
$T->Insert(1,2,"Type");
$T->Insert(2,2,"Count");
$T->Insert(3,2,"Probability");
$T->Join(1,1,3,1);
$T->aRowClass[2]='legend';
$Totals=$sql->query("SELECT count(*) C FROM NC_Planet");
$Total=$Totals[0]['C'];
$row=2;
$Types=planet_get_types($sql);
foreach ($Types as $Type) {
    ++$row;
    $T->Insert(1,$row,$Type['TypeName']);
    $C=$sql->query("SELECT count(*) C FROM NC_Planet WHERE Type={$Type['PTID']}");
    $T->Insert(2,$row,'' . $C[0]['C']);
    $T->Insert(3,$row,sprintf("%.1f%%",$C[0]['C']*100.0/$Total));
}
++$row;
$T->Insert(1,$row,'Total');
$T->Insert(2,$row,'' . $Total);
$T->sClass='block';
$H->Insert($T);
}

$H->Insert("Newcommers and players with no planets are not included in the statistics");

include("part/mainsubmenu.php");

$H->Draw();
CloseSQL($sql);
?>
