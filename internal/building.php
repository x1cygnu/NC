<?php

// building/growth interface

//$pop_table=array(0,0,21,78,189,372,645,1026,1533,2184,2997,3990,5181,6588,8229,10122,12285,14736,17493,20574,23997,27780,31941,36498,41469,46872,52725,59046,65853,73164,80997,89370,98301,107808,117909,128662,139965,151956,164613,177954,191997,206760,222261,238518,255549,273372);
//$building_table=array(0,5,13,24,41,66,104,161,246,374,567,855,1287,1936,2909,4369,6558,9843,14769,22158,33243,49869,74808,112217,168331,252502,378758,568141,852217,1278330,1917501,2876256,);

$buildings=array("Farm","Factory","Cybernet","Lab","Refinery","Starbase");
$sciences=array("Sensory","Engineering","Warp","Physics","Mathematics","Urban");

$fightships=array('Vpr','Int','Fr','Bs','Drn');
$transships=array('CS','Tr');
$anyships=array('Vpr','Int','Fr','Bs','Drn','CS','Tr');

$battleresult = array('AE','AU','R','=','DU','DE');

$BuildSTx=array(
    "Farm" => 80,
    "Factory" => 190,
    "Cybernet" => 80,
    "Lab" => 120,
    "Refinery" => 60,
    "Starbase" => 10
);


$DestroySTx=array(
    "Population" => 20,
    "Farm" => 10,
    "Factory" => 60,
    "Cybernet" => 20,
    "Lab" => 60,
    "Refinery" => 16,
    "Starbase" => 0
);

$WorkSTx=array(
    "Population" => 1.0,
    "Farm" => 0.2,
    "Factory" => 6.0,
    "Cybernet" => 1.0,
    "Lab" => 2.2,
    "Refinery" => -5.0,
    "Starbase" => 0.0
);


function building_points_for_lvl($lvl,$baseCost=10)
{
    return round($baseCost*pow(1.3,$lvl-1));
//    return round(5*pow(1.5,$lvl-1));
}

function pl_points_for_lvl($lvl)
{
    return round(5*(pow($lvl,2.7)-pow($lvl-1,2.7)));
}

function vl_points_for_lvl($lvl)
{
    return round(4*(pow($lvl,1.5)));
}

function growth_points_for_lvl($lvl)
{
    return 9*$lvl*($lvl-1)+3;
}

function culture_points_for_lvl($lvl)
{
    return 80*pow($lvl,2.2)+100*$lvl-400;
}

function science_points_for_lvl($lvl)
{
    return round(3*pow($lvl,2.5)+18*$lvl);
}

function eco_lvl($eco)
{
    return floor($eco/2);
}

function Int_points($eco)
{
 //   return 40-floor($eco/2);
	return floor(40.5*pow(0.985,$eco));
}

function Fr_points($eco)
{
//    return 280-floor(3.5*$eco);
	return floor(280.5*pow(0.985,$eco));
}

function Bs_points($eco)
{
    return floor(840.5*pow(0.985,$eco));
}

function Vpr_points($eco)
{
	return floor(24.5*pow(0.985,$eco));
//    return 24-floor(0.3*$eco);
}

function Drn_points($eco)
{
	return floor(2870.5*pow(0.985,$eco));
//    return 2870-floor(35.875*$eco);
}

function CS_points($eco)
{
    return 60;
}

function Tr_points($eco)
{
    return 60;
}

?>
