//simple functions for ajax support

var _xmlHttp;

try {
    _xmlHttp=new XMLHttpRequest();
} catch (e) {
    try {
        _xmlHttp = new ActiveXObject("Microsoft.XMLHttp");
    }
    catch (e) {}
}

    
function ajaxRequest(addr,params,handler,usePost) {
    if (_xmlHttp && (_xmlHttp.readyState == 4 || _xmlHttp.readyState == 0)) {
	if (usePost) {
	    _xmlHttp.open('POST', addr, true);
	    _xmlHttp.onreadystatechange = handler;
	    _xmlHttp.send(params);
	} else {
	    _xmlHttp.open('GET', addr + '?' + params, true);
	    _xmlHttp.onreadystatechange = handler;
	    _xmlHttp.send(null);
	}
	return true;
    }
    else
	setTimeout("ajaxRequest(addr,params,handler,usePort)",1000);
    return false;
}

function ajaxGetResponse(debug) {
    if (_xmlHttp.readyState==4 && _xmlHttp.status == 200) {
	try {
	    if (debug)
		alert(_xmlHttp.responseText);
	    return _xmlHttp.responseXML.documentElement;
	} catch (e) {
	    return false;
	}
    }
    return false;
}

function ajaxAbort() {
    _xmlHttp.abort();
}
