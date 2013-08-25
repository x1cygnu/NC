<?php

	$H->AddStyle("race.css");

function D(&$T, $y, $mod, $val,$skipInput)
{
    $lvl=$val+6;
    $T->Insert($lvl,$y,eval("return $mod($val);") . sprintf("%%<br>(%+d)",$val));
    $T->SetClass($lvl,$y,"race{$lvl}");
    if ($skipInput===true); else
    {
    $I=new Input("radio",$mod,"$val");
    if ($val==0)
	$I->bChecked=true;
    $T->Insert($lvl,$y,$I);
    }
}

	$T=new Table();
	$T->Insert(1,1,"Growth:"); $T->SetClass(1,1,"racelegend");
	for ($i=-4; $i<=4; ++$i) D($T,1,"Growth",$i,$skipInput);
	$T->Insert(1,2,"Science:"); $T->SetClass(1,2,"racelegend");
	for ($i=-4; $i<=4; ++$i) D($T,2,"Science",$i,$skipInput);
	$T->Insert(1,3,"Culture:"); $T->SetClass(1,3,"racelegend");
	for ($i=-4; $i<=4; ++$i) D($T,3,"Culture",$i,$skipInput);
	$T->Insert(1,4,"Production:"); $T->SetClass(1,4,"racelegend");
	for ($i=-4; $i<=4; ++$i) D($T,4,"Production",$i,$skipInput);
	$T->Insert(1,5,"Speed:"); $T->SetClass(1,5,"racelegend");
	for ($i=-4; $i<=4; ++$i) D($T,5,"Speed",$i,$skipInput);
	$T->Insert(1,6,"Attack:"); $T->SetClass(1,6,"racelegend");
	for ($i=-4; $i<=4; ++$i) D($T,6,"Attack",$i,$skipInput);
	$T->Insert(1,7,"Defence:"); $T->SetClass(1,7,"racelegend");
	for ($i=-4; $i<=4; ++$i) D($T,7,"Defence",$i,$skipInput);

	if ($skipInput)
	  $H->Insert($T);
	else
	{

	  $F=new Form("race.php",true);
	  $F->Insert($T);
	  $F->Insert(new Input("submit","Create","Create","smbutton"));

	  $H->Insert($F);
	}
?>
