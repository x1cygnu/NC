
function createFadingElement(tag,initialFade) {
    obj=document.createElement(tag);
    obj.fadeLevel = initialFade;
    obj.fadingIn = false;
    obj.fadingOut = false;
    obj.fadeIn=prototypeFadeIn;
    obj.gentlyFadeIn=prototypeGentlyFadeIn;
    obj.fadeOut=prototypeFadeOut;
    obj.gentlyFadeOut=prototypeGentlyFadeOut;
    obj.fadeKill=prototypeFadeKill;
    return obj;
}

prototypeFadeIn = function(fadeStep, delayStep) {
    this.fadingOut=false;
    this.fadingIn=true;
    this.gentlyFadeIn(fadeStep, delayStep);
};

prototypeGentlyFadeIn = function(fadeStep, delayStep) {
    if (this.fadingIn) {
    if (this.fadeLevel>=1) {
	this.style.opacity="1.0";
	this.style.filter="alpha(opacity=100)";
    } else {
	this.style.opacity=this.fadeLevel;
	this.style.filter="alpha(opacity=" + makeint(this.fadeLevel*100) + ")";
        this.fadeLevel=this.fadeLevel+fadeStep;
	var obj=this;
	setTimeout(function(){obj.gentlyFadeIn(fadeStep,delayStep)},delayStep);
    }}
};

prototypeFadeOut = function(fadeStep, delayStep) {
    this.fadingOut=true;
    this.fadingIn=false;
    this.gentlyFadeOut(fadeStep, delayStep);
};


prototypeGentlyFadeOut = function(fadeStep, delayStep) {
    if (this.fadingOut) {
    if (this.fadeLevel<=0) {
	this.style.opacity="0.0";
	this.style.filter="alpha(opacity=0)";
	if (this.killOnFadeOut) {
	    this.parentNode.removeChild(this);
	}
    } else {
	this.style.opacity=this.fadeLevel;
	this.style.filter="alpha(opacity=" + makeint(this.fadeLevel*100) + ")";
        this.fadeLevel=this.fadeLevel-fadeStep;
	var obj=this;
	setTimeout(function(){obj.gentlyFadeOut(fadeStep,delayStep)},delayStep);

    }}
};

prototypeFadeKill = function(fadeStep, delayStep) {
    this.killOnFadeOut=true;
    this.fadeOut(fadeStep, delayStep);
};
