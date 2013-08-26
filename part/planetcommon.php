<?php

global $GET;
$GET=array();


$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");

$H->sTitle="Northern Cross - Planet";

$sql=&OpenSQL($H);

ForceActivePlayer($sql, $H, "planet.php");

ForceFrozen($sql, $H);

ForceNoSitting($sql, $H, $_SESSION['PID']);

include("part/sitpid.php");

$H->AddStyle("detail.css");
$H->AddStyle("planet.css");
$H->AddStyle("hint.css");

$menuselected="Planets";
include("./part/mainmenu.php");


$eco=player_get_science($sql,$MainPID,"Engineering");

get("g","integer");
if (isset($GET['g']) and $GET['g']>0)
$GET['id']=$GET['g']-1;
elseif (isset($GET['emb']) and $GET['emb']>0)
$GET['id']=$GET['emb']-1;
else
get("id","integer");


$Index=planet_index($sql,$MainPID,$GET['id']);

$makeChanges=PostControl(true);

function B($thing)
{
  global $sql; global $buy; global $POST; global $H; global $GET; global $Error;
  global $Index; global $MainPID;
  post($thing . 'v','integer');
  if (exists($POST[$thing . 'v']) and $POST[$thing . 'v']>0)
  {
    if (planet_spend_pp($sql, $Index['here'], $POST[$thing . 'v']))
    {
      if (planet_building_build($sql, $MainPID, $Index['here'], $thing, $POST[$thing . 'v']))
        $buy=true;
      else
        $H->Insert($Error->Report());
    }
  }
}

function BRP($thing)
{
  global $sql; global $buy; global $POST; global $H; global $GET; global $Error;
  global $Index; global $MainPID;
  post($thing . 'v','integer');
  if (exists($POST[$thing . 'v']) and $POST[$thing . 'v']>0)
  {
    planet_build_RP($sql, $MainPID, $Index['here'], $thing, $POST[$thing . 'v']);
    $buy=true;    
  }
}



function S($thing)
{
  global $sql; global $buy; global $POST; global $H; global $GET; global $Error;
  global $Index; global $MainPID;
  post($thing . 'v','integer');
  if (exists($POST[$thing . 'v']) and $POST[$thing . 'v']>0)
  {
    if (planet_spend_pp($sql, $Index['here'], $POST[$thing . 'v']))
    {
      if (planet_build_ship($sql, $MainPID, $Index['here'], $thing, $POST[$thing . 'v']))
        $buy=true;
      else
        $H->Insert($Error->Report());
    }
  }
}

$Techs=tech_get_player_names($sql, $MainPID);


$P=planet_get_all($sql, $Index['here']);



/////////////////////////////////////////
// SUMMARY OF A PLANET
/////////////////////////////////////////

function planetSummary($P) {
  global $GET;
  global $Index;
  global $sitAddition;
  global $sql;

  $SummaryT=new Table();
  $SummaryT->sClass='title';
  $SummaryT->Insert(2,1,"{$P['Name']} {$P['Ring']}");
  $SummaryT->Insert(1,2,new Link("detail.php?id={$P['SID']}","View starsystem"));
  $prvid=$GET['id']-1;
  $nxtid=$GET['id']+1;
  if (isset($Index['prev']))
    $SummaryT->Insert(1,1,new Link("planet.php?id=$prvid" . $sitAddition,"<< Previous"));
  if (isset($Index['next']))
    $SummaryT->Insert(3,1,new Link("planet.php?id=$nxtid" . $sitAddition,"Next >>"));
  $SummaryT->Insert(2,2,"{$P['CustomName']}");
  get("nmch","integer");
  if (isset($GET['nmch']))
  {
    $F=new Form("planet.php?id={$GET['id']}" . $sitAddition,true);
    $F->Insert(new Input("text","newname","{$P['CustomName']}","text"));
    $F->Insert(new Input("hidden","orderid",$_SESSION['PostCode']+1));
    $F->Insert(new Input("submit","namechange","Change Name","smbutton"));
    $SummaryT->Insert(2,2,$F);
  }
  else
    $SummaryT->Insert(3,2,new Link("planet.php?id={$GET['id']}&nmch=1" . $sitAddition,"Change Name"));

  $SummaryT->Get(1,1)->sClass='lefttitle';
  $SummaryT->Get(2,1)->sClass='maintitle';
  $SummaryT->Get(3,1)->sClass='righttitle';

  $SummaryT->Insert(1,3,$P['TypeName']);
  $SummaryT->Insert(2,3,'Gr: ' . $P['Growth'] . '%, Sci: ' . $P['Science'] . '%, Cul: ' . $P['Culture'] . '%, Prod: ' . $P['Production']
      . '%, Tx:' . $P['ToxicStability'] . '%, Cost: ' . $P['BaseCost']);
  $SummaryT->Insert(2,3,new Br());
  if ($P['TechReq']==0)
    $TechReq='none';
  else
  {
    $Info=tech_get_info($sql, $P['TechReq']);
    $TechReq=$Info['Name'];
  }
  $SummaryT->Insert(2,3,'Culture ' . ($P['CultureSlot']==1?'Yes':'No') . ', Tech: '
      . $TechReq);
  $SummaryT->SetClass(1,3,'typeexpl');
  $SummaryT->SetClass(2,3,'typeexpl');
  return $SummaryT;
}


