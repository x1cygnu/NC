<?php


function ranking_players_rank(&$sql, $from, $to)
{
    $from=makeinteger($from)-1;
    $to=makeinteger($to)-1;
    $count=$to-$from+1;
    return $sql->query("SELECT P.*, A.Nick FROM NC_Player P "
		    . "JOIN NC_Account A ON P.PID=A.PID "
		    . "WHERE P.PID>0 AND A.ForumAdmin=0 "
		    . "ORDER BY Rank ASC "
		    . "LIMIT $from, $count");
}

function ranking_players_points(&$sql, $from, $to)
{
    $from=makeinteger($from)-1;
    $to=makeinteger($to)-1;
    $count=$to-$from+1;
    return $sql->query("SELECT P.*, A.Nick FROM NC_Player P "
		    . "JOIN NC_Account A ON P.PID=A.PID "
		    . "WHERE P.PID>0 AND A.ForumAdmin=0 "
		    . "ORDER BY Points DESC "
		    . "LIMIT $from, $count");
}

function ranking_players_science(&$sql, $from, $to)
{
    $from=makeinteger($from)-1;
    $to=makeinteger($to)-1;
    $count=$to-$from+1;
    return $sql->query("SELECT P.*, A.Nick FROM NC_Player P "
		    . "JOIN NC_Account A ON P.PID=A.PID "
		    . "WHERE P.PID>0 AND A.ForumAdmin=0 "
		    . "ORDER BY GREATEST(Sensory, Engineering, Warp, Physics, Mathematics, Urban) DESC "
		    . "LIMIT $from, $count");
}

function ranking_players_culture(&$sql, $from, $to)
{
    $from=makeinteger($from)-1;
    $to=makeinteger($to)-1;
    $count=$to-$from+1;
    return $sql->query("SELECT P.*, A.Nick FROM NC_Player P "
		    . "JOIN NC_Account A ON P.PID=A.PID "
		    . "WHERE P.PID>0 AND A.ForumAdmin=0 "
		    . "ORDER BY CultureLvl DESC, CultureRemain DESC "
		    . "LIMIT $from, $count");
}

function ranking_players_pl(&$sql, $from, $to)
{
    $from=makeinteger($from)-1;
    $to=makeinteger($to)-1;
    $count=$to-$from+1;
    return $sql->query("SELECT P.*, A.Nick FROM NC_Player P "
		    . "JOIN NC_Account A ON P.PID=A.PID "
		    . "WHERE P.PID>0 AND A.ForumAdmin=0 "
		    . "ORDER BY PL DESC, PLRemain ASC "
		    . "LIMIT $from, $count");
}

function ranking_players_vl(&$sql, $from, $to)
{
    $from=makeinteger($from)-1;
    $to=makeinteger($to)-1;
    $count=$to-$from+1;
    return $sql->query("SELECT P.*, A.Nick FROM NC_Player P "
		    . "JOIN NC_Account A ON P.PID=A.PID "
		    . "WHERE P.PID>0 AND A.ForumAdmin=0 "
		    . "ORDER BY VL DESC, VLRemain ASC "
		    . "LIMIT $from, $count");
}


function ranking_players_ta(&$sql, $from, $to)
{
    $from=makeinteger($from)-1;
    $to=makeinteger($to)-1;
    $count=$to-$from+1;
    return $sql->query("SELECT P.*, A.Nick FROM NC_Player P "
		    . "JOIN NC_Account A ON P.PID=A.PID "
		    . "WHERE P.PID>0 AND A.ForumAdmin=0 "
		    . "ORDER BY TA DESC "
		    . "LIMIT $from, $count");
}

function ranking_player_count(&$sql)
{
    $U=$sql->query("SELECT count(*) AS C FROM NC_Player");
    return $U[0]['C'];
}

function ranking_alliance_count(&$sql)
{
    $U=$sql->query("SELECT count(*) AS C FROM NC_Alliance");
    return $U[0]['C'];
}


function ranking_alliance_points(&$sql, $from, $to)
{
    $from=makeinteger($from)-1;
    $to=makeinteger($to)-1;
    $count=$to-$from+1;
    return $sql->query("SELECT * FROM NC_Alliance ORDER BY Points DESC");
}

function ranking_alliance_members(&$sql, $from, $to)
{
    $from=makeinteger($from)-1;
    $to=makeinteger($to)-1;
    $count=$to-$from+1;
    return $sql->query("SELECT * FROM NC_Alliance ORDER BY NoMembers DESC");
}

function ranking_alliance_TCP(&$sql, $from, $to)
{
    $from=makeinteger($from)-1;
    $to=makeinteger($to)-1;
    $count=$to-$from+1;
    return $sql->query("SELECT * FROM NC_Alliance ORDER BY Countdown ASC, TCP DESC, Points DESC");
}


?>
