<?php
//include_once ("debug.php");

//see documentation in ~/documentation/html/ !!!!! 

class Br
{
    function Draw() {echo "<br/>\n";}
}

class Text
{
    var $sText;
    function Draw() {echo $this->sText;}
    function Text($sV="") {$this->sText=$sV;}
}

class Container
{
    var $aLines;

    function Insert($oEntity,$iPos=-1)
    {
	if (is_string($oEntity))
	    $oEntity=new Text($oEntity);
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
    
    function Br($iPos=-1)
    {
	return $this->Insert(new Br(), $iPos);
    }    

    function Remove($iPos)
    {
	unset($this->aLines[$iPos]);
    }
    
    function Draw()
    {
	foreach ($this->aLines as $oEntity)
	    $oEntity->Draw();
    }
    
    function Container()
    {
	$this->aLines=array();
    }
    
    function Clear()
    {
	$this->aLines=array();
    }
}

class EventContainer extends Container
{
    var $aEvent=array();	//private

    function onClick($sEvent) {$this->aEvent["onclick"]=$sEvent;}
    function onLoad($sEvent) {$this->aEvent["onload"]=$sEvent;}
    function onMouseOver($sEvent) {$this->aEvent["onmouseover"]=$sEvent;}
    function onMouseOut($sEvent) {$this->aEvent["onmouseout"]=$sEvent;}
    function onChange($sEvent) {$this->aEvent["onchange"]=$sEvent;}
    function onKeyPress($sEvent) {$this->aEvent["onkeypress"]=$sEvent;}
    function onKeyUp($sEvent) {$this->aEvent["onkeyup"]=$sEvent;}
    function onSubmit($sEvent) {$this->aEvent["onsubmit"]=$sEvent;}
    //dodac wiecej funkcji...

    function Draw()
    {
	foreach ($this->aEvent as $sEventKey => $sEventValue)
	{
	    echo " $sEventKey=\"$sEventValue\"";
	}    
    }
}

class NamedContainer extends EventContainer
{
    var $sStyle;
    var $sClass;
    var $sId;
    var $sName;


    function Draw()
    {
	if ($this->sClass!="")
	    echo " class=\"{$this->sClass}\"";
	if ($this->sId!="")
	    echo " id=\"{$this->sId}\"";
	if ($this->sStyle!="")
	    echo " style=\"{$this->sStyle}\"";
	if ($this->sName!="")
	    echo " name=\"{$this->sName}\"";
	EventContainer::Draw();
    }
    function NamedContainer()
    {
	$this->Container();
	$this->sClass="";
	$this->sId="";
	$this->sStyle="";
	$this->aEvent=array();
    }
    
}

class Paragraph extends NamedContainer
{
    function Draw()
    {
	echo "<p";
	NamedContainer::Draw();
	echo ">";
	Container::Draw();
	echo "</p>";	
    }
}

class Div extends NamedContainer
{
    function Draw()
    {
	echo "<div";
	NamedContainer::Draw();
	echo ">";
	Container::Draw();
	echo "</div>";	
    }
}


class HTML extends NamedContainer
{
    var $sTitle;
    var $sRedirectURL;
    var $iRedirectTime;
    var $aStyles;
    var $bCentered;
    var $sCharset = "UTF-8";
    
    var $sJavascript = "";
    var $aJavascriptIncludes = array();
    var $sStyleThemePrefix = "";
    
    var $iMultipleLinkNum=0;	//private

    function Draw()
    {

    echo "<?xml version=\"1.0\" encoding=\"{$this->sCharset}\"?>\n";
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php
	echo '<html xmlns="http://www.w3.org/1999/xhtml"><head>' . "\n";
	echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset={$this->sCharset}\"/>\n";
	if ($this->sJavascript!="")
	{
	    echo "<script type=\"text/javascript\">\n";
	    echo $this->sJavascript;
	    echo "</script>\n";
	}
        echo "<title>{$this->sTitle}</title>\n";
	if ($this->sRedirectURL!="")
	    echo "<meta http-equiv=\"REFRESH\" content=\"{$this->iRedirectTime};url={$this->sRedirectURL}\"/>\n";
	foreach ($this->aStyles as $sCSSFile)
	    echo "<link rel=\"stylesheet\" href=\"{$sCSSFile}\"/>\n";
	foreach ($this->aJavascriptIncludes as $sJSFile)
	    echo "<script type=\"text/javascript\" src=\"{$sJSFile}\"></script>\n";
	echo "</head><body"; NamedContainer::Draw(); echo "><p>\n";
//	if ($this->bCentered)
//	    echo "<center>\n";
	Container::Draw();
//	if ($this->bCentered)
//	    echo "</center>";
	echo "</p></body></html>\n";
}
    
