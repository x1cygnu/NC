
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
    value.old = parseInt(window.document.getElementById(box).innerHTML);
    value.curr = value.old;
    perhour = ph;
    modifier = mod;
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
  this.toString = function() {
    return name+'[lvl='+level.old+' pp='+ppToNext.old+'] -> [lvl='+level.curr+' pp='+ppToNext.curr+']';
  }
  this.currLevelCost = function() {
    return buildingPointsForLvl(level.curr+1,baseCost);
  }
  this.init = function(nppToNext, nbaseCost) {
    level.old = parseInt(window.document.getElementById(box).innerHTML);
    level.curr = level.old;
    baseCost = nbaseCost;
    ppToNext.old = nppToNext;
    ppToNext.curr = ppToNext.old;
//    alert(this.toString());
  }
}

var farm = new Building('Farm');
var factory = new Building('Factory');
var cybernet = new Building('Cybernet');
var laboratory = new Building('Lab');
var refinery = new Building('Refinery');

function Ship(name) {
  var box = name + 'bx';
  var boxprg = name + 'prgbx';
  var boxspend = name + 'v';
  var amount = new Updatable(0);
  var ppToNext = new Updatable(0);
  var spend = 0;
  var baseCost = 0;
  this.toString = function() {
    return name+'['+amount.old+' pp='+ppToNext.old+'] -> ['+amount.curr+' pp='+ppToNext.curr+']';
  }
  this.init = function(nppToNext, nbaseCost) {
    amount.old = parseInt(window.document.getElementById(box).innerHTML);
    amount.curr = amount.old;
    baseCost = nbaseCost;
    ppToNext.old = nppToNext;
    ppToNext.curr = ppToNext.old;
//    alert(this.toString());
  }
  this.checkSpend = function() {
    var money = parseInt(window.document.getElementById(boxspend).value);
    var spenddiff = money-spend;
    if (spenddiff != 0) {
      spend = money;
      var money = spend + ppToNext.old;
      var fullships = Math.floor(money/baseCost);
      amount.curr = amount.old + fullships;
      ppToNext.curr = money - fullships * baseCost;
      window.document.getElementById(boxprg).innerHTML = ppToNext.curr + "/" + baseCost;
      window.document.getElementById(box).innerHTML = amount.curr;
    }
  }
  this.increase = function(amount) {
    var additionalMoney = amount * baseCost - ppToNext.curr;
    var oldMoney = parseInt(window.document.getElementById(boxspend).value);
    window.document.getElementById(boxspend).value = oldMoney + additionalMoney;
    //this.checkSpend is NOT triggered by change in value
    this.checkSpend();
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

