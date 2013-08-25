<?php

// permission semi-class

function get_permission_for_group(&$sql, $gid)
{
    $gid=makeinteger($gid);
//    $As=$sql->query("SELECT * FROM NC_Permissions WHERE GID=$gid");
    $Ss=$sql->query("SELECT S.SectID, S.Name, Prm.Read, Prm.Write, Prm.New, Prm.Modify, Prm.Delete, Prm.Lock FROM NC_Sections S "
		. "LEFT JOIN NC_Permissions Prm ON (Prm.SectID=S.SectID AND Prm.GID=$gid) "
		. "ORDER BY S.Name");
    foreach($Ss as $S)
    {
	$P[$S['SectID']]['Name']=$S['Name'];
	$P[$S['SectID']]['Read']=($S['Read']==1);
	$P[$S['SectID']]['Write']=($S['Write']==1);
	$P[$S['SectID']]['New']=($S['New']==1);
	$P[$S['SectID']]['Modify']=($S['Modify']==1);
	$P[$S['SectID']]['Delete']=($S['Delete']==1);
	$P[$S['SectID']]['Lock']=($S['Lock']==1);
    }
    return $P;    
}

function set_permission(&$sql, $gid, $section, $field)
{
    $gid=makeinteger($gid);
    $section=makeinteger($section);
    $field=makefieldstring($field);
    $sql->query("INSERT INTO NC_Permissions SET GID=$gid, SectID=$section, $field=1"
	    . " ON DUPLICATE KEY UPDATE $field=NOT $field");
}


?>