    function AddJavascriptFile($sFileName)
    {
	$this->aJavascriptIncludes[]=$sFileName;
    }
    
    function AddStyle($sFileName)
    {
	if ($this->sStyleThemePrefix!="")
	{
	    $sStylePrefix=htmlentities($_COOKIE[$this->sStyleThemePrefix . '_StyleTheme']);
	    if ($sStylePrefix!="")
	        $sFileName="themes/" . $sStylePrefix . "/" . $sFileName;
	}
	if ($bv=!in_array($sString,$this->aStyles))
	    $this->aStyles[]=$sFileName;
	return $bv;
    }    
    
    function HTML ($sTitle="")
    {
	$this->NamedContainer();
        $this->sRedirectURL="";
	$this->iRedirectTime=0;
	$this->aStyles=array();
	$this->bCentered=true;
	$this->sBodyClass="";
	$this->sTitle=$sTitle;
    }
    
    /*
    function &MultipleLink($aURLs, $oContent=NULL)
    {
	$this->iMultipleLinkNum++;
	$L = new Link("#",$oContent);
	$L->onClick("javascript:multiplelink{$this->iMultipleLinkNum}()");
	$this->Scriptbr();
	$this->Script("function multiplelink{$this->iMultipleLinkNum}()\n{");
	foreach ($aURLs as $sTarget => $sURL)
	    {
		if ($sTarget[0]=='_')	//special name
		    $this->Script($sTarget);
		else
		    $this->Script("parent." . $sTarget);
		$this->Script(".location='{$sURL}';\n");
	    }
	$this->Script("alert('dupa!');\n");
	$this->Script("return false;}\n");
	return $L;
    }
    */
    
    function Script($sString)
    {
	$this->sJavascript=$this->sJavascript . $sString;
    }
    
    function Scriptbr()
    {
	$this->sJavascript = $this->sJavascript . "\n";
    }
}


class Link extends NamedContainer
{
    var $sURL;
    var $aParams;
    var $sTarget;
    var $sHint;
    function Link($sURL="#",$oContent=NULL,$sTarget="",$sHint="")
    {
	$this->Container();
	$this->sURL=$sURL;
	if (!is_null($oContent))
	    Container::Insert($oContent);
	if ($sTarget!="")
	    $this->sTarget=$sTarget;
	if ($sHint!="")
	    $this->sHint=$sHint;
    }
    function MultipleLink($aURLs, $oContent=NULL)
    {
	$this->Link("#",$oContent);
	$S="";
	foreach ($aURLs as $sTarget => $sURL)
	    {
		if ($sTarget[0]=='_')	//special name
		    $S=$S . $sTarget;
		else
		    $S=$S . "parent." . $sTarget;
		$S = $S . ".location='{$sURL}'; ";
	    }
	$S=$S . "return false;";
	$this->onClick($S);
    }
    function Param($sVariable,$mValue)
    {
	$this->aParams[$sVariable]=$mValue;
    }
    function ResolveAddress()
    {
	$s=htmlentities($this->sURL);
	$first=true;
	if (count($this->aParams)>0)
	{
	    foreach ($this->aParams as $key => $param)
		{
		    if ($first)
			$s=$s . "?";
		    else
			$s=$s . "&amp;";
		    $s=$s . $key . "=" . $param;
		    $first=false;
		}
	}
	return $s;
    }
    function Draw()
    {
	echo "<a href=\"";
	print($this->ResolveAddress());
	echo "\"";
	NamedContainer::Draw();
	if ($this->sTarget!="")
	    echo " target=\"{$this->sTarget}\"";
	if ($this->sHint!="")
	    echo " title=\"{$this->sHint}\"";
	
	echo ">";
	Container::Draw();
	echo "</a>";
    }
}

class Image extends NamedContainer
{
    var $sImage;
    var $sAlt;
    var $sTitle;
    var $iWidth = 0;
    var $iHeight = 0;
    function Image($sTarget,$sAlt="",$sTitle="")
    {
	$this->sClass="";
	$this->sId="";
	$this->sTarget=$sTarget;
	if ($sAlt=="")
	    $this->sAlt=$sTarget;	//todo - wywalac sciezke i rozszezenie
	else
	    $this->sAlt=$sAlt;
	if ($sTitle!="")
	    $this->sTitle=$sTitle;
    }
    function Draw()
    {
	$target=htmlentities($this->sTarget);
	$title=htmlentities($this->sTitle);
	$alt=htmlentities($this->sAlt);
	echo "<img src=\"$target\" alt=\"$alt\"";
	if ($this->sTitle!="")
	    echo " title=\"$title\"";
	NamedContainer::Draw();
//	if ($this->sClass!="")
//	    echo " class=\"{$this->sClass}\"";
//	if ($this->sId!="")
//	    echo " id=\"{$this->sId}\"";
	if ($this->iWidth>0)
	    echo " width=\"{$this->iWidth}\"";
	if ($this->iHeight>0)
	    echo " height=\"{$this->iHeight}\"";
	echo "/>";
    }
}


class Table extends NamedContainer
{
    var $iWidth=0;

