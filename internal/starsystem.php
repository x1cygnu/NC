<?php

function starsystem_cluster_create($sql, $x, $y, $ring, $numSattelite) {
  try {
    $sql->NC_StarsystemCreate($ring, $x, $y, mt_rand(11,14), now());
    for ($n=1; $n<=$numSattelite; ++$n) {
      $sql->NC_StarsystemCreate($ring, $x+mt_rand(-2,+2), $y+mt_rand(-2,+2), mt_rand(3,5), now()+3600*24*7*$n);
    }
  } catch (SQLDuplicateKeyException $e) {
    //stars overlap - ignore the error
  }
}

function starsystem_spawn_ring($sql) {
  $ring = $sql->getGlobal('RingLevel');
  if ($ringlvl==0) {
    //make special systems
  } else {
    $translation=0;
    for ($i=0; $i<$ringlvl; ++$i)
      $translation+=0.25*PI/($i+1);

    $radfrom = -PI/pow(($ring+20)/10.0,2.3)+$translation;
    $radto   =  PI/pow(($ring+20)/10.0,2.3)+$translation;
    $raddiff = $radto - $radfrom;
    if ($raddiff>PI)
      $raddiff=PI;

    $numstars = floor($raddiff * $ring);
    if ($numstars == 0)
      $numstars=1;

    for ($i=0; $i<$ringnumofstars; ++$i) {
      $angle=$radfrom+($raddiff*$i)/$ringnumofstars;
      $x=round($ring*3*cos($angle)+mt_rand(-1,+1));
      $y=round($ring*3*sin($angle)+mt_rand(-1,+1));
      $sattelite=mt_rand(0,1);
      if ($i==0) $sattelite+=2;
      if ($i==$numstars-1) $sattelite+=1;
      starsystem_cluster_create($sql,$x,$y,$ring,'','',$sattelite,false);
    }

    for ($i=0; $i<$ringnumofstars; ++$i) {
      $angle=$radfrom+($raddiff*$i)/$ringnumofstars;
      $x=-round($ring*3*cos($angle)+mt_rand(-1,+1));
      $y=-round($ring*3*sin($angle)+mt_rand(-1,+1));
      $sattelite=mt_rand(0,1);
      if ($i==0) $sattelite+=2;
      if ($i==$numstars-1) $sattelite+=1;
      starsystem_cluster_create($sql,$x,$y,$ring,'','',$sattelite,false);
    }

  }

  $sql->setGlobal('RingLevel',$ring+1);
}

?>
