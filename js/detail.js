
function drawStarsystem() {
    var doc = ajaxGetResponse();
    if (!doc) return;
    var dest = window.document.getElementById('planets');
    if (!dest) return;
    var star = window.document.getElementById('star');
    if (!star) return;

    var rowCount=dest.rows.length;
    var starsystems = doc.getElementsByTagName('starsystem');
    if (starsystems.length == 0)
	reportError("no starsystem data");
    else {
	var starsystem=starsystems[0];
	star.rows[0].cells[0].innerHTML=starsystem.getAttribute('name');
	star.rows[1].cells[1].innerHTML=starsystem.getAttribute('x')+'/'+starsystem.getAttribute('y');
	var sid=starsystem.getAttribute('sid');
	star.rows[2].cells[1].innerHTML=sid;
	star.rows[3].cells[1].innerHTML=starsystem.getAttribute('lvl');
	star.rows[4].cells[0].innerHTML='<a href="" onclick="ajaxRequest(\'xml/detail.php\',\'id='+sid+'\',textStarsystem,false);return false;">text form</a>';
	star.rows[4].cells[0].removeAttribute('style');
	
        var planets = starsystem.getElementsByTagName('planet');
	var numPlanets = planets.length;
	var legendSize = 2;
	for (j=numPlanets; j<rowCount-legendSize; ++j)
	    dest.deleteRow(numPlanets+legendSize);
	for (i=0;i<planets.length;++i) {
	    if (rowCount-legendSize<=i) {
		dest.insertRow(i+legendSize);
		for (j=0; j<7; ++j)
		    dest.rows[i+legendSize].insertCell(j);
	    }
	    var modRow=dest.rows[i+legendSize].cells;
	    var modRowGl=dest.rows[i+legendSize];
	    modRow[0].className='sublegend';
	    modRow[0].innerHTML=planets[i].getAttribute('ring');
	    modRow[1].innerHTML=planets[i].getAttribute('type');
	    modRow[2].innerHTML=getTextOf(planets[i],'pop');
	    modRow[3].innerHTML=getTextOf(planets[i],'tx');
	    modRow[4].innerHTML=getTextOf(planets[i],'SB');
	    if (planets[i].getAttribute('conq')==0) {
		modRow[5].innerHTML='Free Planet';
		modRowGl.className="freeplanet";
	    } else {
		owner=planets[i].getElementsByTagName('player');
		
		if (owner.length==0) {
		    modRow[5].innerHTML='unknown';
		    modRowGl.className="unknownplanet";
		} else {
		    modRowGl.className="takenplanet";
		    tag=getTextOf(owner[0],'tag');
		    player=getTextOf(owner[0],'name');
		    pid=getTextOf(owner[0],'pid');
				colorStyle="independent";
				if (tag == "RED")
					colorStyle="red";
				if (tag == "BLUE")
					colorStyle="blue";
		    var S='';
		    if (tag)
					S='<a class="'+colorStyle+'" href="alliance.php?tag='+tag+'&amp;b='+sid+'">['+tag+']</a> ';
		    S=S+'<a class="'+colorStyle+'" href="pinfo.php?id='+pid+'&amp;b='+sid+'">'+player+'</a>';
		    modRow[5].innerHTML=S;
		}
	    }
	    if (planets[i].getAttribute('slot')==0)
		modRowGl.className=modRowGl.className+"nocul";
	    modRow[6].innerHTML=planets[i].getAttribute('name');
	    if (planets[i].getElementsByTagName('siege').length>0)
		modRowGl.className="siegedplanet";
	    
	}

    }
    checkError(doc);
}

function textStarsystem() {
    var doc = ajaxGetResponse();
    if (!doc) return;
    var dest = window.document.getElementById('textsystem');
    if (!dest) return;
    dest.setAttribute('style','text-align : left;');

    var S = '<pre>' + getTextOf(doc,'time') + '<br/>';
    var starsystems = doc.getElementsByTagName('starsystem');
    if (starsystems.length == 0)
	reportError("no starsystem data");
    else {
	var starsystem=starsystems[0];
	var sid=starsystem.getAttribute('sid');
	S = S + 'Star <b>' + starsystem.getAttribute('name') + '</b> ('
	    + starsystem.getAttribute('x')+'/'+starsystem.getAttribute('y') + ') ['
	    + sid +']<br/>';
        var planets = starsystem.getElementsByTagName('planet');
	for (pidx=0;pidx<planets.length;pidx++) {
	    var P=planets[pidx];
	    S = S + '#' + stringFill(P.getAttribute('ring'),2) + ' '
		    + stringFill(P.getAttribute('type'),10) + ' '
		    + stringFill(getTextOf(P,'pop'),2) + 'p '
		    + stringFill(getTextOf(P,'tx'),2) + 'tx '
		    + stringFill(getTextOf(P,'SB'),2) + 'sb ';
	    if (P.getAttribute('conq')==0) {
		S = S + '--- free ---<br/>';
	    } else {
		owner=P.getElementsByTagName('player');
		if (owner.length==0) {
		S = S + '--- unknown ---<br/>';
		} else {
		    tag=getTextOf(owner[0],'tag');
		    player=getTextOf(owner[0],'name');
		    pid=getTextOf(owner[0],'pid');
		    if (player=='The Consortium')
			S=S + '--- consortium ---<br/>';
		    else {
			if (tag)
			    S=S + stringFill('['+tag+'] ',8);
			else
			    S=S + '        ';
			S = S + player + '<br/>';
		    }
		}
	    }
	}
	S = S + '</pre>';
	dest.innerHTML=S;
    }
    checkError(doc);
}
