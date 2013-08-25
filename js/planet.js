/* most ugly code I have ever written.
I hope I won't have to look here again! */

var PP;
var prodMod;
var pop;
var fLvl, fRemain;
var rLvl, rRemain;
var cLvl, cRemain;
var lLvl, lRemain;
var eLvl, eRemain;
var sbLvl, sbRemain;

var vprsCount, vprsRemain;
var intsCount, intsRemain;
var frsCount, frsRemain;
var bssCount, bssRemain;
var drnsCount, drnsRemain;
var cssCount, cssRemain;
var trsCount, trsRemain;

var initPP;
var PPH;
var initfLvl, initfRemain, initrLvl, initrRemain, initcLvl, initcRemain;
var initlLvl, initlRemain, initeLvl, initeRemain, initsbLvl, initsbRemain;
var initvprsCount, initvprsRemain;
var initintsCount, initintsRemain;
var initfrsCount, initfrsRemain;
var initbssCount, initbssRemain;
var initdrnsCount, initdrnsRemain;
var initcssCount, initcssRemain;
var inittrsCount, inittrsRemain;
var vprsCost, intsCost, frsCost, bssCost, drnsCost, cssCost, trsCost;

var embassyState=0;
var gatewayState=0;
var spacestationState=0;

var buildingBaseCost=0;
var initBuildingBaseCost=0;

function initAll(vPP,vPPH,vpop,vprodMod,vfLvl,vfRem,vrLvl,vrRem,vcLvl,vcRem,
	vlLvl,vlRem,veLvl,veRem,vsbLvl,vsbRem,
	vvprsCount,vvprsRem,vintsCount,vintsRem,vfrsCount,vfrsRem,
	vbssCount, vbssRem, vdrnsCount, vdrnsRem, vcssCount, vcssRem,
	vtrsCount, vtrsRem,
	vvprsCost, vintsCost, vfrsCost, vbssCost, vdrnsCost, vcssCost, vtrsCost, vInitBuildingBaseCost)
    {
    initPP=vPP;
    PPH=vPPH;
    prodMod=vprodMod;
    pop=vpop;
    initfLvl=vfLvl;
    initfRemain=vfRem;
    initrLvl=vrLvl;
    initrRemain=vrRem;
    initcLvl=vcLvl;
    initcRemain=vcRem;
    initlLvl=vlLvl;
    initlRemain=vlRem;
    initeLvl=veLvl;
    initeRemain=veRem;
    initsbLvl=vsbLvl;
    initsbRemain=vsbRem;
    initvprsCount=vvprsCount;
    initvprsRemain=vvprsRem;
    initintsCount=vintsCount;
    initintsRemain=vintsRem;
    initfrsCount=vfrsCount;
    initfrsRemain=vfrsRem;
    initbssCount=vbssCount;
    initbssRemain=vbssRem;
    initdrnsCount=vdrnsCount;
    initdrnsRemain=vdrnsRem;
    initcssCount=vcssCount;
    initcssRemain=vcssRem;
    inittrsCount=vtrsCount;
    inittrsRemain=vtrsRem;
    
    vprsCost=vvprsCost;
    intsCost=vintsCost;
    frsCost=vfrsCost;
    bssCost=vbssCost;
    drnsCost=vdrnsCost;
    cssCost=vcssCost;
    trsCost=vtrsCost;
    
    initBuildingBaseCost=vInitBuildingBaseCost;
}

function resetAll() {
    PP=initPP;
    fLvl=initfLvl; fRemain=initfRemain;
    rLvl=initrLvl; rRemain=initrRemain;
    cLvl=initcLvl; cRemain=initcRemain;
    lLvl=initlLvl; lRemain=initlRemain;
    eLvl=initeLvl; eRemain=initeRemain;
    sbLvl=initsbLvl; sbRemain=initsbRemain;
    
    vprsCount=initvprsCount; vprsRemain=initvprsRemain;
    intsCount=initintsCount; intsRemain=initintsRemain;
    frsCount=initfrsCount; frsRemain=initfrsRemain;
    bssCount=initbssCount; bssRemain=initbssRemain;
    drnsCount=initdrnsCount; drnsRemain=initdrnsRemain;
    cssCount=initcssCount; cssRemain=initcssRemain;
    trsCount=inittrsCount; trsRemain=inittrsRemain;
    
    buildingBaseCost=initBuildingBaseCost;
}

