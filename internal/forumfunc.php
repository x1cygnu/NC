<?php

// forum functions

include_once("./internal/stack.php");
include_once("./internal/account.php");
include_once("./internal/forumparser.php");

function htmltags($string)
{
    return htmlentities($string,ENT_QUOTES,"UTF-8");
}


function forum_check_thread_permission(&$sql,$thread,$type)
{
    if ($_SESSION['IsAdmin']==1)
	return true;
    $type=makestring($type);
    $thread=makeinteger($thread);
    $tag=makequotedstring($_SESSION['PermTAG']);
    $A=$sql->query("SELECT Sec.Owner, Sec.OwnerTag, Sec.SectID, Sec.`$type` AS U, Th.Locked AS L FROM NC_Threads Th " .
		"JOIN NC_Sections Sec ON Th.SectID=Sec.SectID " . 
		"WHERE Th.ThID=$thread");

    if ($A[0]['Owner']==$_SESSION['AID'])
      return true;
    if ($type=="Write" and $A[0]['L']==1)
      return false;
    if ($A[0]['U']==3) //available to everyone
      return true;
    if ($A[0]['U']==1 and $A[0]['OwnerTag']==$_SESSION['PermTAG'] and $A[0]['OwnerTag']!='') //available to alliance
      return true;
    if ($A[0]['U']==2) { //available by permission list
      $SectID=makeinteger($A[0]['SectID']);
      $A=$sql->query("SELECT count(*) AS C FROM NC_Permissions "
	. "WHERE (User={$_SESSION['AID']} or TAG=$tag) " 
	. " AND SectID=$SectID");
      return $A[0]['C']>0;
    }
    return false;  //available to noone (except for creator)
}

function forum_check_censure(&$sql, $post) {
    $post=makeinteger($post);
    $A=$sql->query("SELECT Censure FROM NC_Post WHERE PstID=$post");
    return ($A[0]['Censure']=='');
}

function forum_check_post_permission(&$sql,$post,$type)
{
    if ($_SESSION['IsAdmin']==1)
	return true;
    $type=makestring($type);
    $thread=makeinteger($thread);
    $tag=makequotedstring($_SESSION['PermTAG']);
    $A=$sql->query("SELECT Sec.Owner, Sec.OwnerTag, Sec.SectID, Sec.`$type` AS U, Th.Locked AS L FROM NC_Threads Th " .
      "JOIN NC_Sections Sec ON Th.SectID=Sec.SectID " . 
      "JOIN NC_Post Pst ON Pst.ThID=Th.ThID " .
      "WHERE Pst.PstID=$post");
    if ($A[0]['Owner']==$_SESSION['AID'])
      return true;
    if ($type=="Write" and $A[0]['L']==1)
      return false;
    if (($type=="Moderate") and $A[0]['L']==0)
	{
	    $SE=$sql->query("SELECT Th.Newest, Pst.Author FROM "
		. "NC_Threads Th JOIN NC_Post Pst ON Pst.ThID=Th.ThID "
		. "WHERE Pst.PstID=$post");
	    if ($SE[0]['Newest']==$post and $SE[0]['Author']==$_SESSION['AID'])
	      return true;
	}
    if ($A[0]['U']==3) //available to everyone
      return true;
    if ($A[0]['U']==1 and $A[0]['OwnerTag']==$_SESSION['PermTAG'] and $A[0]['OwnerTag']!='') //available to alliance
      return true;
    if ($A[0]['U']==2) { //available by permission list
      $SectID=makeinteger($A[0]['SectID']);
      $A=$sql->query("SELECT count(*) AS C FROM NC_Permissions "
	. "WHERE (User={$_SESSION['AID']} or TAG=$tag) AND SectID=$SectID");
/*      $A=$sql->query("SELECT count(*) AS C FROM NC_Permissions "
	. "WHERE (User={$_SESSION['AID']} or TAG={$_SESSION['PermTAG']}) "
        . "AND SectID=$SectID");*/
      return $A[0]['C']>0;
    }
    return false;  //available to noone (except for creator)
}


