var fadingElementInternalId=0;

function fadingElement(tag,initialFade) {
    document.createElement.call(this);
    alert(this);
    this.fadeLevel = initialFade;
    this.fadingIn = false;
    this.fadingOut = false;
    fadingElementInternalId=fadingElementInternalId+1;
    this.setAttribute("id","fElem" + fadingElementInternalId);
}

fadingElement.prototype = Node;


fadingElement.prototype.fadeIn = function(fadeStep, delayStep) {
    this.fadingOut=false;
    this.fadingIn=true;
    this.gentlyFadeIn(fadeStep, delayStep);
};

fadingElement.prototype.gentlyFadeIn = function(fadeStep, delayStep) {
    if (this.fadingIn) {
    if (this.fadeLevel>=1) {
	this.style.opacity="1.0";
	this.style.filter="alpha(opacity=100)";
    } else {
	this.style.opacity=this.fadeLevel;
	this.style.filter="alpha(opacity=" + makeint(this.fadeLevel*100) + ")";
        this.fadeLevel=this.fadeLevel+fadeStep;
	var str="document.getElementById('" + this.getAttribute("id") + "').fadeIn(" + fadeStep + "," + delayStep + ")";
	setTimeout(str,delayStep);
    }}
};

fadingElement.prototype.fadeOut = function(fadeStep, delayStep) {
    this.fadingOut=true;
    this.fadingIn=false;
    this.gentlyFadeOut(fadeStep, delayStep);
};


fadingElement.prototype.gentlyFadeOut = function(fadeStep, delayStep) {
    if (this.fadingOut) {
    if (this.fadeLevel<=0) {
	this.style.opacity="0.0";
	this.style.filter="alpha(opacity=0)";
	if (this.killOnFadeOut && this.killFrom) {
	    this.killFrom.removeChild(this);
	}
    } else {
	this.style.opacity=this.fadeLevel;
	this.style.filter="alpha(opacity=" + makeint(this.fadeLevel*100) + ")";
        this.fadeLevel=this.fadeLevel-fadeStep;
	var str="document.getElementById('" + this.getAttribute("id") + "').gentlyFadeOut(" + fadeStep + "," + delayStep + ")";
	setTimeout(str,delayStep);
    }}
};

fadingElement.prototype.fadeKill = function(fadeStep, delayStep, parentElement) {
    this.killOnFadeOut=true;
    this.killFrom=parentElement;
    this.fadeOut(fadeStep, delayStep);
};