var fv, rv, cv, lv, ev, sbv, vprsv, intsv, frsv, bssv, drnsv, cssv, trsv;
var pphbx;
var ppbx, fbx, rbx, cbx, lbx, ebx, sbbx, vprsbx, intsbx, frsbx, bssbx, drnsbx, cssbx, trsbx;
var pprbx, frbx, rrbx, crbx, lrbx, erbx, sbrbx, vprsrbx, intsrbx, frsrbx, bssrbx, drnsrbx, cssrbx, trsrbx;
var ppprgbx, fprgbx, rprgbx, cprgbx, lprgbx, eprgbx, sbprgbx, vprsprgbx, intsprgbx, frsprgbx, bssprgbx, drnsprgbx, cssprgbx, trsprgbx;
var pptimebx, fRPbx, rRPbx, cRPbx, lRPbx, eRPbx;
var gateway;
var embassy;

function identifyBoxes() {
    gateway=window.document.getElementById('gateway');
    embassy=window.document.getElementById('embassy');
    spacestation=window.document.getElementById('spacestation');

    pphbx=window.document.getElementById('PPHbx');
    
    fv=window.document.getElementById('Farmv');
    rv=window.document.getElementById('Factoryv');
    cv=window.document.getElementById('Cybernetv');
    lv=window.document.getElementById('Labv');
    ev=window.document.getElementById('Refineryv');
    sbv=window.document.getElementById('Starbasev');
    vprsv=window.document.getElementById('Vprv');
    intsv=window.document.getElementById('Intv');
    frsv=window.document.getElementById('Frv');
    bssv=window.document.getElementById('Bsv');
    drnsv=window.document.getElementById('Drnv');
    cssv=window.document.getElementById('CSv');
    trsv=window.document.getElementById('Trv');

    ppbx=window.document.getElementById('PPbx');
    fbx=window.document.getElementById('Farmbx');
    rbx=window.document.getElementById('Factorybx');
    cbx=window.document.getElementById('Cybernetbx');
    lbx=window.document.getElementById('Labbx');
    ebx=window.document.getElementById('Refinerybx');
    sbbx=window.document.getElementById('Starbasebx');
    vprsbx=window.document.getElementById('Vprbx');
    intsbx=window.document.getElementById('Intbx');
    frsbx=window.document.getElementById('Frbx');
    bssbx=window.document.getElementById('Bsbx');
    drnsbx=window.document.getElementById('Drnbx');
    cssbx=window.document.getElementById('CSbx');
    trsbx=window.document.getElementById('Trbx');

    ppprgbx=window.document.getElementById('PPprgbx');
    fprgbx=window.document.getElementById('Farmprgbx');
    rprgbx=window.document.getElementById('Factoryprgbx');
    cprgbx=window.document.getElementById('Cybernetprgbx');
    lprgbx=window.document.getElementById('Labprgbx');
    eprgbx=window.document.getElementById('Refineryprgbx');
    sbprgbx=window.document.getElementById('Starbaseprgbx');
    vprsprgbx=window.document.getElementById('Vprprgbx');
    intsprgbx=window.document.getElementById('Intprgbx');
    frsprgbx=window.document.getElementById('Frprgbx');
    bssprgbx=window.document.getElementById('Bsprgbx');
    drnsprgbx=window.document.getElementById('Drnprgbx');
    cssprgbx=window.document.getElementById('CSprgbx');
    trsprgbx=window.document.getElementById('Trprgbx');

    pprbx=window.document.getElementById('PPrbx');
    frbx=window.document.getElementById('Farmrbx');
    rrbx=window.document.getElementById('Factoryrbx');
    crbx=window.document.getElementById('Cybernetrbx');
    lrbx=window.document.getElementById('Labrbx');
    erbx=window.document.getElementById('Refineryrbx');
    sbrbx=window.document.getElementById('Starbaserbx');
    vprsrbx=window.document.getElementById('Vprrbx');
    intsrbx=window.document.getElementById('Intrbx');
    frsrbx=window.document.getElementById('Frrbx');
    bssrbx=window.document.getElementById('Bsrbx');
    drnsrbx=window.document.getElementById('Drnrbx');
    cssrbx=window.document.getElementById('CSrbx');
    trsrbx=window.document.getElementById('Trrbx');

    pptimebx=window.document.getElementById('PPtimebx');
    fRPbx=window.document.getElementById('FarmRPbx');
    rRPbx=window.document.getElementById('FactoryRPbx');
    cRPbx=window.document.getElementById('CybernetRPbx');
    lRPbx=window.document.getElementById('LabRPbx');
    eRPbx=window.document.getElementById('RefineryRPbx');
}

