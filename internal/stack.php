<?php

class Stack
{
    var $stack=array();
    var $pos=0;
    
    function push($v)
    {
	$this->stack[$this->pos]=$v;
	++$this->pos;
    }
    function pop()
    {
	if ($this->pos>0)
	    unset($this->stack[--$this->pos]);
    }
    function top()
    {
	if ($this->pos>0)
	    return $this->stack[$this->pos-1];
    }
    function find($v)
    {
	for ($i=$this->pos-1; $i>=0; --$i)
	    if ($this->stack[$i]==$v)
		return $i;
	return -1;
    }
    function cut($newpos)
    {
	while ($newpos<$this->pos)
	    $this->pop();
    }
    function isempty()
    {
	return ($this->pos==0);
    }
}

?>