//////////////////////////////////////
// Environment
//////////////////////////////////////

include("part/planetcompute2.php");

$RP=makeinteger(player_get_RP($sql,$MainPID));

class EntryConfig {
  var $table;
  var $titleXY;
  var $valueXY;
  var $progressXY;
  var $hourXY;
  var $incomeXY;
  var $detailXY;
  var $PPremXY;
  var $RPremXY;
  var $buildboxXY;
}

$hintstr="hinted" . $_SESSION['Hint'];
$subhint="sublegend " . $hintstr;

function planetResources($ResT, $offset, $P,$Config) {
  global $hintstr;
  global $subhint;

  $ObjT=clone $Config->table;

  $DO=new Div();
  $DO->Insert("Population");
  $DO->sClass=$hintstr;
  if ($_SESSION['Hint']>0)
  {
    $D=new Div();
    $D->Insert("Produces:"); $D->Br();
    $D->Insert("* 1 production point per hour"); $D->Br();
    $D->Insert("* 1 science point per hour"); $D->Br();
    $DO->Insert($D);
  }
  if (isset($Config->titleXY)) $ObjT->Insert($Config->titleXY,$DO);
  if (isset($Config->valueXY)) $ObjT->Insert($Config->valueXY,$P['Pop']);

  if (isset($Config->progressXY)) $ObjT->Insert($Config->progressXY,$P['PopGo'] . '/' . $P['PopMax']);
  if (isset($Config->incomeXY)) {
    $ObjT->Insert($Config->incomeXY,'+'.sprintf("%.1f",$P['PopH']).'/h');
    $ObjT->SetClass($Config->incomeXY,"additional " . ($P['PopH']>0?"positive":"negative"));
  }
  if (isset($Config->timeremXY)) {
    if ($P['PopH']>0)
    {
      $PopTRemainReadable=time_period_short($P['PopTRemain']);
      $ObjT->Insert($Config->timeremXY,$PopTRemainReadable);
      $ObjT->SetClass($Config->timeremXY,"additional positive");
    }
    else
    {
      $ObjT->Insert($Config->timeremXY,"infinity");
      $ObjT->SetClass($Config->timeremXY,"additional negative");
    }
  }
  if (isset($Config->detailXY)) {
    $ObjT->Set($Config->detailXY, InfoBoxCell('Population'));
  }

  $ResT->Insert($offset->x,$offset->y,$ObjT);

  ///////////////////////////////////////
  // Toxic
  ///////////////////////////////////////

  $ObjT=clone $Config->table;

  $DO=new Div();
  $DO->Insert("Toxic");
  $DO->sClass=$hintstr;
  if ($_SESSION['Hint']>0)
  {
    $D=new Div();
    $D->Insert("Impact:"); $D->Br();
    $D->Insert("Reduces your growth and production by 0.5% per level."); $D->Br();
    $D->Insert("Affected by:"); $D->Br();
    $D->Insert("Constructing refineries and developping urban science will help you reducing pollution");
    $DO->Insert($D);
  }
  if (isset($Config->titleXY)) $ObjT->Insert($Config->titleXY,$DO);
  if (isset($Config->valueXY)) $ObjT->Insert($Config->valueXY,$P['Tx']);

  if (isset($Config->progressXY)) $ObjT->Insert($Config->progressXY,$P['TxGo'] . '/1000');
  if (isset($Config->incomeXY)) {
    $ObjT->Insert($Config->incomeXY,'+'.sprintf("%.1f",$P['TxH']).'/h');
    $ObjT->SetClass($Config->incomeXY,"additional " . ($P['TxH']>0?"positive":"negative"));
  }
  if (isset($Config->timeremXY)) {
    if ($P['TxTRemain']!=0)
    {
      $TxTRemainReadable=time_period_short($P['TxTRemain']);
      $ObjT->Insert($Config->timeremXY,$TxTRemainReadable);
      if ($P['TxH']>0)
        $ObjT->SetClass($Config->timeremXY,"additional negative");
      else
        $ObjT->SetClass($Config->timeremXY,"additional positive");
    }
    else
    {
      $ObjT->Insert($Config->timeremXY,"balanced");
      $ObjT->SetClass($Config->timeremXY,"additional positive");
    }
  }
  if (isset($Config->detailXY)) {
    $ObjT->Set($Config->detailXY, InfoBoxCell('Toxic'));
  }

  $ResT->Insert($offset->x+1,$offset->y,$ObjT);

  ///////////////////////////////////////
  // Production Points
  ///////////////////////////////////////

  $ObjT=clone $Config->table;

  $DO=new Div();
  $DO->Insert("Production Points");
  $DO->sClass=$hintstr;
  if ($_SESSION['Hint']>0)
  {
    $D=new Div();
    $D->Insert("Production Points is 'money' which you can spend on various buildings and ships on this planet"); $D->Br();
    $DO->Insert($D);
  }
  if (isset($Config->titleXY)) $ObjT->Insert($Config->titleXY,$DO);
  if (isset($Config->valueXY)) {
    $ObjT->Insert($Config->valueXY,$P['PP']);
    $ObjT->Get($Config->valueXY)->sId="PPbx";
  }

  if (isset($Config->progressXY)) {
    $ObjT->Insert($Config->progressXY,$P['PP']);
    $ObjT->Get($Config->progressXY)->sId="PPprgbx";
  }
  if (isset($Config->incomeXY)) {
    $ObjT->Insert($Config->incomeXY,'+'.sprintf("%.1f",$P['PPH']).'/h');
    $ObjT->SetClass($Config->incomeXY,"additional " . ($P['PPH']>0?"positive":"negative"));
    $ObjT->Get($Config->incomeXY)->sId="PPHbx";
  }
  if (isset($Config->timeremXY)) {
    if (isset($P['PP1Time']))
    {
      $ObjT->Insert($Config->timeremXY,time_period_short($P['PP1Time']));
      $ObjT->SetClass($Config->timeremXY,"additional positive");
    }
    else
    {
      $ObjT->Insert($Config->timeremXY,"infinity");
      $ObjT->SetClass($Config->timeremXY,"additional negative");
    }
    $ObjT->Get($Config->timeremXY)->sId="PPtimebx";
  }
  if (isset($Config->detailXY)) {
    $ObjT->Set($Config->detailXY, InfoBoxCell('PP'));
  }

  $ResT->Insert($offset->x+2,$offset->y,$ObjT);


  $ResT->Get($offset->x,$offset->y)->sStyle='vertical-align : top';
  $ResT->Get($offset->x+1,$offset->y)->sStyle='vertical-align : top';
  $ResT->Get($offset->x+2,$offset->y)->sStyle='vertical-align : top';
  return $ResT;
}