function ppForLevel(lvl) {
    return Math.round(buildingBaseCost*Math.pow(1.3,lvl-1));
}

function ppForLevelSB(lvl) {
    return Math.round(10*Math.pow(1.3,lvl-1));
}

function increasef() {
    if (PP>=fRemain)
    {
	fv.value=fRemain+makeint(fv.value);
	PP=0+PP-fRemain;
	fLvl=1+fLvl;
	fRemain=ppForLevel(fLvl+1);
	show();
    }
}

function increaser() {
    if (PP>=rRemain)
    {
	rv.value=rRemain+makeint(rv.value);
	PP=0+PP-rRemain;
	rLvl=1+rLvl;
	rRemain=ppForLevel(rLvl+1);
	show();
    }
}

function increasec() {
    if (PP>=cRemain) {
	cv.value=cRemain+makeint(cv.value);
	PP=0+PP-cRemain;
	cLvl=1+cLvl;
	cRemain=ppForLevel(cLvl+1);
	show();
    }
}

function increasel() {
    if (PP>=lRemain)
    {
	lv.value=lRemain+makeint(lv.value);
	PP=0+PP-lRemain;
	lLvl=1+lLvl;
	lRemain=ppForLevel(lLvl+1);
	show();
    }
}

function increasee() {
    if (PP>=eRemain)
    {
	ev.value=eRemain+makeint(ev.value);
	PP=0+PP-eRemain;
	eLvl=1+eLvl;
	eRemain=ppForLevel(eLvl+1);
	show();
    }
}

function increasesb() {
    if (PP>=sbRemain)
    {
	sbv.value=sbRemain+makeint(sbv.value);
	PP=0+PP-sbRemain;
	sbLvl=1+sbLvl;
	sbRemain=ppForLevelSB(sbLvl+1);
	show();
    }
}

function allf() {
    if (PP>0)
    {
	fv.value=PP+makeint(fv.value);
	PP=0;
	checkPrice();
	show();
    }
}

function allr() {
    if (PP>0)
    {
	rv.value=PP+makeint(rv.value);
	PP=0;
	checkPrice();
	show();
    }
}

function allc() {
    if (PP>0)
    {
	cv.value=PP+makeint(cv.value);
	PP=0;
	checkPrice();
	show();
    }
}

function alll() {
    if (PP>0)
    {
	lv.value=PP+makeint(lv.value);
	PP=0;
	checkPrice();
	show();
    }
}

function alle() {
    if (PP>0)
    {
	ev.value=PP+makeint(ev.value);
	PP=0;
	checkPrice();
	show();
    }
}

function allsb() {
    if (PP>0)
    {
	sbv.value=PP+makeint(sbv.value);
	PP=0;
	checkPrice();
	show();
    }
}

function increasevprs(amount) {
    var toSpend=vprsCost*amount+vprsRemain-vprsCost;
    if (PP>=toSpend)
    {
	vprsv.value=toSpend+makeint(vprsv.value);
	vprsCount=0+vprsCount+amount;
	vprsRemain=vprsCost;
	PP=0+PP-toSpend;
	show();
    }
}

function increaseints(amount) {
    var toSpend=intsCost*amount+intsRemain-intsCost;
    if (PP>=toSpend)
    {
	intsv.value=toSpend+makeint(intsv.value);
	intsCount=0+intsCount+amount;
	intsRemain=intsCost;
	PP=0+PP-toSpend;
	show();
    }
}

