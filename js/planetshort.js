
function Updatable(v) {
  this.old = v;
  this.curr = v;
}

function Resource(name) {
  var box = name + 'bx';
  var boxprg = name + 'prgbx';
  var boxperhour = name + 'Hbx';
  var boxtimenext = name + 'timebx';
  var value = new Updatable(0);
  var perhour = 0;
  var modifier = 0;
  this.toString = function() {
    return 'o'+name+'=' + value.old + ', '+name+'=' + value.curr + ', '+name+'H=' + perhour + ', '+name+'Mod=' + modifier;
  }
  this.init = function(ph, mod) {
    value.old = makeint(window.document.getElementById(box).innerHTML);
    value.curr = value.old;
    perhour = ph;
    modifier = mod;
  }

  this.has = function(v) {
    return (value.curr >= v)
  }

  //this should relate to PP only
  this.consume = function(v) {
    value.curr = value.curr - v;
    var diff = value.old - value.curr;
    window.document.getElementById(boxtimenext).innerHTML = showTime(diff * 3600.0 / perhour);
    window.document.getElementById(boxprg).innerHTML = '+' + diff;
    window.document.getElementById(box).innerHTML = value.curr;
  }
  this.reset = function() {
    value.curr = value.old;
    window.document.getElementById(box).innerHTML = value.curr;
    window.document.getElementById(boxprg).innerHTML = value.curr;

  }

}

var pop = new Resource('Pop');
var tx = new Resource('Tx');
var pp = new Resource('PP');

function buildingPointsForLvl(lvl,baseCost) {
  return Math.round(baseCost*Math.pow(1.3,lvl-1));
}

function Building(name) {
  var box = name + 'bx';
  var boxprg = name + 'prgbx';
  var boxspend = name + 'v';
  var level = new Updatable(0);
  var ppToNext = new Updatable(0);
  var spend = 0;
  var baseCost = 0;
  this.checkShow = function() {
    var obj = window.document.getElementById(boxspend);
    var money = makeint(obj.value);
    if (money>0)
      obj.parentNode.classList.remove("hiding");
    else
      obj.parentNode.classList.add("hiding");
  }
  this.alwaysShow = function() {
    window.document.getElementById(boxspend).parentNode.classList.remove("hiding");
  }
  this.toString = function() {
    return name+'[lvl='+level.old+' pp='+ppToNext.old+'] -> [lvl='+level.curr+' pp='+ppToNext.curr+']';
  }
  this.currLevelCost = function() {
    return buildingPointsForLvl(level.curr+1,baseCost);
  }
  this.init = function(nppToNext, nbaseCost) {
    level.old = makeint(window.document.getElementById(box).innerHTML);
    level.curr = level.old;
    baseCost = nbaseCost;
    ppToNext.old = nppToNext;
    ppToNext.curr = ppToNext.old;
//    alert(this.toString());
  }
  this.increase = function() {
    var additionalMoney = this.currLevelCost() - ppToNext.curr;
    if (pp.has(additionalMoney)) {
      var oldMoney = makeint(window.document.getElementById(boxspend).value);
      window.document.getElementById(boxspend).value = oldMoney + additionalMoney;
      //this.checkSpend is NOT triggered by change in value
      this.checkSpend();
    }
  }
  this.checkSpend = function() {
    var money = makeint(window.document.getElementById(boxspend).value);
    var spenddiff = money-spend;
    if (spenddiff != 0) {
      spend = money;
      level.curr = level.old;
      ppToNext.curr = ppToNext.old;
      var money = spend + ppToNext.old;
      while (this.currLevelCost() <= money) {
        money = money - this.currLevelCost();
        level.curr = level.curr + 1;
      }
      ppToNext.curr = money;
      window.document.getElementById(boxprg).innerHTML = ppToNext.curr + "/" + this.currLevelCost();
      window.document.getElementById(box).innerHTML = level.curr;
      pp.consume(spenddiff);
    }
    this.checkShow();
  }
}