function forum_check_section_permission(&$sql,$section,$type)
{
    if ($_SESSION['IsAdmin']==1)
	return true;
    $type=makestring($type);
    $section=makeinteger($section);
    $tag=makequotedstring($_SESSION['PermTAG']);
    $A=$sql->query("SELECT Owner, OwnerTag, `$type` AS U FROM NC_Sections WHERE SectID=$section");
    if ($A[0]['Owner']==$_SESSION['AID']) {
      return true;
    }
    if ($type=="Write" and $A[0]['L']==1)
      return false;
    if ($A[0]['U']==3) //available to everyone
      return true;
    if ($A[0]['U']==1 and $A[0]['OwnerTag']==$_SESSION['PermTAG'] and $A[0]['OwnerTag']!='') //available to alliance
      return true;
    if ($A[0]['U']==2) { //available by permission list
      $A=$sql->query("SELECT count(*) AS C FROM NC_Permissions "
	. "WHERE (User={$_SESSION['AID']} or TAG=$tag) AND SectID=$section");
//      var_dump($A);
      return $A[0]['C']>0;
    }
    return false;  //available to noone (except for creator)
}


function double_post($text)
{
    $Code=md5($text);
    if ($_SESSION['DoublePost']==$Code)
	return true;
    $_SESSION['DoublePost']=$Code;
    return false;
}

function forum_new_post(&$sql,$thread,$post)
{
  $thread=makeinteger($thread);
    if (forum_check_thread_permission($sql, $thread, "Write"))
    {
	$post=makequotedstring($post);
	$censure=makequotedstring($censure);
	$Now=EncodeNow();
	$sql->query("INSERT INTO NC_Post VALUES ( NULL, $thread, {$_SESSION['AID']},$Now, $post, $censure)");
	$id=$sql->query("SELECT LAST_INSERT_ID() AS A");
	$sql->query("UPDATE NC_Threads SET Newest={$id[0]['A']} WHERE ThID=$thread");
	$SectID=$sql->query("SELECT SectID FROM NC_Threads WHERE ThID=$thread");
	$sql->query("UPDATE NC_Sections SET Newest=IF(Newest>{$id[0]['A']},Newest,{$id[0]['A']}) WHERE SectId={$SectID[0]['SectID']}");
	return "";
    }
	return "You have no permission to post here";
}

function forum_new_thread(&$sql,$section,$threadname,$threaddesc)
{
    if (forum_check_section_permission($sql, $section, "New"))
    {
	$threadname=makequotedstring($threadname);
	$threaddesc=makequotedstring($threaddesc);
	$Now=EncodeNow();
	$sql->query("INSERT INTO NC_Threads VALUES ($section, NULL, $threadname, $threaddesc, $Now, 0)");	
	$A=$sql->query("SELECT LAST_INSERT_ID() AS A");
	return $A[0]['A'];
    }
    return -1;
}

function forum_remove_thread(&$sql,$THid)
{
  $THid=makeinteger($THid);
  if (forum_check_thread_permission($sql, $THid, "Moderate")) {
    $sql->query("DELETE FROM NC_Post WHERE ThID=$THid");
    $sql->query("DELETE FROM NC_Threads WHERE ThID=$THid");
    return "";
  }
  else {
    return "You have no permission to remove this thread";
  }
}

function forum_get_section_owner(&$sql, $SectID) {
  $SectID=makeinteger($SectID);
  $A=$sql->query("SELECT Owner FROM NC_Sections WHERE SectID=$SectID");
  return $A[0]['Owner'];
}
function forum_remove_section(&$sql,$SectID)
{
  $SectID=makeinteger($SectID);
  if ($_SESSION['IsAdmin'] or $_SESSION['AID']==forum_get_section_owner($sql, $SectID)) {
    $sql->query("DELETE FROM NC_Post "
      . "WHERE ThID IN "
      . "(Select Th.ThID FROM NC_Threads Th WHERE Th.SectID=$SectID)");
    $sql->query("DELETE FROM NC_Threads WHERE SectID=$SectID");
    $sql->query("DELETE FROM NC_Sections WHERE SectID=$SectID");
  }
}


