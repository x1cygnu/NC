<?php

function xmlentities($string) {
   return str_replace (
    array ( '&', '"', "'", '<', '>', '.' ),
    array ( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;', '&apos;' ),
    $string );
  }

class XMLText
{
    var $sString;
    
    function XMLText($sNewString)
    {
	$this->sString=$sNewString;
    }
    
    function Draw()
    {
	echo xmlentities($this->sString);
    }
}
class XMLEntity
{
    var $aLines=array();
    var $aAttributes=array();
    var $sName;

    function Insert($oEntity,$iPos=-1)
    {
	if (is_string($oEntity))
	    $oEntity=new XMLText($oEntity);
	if ($iPos<0)
	    {
	    $this->aLines[]=clone $oEntity;
	    end($this->aLines);
	    return key($this->aLines);
	    }
	else
	    {
	    $this->aLines[$iPos]=clone $oEntity;
	    return $iPos;
	    }
    }
    
    function AddAttribute($sAttrName,$sAttrValue)
    {
	$this->aAttributes[$sAttrName]=$sAttrValue;
    }
    
    function RemoveAttribute($sAttrName)
    {
	unset($this->aAttributes[$sAttrName]);
    }
    
    function Remove($iPos)
    {
	unset($this->aLines[$iPos]);
    }
    
    function Draw()
    {
	echo '<' . xmlentities($this->sName);
	foreach ($this->aAttributes as $sAttrName => $sAttrValue)
	    echo ' ' . xmlentities($sAttrName) . '="' . xmlentities($sAttrValue) . '"';
	echo '>';
	foreach ($this->aLines as $oEntity)
	    $oEntity->Draw();
	echo '</' . xmlentities($this->sName) . ">";
    }
    
    function Clear()
    {
	$this->aLines=array();
    }
    
    function XMLEntity($sNewName,$content=null)
    {
	$this->sName=$sNewName;
	if (isset($content))
	    $this->Insert($content);
    }
}

class XML extends XMLEntity
{
    function Draw()
    {
        header('Content-type: text/xml');
	print('<?xml version="1.0" encoding="utf-8" standalone="yes"?>' . "\n");
	XMLEntity::Draw();
    }
    
    function XML ($sRootName)
    {
	$this->sName=$sRootName;
    }
}

?>