    var $iCols=0;
    var $iRows=0;
    var $aRowClass=array();
    var $aRowEvent=array();
    var $aRowPrefix=array();
    var $aRowSuffix=array();
    var $sDefaultRowClass;
    
//rowevents

    function onRowClick($iRow,$sEvent) {$this->aRowEvent[$iRow]["onClick"]=$sEvent;}
    function onRowLoad($iRow,$sEvent) {$this->aRowEvent[$iRow]["onLoad"]=$sEvent;}
    function onRowMouseOver($iRow,$sEvent) {$this->aRowEvent[$iRow]["onMouseOver"]=$sEvent;}
    function onRowMouseOut($iRow,$sEvent) {$this->aRowEvent[$iRow]["onMouseOut"]=$sEvent;}

//cellevents

    function onCellClick($iCol,$iRow,$sEvent) {$this->aLines[$iRow][$iCol]->onClick($sEvent);}
    function onCellLoad($iCol,$iRow,$sEvent) {$this->aLines[$iRow][$iCol]->onLost($sEvent);}
    function onCellMouseOver($iCol,$iRow,$sEvent) {$this->aLines[$iRow][$iCol]->onMouseOver($sEvent);}
    function onCellMouseOut($iCol,$iRow,$sEvent) {$this->aLines[$iRow][$iCol]->onMouseOut($sEvent);}
    
    function SetCols($iNumCols)
    {
	if ($iNumCols>$this->iCols)
	foreach ($this->aLines as $rownum => $row)
	    {
		for ($i=$this->iCols+1;$i<=$iNumCols;++$i)
		    $this->aLines[$rownum][$i]=new Cell();
	    }
	elseif ($iNumCols<$this->iCols)
	foreach ($this->aLines as $rownum => $row)
	    {
		for ($i=$iNumCols+1;$i<=$this->iCols;++$i)
		    unset($this->aLines[$rownum][$i]);
	    }
	$this->iCols=$iNumCols;
    }
    
    function SetRows($iNumRows)
    {
	if ($iNumRows>$this->iRows)
	for ($i=$this->iRows+1; $i<=$iNumRows; ++$i)
	    for ($j=1; $j<=$this->iCols; ++$j)
	        $this->aLines[$i][$j]=new Cell();
	elseif ($iNumRows<$this->iRows)
	for ($i=$iNumCols+1; $i<=$this->iRows; ++$i)
	    unset($this->aLines[$i]);
	$this->iRows=$iNumRows;
    }
    
