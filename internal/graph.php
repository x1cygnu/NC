<?php

include_once("internal/html.php");

function graphResults($entries, $width, $height) {
	$cnt=count($entries);
	$DD=new Div();
	$D=new Div();
	$H=new Div();
	$D->sStyle="position : relative; width : " . $width . "px";
	$current=0;
	for ($i=0; $i<$cnt; ++$i) {
		$entry=$entries[$i];
		$E = new Div();
		$currentInt=makeinteger($current);
		$E->sStyle="position : absolute; left : {$current}px; top : 0px;";
		$entryWidth=$entry['value']*$width;
		$entryWidthInt=makeinteger($entryWidth);
		if ($entry['value']>0 && $entryWidthInt<1) $entryWidthInt=1;
		$E->sStyle.=" width : ".$entryWidthInt."px;";
		$E->sStyle.=" height : ".$height."px;";
		$current+=$entryWidthInt;
		$E->sStyle.=" background: #" . $entry['color'];
		$D->Insert($E);
		$H->Insert($entry['name']);
		$H->Insert(new Br());
	}
	$DD->Insert($D);
	$HC=new Div();
	$HC->sStyle="left : 0px; top : 0px; width : {$width}px; height : {$height}px;";
	$HC->sClass='hinted1';
	$HC->Insert($H);
	$DD->Insert($HC);
	return $DD;
}
?>
