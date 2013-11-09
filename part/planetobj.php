<?php

$hintstr="hinted" . $_SESSION['Hint'];
$subhint="sublegend " . $hintstr;

function prepObj($prefx) {
  $Config = new EntryConfig();
  $Config->objectDiv = $main = new Div();
  $main->sClass = "{$prefx}object";
  $Config->titleDiv = $title = new Div();
  $title->sClass = "legend {$prefx}title";
  $main->Place($title);
  $Config->valueDiv = $value = new Div();
  $value->sClass = "levelnum {$prefx}value";
  $main->Place($value);
  $Config->progressDiv = $progress = new Div();
  $progress->sClass = "additional {$prefx}progress";
  $main->Place($progress);
  $Config->incomeDiv = $income = new Span();
  $income->sClass = "additional {$prefx}income";
  $main->Place($income);
  $Config->timeremDiv = $timerem = new Span();
  $timerem->sClass = "additional {$prefx}timerem";
  $main->Place($timerem);
  $Config->detailDiv = $detail = new Div();
  $detail->sClass = "additional {$prefx}detail";
  $main->Place($detail);
  return $Config;
}

function prepBld($prefx) {
  $Config = new EntryConfig();
  $Config->objectDiv = $main = new Div();
  $main->sClass = "{$prefx}object";
  $Config->titleDiv = $title = new Div();
  $title->sClass = "legend {$prefx}title";
  $main->Place($title);
  $Config->valueDiv = $value = new Div();
  $value->sClass = "levelnum {$prefx}value";
  $main->Place($value);
  $Config->progressDiv = $progress = new Div();
  $progress->sClass = "additional {$prefx}progress";
  $main->Place($progress);
  $Config->buildDiv = $build = new Div();
  $build->sClass = "additional {$prefx}build";
  $main->Place($build);
  $Config->spendDiv = $spend = new Div();
  $spend->sClass = "additional {$prefx}spend hiding";
  $main->Place($spend);
  return $Config;
}

function prepCnstr($prefx) {
  $Config = new EntryConfig();
  $Config->objectDiv = $main = new Div();
  $main->sClass = "{$prefx}object";
  $Config->titleDiv = $title = new Div();
  $title->sClass = "{$prefx}title";
  $main->Place($title);
  $Config->valueDiv = $value = new Div();
  $value->sClass = "{$prefx}value hiding";
  $main->Place($value);
  $Config->spendDiv = $value;
  return $Config;
}

?>

