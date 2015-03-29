<?php

const NC_ITEM_INVALID = 0;

// 1..1000 buildings

const NC_ITEM_BASE_START = 1;

const NC_ITEM_FARM = 1;
const NC_ITEM_MINE = 2;
const NC_ITEM_CYBERNETIC_NETWORK = 3;
const NC_ITEM_LABORATORY = 5;
const NC_ITEM_FUSION_PLANT = 6;
const NC_ITEM_GEOTHERMAL_PLANT = 7;

const NC_ITEM_BASE_END = 1000;

// 1001..2000 inventory

// 2001..3000 low orbit

const NC_ITEM_LOW_START = 2001;

const NC_ITEM_SOLAR_SATELLITE = 2001;

const NC_ITEM_LOW_END = 3000;

// 3001..4000 high orbit/fleet

const NC_ITEM_SHIP_START = 3001;

const NC_ITEM_SHIP_LIGHT_FIGHTER = 3001;

const NC_ITEM_SHIP_END = 3010;

function isItemBase($type) {
  return $type>=NC_ITEM_BASE_START and $type<NC_ITEM_BASE_END;
}
function isItemLowOrbit($type) {
  return $type>=NC_ITEM_LOW_START and $type<NC_ITEM_LOW_END;
}

?>