/////////////////////////////////////
// Building info box
/////////////////////////////////////
function SmartButton(&$Inc,$sn)
{
  $Inc->onClick("increase{$sn}()");
}

function IncreaseButton(&$Inc,$sn,$v)
{
  $Inc->onClick("increase{$sn}($v)");
}

function AllButton(&$AllB,$sn)
{
  $AllB->onClick("all{$sn}();");
}

function BuildingInfoBox($Config, $ObjName, $ObjDBName, $ObjShortName, $HintMessage, $UseRP, $Max=NULL, $showButtons=true)
{
  global $Siege;
  global $P;
  if ($ObjDBName=="Starbase")
    $BaseCost=10;
  else
    $BaseCost=$P['BaseCost'];
  $ObjT=clone $Config->table;
  $DO=new Div();
  $DO->Insert($ObjName);
  global $hintstr;
  $DO->sClass=$hintstr;
  if ($_SESSION['Hint']>0)
  {
    $D=new Div();
    $D->Insert($HintMessage);
    $DO->Insert($D);
  }
  $ObjT->Insert($Config->titleXY,$DO);

  $ObjT->Insert($Config->valueXY,'' . $P[$ObjDBName]);
  $ObjT->Get($Config->valueXY)->sId=$ObjDBName . 'bx';

  if (!isset($Max))
  {
    $FMax=building_points_for_lvl($P[$ObjDBName]+1,$BaseCost);
    $FRemain=$P[$ObjDBName . 'Remain'];
  }
  else
  {
    $FMax=$Max;
    $FRemain=$FMax-$P[$ObjDBName . 'Remain'];
  }
  if (!$Max || !$Siege)
  {
    if ($FMax<100000)
      $ObjT->Insert($Config->progressXY,($FMax-$FRemain).'/'.$FMax);
    else
      $ObjT->Insert($Config->progressXY,floor(100*($FMax-$FRemain)/$FMax).'%');
    $ObjT->Get($Config->progressXY)->sId=$ObjDBName . 'prgbx';
    if (isset($Config->PPremXY)) {
      if ($FRemain<100000 || !$UseRP)
        $ObjT->Insert($Config->PPremXY,'+'.$FRemain);
      else
        $ObjT->Insert($Config->PPremXY,'a lot');
    }
    if (isset($Config->RPremXY))
      if ($UseRP)
        $ObjT->Insert($Config->RPremXY,ceil($FRemain*10/$FMax) . 'RP');
  }
  if (isset($Config->PPremXY)) $ObjT->Get($Config->PPremXY)->sId=$ObjDBName . 'rbx';
  if (isset($Config->RPremXY)) $ObjT->Get($Congig->RPremXY)->sId=$ObjDBName . 'RPbx';

  if (!$Siege and isset($Config->buildboxXY))
  {
    $Inc=new Input("button","","I","incbutton");
    $IncX=new Input("button","","X","incbutton");
    $IncC=new Input("button","","C","incbutton");
    $IncM=new Input("button","","M","incbutton");
    $AllB=new Input("button","","A","allbutton");
    $Field=new Input("text","","0","text number");
    $Field->onChange("checkPrice(); count(); show()");
    if ($showButtons)
    {
      if (!isset($Max))
      {
        SmartButton($Inc,$ObjShortName);
        AllButton($AllB,$ObjShortName);
        $ObjT->Insert(1,5,$Inc);
        $ObjT->Insert(1,5,$AllB);
      }
      else
      {
        IncreaseButton($Inc,$ObjShortName,1);
        IncreaseButton($IncX,$ObjShortName,10);
        IncreaseButton($IncC,$ObjShortName,100);
        IncreaseButton($IncM,$ObjShortName,1000);
        $ObjT->Insert(1,5,$Inc);
        $ObjT->Insert(1,5,$IncX);
        $ObjT->Insert(1,5,$IncC);
        $ObjT->Insert(1,5,$IncM);
      }
      $Field->sStyle='';
    }
    else
    {
      $Field->sStyle='display : none;';
      $ObjT->Insert(1,5,"Prototype required");
      $ObjT->SetClass(1,5,'negative');
    }
    $Field->sName=$Field->sId=$ObjDBName . "v";
    $ObjT->Insert(1,5,$Field);
  }

  return $ObjT;
}