function increasefrs(amount) {
    var toSpend=frsCost*amount+frsRemain-frsCost;
    if (PP>=toSpend)
    {
	frsv.value=toSpend+makeint(frsv.value);
	frsCount=0+frsCount+amount;
	frsRemain=frsCost;
	PP=0+PP-toSpend;
	show();
    }
}

function increasebss(amount) {
    var toSpend=bssCost*amount+bssRemain-bssCost;
    if (PP>=toSpend)
    {
	bssv.value=toSpend+makeint(bssv.value);
	bssCount=0+bssCount+amount;
	bssRemain=bssCost;
	PP=0+PP-toSpend;
	show();
    }
}

function increasedrns(amount) {
    var toSpend=drnsCost*amount+drnsRemain-drnsCost;
    if (PP>=toSpend)
    {
	drnsv.value=toSpend+makeint(drnsv.value);
	drnsCount=0+drnsCount+amount;
	drnsRemain=drnsCost;
	PP=0+PP-toSpend;
	show();
    }
}

function increasetrs(amount) {
    var toSpend=trsCost*amount+trsRemain-trsCost;
    if (PP>=toSpend)
    {
	trsv.value=toSpend+makeint(trsv.value);
	trsCount=0+trsCount+amount;
	trsRemain=trsCost;
	PP=0+PP-toSpend;
	show();
    }
}

function increasecss(amount) {
    var toSpend=cssCost*amount+cssRemain-cssCost;
    if (PP>=toSpend)
    {
	cssv.value=toSpend+makeint(cssv.value);
	cssCount=0+cssCount+amount;
	cssRemain=cssCost;
	PP=0+PP-toSpend;
	show();
    }
}