function forum_remove_post(&$sql,$PstID)
{
    $PstID=makeinteger($PstID);
    $old=forum_get_post($sql, $PstID);
    if (forum_check_post_permission($sql, $PstID, "Moderate"))
	{
	    $sql->query("DELETE FROM NC_Post WHERE PstID=$PstID");

	$sql->query("UPDATE NC_Threads T SET T.Newest="
	. " (SELECT PstID FROM NC_Post P WHERE P.ThID={$old['ThID']}"
	. " ORDER BY P.Time DESC LIMIT 0, 1) WHERE T.ThID={$old['ThID']}");

	$sql->query("UPDATE NC_Sections S SET S.Newest="
	. " (SELECT Pst.PstID FROM NC_Post Pst"
	. " JOIN NC_Threads Th ON Pst.ThID=Th.ThID"
	. " WHERE Th.SectID=S.SectID"
	. " ORDER BY Pst.Time DESC LIMIT 0, 1)");

	}
}

function forum_edit_post(&$sql, $PstID, $post)
{
    $A=forum_get_post($sql,$PstID);
    $Now=EncodeNow();
    if (forum_check_post_permission($sql, $PstID, "Moderate"))
    {
	$post=makequotedstring($post);
	$sql->query("UPDATE NC_Post SET Text=$post WHERE PstID=$PstID");
	return "";
	}
    else
	return "You have no permission to edit this post";
}

function forum_censure_post(&$sql, $PstID, $censure)
{
    $A=forum_get_post($sql,$PstID);
    $Now=EncodeNow();
    if (forum_check_thread_permission($sql, $A['ThID'], "Moderate"))
    {
	$censure=makequotedstring($censure);
	$sql->query("UPDATE NC_Post SET censure=$censure WHERE PstID=$PstID");
	}
}

function forum_count_owned(&$sql, $aid) {
  $aid=makeinteger($aid);
  $A=$sql->query("SELECT count(*) AS C From NC_Sections WHERE Owner=$aid");
  return $A[0]['C'];
}

function forum_new_section(&$sql, $sectionname, $sectiondesc)
{
  if (forum_count_owned($sql, $_SESSION['AID'])>=5 and (!$_SESSION['IsAdmin']))
    return -1;

  $sectionname=makequotedstring($sectionname);
  $sectiondesc=makequotedstring($sectiondesc);
  $aid=makeinteger($_SESSION['AID']);
  $permTag=makequotedstring(account_get_perm_tag($sql,$aid));
  $sql->query("INSERT INTO NC_Sections VALUES (NULL, $sectionname, $sectiondesc, 0, " .
    "$aid, $permTag, 1, 1, 1, 0)");
  $A=$sql->query("SELECT Last_Insert_ID() AS A");
  return $A[0]['A'];
}

function forum_sections(&$sql) //show PUBLIC forum sections
{
  $A=$sql->query("SELECT Sect.*, Act.Nick AS N, Pst.Time AS T,"
    . " COUNT(Unr.LastRead) AS R"
    . " FROM NC_Sections Sect"
    . " LEFT JOIN "
    . " (NC_Post Pst "
    . " JOIN NC_Account Act ON Act.AID=Pst.Author" 
    . " ) ON Pst.PstID=Sect.Newest"	

    . " LEFT JOIN "
    . " (NC_Unread Unr "
    . " JOIN NC_Threads Th ON Unr.ThID=Th.ThID)"
    . " ON Unr.AID={$_SESSION['AID']} AND Th.SectID=Sect.SectID"
    . " WHERE Sect.Owner=2"

    . " GROUP BY Sect.SectID");
  foreach ($A as $i=>$Ai)
  {
    $A[$i]['Name']=htmltags($Ai['Name']);
    $A[$i]['Description']=htmltags($Ai['Description']);
  }
  return $A;
}