/////////////////////////////////////
// Buildings
/////////////////////////////////////
function planetBuildings($BuildT,$offset,$P,$Config) {

  $BuildT->Insert($offset->x,$offset->y,
      BuildingInfoBox($Config,"Farm","Farm",'f',"Increases growth - population grows faster",true));
  $BuildT->Insert($offset->x+1,$offset->y,
      BuildingInfoBox($Config,"Factory","Factory",'r',"Produces Production Points",true));
  $BuildT->Insert($offset->x+2,$offset->y,
      BuildingInfoBox($Config,"Cybernet","Cybernet",'c',"Increases culture level which will allow you to control more planets",true));
  $BuildT->Insert($offset->x+3,$offset->y,
      BuildingInfoBox($Config,"Laboratory","Lab",'l',"Increases speed of your research",true));
  $BuildT->Insert($offset->x+4,$offset->y,
      BuildingInfoBox($Config,"Refinery","Refinery",'e',"Depollutes your planet",true));

  return $BuildT;
}

/////////////////////////////////////
// Low Orbit 
/////////////////////////////////////
function planetLowOrbit($BuildT,$offset,$P,$Config) {

  $BuildT->Insert($offset->x,$offset->y,
      BuildingInfoBox($Config,"Starbase","Starbase",'sb',"Cheap yet stationary defence",false,SB_points()));

  return $BuildT;
}