var farm = new Building('Farm');
var factory = new Building('Factory');
var cybernet = new Building('Cybernet');
var laboratory = new Building('Lab');
var refinery = new Building('Refinery');

function Construct(name) {
  var box = name + 'bx';
  var level = new Updatable(0);
  var baseCost = 0;
  this.checkShow = function() {
    var obj = window.document.getElementById(box);
    if (obj.checked)
      obj.parentNode.classList.remove("hiding");
    else
      obj.parentNode.classList.add("hiding");
  }
  this.alwaysShow = function() {
    window.document.getElementById(box).parentNode.classList.remove("hiding");
  }
  this.toString = function() {
    return name+'['+level.old+'] -> ['+level.curr+']';
  }
  this.init = function(present, nbaseCost) {
    level.old = present;
    level.curr = level.old;
    baseCost = nbaseCost;
  }
  this.increase = function() {
    if (level.curr == 0) {
        if (pp.has(baseCost)) {
          level.curr = 1;
          pp.consume(baseCost);
          return;
        }
    }
    window.document.getElementById(box).checked = false;
  }
  this.decrease = function() {
    if (level.curr == 1 && level.old == 0) {
      level.curr = 0;
      pp.consume(-baseCost);
    }
  }
  this.change = function() {
    if (window.document.getElementById(box).checked)
      increase();
    else
      decrease();
    this.checkShow();
  }
}

var spacestation = new Construct("sps");
var embassy = new Construct("emb");
var gateway = new Construct("gtw");

function Ship(name) {
  var box = name + 'bx';
  var boxprg = name + 'prgbx';
  var boxspend = name + 'v';
  var amount = new Updatable(0);
  var ppToNext = new Updatable(0);
  var spend = 0;
  var baseCost = 0;
  this.checkShow = function() {
    var obj = window.document.getElementById(boxspend);
    var money = makeint(obj.value);
    if (money>0)
      obj.parentNode.classList.remove("hiding");
    else
      obj.parentNode.classList.add("hiding");
  }
  this.alwaysShow = function() {
    window.document.getElementById(boxspend).parentNode.classList.remove("hiding");
  }
  this.toString = function() {
    return name+'['+amount.old+' pp='+ppToNext.old+'] -> ['+amount.curr+' pp='+ppToNext.curr+']';
  }
  this.init = function(nppToNext, nbaseCost) {
    amount.old = makeint(window.document.getElementById(box).innerHTML);
    amount.curr = amount.old;
    baseCost = nbaseCost;
    ppToNext.old = nppToNext;
    ppToNext.curr = ppToNext.old;
//    alert(this.toString());
  }
  this.checkSpend = function() {
    var money = makeint(window.document.getElementById(boxspend).value);
    var spenddiff = money-spend;
    if (spenddiff != 0) {
      spend = money;
      var money = spend + ppToNext.old;
      var fullships = Math.floor(money/baseCost);
      amount.curr = amount.old + fullships;
      ppToNext.curr = money - fullships * baseCost;
      window.document.getElementById(boxprg).innerHTML = ppToNext.curr + "/" + baseCost;
      window.document.getElementById(box).innerHTML = amount.curr;
      pp.consume(spenddiff);
    }
    this.checkShow();
  }
  this.increase = function(amount) {
    var additionalMoney = amount * baseCost - ppToNext.curr;
    if (pp.has(additionalMoney)) {
      var oldMoney = makeint(window.document.getElementById(boxspend).value);
      window.document.getElementById(boxspend).value = oldMoney + additionalMoney;
      //this.checkSpend is NOT triggered by change in value
      this.checkSpend();
    }
  }
}

var starbase = new Ship('Starbase');
var viper = new Ship('Vpr');
var interceptor = new Ship('Int');
var frigate = new Ship('Fr');
var battleship = new Ship('Bs');
var dreadnought = new Ship('Drn');
var transporter = new Ship('Tr');
var colonyship = new Ship('CS');


