<?php
include_once("internal/html/node.php");
include_once("internal/html/html.php");

$N = new Node("b");
$N[] = 'Zupa pomidorowa';
$N[2] = 'Krupnik';
$N[3] = 'Woda';

$S = new HTML("Tytul");
$S[] = $N;

echo $S;

?>
