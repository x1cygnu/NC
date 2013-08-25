
function getTextOf(XMLNode,tag)
{
    var T=XMLNode.getElementsByTagName(tag);
    if (T.length>0)
	return T[0].firstChild.nodeValue;
    return false;
}

function reportError(err)
{
    var errorcnt=window.document.getElementById("errorcnt");
    if (errorcnt)
	errorcnt.removeAttribute('style');
    var error=window.document.getElementById("error");
    if (!error) {
    var T=window.document.createElement("table");
    T.className="error";
    T.setAttribute("id","errorcnt");
    T.insertRow(0);
    var C=T.rows[0].insertCell(0);
    C.setAttribute("id","error");
    C.innerHTML=err;
    window.document.body.appendChild(T);
    }
    else
	error.innerHTML=err;
}

function unreportError()
{
    var errorcnt=window.document.getElementById("errorcnt");
    if (errorcnt)
	errorcnt.setAttribute('style','display : none;');
}

function checkError(XMLdoc)
{
    e=getTextOf(XMLdoc,'error');
    if (e)
    {
	reportError(e);
	return true;
	}
    unreportError();
    return false;
}

function stringFill(val,len)
{
    var S=String(val);
    for (i=val.length; i<len; ++i)
	S=' ' + S;
    return S;
}

function valFill(val,len)
{
    var S=new String(val);
    for (i=S.length; i<len; ++i)
	S='0' + S;
    return S;
}

function showTime(time)
{
    if (time>0)
    {
    days=Math.floor(time/86400);
    hours=Math.floor(time/3600)%24;
    minutes=Math.floor(time/60)%60;
    seconds=Math.floor(time)%60;
    if (days>0)
	return String(days) + 'd' + valFill(hours,2) + 'h';
    if (hours>0)
	return String(hours) + 'h' + valFill(minutes,2) + 'm';
    if (minutes>0)
	return String(minutes) + 'm' + valFill(seconds,2) + 's';
    return String(seconds) + 's';
    }
    else
	return '0';
}

function showFullTime(time)
{
    if (time>0)
    {
    days=Math.floor(time/86400);
    hours=Math.floor(time/3600)%24;
    minutes=Math.floor(time/60)%60;
    seconds=Math.floor(time)%60;
    if (days>0)
	return String(days) + 'd' + valFill(hours,2) + 'h' + valFill(minutes,2) + 'm' + valFill(seconds,2) + 's';
    if (hours>0)
	return String(hours) + 'h' + valFill(minutes,2) + 'm' + valFill(seconds,2) + 's';
    if (minutes>0)
	return String(minutes) + 'm' + valFill(seconds,2) + 's';
    return String(seconds) + 's';
    }
    else
	return '0';
}

var months=new Array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');

function showDate(date)
{
    var d=new Date(date*1000);
//    var d=new Date(0);
    var year=d.getUTCFullYear();
    var month=months[d.getUTCMonth()];
    var day=d.getUTCDate();
    var hour=d.getUTCHours();
    var minute=d.getUTCMinutes();
    var second=d.getUTCSeconds();
    return String(day)+' '+month+' '+hour+':'+valFill(minute,2)+':'+valFill(second,2);
}

function showFloat(f,decim)
{
    var S=new String(f);
    var pointIndex=S.indexOf('.');
    if (pointIndex==-1)
	return S+'.'+valFill('0',decim);
    if (S.length>pointIndex+1+decim)
        return S.substr(0,pointIndex+1+decim);
    return S+valFill('',pointIndex+1+decim-S.length);
}

function getRadioValue(frm,radioName)
{
    for (var i=0; i<frm.length; ++i)
    {
	var element=frm.elements[i];
	if (element.getAttribute('type')=='radio' && element.getAttribute('name')==radioName && element.checked==true)
	    return element.value;
    }
    return null;
}

function makeint(x)
{
    u=parseInt(x);
    if (!isNaN(u))
	return u;
    return 0;
}

function fadein(objid, fadeLevel, fadeStep, delayStep) {
    obj=document.getElementById(objid);
    if (!obj) return;
    fadeLevel=fadeLevel+fadeStep;
    if (fadeLevel>=1) {
	obj.style.opacity="0.99";
	obj.style.filter="alpha(opacity=99)";
    } else {
	obj.style.opacity=fadeLevel;
	obj.style.filter="alpha(opacity=" + makeint(fadeLevel*100) + ")";
	str="fadein('" + objid + "', " + fadeLevel + "," + fadeStep + "," + delayStep + ")";
	setTimeout(str,delayStep);
    }
}

function fadeout(objid, fadeLevel, fadeStep, delayStep, parentid) {
    obj=document.getElementById(objid);
    if (!obj) return;
    fadeLevel=fadeLevel-fadeStep;
    if (fadeLevel<=0) {
	parent=document.getElementById(parentid);
	if (parent)
	    parent.removeChild(obj);
    } else {
	obj.style.opacity=fadeLevel;
	obj.style.filter="alpha(opacity=" + makeint(fadeLevel*100) + ")";
	str="fadeout('" + objid + "', " + fadeLevel + "," + fadeStep + "," + delayStep + ",'" + parentid + "')";
	setTimeout(str,delayStep);
    }
}

