<?php

include_once('./internal/level.php');
include_once('./internal/items.php');

const PLANET_CORONA = 0;
const PLANET_GAIA = 1;

function planet_create($sql, $SID, $type) {
  return $sql->NC_PlanetCreate($SID, $type);
}

function planet_get_owner($sql, $PLID) {
  return $sql->NC_PlanetGetOwner($PLID);
}

class Item {
  public $baseCost;
  public $amount;
  public $currentCost;
  public function __construct($cost) {
    $this->baseCost = $cost;
    $this->amount = 0;
    $this->currentCost = $cost;
  }
  public function setBuildingLevel($level) {
    $this->amount = $level;
    $cutoff = 15;
    if ($level<$cutoff)
      $this->currentCost = pow(1.3,$level)*($cutoff-$level)/$cutoff+$level*log($level);
    else
      $this->currentCost = $cutoff * log($level);
    $this->currentCost *= $this->baseCost;
  }
}

class Planet {
  public $name;
  public $PLID;
  public $owner;

  public $pop;
  public $minerals;

  public $base = array();
  public $low = array();
  public $high = array();
  public $siege = array();

  public function __construct($sql, $PLID) {
    $header = $sql->NC_PlanetGet($PLID);
    $this->name = $header['Name'] . ' ' . $header['Orbit'];
    $this->PLID = intval($PLID);
    $this->owner = intval($header['Owner']);
    $this->pop = pop_get_all(intval($header['Pop']));
    $this->minerals = minerals_get_all(intval($header['Minerals']));

    $poss = $sql->get('NC_PlanetGetPossibleConstructs',$PLID);
    foreach ($poss as $item) {
      $type = intval($item['ItemType']);
      $cost = intval($item['BaseCost']);
      if (isItemBase($type))
        $this->base[$type] = new Item($cost);
      else if (isItemLowOrbit($type))
        $this->low[$type] = new Item($cost);
      else
        throw new NCException("Invalid item classification ($type)");
    }

    $lows = $sql->get('NC_LowOrbitGet',$PLID);
    foreach ($lows as $item) {
      $type = intval($item['ItemType']);
      $amount = intval($item['Amount']);
      if (isItemBase($type))
        $this->base[$type]->setBuildingLevel($amount);
      else if (isItemLowOrbit($type))
        $this->low[$type]->amount = $amount;
    }

    $highs = $sql->get('NC_ShipsAvailable',$this->owner);
    foreach ($highs as $ship) {
      $type = intval($ship['ItemType']);
      $cost = intval($ship['Cost']);
      $this->high[$type] = new Item($cost);
    }

    $highs = $sql->get('NC_HighOrbitGet',$PLID);
    foreach ($highs as $item) {
      $owner = intval($item['Owner']);
      $type = intval($item['ItemType']);
      $amount = intval($item['Amount']);
      if ($owner = $this->owner)
        $this->high[$type]->amount = $amount;
      else
        $this->siege[$owner][$type] = $amount;
    }

  }


};


?>
