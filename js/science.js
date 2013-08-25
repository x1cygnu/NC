
function sciRemainTimeTimer() {
    window.document.getElementById('sciRemainTimeBox').innerHTML=showTime(sciRemainTime);
    if (sciRemainTime>0) {
	--sciRemainTime;
	setTimeout('sciRemainTimeTimer();',1000);
    }
}

function sciRemainPtsTimer(delay) {
    window.document.getElementById('sciRemainPtsBox').innerHTML=''+showFloat(sciRemainPts,2);
    if (sciRemainPts>0) {
	sciRemainPts=sciRemainPts-0.01;
	setTimeout('sciRemainPtsTimer('+delay+');',delay);
    }
}

function culRemainTimeTimer() {
    window.document.getElementById('culRemainTimeBox').innerHTML=showTime(culRemainTime);
    if (culRemainTime>0) {
	--culRemainTime;
	setTimeout('culRemainTimeTimer();',1000);
    }
}

function culRemainPtsTimer(delay)
{
    window.document.getElementById('culRemainPtsBox').innerHTML=''+showFloat(culRemainPts,2);
    if (culRemainPts>0) {
	culRemainPts=culRemainPts-0.01;
	setTimeout('culRemainPtsTimer('+delay+');',delay);
    }
}


function runTimers()
{
    if (typeof sciRemainTime!="undefined") {
	sciRemainTimeTimer();
	sciRemainPtsTimer((sciRemainTime/sciRemainPts)*10);
    }
    if (typeof culRemainTime!="undefined") {
	culRemainTimeTimer();
	culRemainPtsTimer((culRemainTime/culRemainPts)*10);
    
    }
}