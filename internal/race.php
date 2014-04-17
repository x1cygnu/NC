<?php

// here race modificators are defined

$Attributes = array("Growth", "Science", "Culture", "Production", "Speed", "Attack", "Defence");

/* race modifiers:
   1 - 3.4 race
   1 - 2.7 race
   1 - 1.9 race
   1 - 1.0 race
   1
   1 + 1.0 race
   1 + 1.8 race
   1 + 2.4 race
   1 + 3.0 race
 */

function Growth($mod) /* 14 */
{
    switch ($mod)
    {
    case -4:return 52;
    case -3:return 62;
    case -2:return 73;
    case -1:return 86;
    case 0:return 100;
    case 1:return 114;
    case 2:return 125;
    case 3:return 134;
    case 4:return 142;
    default:return 100;
    }
}

function Science($mod) /* 7 */
{
    switch ($mod)
    {
    case -4:return 76;
    case -3:return 81;
    case -2:return 87;
    case -1:return 93;
    case 0:return 100;
    case 1:return 107;
    case 2:return 113;
    case 3:return 117;
    case 4:return 121;
    default:return 100;
    }
}

function Culture($mod) /* 8 */
{
    switch ($mod)
    {
    case -4:return 73;
    case -3:return 78;
    case -2:return 85;
    case -1:return 92;
    case 0:return 100;
    case 1:return 108;
    case 2:return 114;
    case 3:return 119;
    case 4:return 124;
    default:return 100;
    }
}

function Production($mod) /* 4 */
{
    switch ($mod)
    {
    case -4:return 86;
    case -3:return 89;
    case -2:return 92;
    case -1:return 96;
    case 0:return 100;
    case 1:return 104;
    case 2:return 107;
    case 3:return 110;
    case 4:return 112;
    default:return 100;
    }
}

function Speed($mod) /* 18 */
{
    switch ($mod)
    {
    case -4:return 39;
    case -3:return 51;
    case -2:return 66;
    case -1:return 82;
    case 0:return 100;
    case 1:return 118;
    case 2:return 132;
    case 3:return 143;
    case 4:return 154;
    default:return 100;
    }
}

function Attack($mod) /* 10 */
{
    switch ($mod)
    {
    case -4:return 66;
    case -3:return 73;
    case -2:return 81;
    case -1:return 90;
    case 0:return 100;
    case 1:return 110;
    case 2:return 118;
    case 3:return 124;
    case 4:return 130;
    default:return 100;
    }
}

function Defence($mod) /* 14 */
{
    switch ($mod)
    {
    case -4:return 52;
    case -3:return 62;
    case -2:return 73;
    case -1:return 86;
    case 0:return 100;
    case 1:return 114;
    case 2:return 125;
    case 3:return 134;
    case 4:return 142;
    default:return 100;
    }
}

?>
