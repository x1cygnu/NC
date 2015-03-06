<?php
include_once('./internal/planet.php');

const STAR_NORMAL = 0;
const STAR_SPECIAL = 1;
const STAR_SATTELITE = 2;

function starsystem_cluster_create($sql, $x, $y, $ring, $numSattelite) {
  try {
    $sql->NC_StarsystemCreate($ring, $x, $y, STAR_NORMAL, mt_rand(11,14), now());
    for ($n=1; $n<=$numSattelite; ++$n) {
      $sql->NC_StarsystemCreate($ring, $x+mt_rand(-2,+2), $y+mt_rand(-2,+2), STAR_SATTELITE, mt_rand(3,5), now()+3600*24*7*$n);
    }
  } catch (SQLDuplicateKeyException $e) {
    //stars overlap - ignore the error
  }
}

function starsystem_special_create($sql, $x, $y, $name) {
  $SID=$sql->NC_StarsystemCreateSpecial($x, $y, 12, now(), STAR_SPECIAL);
  planet_create($SID, PLANET_CORONA);
  planet_create($SID, PLANET_GAIA);
  planet_create($SID, PLANET_GAIA);
  planet_create($SID, PLANET_GAIA);
  planet_create($SID, PLANET_GAIA);
  planet_create($SID, PLANET_GAIA);
  planet_create($SID, PLANET_GAIA);
  planet_create($SID, PLANET_GAIA);
  planet_create($SID, PLANET_GAIA);
  planet_create($SID, PLANET_GAIA);
  planet_create($SID, PLANET_GAIA);
  planet_create($SID, PLANET_GAIA);
}

function starsystem_spawn_ring($sql) {
  $ring = $sql->getGlobal('RingLevel');
  if ($ringlvl==0) {
    starsystem_special_create($sql, 7, -5, "Albireo A");
    starsystem_special_create($sql, -7, 5, "Albireo B");
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
      starsystem_cluster_create($sql,$x,$y,$ring,$sattelite);
    }

    for ($i=0; $i<$ringnumofstars; ++$i) {
      $angle=$radfrom+($raddiff*$i)/$ringnumofstars;
      $x=-round($ring*3*cos($angle)+mt_rand(-1,+1));
      $y=-round($ring*3*sin($angle)+mt_rand(-1,+1));
      $sattelite=mt_rand(0,1);
      if ($i==0) $sattelite+=2;
      if ($i==$numstars-1) $sattelite+=1;
      starsystem_cluster_create($sql,$x,$y,$ring,$sattelite);
    }
  }

  $sql->setGlobal('RingLevel',$ring+1);
}

function starsystem_spawn_planets_for_player() {
  $sql->query("START TRANSACTION");
  $try = 0;
  do {
    $SID = $sql->NC_FindEmptySystem(now());
    if (empty($SID)) {
      ++$try;
      if ($try>2) {
        $sql->query("ROLLBACK");
        throw NCException("Could not find suitable planet");
      }
      starsystem_spawn_ring($sql);
    }
  } while (empty($SID))
  $size = NC_StarsystemSize($SID);
  if ($size<1)
    planet_create($SID, PLANET_CORONA);
  if ($size<3) {
    planet_create($SID, PLANET_GAIA);
    planet_create($SID, PLANET_GAIA);
  }
  if ((mt_rand(1,10)<4) and $size<6)
    planet_create($SID, PLANET_GAIA);
  planet_create($SID, PLANET_GAIA);
  $here = planet_create($SID, PLANET_GAIA);
  planet_create($SID, PLANET_GAIA);
  if ($mt_rand(1,10)<4)
    planet_create($SID, PLANET_GAIA);
  $sql->query("COMMIT");
  return $here;
}

?>
