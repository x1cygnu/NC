<?php
get("sit","integer");
if (isset($GET['sit']) and $GET['sit']==1 and $_SESSION['SitPID']!=0)
{
    $MainPID=$_SESSION['SitPID'];
    $sitAddition="&sit=1";
}
else
{
    $MainPID=$_SESSION['PID'];
    $sitAddition="";
}
?>