function checkPrice() {
    var nPP=0;

    
    fLvl=initfLvl; fRemain=initfRemain;
    nPP=makeint(fv.value);
    while (nPP>=fRemain)
    {
	nPP=0+nPP-fRemain;
	fLvl=1+fLvl;
	fRemain=ppForLevel(fLvl+1);
    }
    fRemain=0+fRemain-nPP;



    rLvl=initrLvl; rRemain=initrRemain;
    nPP=makeint(rv.value);
    while (nPP>=rRemain)
    {
	nPP=0+nPP-rRemain;
	rLvl=1+rLvl;
	rRemain=ppForLevel(rLvl+1);
    }
    rRemain=0+rRemain-nPP;
    
    cLvl=initcLvl; cRemain=initcRemain;
    nPP=makeint(cv.value);
    while (nPP>=cRemain)
    {
	nPP=0+nPP-cRemain;
	cLvl=1+cLvl;
	cRemain=ppForLevel(cLvl+1);
    }
    cRemain=0+cRemain-nPP;

    lLvl=initlLvl; lRemain=initlRemain;
    nPP=makeint(lv.value);
    while (nPP>=lRemain)
    {
	nPP=0+nPP-lRemain;
	lLvl=1+lLvl;
	lRemain=ppForLevel(lLvl+1);
    }
    lRemain=0+lRemain-nPP;

    eLvl=initeLvl; eRemain=initeRemain;
    nPP=makeint(ev.value);
    while (nPP>=eRemain)
    {
	nPP=0+nPP-eRemain;
	eLvl=1+eLvl;
	eRemain=ppForLevel(eLvl+1);
    }
    eRemain=0+eRemain-nPP;

    sbLvl=initsbLvl; sbRemain=initsbRemain;
    nPP=makeint(sbv.value);
    while (nPP>=sbRemain) {
	nPP=0+nPP-sbRemain;
	sbLvl=1+sbLvl;
	sbRemain=ppForLevelSB(sbLvl+1);
    }
    sbRemain=0+sbRemain-nPP;

    var addit;
    
    vprsCount=initvprsCount; vprsRemain=initvprsRemain;
    nPP=makeint(vprsv.value);
    if (nPP>vprsRemain) {
	nPP=nPP-vprsRemain;
	vprsRemain=vprsCost;
	vprsCount=1+vprsCount;
    }
    if (nPP>=vprsCost) {
	addit=Math.floor(nPP/vprsCost);
        nPP=0+nPP-vprsCost*addit;
	vprsCount=0+vprsCount+addit;
    }
    vprsRemain=vprsRemain-nPP;

    intsCount=initintsCount; intsRemain=initintsRemain;
    nPP=makeint(intsv.value);
    if (nPP>intsRemain) {
	nPP=0+nPP-intsRemain;
	intsRemain=intsCost;
	intsCount=1+intsCount;
    }
    if (nPP>=intsCost) {
	addit=Math.floor(nPP/intsCost);
        nPP=0+nPP-intsCost*addit;
	intsCount=0+intsCount+addit;
    }
    intsRemain=intsRemain-nPP;

    frsCount=initfrsCount; frsRemain=initfrsRemain;
    nPP=makeint(frsv.value);
    if (nPP>frsRemain) {
	nPP=0+nPP-frsRemain;
	frsRemain=frsCost;
	frsCount=1+frsCount;
    }
    if (nPP>=frsCost) {
	addit=Math.floor(nPP/frsCost);
        nPP=0+nPP-frsCost*addit;
	frsCount=0+frsCount+addit;
    }
    frsRemain=frsRemain-nPP;

    bssCount=initbssCount; bssRemain=initbssRemain;
    nPP=makeint(bssv.value);
    if (nPP>bssRemain) {
	nPP=0+nPP-bssRemain;
	bssRemain=bssCost;
	bssCount=1+bssCount;
    }
    if (nPP>=bssCost) {
	addit=Math.floor(nPP/bssCost);
        nPP=0+nPP-bssCost*addit;
	bssCount=0+bssCount+addit;
    }
    bssRemain=bssRemain-nPP;

    drnsCount=initdrnsCount; drnsRemain=initdrnsRemain;
    nPP=makeint(drnsv.value);
    if (nPP>drnsRemain) {
	nPP=0+nPP-drnsRemain;
	drnsRemain=drnsCost;
	drnsCount=1+drnsCount;
    }
    if (nPP>=drnsCost) {
	addit=Math.floor(nPP/drnsCost);
        nPP=0+nPP-drnsCost*addit;
	drnsCount=0+drnsCount+addit;
    }
    drnsRemain=drnsRemain-nPP;

    cssCount=initcssCount; cssRemain=initcssRemain;
    nPP=makeint(cssv.value);
    if (nPP>cssRemain) {
	nPP=0+nPP-cssRemain;
	cssRemain=cssCost;
	cssCount=1+cssCount;
    }
    if (nPP>=cssCost) {
	addit=Math.floor(nPP/cssCost);
        nPP=0+nPP-cssCost*addit;
	cssCount=0+cssCount+addit;
    }
    cssRemain=cssRemain-nPP;

    trsCount=inittrsCount; trsRemain=inittrsRemain;
    nPP=makeint(trsv.value);
    if (nPP>trsRemain) {
	nPP=0+nPP-trsRemain;
	trsRemain=trsCost;
	trsCount=1+trsCount;
    }
    if (nPP>=trsCost) {
	addit=Math.floor(nPP/trsCost);
        nPP=0+nPP-trsCost*addit;
	trsCount=0+trsCount+addit;
    }
    trsRemain=trsRemain-nPP;

}

function count()
{
    PP=0+initPP-makeint(fv.value)-makeint(rv.value)-makeint(cv.value)
	-makeint(lv.value)-makeint(ev.value)-makeint(sbv.value)
	-makeint(vprsv.value)-makeint(intsv.value)-makeint(frsv.value)
	-makeint(bssv.value)-makeint(drnsv.value)-makeint(cssv.value)
	-makeint(trsv.value);
    if (spacestationState>0) PP-=256;
    if (embassyState>0)	PP-=512;
    if (gatewayState>0) PP-=6144;
}

