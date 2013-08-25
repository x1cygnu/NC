<?php
include_once("./internal/player.php");
include_once("./internal/log.php");
//technology semi-class

function tech_get_tech(&$sql, $pid)
{
    $pid=makeinteger($pid);
    $PlayerTechs=$sql->query("SELECT Technology FROM NC_Technology WHERE PID=$pid ORDER BY Technology");
    $PT=array();
    foreach($PlayerTechs as $PTC)
    {
	$PT[]=$PTC['Technology'];
    }
    return $PT;
}

function tech_get_player_names(&$sql, $pid)
{
    $pid=makeinteger($pid);
    $PlayerTechs=$sql->query("SELECT L.Help FROM NC_Technology T JOIN NC_TechList L ON T.Technology=L.TechID WHERE PID=$pid ORDER BY L.TechID");
    $PT=array();
    foreach($PlayerTechs as $PTC)
    {
	$PT[]=$PTC['Help'];
    }
    return $PT;
}

function tech_check(&$PT, $techID)
{
    $techID=makeinteger($techID);
    return in_array($techID,$PT);
}

function tech_check_name(&$PT, $techName)
{
    return in_array($techName,$PT);
}

function tech_check_player(&$sql, $pid, $techID)
{
$pid=makeinteger($pid);
$techID=makeinteger($techID);
$V=$sql->query("SELECT count(*) AS C FROM NC_Technology WHERE PID=$pid AND Technology=$techID");
return ($V[0]['C']>0);
}

function tech_get_list(&$sql, $pid)
{
    $pid=makeinteger($pid);
    $TechList=$sql->query("SELECT * FROM NC_TechList");
    $PlayerTech=tech_get_tech($sql, $pid);
    $P=player_get_sciences(&$sql, $pid);
    $PT=0;

    foreach ($TechList as $T)
    {
	$K=makeinteger($T['TechID']);
	$TechListC[$K]=$T;
	$TechListC[$K]['TechID']=$K;
	$MBD=true; //May Be Developped
	if ($T['TechID']==$PlayerTech[$PT])
	    {
	    $TechListC[$K]['Status']=2;	//already developped
	    ++$PT;	//move cursor to next
	    }
	else
	{
	if ($T['Sensory']>$P['Sensory'])
	    {$MBD=false; $TechListC[$K]['Error'][1]=true;}
	if ($T['Engineering']>$P['Engineering'])
	    {$MBD=false; $TechListC[$K]['Error'][2]=true;}
	if ($T['Warp']>$P['Warp'])
	    {$MBD=false; $TechListC[$K]['Error'][3]=true;}
	if ($T['Physics']>$P['Physics'])
	    {$MBD=false; $TechListC[$K]['Error'][4]=true;}
	if ($T['Mathematics']>$P['Mathematics'])
	    {$MBD=false; $TechListC[$K]['Error'][5]=true;}
	if ($T['Urban']>$P['Urban'])
	    {$MBD=false; $TechListC[$K]['Error'][6]=true;}
	if ($T['Tech1']>0 and (!tech_check($PlayerTech,$T['Tech1'])))
	    {$MBD=false; $TechListC[$K]['Error'][7]=true;}
	if ($T['Tech2']>0 and (!tech_check($PlayerTech,$T['Tech2'])))
	    {$MBD=false; $TechListC[$K]['Error'][8]=true;}
	if ($MBD==true)
	    $TechListC[$K]['Status']=1;	//may be developped
	else
	    $TechListC[$K]['Status']=0;	//may not be developped
	}
    }
    return $TechListC;
}

function tech_update(&$sql, $pid, $amount)
{
    $pid=makeinteger($pid);
    $amount=makereal($amount);
    $P=$sql->query("SELECT TechDevelop, TechSelected, TechRemain FROM NC_Player WHERE PID=$pid");
    if ($P[0]['TechDevelop']==0)
	return $amount;
    $namount=$P[0]['TechRemain']-$amount;
    if ($namount<0)
	{
	    $sql->query("INSERT INTO NC_Technology VALUES ($pid, {$P[0]['TechSelected']})");
	    $sql->query("UPDATE NC_Player SET TechDevelop=0, TechSelected=0, TechRemain=0 WHERE PID=$pid");
	    return -$namount;
	}
    $sql->query("UPDATE NC_Player SET TechRemain=$namount WHERE PID=$pid");
    return 0;
}

function tech_get_info(&$sql, $techID)
{
    $techID=makeinteger($techID);
    $N=$sql->query("SELECT * FROM NC_TechList WHERE TechID=$techID");
    return $N[0];
}

function tech_halt(&$sql, $pid)
{
    $pid=makeinteger($pid);
    $sql->query("UPDATE NC_Player SET TechDevelop=0 WHERE PID=$pid");
}

function tech_continue(&$sql, $pid)
{
    $pid=makeinteger($pid);
    $sql->query("UPDATE NC_Player SET TechDevelop=1 WHERE PID=$pid");
}

function tech_cancel(&$sql, $pid)
{
    $pid=makeinteger($pid);
    $sql->query("UPDATE NC_Player SET TechDevelop=0, TechSelected=0, TechRemain=0 WHERE PID=$pid");
}

function tech_select(&$sql, $pid, $tech, $sci, $AT)
{
    $tech=makeinteger($tech);
    $sci=makeinteger($sci);
    $AT=makeinteger($AT);
    $pid=makeinteger($pid);
    $ATP=player_get_AT($sql, $pid);
    if ($ATP<$AT)
	return "Not enough money";
    player_spend_AT($sql, $pid, $AT);
    $sql->query("UPDATE NC_Player SET TechDevelop=1, TechSelected=$tech, TechRemain=$sci WHERE PID=$pid");
    return "";
}

?>