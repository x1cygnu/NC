
var fromx=0;
var fromy=0;
var tox=0;
var toy=0;
var descr;

function initmap(newX, newY, newR)
{
    fromx=newX-newR;
    fromy=newY-newR;
    tox=newX+newR;
    toy=newY+newR;
}


function mIn(SID,Name,X,Y,Level)
{
    var map=window.document.getElementById('map');
    if (!map) return;
	
    cursor=document.createElement("div");

    descr=createFadingElement("div",0.0);
    descr.style.position="absolute";
    descr.style.zIndex="11";
    descr.innerHTML="<b>Star "+Name+"</b><br>Level "+Level+"<br>("+X+"/"+Y+")";
    if (X<tox-3)
        descr.style.left=((X-fromx+1)*25)+'px';
    else
        descr.style.right=((tox-X+1)*25)+'px';
    
    if (Y<toy-2)
	descr.style.top=((Y-fromy+1)*25)+'px';    
    else
	descr.style.bottom=((toy-Y+1)*25)+'px';    
    map.appendChild(descr);
    descr.fadeIn(0.1,60);
}

function mOut(SID,Name,X,Y,Level)
{
    var map=window.document.getElementById('map');
    if (!map) return;
    descr.fadeKill(0.1,60);
    var cursor=window.document.getElementById('starcursor');
    if (cursor)
	map.removeChild(cursor);
}

