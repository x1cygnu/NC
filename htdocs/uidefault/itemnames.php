<?php

$ITEM_NAME = array (
    NC_ITEM_FARM => 'Hydrophonic Farm',

    NC_ITEM_MINE => 'Mineral Mine',
    NC_ITEM_CYBERNETIC_NETWORK => 'Cybernetic Network',
    NC_ITEM_LABORATORY => 'Laboratory',
    NC_ITEM_FUSION_PLANT => 'Fusion Power Plant',
    NC_ITEM_GEOTHERMAL_PLANT => 'Geothermal Power Plant',

    NC_ITEM_SOLAR_SATELLITE => 'Solar Sattelite',

    NC_ITEM_SHIP_LIGHT_FIGHTER => 'Light Fighter'

    );

function item_name($type) {
  return $GLOBALS['ITEM_NAME'][$type];
}

?>
