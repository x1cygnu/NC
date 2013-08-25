
function checkTT(fromPID) {
	var Vpr=window.document.getElementById('vpr').value;
	var Ints=window.document.getElementById('ints').value;
	var Fr=window.document.getElementById('fr').value;
	var Bs=window.document.getElementById('bs').value;
	var Drn=window.document.getElementById('drn').value;
	var CS=window.document.getElementById('cs').value;
	var Tr=window.document.getElementById('tr').value;

	var ToS=getRadioValue(window.document.getElementById('fleet'),'system');
	var ToO=0;
	if (window.document.getElementById('toorb'))
		ToO=window.document.getElementById('toorb').value;
	//    alert('making request!');
	var ToP=getRadioValue(window.document.getElementById('fleet'),'planet');
	if ((!ToS || !ToO) && (!ToP)) {hideTT(); return;}
	var fleetString=
		'&Vpr='+Vpr+
		'&Int='+Ints+
		'&Fr='+Fr+
		'&Bs='+Bs+
		'&Drn='+Drn+
		'&CS='+CS+
		'&Tr='+Tr;
	if (ToP) {
		ajaxRequest('xml/tt.php',
				'FromPID='+fromPID+'&ToPID='+ToP+fleetString
				,showTT,false);
	}
	else {
		ajaxRequest('xml/tt.php',
				'FromPID='+fromPID+'&ToS='+ToS+'&ToO='+ToO+fleetString,
				showTT,false);
	}
}

var traveltime=0;
var ETA=0;
var dest;
var simul;

function printTT()
{
    if (ETA>10 && dest) {
	dest.innerHTML=showDate(ETA)+'<br/>'+showFullTime(traveltime);
	ETA++;
	simul=setTimeout('printTT();',1000);
    }
}

function showTT()
{
    var doc = ajaxGetResponse();
    if (!doc) { hideTT(); return;}
    var destc = window.document.getElementById('ttcont');
    if (!destc) return;
    dest = window.document.getElementById('ttsum');
    if (!dest) return;
    var ttresp=doc.getElementsByTagName('traveltime')[0];
    if (ttresp)
    {
	ETA=getTextOf(ttresp,'ETA');
	traveltime=getTextOf(ttresp,'seconds');
        printTT(dest);
	destc.removeAttribute('style'); //show info
    }
    checkError(doc);
}

function hideTT()
{
    ETA=0;
    traveltime=0;
    if (simul)
    {
	clearTimeout(simul);
	simul=0;
    }
    var destc = window.document.getElementById('ttcont');
    if (destc) {
	destc.setAttribute('style','display : none;'); //hide info
    }
    unreportError();
}