function forum_owned_sections(&$sql,$AID) //show PUBLIC forum sections
{
  $AID=makeinteger($AID);
  $A=$sql->query("SELECT Sect.*, Act.Nick AS N, Pst.Time AS T,"
    . " COUNT(Unr.LastRead) AS R"
    . " FROM NC_Sections Sect"
    . " LEFT JOIN "
    . " (NC_Post Pst "
    . " JOIN NC_Account Act ON Act.AID=Pst.Author" 
    . " ) ON Pst.PstID=Sect.Newest"	

    . " LEFT JOIN "
    . " (NC_Unread Unr "
    . " JOIN NC_Threads Th ON Unr.ThID=Th.ThID)"
    . " ON Unr.AID={$_SESSION['AID']} AND Th.SectID=Sect.SectID"
    . " WHERE Sect.Owner=$AID"

    . " GROUP BY Sect.SectID");
  foreach ($A as $i=>$Ai)
  {
    $A[$i]['Name']=htmltags($Ai['Name']);
    $A[$i]['Description']=htmltags($Ai['Description']);
  }
  return $A;
}


function forum_public_user_sections(&$sql) 
{
  $A=$sql->query("SELECT Sect.*, Act.Nick AS N, Pst.Time AS T,"
    . " COUNT(Unr.LastRead) AS R"
    . " FROM NC_Sections Sect"
    . " LEFT JOIN "
    . " (NC_Post Pst "
    . " JOIN NC_Account Act ON Act.AID=Pst.Author" 
    . " ) ON Pst.PstID=Sect.Newest"	

    . " LEFT JOIN "
    . " (NC_Unread Unr "
    . " JOIN NC_Threads Th ON Unr.ThID=Th.ThID)"
    . " ON Unr.AID={$_SESSION['AID']} AND Th.SectID=Sect.SectID"
    . " WHERE Sect.Owner!=2 AND Sect.Read=3"

    . " GROUP BY Sect.SectID");
  foreach ($A as $i=>$Ai)
  {
    $A[$i]['Name']=htmltags($Ai['Name']);
    $A[$i]['Description']=htmltags($Ai['Description']);
  }
  return $A;
}


function forum_private_user_sections(&$sql) 
{
  $aid=makeinteger($_SESSION['AID']);
  $permTAG=makequotedstring($_SESSION['PermTAG']);
//var_dump($permTAG);
  $A=$sql->query("SELECT Sect.*, Act.Nick AS N, Pst.Time AS T,"
    . " COUNT(Unr.LastRead) AS R"
    . " FROM NC_Sections Sect"
    . " LEFT JOIN NC_Permissions Perm ON Perm.SectID=Sect.SectID"
    . " LEFT JOIN "
    . " (NC_Post Pst "
    . " JOIN NC_Account Act ON Act.AID=Pst.Author" 
    . " ) ON Pst.PstID=Sect.Newest"	

    . " LEFT JOIN "
    . " (NC_Unread Unr "
    . " JOIN NC_Threads Th ON Unr.ThID=Th.ThID)"
    . " ON Unr.AID=$aid AND Th.SectID=Sect.SectID"

    . " WHERE Sect.Owner!=2 AND Sect.Read!=3 AND "
    . "(Sect.Owner=$aid OR (Sect.Read=1 AND Sect.OwnerTag!=\"\" AND Sect.OwnerTag=$permTAG) OR "
    . "(Sect.Read=2 AND (Perm.User=$aid OR Perm.TAG=$permTAG)) OR {$_SESSION['IsAdmin']}=1)"
  
    . " GROUP BY Sect.SectID");
  foreach ($A as $i=>$Ai)
  {
    $A[$i]['Name']=htmltags($Ai['Name']);
    $A[$i]['Description']=htmltags($Ai['Description']);
  }
  return $A;
}
//listuje watki
function forum_threads(&$sql, $SectID)
{
$SectID=makeinteger($SectID);
if (forum_check_section_permission(&$sql,$SectID,"Read"))
{
    $A=$sql->query("SELECT Th.*, Act.Nick AS N, Pst.Time AS T, "
	    . " IF (ISNULL(Unr.LastRead),0,1) AS R FROM NC_Threads Th"
		    . " LEFT JOIN "
		    . " (NC_Post Pst "
		    . " JOIN NC_Account Act ON Act.AID=Pst.Author" 
		    . " ) ON Pst.PstID=Th.Newest"
	    . " LEFT JOIN NC_Unread Unr ON Unr.AID={$_SESSION['AID']} AND Unr.ThID=Th.ThID" 
	    . " WHERE Th.SectID=$SectID ORDER BY Newest DESC");
    foreach ($A as $i=>$Ai)
    {
	$A[$i]['Name']=htmltags($Ai['Name']);
	$A[$i]['Description']=htmltags($Ai['Description']);
    }
    return $A;
}
else
    return array();
}

