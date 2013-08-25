<?php
include_once("internal/html.php");

function MakeHint($V,$U)
{
    if ($_SESSION['Hint']>0)
    {
    $DO=new Div();
    $DO->sClass="hinted" . $_SESSION['Hint'];
    $D=new Div();
    $D->Insert($U);
    $DO->Insert($V);
    $DO->Insert($D);
    return $DO;
    }
    return $V;
}

?>