<?php
/*
 * Prosty obiekt dostepu do bazy danych
 */
    class SQL {
        var $db_host;
        var $db_port;
        var $db_user;
        var $db_pass;
        var $db_name;

        var $db_handle;
        var $last_result;
        var $last_id;
        
        var $connected;
	
	var $querycount;
	var $debug;

        function SQL($host, $user, $pass, $name, $port = 3306)
	{
	    $this->ok=true;
            $this->db_host = $host;
            $this->db_user = $user;
            $this->db_pass = $pass;
            $this->db_name = $name;
            $this->db_port = $port;
	    
	    $this->querycount=0;
	    $this->debug=false;

            $this->last_result = NULL;
            
            $this->connected = FALSE;

//            $this->db_handle = @mysql_connect($this->db_host, $this->db_user, $this->db_pass);
	    $this->db_handle = mysql_connect($this->db_host . ':' . $this->db_port, $this->db_user, $this->db_pass);
            if($this->db_handle)
            {
            	if(@mysql_select_db($this->db_name, $this->db_handle))
            	{
            		$this->connected = TRUE;            		
            	}
	    }
	    else {
		printf("MySql error: (%d) %s",mysql_errno(), mysql_error());
	    }
        }

        function close()
        {
            @mysql_close($this->db_handle);
            $this->db_handle = FALSE;
            $this->last_result = NULL;
            $this->connected = FALSE;
        }
        function reopen()
        {
            if(!$this->db_handle)
                @mysql_close($this->db_handle);
//            $this->db_handle = @mysql_connect($this->db_host, $this->db_name, $this->db_pass) or print("Nie mo¿na nawi±zaæ po³±czenia z baz± danych");
            $this->db_handle = mysql_connect('', $this->db_name, $this->db_pass) or print("Nie mo¿na nawi±zaæ po³±czenia z baz± danych");
            mysql_select_db($this->db_name, $this->db_handle) or print("Nie mozna wybrac bazy danych");
            $this->last_result = NULL;
        }

        function query($query, $debug=false)
	{
		++$this->querycount;
		if ($debug)
			print("<br/>$query<br/>");
		$return_array = array();
		if(!$this->db_handle) print("Obiekt bazy danych stracil waznosc");
		$query_result = @mysql_query($query, $this->db_handle);
		if(!$query_result)
		{
			$error=mysql_error($this->db_handle);
			if ($this->debug)
				printf("Error: $error<br>\n");
			else
			{
				$F=fopen("/home/cygnus/sql.log","a+");
				$Now=DecodeTime(EncodeNow());
				fwrite($F,"[{$_SESSION['AID']}] $Now: {$query} :: $error\n");
				fclose($F);
			}
		}
		while($row = @mysql_fetch_assoc($query_result))
			$return_array[] = $row;
		@mysql_free_result($query_result);
		$this->last_id = @mysql_insert_id();
		if(sizeof($return_array) != 0)
		{
			$this->last_result = $return_array;
			return $return_array;
		}
		else
			return array();
	}
        function result()
        {
            return $this->last_result;
        }
        function id()
        {
        	return $this->last_id;
        }
        function connected()
        {
        	return $this->connected;
        }
    }
    
function sqltext($sString)
{
    return mysql_real_escape_string($sString);
}

function sqlnum($iNum)
{
    settype($iNum,'integer');
    return $iNum;
}


?>