function show()
{
    PPH=(pop+rLvl)*prodMod;

    ppbx.innerHTML=''+PP;
    if (PP<initPP) {
	ppbx.className='levelnum negative';
	ppprgbx.innerHTML=''+PP+'/'+initPP;
	pprbx.innerHTML='+'+(initPP-PP);
	pprbx.className='additional negative';
	pptimebx.className='additional negative';
	pptimebx.innerHTML=showTime((initPP-PP)*3600/PPH);
    } else {
	ppbx.className='levelnum';
	ppprgbx.innerHTML=''+initPP;
	pprbx.innerHTML='+1';
	pprbx.className='additional';
	pptimebx.className='additional positive';
    }
    pphbx.innerHTML='+'+showFloat(PPH,1)+'/h';
    
    fbx.innerHTML=''+fLvl;
    if (makeint(fv.value)>0) {
	fbx.className='levelnum positive';
	fprgbx.className='additional positive';
	frbx.className='additional positive';
    } else {
	fbx.className='levelnum';
	fprgbx.className='additional';
	frbx.className='additional';
    }
    fMax=ppForLevel(fLvl+1);
    fprgbx.innerHTML=''+(fMax-fRemain)+'/'+fMax;
    frbx.innerHTML='+'+fRemain;
    
    rbx.innerHTML=''+rLvl;
    if (makeint(rv.value)>0) {
	rbx.className='levelnum positive';
	rprgbx.className='additional positive';
	rrbx.className='additional positive';
    } else {
	rbx.className='levelnum';
	rprgbx.className='additional';
	rrbx.className='additional';
    }
    rMax=ppForLevel(rLvl+1);
    rprgbx.innerHTML=''+(rMax-rRemain)+'/'+rMax;
    rrbx.innerHTML='+'+rRemain;
    
    cbx.innerHTML=''+cLvl;
    if (makeint(cv.value)>0) {
	cbx.className='levelnum positive';
	cprgbx.className='additional positive';
	crbx.className='additional positive';
    } else {
	cbx.className='levelnum';
	cprgbx.className='additional';
	crbx.className='additional';
    }
    cMax=ppForLevel(cLvl+1);
    cprgbx.innerHTML=''+(cMax-cRemain)+'/'+cMax;
    crbx.innerHTML='+'+cRemain;

    lbx.innerHTML=''+lLvl;
    if (makeint(lv.value)>0) {
	lbx.className='levelnum positive';
	lprgbx.className='additional positive';
	lrbx.className='additional positive';
    } else {
	lbx.className='levelnum';
	lprgbx.className='additional';
	lrbx.className='additional';
    }
    lMax=ppForLevel(lLvl+1);
    lprgbx.innerHTML=''+(lMax-lRemain)+'/'+lMax;
    lrbx.innerHTML='+'+lRemain;

    ebx.innerHTML=''+eLvl;
    if (makeint(ev.value)>0) {
	ebx.className='levelnum positive';
	eprgbx.className='additional positive';
	erbx.className='additional positive';
    } else {
	ebx.className='levelnum';
	eprgbx.className='additional';
	erbx.className='additional';
    }
    eMax=ppForLevel(eLvl+1);
    eprgbx.innerHTML=''+(eMax-eRemain)+'/'+eMax;
    erbx.innerHTML='+'+eRemain;

    sbbx.innerHTML=''+sbLvl;
    if (makeint(sbv.value)>0) {
	sbbx.className='levelnum positive';
	sbprgbx.className='additional positive';
	sbrbx.className='additional positive';
    } else {
	sbbx.className='levelnum';
	sbprgbx.className='additional';
	sbrbx.className='additional';
    }
    sbMax=ppForLevelSB(sbLvl+1);
    sbprgbx.innerHTML=''+(sbMax-sbRemain)+'/'+sbMax;
    sbrbx.innerHTML='+'+sbRemain;

    vprsbx.innerHTML=''+vprsCount;
    if (makeint(vprsv.value)>0) {
	vprsbx.className='shipnum positive';
	vprsprgbx.className='additional positive';
	vprsrbx.className='additional positive';
    } else {
	vprsbx.className='shipnum';
	vprsprgbx.className='additional';
	vprsrbx.className='additional';
    }
    vprsprgbx.innerHTML=''+(vprsCost-vprsRemain)+'/'+vprsCost;
    vprsrbx.innerHTML='+'+vprsRemain;

    intsbx.innerHTML=''+intsCount;
    if (makeint(intsv.value)>0) {
	intsbx.className='shipnum positive';
	intsprgbx.className='additional positive';
	intsrbx.className='additional positive';
    } else {
	intsbx.className='shipnum';
	intsprgbx.className='additional';
	intsrbx.className='additional';
    }
    intsprgbx.innerHTML=''+(intsCost-intsRemain)+'/'+intsCost;
    intsrbx.innerHTML='+'+intsRemain;

    frsbx.innerHTML=''+frsCount;
    if (makeint(frsv.value)>0) {
	frsbx.className='shipnum positive';
	frsprgbx.className='additional positive';
	frsrbx.className='additional positive';
    } else {
	frsbx.className='shipnum';
	frsprgbx.className='additional';
	frsrbx.className='additional';
    }
    frsprgbx.innerHTML=''+(frsCost-frsRemain)+'/'+frsCost;
    frsrbx.innerHTML='+'+frsRemain;

    bssbx.innerHTML=''+bssCount;
    if (makeint(bssv.value)>0) {
	bssbx.className='shipnum positive';
	bssprgbx.className='additional positive';
	bssrbx.className='additional positive';
    } else {
	bssbx.className='shipnum';
	bssprgbx.className='additional';
	bssrbx.className='additional';
    }
    bssprgbx.innerHTML=''+(bssCost-bssRemain)+'/'+bssCost;
    bssrbx.innerHTML='+'+bssRemain;

    drnsbx.innerHTML=''+drnsCount;
    if (makeint(drnsv.value)>0) {
	drnsbx.className='shipnum positive';
	drnsprgbx.className='additional positive';
	drnsrbx.className='additional positive';
    } else {
	drnsbx.className='shipnum';
	drnsprgbx.className='additional';
	drnsrbx.className='additional';
    }
    drnsprgbx.innerHTML=''+(drnsCost-drnsRemain)+'/'+drnsCost;
    drnsrbx.innerHTML='+'+drnsRemain;

    cssbx.innerHTML=''+cssCount;
    if (makeint(cssv.value)>0) {
	cssbx.className='shipnum positive';
	cssprgbx.className='additional positive';
	cssrbx.className='additional positive';
    } else {
	cssbx.className='shipnum';
	cssprgbx.className='additional';
	cssrbx.className='additional';
    }
    cssprgbx.innerHTML=''+(cssCost-cssRemain)+'/'+cssCost;
    cssrbx.innerHTML='+'+cssRemain;

    trsbx.innerHTML=''+trsCount;
    if (makeint(trsv.value)>0) {
	trsbx.className='shipnum positive';
	trsprgbx.className='additional positive';
	trsrbx.className='additional positive';
    } else {
	trsbx.className='shipnum';
	trsprgbx.className='additional';
	trsrbx.className='additional';
    }
    trsprgbx.innerHTML=''+(trsCost-trsRemain)+'/'+trsCost;
    trsrbx.innerHTML='+'+trsRemain;
    
}

