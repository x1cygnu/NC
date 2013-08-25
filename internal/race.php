<?php

// here race modificators are defined

$Attributes = array("Growth", "Science", "Culture", "Production", "Speed", "Attack", "Defence");

/* race modifiers:
 * Ans*(1+race%*(1-0.11*raceLvl)) */

function Growth($mod) /* 12 */
{
    switch ($mod)
    {
    case -4:return 57;
    case -3:return 66;
    case -2:return 78;
    case -1:return 88;
    case 0:return 100;
    case 1:return 111;
    case 2:return 121;
    case 3:return 131;
    case 4:return 140;
    default:return 100;
    }
}

function Science($mod) /* 7 */
{
    switch ($mod)
    {
    case -4:return 71;
    case -3:return 78;
    case -2:return 85;
    case -1:return 93;
    case 0:return 100;
    case 1:return 106;
    case 2:return 112;
    case 3:return 117;
    case 4:return 122;
    default:return 100;
    }
}

function Culture($mod) /* 6 */
{
    switch ($mod)
    {
    case -4:return 74;
    case -3:return 81;
    case -2:return 87;
    case -1:return 94;
    case 0:return 100;
    case 1:return 105;
    case 2:return 110;
    case 3:return 115;
    case 4:return 119;
    default:return 100;
    }
}

function Production($mod) /* 4 */
{
    switch ($mod)
    {
    case -4:return 82;
    case -3:return 87;
    case -2:return 91;
    case -1:return 96;
    case 0:return 100;
    case 1:return 104;
    case 2:return 107;
    case 3:return 110;
    case 4:return 112;
    default:return 100;
    }
}

function Speed($mod)
{
    switch ($mod)
    {
    case -4:return 40;
    case -3:return 52;
    case -2:return 66;
    case -1:return 82;
    case 0:return 100;
    case 1:return 118;
    case 2:return 136;
    case 3:return 154;
    case 4:return 172;
    default:return 100;
    }
}

function Attack($mod)
{
    switch ($mod)
    {
    case -4:return 66;
    case -3:return 73;
    case -2:return 81;
    case -1:return 90;
    case 0:return 100;
    case 1:return 110;
    case 2:return 120;
    case 3:return 128;
    case 4:return 136;
    default:return 100;
    }
}

function Defence($mod)
{
    switch ($mod)
    {
    case -4:return 66;
    case -3:return 73;
    case -2:return 81;
    case -1:return 90;
    case 0:return 100;
    case 1:return 110;
    case 2:return 120;
    case 3:return 128;
    case 4:return 136;
    default:return 100;
			/*
    case -4:return 44;
    case -3:return 55;
    case -2:return 68;
    case -1:return 83;
    case 0:return 100;
    case 1:return 116;
    case 2:return 132;
    case 3:return 148;
    case 4:return 163;
    default:return 100;*/
    }
}

?>
