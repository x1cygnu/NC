<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/validator.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");

get("regcode","string");
$succeed=false;

if (exists($GET['regcode']))
{
    $sql=&OpenSQL($H);
    $regcode=makequotedstring($GET['regcode']);
    $C=$sql->query("SELECT count(*) AS C FROM NC_Regcode WHERE Code=$regcode");
    if ($C[0]['C']==1)
    {
	$succeed=true;
	$sql->query("DELETE FROM NC_Regcode WHERE Code=$regcode");
        $H->Insert(new Info("Your account has been activated"));
        $delay=1000;
    }
}
if ($succeed==false)
{
    $H->Insert(new Error("Unknown regcode. Your link seems to be broken, or your account is already activated (or you maybe you are using Internet ExploDer?)"));
    $delay=5000;
}

$H->Script("success=false;\n");
$H->Script("function refr()\n{
window.location='index.php';}");
$H->Script("\nsetTimeout(refr,$delay);");
$H->Draw();
?>