//zwraca wszystkie posty, bez aktualizacji uzytkownika
function forum_posts(&$sql, $ThID)
{
$ThID=makeinteger($ThID);
if (forum_check_thread_permission(&$sql,$ThID,"Read"))
    return $sql->query("SELECT * FROM NC_Post WHERE ThID=$ThID ORDER BY PstID");
else
    return array();    
}

//zwraca dana sekcje
function forum_get_section(&$sql, $Sect)
{
    $Sect=makeinteger($Sect);
    $A=$sql->query("SELECT * FROM NC_Sections WHERE SectID=$Sect");
    return $A[0];
}

function forum_get_html_section(&$sql, $Sect)
{
    $Sect=makeinteger($Sect);
    $A=$sql->query("SELECT * FROM NC_Sections WHERE SectID=$Sect");
    $A[0]['Name']=htmltags($A[0]['Name']);
    $A[0]['Description']=htmltags($A[0]['Description']);
    return $A[0];
}

//zwraca dany watek
function forum_get_thread(&$sql, $Thread)
{
    $Thread=makeinteger($Thread);
    $A=$sql->query("SELECT * FROM NC_Threads WHERE ThID=$Thread");
    return $A[0];
}

function forum_get_html_thread(&$sql, $Thread)
{
    $Thread=makeinteger($Thread);
    $A=$sql->query("SELECT * FROM NC_Threads WHERE ThID=$Thread");
    $A[0]['Name']=htmltags($A[0]['Name']);
    $A[0]['Description']=htmltags($A[0]['Description']);
    return $A[0];
}

//zwraca dany post
function forum_get_post(&$sql, $Post)
{
    $Post=makeinteger($Post);
    $A=$sql->query("SELECT Pl.TAG, P.*, A.Nick FROM NC_Post P LEFT JOIN NC_Account A ON A.AID=P.Author LEFT JOIN NC_Player Pl ON Pl.AID=A.AID WHERE PstID=$Post");
    return $A[0];
}

//listuje posty
function forum_list_thread(&$sql, $Thread, $Start=0, $Len=25)
{
    $Start=makeinteger($Start);
    $Len=makeinteger($Len);
    $Thread=makeinteger($Thread);
    $A=$sql->query("SELECT Pl.TAG, Pst.*, A.AID, A.Nick, A.Avatar, A.BackgroundSig FROM NC_Post Pst JOIN NC_Account A ON A.AID=Pst.Author LEFT JOIN NC_Player Pl ON Pl.AID=A.AID WHERE ThID=$Thread ORDER BY PstID LIMIT $Start, $Len");
    $T=$sql->query("SELECT count(*) T FROM NC_Post WHERE ThID=$Thread");
    $A['to']=$Start+min(count($A),$Len);
    $A['from']=$Start;
    $A['total']=$T[0]['T'];
    forum_unmark_thread($sql, $_SESSION['AID'], $Thread);
    return $A;
}

//otwiera sprytnie watek
function forum_open_thread(&$sql, $Thread)
{
    $A=$sql->query("SELECT count(*) A FROM NC_Post WHERE ThID=$Thread AND Time<(SELECT LastRead FROM NC_Unread WHERE ThID=$Thread AND AID={$_SESSION['AID']})");
    if ($A[0]['A']>0) //0 -- new thread or no NC_Unread entry
	{
	$P=forum_list_thread($sql, $Thread, max(0,$A[0]['A']-1), 1000);
	$P['special']=true;
	return $P;
	}
    else
	return forum_list_thread($sql, $Thread);
}