    function Insert($iX, $iY, $oEntity)
    {
	if ($iX>$this->iCols)
	    $this->SetCols($iX);
	if ($iY>$this->iRows)
	    $this->SetRows($iY);
	if (!isset($this->aLines[$iY][$iX]))
	{
	    echo "Error: Cell [$iX,$iY] not accessible<br/>\n";
	    return false;
	}
	$DUPA=$this->aLines[$iY][$iX]->Insert($oEntity);
//	echo "$iX $iY {$this->iCols} {$this->iRows}";
    }

    function SetClass($iX, $iY, $sClass)
    {
        $this->aLines[$iY][$iX]->sClass=$sClass;
    }
    
    function GetClass($iX, $iY)
    {
	return $this->aLines[$iY][$iX]->sClass;
    }
    
    function SetStyle($iX, $iY, $sStyle)
    {
        $this->aLines[$iY][$iX]->sStyle=$sStyle;
    }

    function Join($iX, $iY, $iWidth, $iHeight)
    {
//	echo "JOIN [$iX,$iY] [$iWidth,$iHeight]<br/>";
	if ($iX+$iWidth-1>$this->iCols)
	    $iWidth=$this->iRows-$iX+1;
	if ($iY+$iHeight-1>$this->iRows)
	    $iHeight=$this->iCols-$iY+1;
//	val("Rows",$this->iRows);
//	val("Cols",$this->iCols);
//	val("Width",$iWidth);
//	val("Height",$iHeight);
	if ($iWidth<1 or $iHeight<1)
	    return;
	$C=&$this->Get($iX,$iY);
	if (!is_null($C))
	{
	    if ($iWidth>$C->iColSpan)
		for ($x=$iX+$C->iColSpan; $x<$iX+$iWidth; ++$x)
		    for ($y=$iY; $y<$iY+$iHeight; ++$y)
			{
			unset($this->aLines[$y][$x]);
			}
	    elseif ($iWidth<$C->iColSpan)
		for ($x=$iX+$iWidth; $x<$iX+$C->iColSpan; ++$x)
		    for ($y=$iY; $y<$iY+$C->iRowSpan; ++$y)
			{
			$this->aLines[$y][$x]=new Cell();
			}
			
	    if ($iHeight>$C->iRowSpan)
		for ($y=$iY+$C->iRowSpan; $y<$iY+$iHeight; ++$y)
		    for ($x=$iX; $x<$iX+$iWidth; ++$x)
			{
			unset($this->aLines[$y][$x]);
			}
	    elseif ($iHeight<$C->iRowSpan)
		for ($y=$iY+$iHeight; $y<$iY+$C->iRowSpan; ++$y)
		    for ($x=$iX; $x<$iX+$C->iColSpan; ++$x)
			{
			$this->aLines[$y][$x]=new Cell();
			}
	
	    $C->iRowSpan=$iHeight;
	    $C->iColSpan=$iWidth;
	}
    }
    
    function &Get($iX, $iY)
    {
	if (isset($this->aLines[$iY][$iX]))
	    return $this->aLines[$iY][$iX];
	else
	    return NULL;
    }    
    
    function Set($iX, $iY, $oCell)
    {
	if (get_class($oCell)!="Cell")
	{
	    echo "Error: Parameter oCell is not a Cell object, but " . get_class($oCell);
	    return false;
	}
	if ($iX>$this->iCols)
	    $this->SetCols($iX);
	if ($iY>$this->iRows)
	    $this->SetRows($iY);
	if (!isset($this->aLines[$iY][$iX]))
	{
	    echo "Error: Cell [$iX,$iY] not accessible<br/>\n";
	    return false;
	}
	$this->aLines[$iY][$iX]=$oCell;
    }

    function SetRowLink($iY, $sTarget)
    {
	$sTarget=str_replace("&","&amp;",$sTarget);
	$this->onRowClick($iY,"window.location.href='{$sTarget}'");
    }
        
