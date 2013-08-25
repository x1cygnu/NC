<?php

//background semi-class

function background_show_range(&$sql, $pid, $x, $y, $range)
{
    $pid=makeinteger($pid);
    $x=makeinteger($x);
    $y=makeinteger($y);
    $range=makeinteger($range);
    if ($pid!=0)
    {
    return $sql->query("SELECT BG.X, BG.Y, BGI.File, " . 
			" IF (" .
			    "ABS(BG.X-(SELECT M.X FROM NC_Map M WHERE M.SID=(SELECT HomeSID FROM NC_Player WHERE PID=$pid)))" .
			    "<=(SELECT FLOOR(Sensory/2) FROM NC_Player WHERE PID=$pid)" .
			" AND " .
			    "ABS(BG.Y-(SELECT M.Y FROM NC_Map M WHERE M.SID=(SELECT HomeSID FROM NC_Player WHERE PID=$pid)))" .
			    "<=(SELECT FLOOR(Sensory/2) FROM NC_Player WHERE PID=$pid)" .
			" , 1, 0) AS InRange " .
			" FROM NC_Background BG JOIN NC_BackgroundList BGI " .
				" ON BGI.BGID=BG.BGID WHERE " .
			" BG.x>=$x-$range AND BG.x<=$x+$range AND" .
			" BG.y>=$y-$range AND BG.y<=$y+$range");
    }
    else
    {
    return $sql->query("SELECT BG.X, BG.Y, BGI.File " . 
			" FROM NC_Background BG JOIN NC_BackgroundList BGI " .
				" ON BGI.BGID=BG.BGID " .
			"WHERE" .
			" BG.x>=$x-$range AND BG.x<=$x+$range AND" .
			" BG.y>=$y-$range AND BG.y<=$y+$range");
    }    
}

function background_view_image(&$sql, $BaseID)
{
    $BaseID=makeinteger($BaseID);
    return $sql->query("SELECT X, Y, File FROM NC_BackgroundList WHERE BaseID=$BaseID");
}

function background_get_image(&$sql, $BaseID)
{
    $BaseID=makeinteger($BaseID);
    $Rs=$sql->query("SELECT * FROM NC_BackgroundList WHERE BaseID=$BaseID");
    foreach ($Rs as $R)
    {
	$Ans[$R['X']][$R['Y']]=$R['BGID'];
    }
    return $Ans;
}

function background_put(&$sql, $x, $y, $BaseID)
{
    $x=makeinteger($x);
    $y=makeinteger($y);
    $BaseID=makeinteger($BaseID);
    $sql->query("REPLACE INTO NC_Background SELECT L.X+$x, L.Y+$y, L.BGID FROM NC_BackgroundList L WHERE L.BaseID=$BaseID");
}

function background_correct(&$sql, $BaseID)
{
    echo "Correcting!";
    $BaseID=makeinteger($BaseID);
    $Cnt=$sql->query("SELECT count(*) AS C FROM NC_BackgroundList WHERE BaseID=$BaseID AND Y=0");
    echo $Cnt[0]['C'];
    if ($Cnt[0]['C']==0)
	$sql->query("UPDATE NC_BackgroundList SET Y=Y-1 WHERE BaseID=$BaseID");
    $Cnt=$sql->query("SELECT count(*) AS C FROM NC_BackgroundList WHERE BaseID=$BaseID AND X=0");
    if ($Cnt[0]['C']==0)
	$sql->query("UPDATE NC_BackgroundList SET X=X-1 WHERE BaseID=$BaseID");
}

function background_new_range(&$sql, $xfrom, $yfrom, $xto, $yto)
{
    return $sql->query("SELECT * FROM NC_NewBackground Bg JOIN NC_NewBackgroundList L ON L.NBgX=Bg.NBgX"
	. " WHERE X2>=$xfrom AND Y2>=$yfrom AND X1<=$xto AND Y1<=$yto");
}
?>