//////////////////////////////////
// ORBIT
//////////////////////////////////

function planetHighOrbit($BuildT, $offset, $P, $Config) {
  global $Pl; //Player data
  global $Techs; //Player tech data
  global $Siege; //siege status
  global $sql;

  $BuildT->Insert($offset->x,$offset->y,
      BuildingInfoBox($Config,"Vipers","Vpr",'vprs',"Manurevalbe and fast light fighter",false,$VprMax=Vpr_points($Pl['Engineering']),tech_check_name($Techs,'Vpr')));
  $BuildT->Insert($offset->x+1,$offset->y,
      BuildingInfoBox($Config,"Interceptors","Int",'ints',"Standard light fighter",false,$IntMax=Int_points($Pl['Engineering'])));
  $BuildT->Insert($offset->x,$offset->y+1,
      BuildingInfoBox($Config,"Frigates","Fr",'frs',"Well-armoured warship",false,$FrMax=Fr_points($Pl['Engineering']),tech_check_name($Techs,'Fr')));
  $BuildT->Insert($offset->x+1,$offset->y+1,
      BuildingInfoBox($Config,"Battleships","Bs",'bss',"Big, overpowered ship",false,$BsMax=Bs_points($Pl['Engineering']),tech_check_name($Techs,'Bs')));
  $BuildT->Insert($offset->x+2,$offset->y+1,
      BuildingInfoBox($Config,"Dreadnoughts","Drn",'drns',"Strongest of all warhips, yet relatively slow",false,$DrnMax=Drn_points($Pl['Engineering']),tech_check_name($Techs,'Drn')));
  $BuildT->Insert($offset->x+3,$offset->y,
      BuildingInfoBox($Config,"Transporters","Tr",'trs',"Defenceless ship carrying infrantry for onground desant.<br>Use these to conquer enemy planets.",false,$TrMax=Tr_points($Pl['Engineering'])));
  $BuildT->Insert($offset->x+3,$offset->y+1,
      BuildingInfoBox($Config,"Colony Ships","CS",'css',"Defenceless ship carrying settlers for new worlds.<br>Use these to take over free planets",false,$CSMax=CS_points($Pl['Engineering'])));

  return $BuildT;
}

