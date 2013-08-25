
function drawStarsystem() {
    var doc = ajaxGetResponse(true);
    if (!doc) return;
    var dest = window.document.getElementById('planets');
    if (!dest) return;
    var sinfo = window.document.getElementById('

    var rowCount=dest.rows.length;
    var starsystems = doc.getElementsByTagName('starsystem');
    if (starsystems.length == 0)
	reportError("no starsystem data");
    else {
	var starsystem=starsystems[0];
        var planets = starsystem.getElementsByTagName('planet');
	for (i=0;i<planets.length;++i) {
	    if (rowCount<=i) {
		dest.insertRow(i);
		dest.rows[i].insertCell(0);
		dest.rows[i].insertCell(1);
	    }
	    var modRow=dest.rows[i].cells;
	    modRow[0].innerHTML=planets[i].getAttribute('ring');
	    modRow[1].innerHTML=getTextOf(planets[i],'pop');
	}

    }
    checkError(doc);
}