function forum_count_posts(&$sql, $Thread)
{
    $Thread=makeinteger($Thread);
    $A=$sql->query("SELECT count(*) AS X FROM NC_Post WHERE ThID=$Thread");
    return $A[0]['X'];
}

function forum_mark_unread(&$sql, $AID, $LastLogin)
{
    $AID=makeinteger($AID);
    $LastLogin=makeinteger($LastLogin);
    $sql->query("INSERT IGNORE INTO NC_Unread"
		. " SELECT $AID, P.ThID, MIN(P.Time)"
		. " FROM NC_Post AS P WHERE P.Time>=$LastLogin AND P.Author!=$AID GROUP BY P.ThID");
}

function forum_unmark_thread(&$sql, $AID, $ThID)
{
    $AID=makeinteger($AID);
    $ThID=makeinteger($ThID);
    $sql->query("DELETE FROM NC_Unread WHERE AID=$AID AND ThID=$ThID");
}

function forum_unmark_all(&$sql, $AID)
{
    $AID=makeinteger($AID);
    $sql->query("DELETE FROM NC_Unread WHERE AID=$AID");
}

function forum_lock_thread(&$sql, $ThID)
{
    if (forum_check_thread_permission($sql, $ThID, "Moderate"))
    {
	$sql->query("UPDATE NC_Threads SET Locked=1 WHERE ThID=$ThID");
	return "";
    }
    return "You have no permission to lock this thread";
}

function forum_unlock_thread(&$sql, $ThID)
{
    if (forum_check_thread_permission($sql, $ThID, "Moderate"))
    {
	$sql->query("UPDATE NC_Threads SET Locked=0 WHERE ThID=$ThID");
	return "";
	}
    return "You have no permission to unlock this thread";
}

function forum_search(&$sql, $aid, $permTAG, $whereParameter, $author, $from=0)
{
    $from=makeinteger($from);
    $aid=makeinteger($aid);
    if ($author!="") {
      $AuthorAID=account_get_id($sql, $author);
      if ($AuthorAID>0)
	$WriterAID=" AND Author=" . $AuthorAID;
      else
	return array();
    }
    $permTAG=makequotedstring($permTAG);

    return $sql->query("SELECT P.*, T.Name, Au.* FROM NC_Post P "
	    . "JOIN NC_Threads T ON T.ThID=P.ThID "
	    . "JOIN NC_Sections S ON S.SectID=T.SectID "
	    . "JOIN NC_Account Au ON P.Author=Au.AID "
	    . "LEFT JOIN NC_Permissions Perm ON Perm.SectID=S.SectID "
	    . "WHERE P.Censure=\"\" AND "
	    . "(S.Read=3 OR S.Owner=$aid OR (S.Read=1 AND S.OwnerTag=$permTAG) OR "
	    . "(S.Read=2 AND (Perm.User=$aid OR Perm.TAG=$permTAG)))"
	    . $whereParameter . $WriterAID
	    . " GROUP BY P.PstID ORDER BY P.Time DESC LIMIT $from, 25");
}

function forum_get_section_type(&$sql, $SectID) {
  $SectID=makeinteger($SectID);
  $A=$sql->query("SELECT Owner, `Read` FROM NC_Sections WHERE SectID=$SectID");
  if ($A[0]['Owner']==2)
    return 'pub'; //public
  if ($A[0]['Read']==3)
    return 'dpub'; //public diplomacy
  return 'dpriv'; //private diplomacy
}

function forum_get_thread_type(&$sql, $ThID) {
  $ThID=makeinteger($ThID);
  $A=$sql->query("SELECT S.Owner, S.Read FROM NC_Sections S JOIN NC_Threads T ON T.SectID=S.SectID WHERE ThID=$ThID");
  if ($A[0]['Owner']==2)
    return 'pub'; //public
  if ($A[0]['Read']==3)
    return 'dpub'; //public diplomacy
  return 'dpriv'; //private diplomacy
}

