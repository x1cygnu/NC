<?php


function InfoBoxAdd($field,$source,$value,$good)
{
    global $InfoBox;
    if (!isset($InfoBox[$field]))
	$InfoBox[$field]=array();
    $i=count($InfoBox[$field]);
    $InfoBox[$field][$i][1]=$source;
    $InfoBox[$field][$i][0]=$value;
    $InfoBox[$field][$i][2]=$good;
}

function InfoBoxCell($field)
{
    global $InfoBox;
    $C=new Div();
    $C->sClass='explain';
    if (!isset($InfoBox[$field]))
    {
    var_dump($InfoBox[$field]);
	return $C;
	}
    foreach ($InfoBox[$field] as $Source)
    {
	$P=new Paragraph();
	$P->Insert($Source[0] . ' ');
	$P->Insert('(' . $Source[1] . ')');
	if ($Source[2])
	    $P->sClass='positive';
	else
	    $P->sClass='negative';
	$C->Insert($P);
    }
    return $C;
}

$Pl=player_get_all($sql, $_SESSION['PID']);
$Art=player_get_artefact_use($sql, $_SESSION['PID']);

$Siege=false;
function planetCompute($P) {
  global $sql;
  global $WorkSTx;
  global $Siege;
  global $Pl;
  global $Art;

  $InfoBox=array();
  $Return=array();

  if ($P['FleetOwner']!=0 and $P['FleetOwner']!=$P['Owner'])
    $Siege=true;

  // P = planet_get_all

  ///////////////////////////////////////////
  //Pollution (Toxic)
  ///////////////////////////////////////////

  $Tx=floor($P['STx']/1000);
  $TxGo=$P['STx']%1000;
  $TxRemain=1000-$TxGo;

  $TxH=0;

  $TxHPop=$WorkSTx['Population']*$P['Population'];
  $TxH+=$TxHPop;
  InfoBoxAdd('Toxic','population '.$P['Population'].'x'.$WorkSTx['Population'],'+'.$TxHPop,false);

  $TxHFrm=$WorkSTx['Farm']*$P['Farm'];
  $TxH+=$TxHFrm;
  if ($TxHFrm>0)
    InfoBoxAdd('Toxic','farm '.$P['Farm'].'x'.$WorkSTx['Farm'],'+'.$TxHFrm,false);

  $TxHFct=$WorkSTx['Factory']*$P['Factory'];
  $TxH+=$TxHFct;
  if ($TxHFct>0)
    InfoBoxAdd('Toxic','factory '.$P['Factory'].'x'.$WorkSTx['Factory'],'+'.$TxHFct,false);

  $TxHCyb=$WorkSTx['Cybernet']*$P['Cybernet'];
  $TxH+=$TxHCyb;
  if ($TxHCyb>0)
    InfoBoxAdd('Toxic','cybernet '.$P['Cybernet'].'x'.$WorkSTx['Cybernet'],'+'.$TxHCyb,false);

  $TxHLab=$WorkSTx['Lab']*$P['Lab'];
  $TxH+=$TxHLab;
  if ($TxHLab>0)
    InfoBoxAdd('Toxic','lab '.$P['Lab'].'x'.$WorkSTx['Lab'],'+'.$TxHLab,false);

  if ($Pl['Urban']>0)
  {
    $TxHUrb=pow(0.9995,$Pl['Urban']*$Pl['Urban']);
    $TxH*=$TxHUrb;
    InfoBoxAdd('Toxic','urban '.$Pl['Urban'],'*'.ceil($TxHUrb*100).'%',true);    
  }

  $TxHRef=$WorkSTx['Refinery']*$P['Refinery'];
  $TxH+=$TxHRef;
  if ($TxHRef<0)
    InfoBoxAdd('Toxic','refinery '.$P['Refinery'].'x'.$WorkSTx['Refinery'],''.$TxHRef,true);

  $TxH-=40;    
  InfoBoxAdd('Toxic','biosphere','-40',true);


  if ($P['ToxicStability']!=100)
  {
    $TxH*=$P['ToxicStability']/100.0;
    InfoBoxAdd('Toxic','planet','*'.$P['ToxicStability'].'%',$P['ToxicStability']<100);
  }

  $TxHRef2=-1*$P['Refinery'];
  $TxH+=$TxHRef2;
  if ($TxHRef<0)
    InfoBoxAdd('Toxic','refinery '.$P['Refinery'].'x -1',''.$TxHRef2,true);


  if ($TxH>0.1)
    $TxTRemain=floor($TxRemain*3600/$TxH);
  elseif ($TxH<-0.1)
    $TxTRemain=floor($TxGo*3600/(-$TxH));
  else
    $TxTRemain=0;

  $TxImpact=pow(0.995,$Tx);
  ///////////////////////////////////////////
  // Population
  ///////////////////////////////////////////

  $Pop=$P['Population'];
  $PopRemain=ceil($P['PopulationRemain']);
  $PopMax=growth_points_for_lvl($Pop+1);
  $PopGo=$PopMax-$PopRemain;

  if ($Siege)
  {
    $PopH=0;
    InfoBoxAdd('Population','siege','0',false);
    $PopTRemain=-1;
  }
  else
  {
    $PopH=1;
    InfoBoxAdd('Population','natural','+1',true);

    $PopHFrm=makeinteger($P['Farm']);
    $PopH+=$PopHFrm;
    if ($PopHFrm>0)
      InfoBoxAdd('Population','farm '.$P['Farm'],'+'.$PopHFrm,true);

    $PopBase=$PopH;

    $PopHGr=Growth($Pl['Growth']);
    $PopH*=$PopHGr/100.0;
    if ($PopHGr!=100)
      InfoBoxAdd('Population','growth '.sprintf("%+d",$Pl['Growth']),'*'.$PopHGr.'%',$PopHGr>100);

    $PopH*=$TxImpact;
    if ($Tx>0)
      InfoBoxAdd('Population','toxic '.$Tx,'*'.floor($TxImpact*100).'%',false);

    $TA=floor($Pl['TA']);
    $PopH*=(1+$Pl['TA']/100);
    if ($TA>0)
      InfoBoxAdd('Population','TA '.$TA,'*'.(100+$TA).'%',true);

    if ($Pl['Urban']!=$Pop)
    {
      $PopHUrb=$Pl['Urban']-$Pop;
      $PopH*=(1+$PopHUrb/100.0);
      InfoBoxAdd('Population','urban '.$Pl['Urban'],sprintf("*%d%%",100+$PopHUrb),$PopHUrb>0);    
    }

    $PopH+=$PopBase*($Art['Growth']/100);
    if ($Art['Growth']!=0)
      InfoBoxAdd('Population','artefact','+'.$Art['Growth'].'%',$Art['Growth']>0);

    if ($P['Growth']!=100) {
      $PopH*=$P['Growth']/100.0;
      InfoBoxAdd('Population','planet','*'.$P['Growth'].'%',$P['Growth']>=100);
    }

    $PopTRemain=ceil($PopRemain*3600/$PopH);
  }

  /////////////////////////////////////////
  // Production Points
  /////////////////////////////////////////
  $PP=floor($P['PP']);

  $ProdMod=1.0;

  if ($Siege)
  {
    $PPH=0;
    InfoBoxAdd('PP','siege','0',false);
  }
  else
  {
    $PPH=$Pop;
    InfoBoxAdd('PP','population','+'.$Pop,true);

    $PPFct=makeinteger($P['Factory']);
    if ($PPFct>0)
    {
      $PPH+=$PPFct;
      InfoBoxAdd('PP','factory','+'.$PPFct,true);
    }

    $PPBase=$PPH;

    $PPHPrd=Production($Pl['Production']);
    $PPH*=$PPHPrd/100.0;
    $ProdMod*=$PPHPrd/100.0;
    if ($PPHPrd!=100)
      InfoBoxAdd('PP','production '.sprintf("%+d",$Pl['Production']),'*'.$PPHPrd.'%',$PPHPrd>100);

    if ($Tx>0)
    {
      $PPH*=$TxImpact;
      $ProdMod*=$TxImpact;
      InfoBoxAdd('PP','toxic '.$Tx,'*'.floor($TxImpact*100).'%',false);
    }

    $PPH*=(1+$Pl['TA']/100);
    $ProdMod*=(1+$Pl['TA']/100);
    if ($TA>0)
      InfoBoxAdd('PP','TA '.$TA,'*'.(100+$TA).'%',true);

    $PPH+=$PPBase*($Art['Production']/100);
    $ProdMod+=($Art['Production']/100);
    if ($Art['Production']!=0)
      InfoBoxAdd('PP','artefact','+'.$Art['Production'].'%',$Art['Production']>0);

    if ($P['Production']!=100) {
      $PPH*=$P['Production']/100.0;
      InfoBoxAdd('PP','planet','*'.$P['Production'].'%',$P['Production']>100);
    }
    if ($PPH>0)
      $Return['PP1Time']=ceil(3600/$PPH);

  }//end not siege case

  planet_mark_output($sql, $P['PLID'], $PopH, $PPH);

  $Return['Pop'] = $Pop;
  $Return['PopGo'] = $PopGo;
  $Return['PopMax'] = $PopMax;
  $Return['PopH'] = $PopH;
  $Return['PopTRemain'] = $PopTRemain;

  $Return['Tx'] = $Tx;
  $Return['TxGo'] = $TxGo;
  $Return['TxH'] = $TxH;
  $Return['TxTRemain'] = $TxTRemain;

  $Return['PP'] = $PP;
  $Return['PPH'] = $PPH;
  $Return['PPMod'] = $ProdMod;


  return $Return;
}

?>
