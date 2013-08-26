<?php

set_time_limit(0);


include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/validator.php");

include_once("internal/core.php");

if ($argc!=2 or $argv[1]!='321HakunaMatataDupad12')
{
    $H = new HTML();
    $H->AddStyle("default.css");
    $H->Insert(new Error("What are you looking at?"));
    $H->Insert("I just hope that if you do find a hole, you will report it");
    include("part/mainsubmenu.php");
    $H->Draw();
    die;
}

$sql=&OpenSQL();
core_launch($sql);
CloseSQL($sql);