function resetInput(){
    fv.value=0;
    rv.value=0;
    cv.value=0;
    lv.value=0;
    ev.value=0;
    sbv.value=0;
    vprsv.value=0;
    intsv.value=0;
    frsv.value=0;
    bssv.value=0;
    drnsv.value=0;
    cssv.value=0;
    trsv.value=0;
    if (spacestation)
	spacestation.checked=false;
    if (embassy)
	embassy.checked=false;
    if (gateway)
	gateway.checked=false;
    spacestationState=0;
    embassyState=0;
    gatewayState=0;
}


function spacestationBuild()
{
    if (spacestation.checked && spacestationState==0) {
	if (PP>=256) {
	    PP=PP-256;
	    spacestationState=1;
	} else spacestation.checked=false;
    } else if (!spacestation.checked && spacestationState==1) {
	spacestationState=0;
	PP=PP+256;
    }
}


function embassyBuild()
{
    if (embassy.checked && embassyState==0) {
	if (PP>=512) {
	    PP=PP-512;
	    embassyState=1;
	} else embassy.checked=false;
    } else if (!embassy.checked && embassyState==1) {
	embassyState=0;
	PP=PP+512;
    }
}

function gatewayBuild()
{
    if (gateway.checked && gatewayState==0) {
	if (PP>=6144) {
	    PP=PP-6144;
	    gatewayState=1;
	} else gateway.checked=false;
    } else if (!gateway.checked && gatewayState==1) {
	gatewayState=0;
	PP=PP+6144;
    }
}
