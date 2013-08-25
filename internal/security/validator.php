<?php

function exists(&$variable)
{
    return (isset($variable) and $variable!=="");
}

function vstring($string)
{
    return addslashes($string);
}

function devstring($string)
{
    return stripslashes($string);
}

function makestring($string)
{
    return mysql_real_escape_string($string);
}

function makequotedstring($string)
{
    return '"' . mysql_real_escape_string($string) . '"';
}

function makefieldstring($string)
{
    return '`' . mysql_real_escape_string($string) . '`';
}

function makeinteger($integer)
{
    settype($integer, "integer");
    return $integer;
}

function makereal($real)
{
    settype($real, "float");
    return $real;
}

function get($name, $type)
{
    global $GET;
    if (exists($_GET[$name]))
    {
    $GET[$name]=$_GET[$name];
    if ($type=="string");
    {
	$GET[$name]=str_replace("\\'","'",$GET[$name]);
	$GET[$name]=str_replace("\\\"","\"",$GET[$name]);
	$GET[$name]=str_replace("\\\\","\\",$GET[$name]);
    }
    settype($GET[$name],$type);
    }
    else
    unset($GET[$name]);
}
function post($name, $type)
{
    global $POST;
    if (exists($_POST[$name]))
    {
    $POST[$name]=$_POST[$name];
    if ($type=="string");
    {
	$POST[$name]=str_replace("\\'","'",$POST[$name]);
	$POST[$name]=str_replace("\\\"","\"",$POST[$name]);
	$POST[$name]=str_replace("\\\\","\\",$POST[$name]);
    }
    settype($POST[$name],$type);
    }
    else
    unset($POST[$name]);
}

function htmlstring($string)
{
    return htmlentities($string);
}

?>