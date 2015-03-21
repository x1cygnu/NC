<?php

const PLANET_CORONA = 0;
const PLANET_GAIA = 1;

function planet_create($sql, $SID, $type) {
  return $sql->NC_PlanetCreate($SID, $type);
}

function planet_get_owner($sql, $PLID) {
  return $sql->NC_PlanetGetOwner($PLID);
}

class Planet {
  public $name;
  public $PLID;

  public $pop;

  public $low;
  public $high;

  public function __construct($sql, $PLID) {
    $header = $sql->NC_PlanetGet($PLID);
    $this->name = $header['Name'] . ' ' . $header['Orbit'];
    $this->PLID = intval($PLID);
    $this->pop = intval($header['Pop']);

    $lows = $sql->get('NC_LowOrbitGet',$PLID);
    foreach ($lows as $item) {
      $this->low[intval($item['ItemType'])] = intval($item['Amount']);
    }

    $highs = $sql->get('NC_HighOrbitGet',$PLID);
    foreach ($highs as $item) {
      $this->high[intval($item['Owner'])][intval($item['ItemType'])] = intval($item['Amount']);
    }
  }

  public function getGround($item) {
    return get_or_default($this->ground[$item][0],0);
  }

  public function getGroundProgress($item) {
    return get_or_default($this->ground[$item][1],0);
  }

};


?>