function forum_favourite_threads(&$sql, $AID, $permTAG)
{
  $AID=makeinteger($AID);
  $permTAG=makequotedstring($permTAG);
    $A=$sql->query("SELECT Th.*, Act.Nick AS N, Pst.Time AS T, "
	    . " IF (ISNULL(Unr.LastRead),0,1) AS R FROM NC_FavoriteThreads Fav JOIN NC_Threads Th ON Th.ThID=Fav.ThID"
		    . " LEFT JOIN "
		    . " (NC_Post Pst "
		    . " JOIN NC_Account Act ON Act.AID=Pst.Author" 
		    . " ) ON Pst.PstID=Th.Newest"
		    . " LEFT JOIN NC_Unread Unr ON Unr.AID={$_SESSION['AID']} AND Unr.ThID=Th.ThID"
		    . " JOIN NC_Sections S ON S.SectID=Th.SectID"
		   . " LEFT JOIN NC_Permissions Perm ON Th.SectID=Perm.SectID" 
	    . " WHERE Fav.AID=$AID AND "
	    . "(S.Read=3 OR S.Owner=$AID OR (S.Read=1 AND S.OwnerTag=$permTAG) OR "
	    . "(S.Read=2 AND (Perm.User=$AID OR Perm.TAG=$permTAG)))"
	    . " ORDER BY Newest DESC");
    foreach ($A as $i=>$Ai)
    {
	$A[$i]['Name']=htmltags($Ai['Name']);
	$A[$i]['Description']=htmltags($Ai['Description']);
    }
    return $A;
}

function forum_add_favourite(&$sql, $AID, $ThID)
{
$AID=makeinteger($AID);
$ThID=makeinteger($ThID);
if (forum_check_thread_permission($sql, $ThID, "Read"))
    $sql->query("INSERT IGNORE INTO NC_FavoriteThreads VALUES ($AID, $ThID)");
}

function forum_remove_favourite(&$sql, $AID, $ThID)
{
$AID=makeinteger($AID);
$ThID=makeinteger($ThID);
$sql->query("DELETE FROM NC_FavoriteThreads WHERE AID=$AID AND ThID=$ThID");
}

function forum_get_section_permission_list(&$sql, $SectID) {
  $SectID=makeinteger($SectID);
  return $sql->query("SELECT * FROM NC_Permissions WHERE SectID=$SectID");
}

function forum_permission_add_player(&$sql, $SectID, $AID) {
  $SectID=makeinteger($SectID);
  $AID=makeinteger($AID);
  $C=$sql->query("SELECT count(*) AS C FROM NC_Permissions WHERE SectID=$SectID AND User=$AID");
  if ($C[0]['C']==0)
    $sql->query("INSERT INTO NC_Permissions VALUES ($SectID, $AID, 0)");
}

function forum_permission_add_group(&$sql, $SectID, $Gr) {
  $SectID=makeinteger($SectID);
  $Gr=makequotedstring($Gr);
  $C=$sql->query("SELECT count(*) AS C FROM NC_Permissions WHERE SectID=$SectID AND TAG=$Gr");
  if ($C[0]['C']==0)
    $sql->query("INSERT INTO NC_Permissions VALUES ($SectID, 0, $Gr)");
}

function forum_permission_remove_player(&$sql, $SectID, $AID) {
  $SectID=makeinteger($SectID);
  $AID=makeinteger($AID);
  $C=$sql->query("DELETE FROM NC_Permissions WHERE SectID=$SectID AND User=$AID");
}

function forum_permission_remove_group(&$sql, $SectID, $Gr) {
  $SectID=makeinteger($SectID);
  $Gr=makequotedstring($Gr);
  $C=$sql->query("DELETE FROM NC_Permissions WHERE SectID=$SectID AND TAG=$Gr");
}

function forum_group_get_members($sql, $tag) {
  $tag=makequotedstring($tag);
  return $sql->query("SELECT A.AID, A.Nick, A.PID, P.TAG, A.LastLogin FROM NC_Account A LEFT JOIN NC_Player P ON P.PID=A.PID WHERE A.PermTag=$tag");
}

?>