if (false) {

  $F->Insert($BuildT);

  $BuildT=new Table();
  $BuildT->SetCols(4);
  $BuildT->Insert(1,1,"Orbit");
  $BuildT->SetClass(1,1,'title');
  $BuildT->Join(1,1,4,1);

  if (!$Siege)
    $ObjTTemplate->SetClass(1,2,'shipnum');
  else
    $ObjTTemplate->SetClass(1,2,'shipnum negative');



  $BuildT->Insert(4,4,new Input("submit","spend","spend PP","smbutton"));
  $BuildT->Insert(3,4,'(' . $RP . ')');
  $BuildT->Insert(3,4,new Input("submit","spend","spend RP","smbutton"));
  $BuildT->Insert(2,4,new Input("submit","spend","spend CS","smbutton"));
  $ResetButton=new Input("button","","Reset","smbutton");
  $ResetButton->onClick("resetAll(); resetInput(); show()");
  $BuildT->Insert(1,4,$ResetButton);
  $BuildT->aRowClass[4]='title';
  $F->Insert($BuildT);

  ///////////////////////////////////////////
  // CONSTRUCTIONS
  ///////////////////////////////////////////


  $BuildT=new Table();
  $BuildT->SetCols(3);
  $BuildT->SetRows(4);
  $BuildT->Insert(1,1,"Constructions");
  $BuildT->Join(1,1,3,1);
  $BuildT->SetClass(1,1,'title');
  $BuildT->Insert(1,2,MakeHint("Space Station","Landing base for your and ally fleets. May reduce travel times even further when equipped with Arrestor Field generator"));
  $BuildT->Insert(2,2,MakeHint("Embassy","Representative building, allowing you to form or join an alliance and have access to alliance screen. You need only one embassy on any of your planets."));
  $BuildT->Insert(3,2,MakeHint("Gateway","Hi-tech nearly-instant travel device based on artifically created wormholes."));
  $BuildT->SetClass(1,2,'legend construction');
  $BuildT->SetClass(2,2,'legend construction');
  $BuildT->SetClass(3,2,'legend construction');

  /////////////////////////////
  // Space Station
  /////////////////////////////

  if ($P['SpaceStation']==1)
  {
    $BuildT->Insert(1,3,'Present');
    $BuildT->Join(1,3,1,2);
  }
  else
  {
    $BuildT->Insert(1,3,'256PP');
    $EmbCh=new Input("checkbox","spacestation",1,"chbx");
    $EmbCh->onChange("spacestationBuild(); show();");
    $EmbCh->sId='spacestation';
    $BuildT->Insert(1,4,$EmbCh);
    $BuildT->Insert(1,4,"build");
  }


  /////////////////////////////
  // Embassy
  /////////////////////////////

  if ($P['Embassy']==1)
  {
    $BuildT->Insert(2,3,'Present');
    $BuildT->Join(2,3,1,2);
  }
  else
  {
    $BuildT->Insert(2,3,'512PP');
    $EmbCh=new Input("checkbox","embassy",1,"chbx");
    $EmbCh->onChange("embassyBuild(); show();");
    $EmbCh->sId='embassy';
    $BuildT->Insert(2,4,$EmbCh);
    $BuildT->Insert(2,4,"build");
  }

  /////////////////////////////
  // Gateway
  /////////////////////////////

  if ($P['Gateway']!="")
  {
    $BuildT->Insert(3,3,'' . $P['Gateway']);
    $BuildT->Insert(3,4,new Input("submit","ccode","Change","smbutton"));
    $BuildT->Insert(3,4,new Input("text","gcode",'' . $P['Gateway'],"text"));
  }
  else
  {
    $BuildT->Insert(3,3,'6144PP');
    if (tech_check_name($Techs,'WHole'))
    {
      $GtwCh=new Input("checkbox","gateway",1,"chbx");
      $GtwCh->onChange("gatewayBuild(); show();");
      $GtwCh->sId='gateway';
      $BuildT->Insert(3,4,$GtwCh);
      $BuildT->Insert(3,4,"build");
    }
    else
    {
      $BuildT->Insert(3,4,"Wormhole tech required");
      $BuildT->SetClass(3,4,'negative');
    }
  }


  $F->Insert($BuildT);

  $H->AddJavascriptFile("js/common.js");
  $H->AddJavascriptFile("js/planet.js");
  $VprRemain=$VprMax-$P['VprRemain'];
  $IntRemain=$IntMax-$P['IntRemain'];
  $FrRemain=$FrMax-$P['FrRemain'];
  $DrnRemain=$DrnMax-$P['DrnRemain'];
  $BsRemain=$BsMax-$P['BsRemain'];
  $CSRemain=$CSMax-$P['CSRemain'];
  $TrRemain=$TrMax-$P['TrRemain'];

  $F->Insert(new Input("hidden","orderid",$_SESSION['PostCode']+1));
  $H->Insert($F);
  $H->onLoad("initAll($PP,$PPH,$Pop,$ProdMod,{$P['Farm']},{$P['FarmRemain']}," .
      $P['Factory'].','.$P['FactoryRemain'].','.
      $P['Cybernet'].','.$P['CybernetRemain'].','.
      $P['Lab'].','.$P['LabRemain'].','.
      $P['Refinery'].','.$P['RefineryRemain'].','.
      $P['Starbase'].','.$P['StarbaseRemain'].','.
      $P['Vpr'].','.($VprMax-$P['VprRemain']).','.
      $P['Int'].','.($IntMax-$P['IntRemain']).','.
      $P['Fr'].','.($FrMax-$P['FrRemain']).','.
      $P['Bs'].','.($BsMax-$P['BsRemain']).','.
      $P['Drn'].','.($DrnMax-$P['DrnRemain']).','.
      $P['CS'].','.($CSMax-$P['CSRemain']).','.
      $P['Tr'].','.($TrMax-$P['TrRemain']).','.
      "$VprMax,$IntMax,$FrMax,$BsMax,$DrnMax,$CSMax,$TrMax," . $P['BaseCost'] . "); identifyBoxes(); resetAll()");

  include("part/mainsubmenu.php");

  $H->Draw();
  CloseSQL($sql);
}
?>
