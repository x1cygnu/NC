<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/validator.php");

include_once("internal/building.php");

session_start();

global $GET;
$GET=array();

for ($i=0; $i<=50; ++$i) {
    echo '<tr><td class="nclegend">';
    echo $i;
    echo '</td>';
    echo '<td>' . Vpr_points($i) . '</td>';
    echo '<td>' . Int_points($i) . '</td>';
    echo '<td>' . Fr_points($i) . '</td>';
    echo '<td>' . Bs_points($i) . '</td>';
    echo '<td>' . Drn_points($i) . '</td>';
    echo '<td>' . CS_points($i) . '</td>';
    echo '<td>' . Tr_points($i) . "</td></tr>\n";
}

?>
