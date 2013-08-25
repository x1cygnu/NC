<?php

function map_gen(&$sql, $pid, $x, $y, $range, $selfPage)
{
	$pid=makeinteger($pid);
	$x=makeinteger($x);
	$y=makeinteger($y);
	$range=makeinteger($range);

	$M=new Div();
	$mapWidth=(2*$range+1)*25;
	$mapHeight=(2*$range+1)*25;
	$M->sStyle="width : " . $mapWidth . "px; height : " . $mapHeight . "px;";
	$M->sClass="map";
	$M->sId='map';

	$hrange=$range/2;
	$newl=$x-$hrange;
	$newt=$y-$hrange;
	$newr=$x+$hrange;
	$newb=$y+$hrange;
	$xfrom=$x-$range;
	$xto=$x+$range;
	$yfrom=$y-$range;
	$yto=$y+$range;

	$T=new Table();
	$T->sClass='mapborder';


	$T->Insert(1,1,new Link($selfPage . "?x=$newl&y=$newt&r=$range",
				new Image('IMG/tlb.png',"TL")));
	$T->Insert(1,3+2*$range,new Link($selfPage . "?x=$newl&y=$newb&r=$range",
				new Image('IMG/blb.png',"BL")));

	$T->Insert(3+2*$range,1,new Link($selfPage . "?x=$newr&y=$newt&r=$range",
				new Image('IMG/trb.png',"TR")));
	$T->Insert(3+2*$range,3+2*$range,new Link($selfPage . "?x=$newr&y=$newb&r=$range",
				new Image('IMG/brb.png',"BR")));

	$T->aRowClass[1]="legend";
	$T->aRowClass[$range*2+3]="legend";
	for ($tx = 1; $tx <= $range*2+1; $tx++)
	{
		if ($tx==$range+1)
		{
			$T->Insert(1+$tx,1,new Link($selfPage . "?x=$x&y=$newt&r=$range",
						new Image('IMG/tb.png',"up")));
			$T->Insert(1+$tx,3+$range*2,new Link($selfPage . "?x=$x&y=$newb&r=$range",
						new Image('IMG/bb.png',"down")));
			$T->Insert(1,$tx+1,new Link($selfPage . "?x=$newl&y=$y&r=$range",
						new Image('IMG/lb.png',"left")));
			$T->Insert(3+$range*2,$tx+1,new Link($selfPage . "?x=$newr&y=$y&r=$range",
						new Image('IMG/rb.png',"right")));
		}
		else
		{
			$T->Insert(1+$tx,1,$xfrom+$tx-1 . "");
			$T->Insert(1,1+$tx,$yfrom+$tx-1 . "");
			$T->Insert($range*2+3,1+$tx,$yfrom+$tx-1 . "");
			$T->Insert(1+$tx,$range*2+3,$xfrom+$tx-1 . "");
		}
		$T->SetClass(1,1+$tx,"legend");
		$T->SetClass($range*2+3,1+$tx,"legend");
	}

	$T->Join(2,2,$range*2+1,$range*2+1);


	function map_get_apply_view($vx, $vy, $vrange, $xfrom, $xto, $yfrom, $yto, $M) {
		$vxfrom=$vx-$vrange;
		$vxto=$vx+$vrange+1;
		$vyfrom=$vy-$vrange;
		$vyto=$vy+$vrange+1;
		TrimDown($vxfrom,$xfrom);
		TrimUp($vxto,$xto+1);
		TrimDown($vyfrom,$yfrom);
		TrimUp($vyto,$yto+1);

		if ($vxfrom<$vxto && $vyfrom<$vyto) {
			$D=new Div();
			$D->sClass='viewbox';
			$D->sStyle="left : " . (($vxfrom-$xfrom)*25)
				. "px; top : " . (($vyfrom-$yfrom)*25)
				. "px; width : " . (($vxto-$vxfrom)*25)
				. "px; height : " . (($vyto-$vyfrom)*25)
				. "px;";
			$M->Insert($D);
		}
	}

	$R=player_get_bio_ranges($sql, $pid);

	foreach ($R as $ran)
		map_get_apply_view($ran['X'], $ran['Y'], $ran['Range'], $xfrom, $xto, $yfrom, $yto, $M);

	$Ss=starsystem_show_range_advanced($sql, $_SESSION['PID'], $x, $y, $range);
	$Now=EncodeNow();
	foreach ($Ss as $S) {
		$D=new Div();
		$D->sClass='star';
		$D->sStyle="left : " . (($S['X']-$xfrom)*25) . "px; top : " . (($S['Y']-$yfrom)*25) . "px;";
		if ($S['Special'])
			$StarImg=new Image("IMG/ssp.gif",$S['Name']);
		elseif ($S['BeginSpawnTime']>$Now)
			$StarImg=new Image("IMG/sn2.png",$S['Name']);
		else {
			$StarImg=new Image("IMG/s" . ($S['Level']) . ".png",$S['Name']);
		}
		if ($S['InRange'])
			$D->Insert(new Link("detail.php?id={$S['SID']}",$StarImg));
		else
			$D->Insert($StarImg);
		$D->onMouseOver("mIn({$S['SID']},'{$S['Name']}',{$S['X']},{$S['Y']},{$S['Level']})");
		$D->onMouseOut("mOut({$S['SID']},'{$S['Name']}',{$S['X']},{$S['Y']},{$S['Level']})");
		$M->Insert($D);
	}

	if ($_SESSION['MapBackground']) {    
		$Bgs=background_new_range($sql, $xfrom, $yfrom, $xto, $yto);
		foreach ($Bgs as $Bg) {
			$D=new Div();
			$D->sClass='background';
			$transparecyString="";
			if ($Bg['Transparency']!=0)
				$transparencyString="opacity : 0." . $Bg['Transparency'] . "; filter : alpha(opacity=" . $Bg['Transparency'] . ");";
			$D->sStyle="left : " . (($Bg['X1']-$xfrom)*25)
				. "px; top : " . (($Bg['Y1']-$yfrom)*25)
				. "px; z-index : " . $Bg['Z'] . "; "
				. $transparencyString;
			$D->Insert(new Image("IMG/bg/" . $Bg['FileName']));
			$M->Insert($D);
		}
	}
	$T->Insert(2,2,$M);
	return $T;
}

function map_gen_get($T)
{
	return $T->Get(2,2)->aLines[0];
}

?>