    function Draw()
    {
	echo "<table"; NamedContainer::Draw();
	if ($this->iWidth>0)
	    echo " width=\"{$this->iWidth}\"";
	echo ">\n";
	foreach ($this->aLines as $iRowNum => $aRow)
	{
	    echo $this->aRowPrefix[$iRowNum];
	    echo "<tr";
	    if (isset($this->aRowClass[$iRowNum]))
		echo " class=\"{$this->aRowClass[$iRowNum]}\"";
	    elseif (isset($this->sDefaultRowClass))
		echo " class=\"{$this->sDefaultRowClass}\"";
	    if (isset($this->aRowEvent[$iRowNum]))
	    {
	    foreach ($this->aRowEvent[$iRowNum] as $name => $value)
		{
		    echo " {$name}=\"{$value}\"";
		}
	    }
	    echo ">";
	    foreach ($aRow as $iColNum => $oCell)
	    {
/*		if (isset($this->aCellLink[$iRowNum][$iColNum]))
		{
		    $this->aCellLink[$iRowNum][$iColNum]->Insert($oCell);
		    $this->aCellLink[$iRowNum][$iColNum]->Draw();
		}
		else*/
		    $oCell->Draw();
	    }
	    echo "</tr>";
	    echo $this->aRowSuffix[$iRowNum];
	    echo "\n";
	}
	echo "</table>\n";
    }
}

class Cell extends NamedContainer
{
    var $iColSpan;
    var $iRowSpan;
    var $iWidth=0;
    var $iHeight=0;
    
    function Draw()
    {
	echo "<td";
	NamedContainer::Draw();
	if ($this->iColSpan>1)
	    echo " colspan=\"{$this->iColSpan}\"";
	if ($this->iRowSpan>1)
	    echo " rowspan=\"{$this->iRowSpan}\"";    
	if ($this->iWidth>0)
	    echo " width=\"{$this->iWidth}\"";
	if ($this->iHeight>0)
	    echo " height=\"{$this->iHeight}\"";
	echo ">";
	Container::Draw();
	echo "</td>";
    }
    
    function Cell()
    {
	$this->NamedContainer();
	$this->iColSpan=1;
	$this->iRowSpan=1;
    }
}

class Form extends NamedContainer
{
    var $bPost;
    var $sTarget;
    
    function Form($sTarget,$bPost=true)
    {
	$this->Container();
	$this->sTarget=$sTarget;
	$this->bPost=$bPost;
    }
    function Draw()
    {
	echo "<form action=\"{$this->sTarget}\" method=\"";
	if ($this->bPost)
		echo "post";
	else
		echo "get";
	echo "\"";
	NamedContainer::Draw();
	echo ">\n";
	Container::Draw();
	echo "</form>\n";
    }
    
    function onSubmit($sEvent) {$this->aEvent["onSubmit"]=$sEvent;}

}

class Input extends NamedContainer
{
    var $sType = "";
    var $sValue = "";
    var $iTabindex = 0;
    var $bChecked = false;
    var $bReadonly = false;
    
    function Draw()
    {
	echo "<input type=\"$this->sType\"";
	NamedContainer::Draw();
	if ($this->sValue!="")
	    echo " value=\"{$this->sValue}\"";
	if ($this->iTabindex>0)
	    echo " tabindex=\"{$this->iTabindex}\"";
	if ($this->bChecked)
	    echo " checked=\"checked\"";
	if ($this->bReadonly)
	    echo " readonly=\"readonly\"";
	echo "/>\n";
    }
    function Input($sType,$sName,$sValue="",$sClass="",$iTabindex=0)
    {
	$this->sType=$sType;
	$this->sName=$sName;
	$this->sValue=$sValue;
	$this->sClass=$sClass;
	$this->iTabindex=$iTabindex;
    }


}

class Select extends NamedContainer
{
    var $aValues=array();
    var $sDefault;
        
    function AddOption($sValue,$sDescription)
    {
	$this->aValues[$sValue]=$sDescription;
    }
    function Draw()
    {
	echo "<select";
	NamedContainer::Draw();
	echo ">\n";
	foreach ($this->aValues as $sValue => $sDescription)
	    {
	    echo "<option";
	    if ($sValue==$this->sDefault)
		echo " selected=\"selected\"";
	    echo " value=\"{$sValue}\">{$sDescription}</option>\n";
	    }
	echo "</select>\n";
    }
}
?>
