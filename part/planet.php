<?php
ob_start("ob_gzhandler");
include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/validator.php");
include_once("internal/planet.php");
include_once("internal/progress.php");
include_once("internal/player.php");
include_once("internal/hint.php");
include_once("internal/tech.php");

session_start();

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
if ($GET['g']>0)
    $GET['id']=$GET['g']-1;
elseif ($GET['emb']>0)
    $GET['id']=$GET['emb']-1;
else
    get("id","integer");

    
$Index=planet_index($sql,$MainPID,$GET['id']);


$makeChanges=PostControl(true);

if ($makeChanges)
{
post("newname","string");
post("namechange","string");
if (isset($POST['namechange']))
    planet_change_custom_name($sql, $Index['here'], $MainPID, $POST['newname']);
}

$P=planet_get_all($sql, $Index['here']);
if ($P['Owner']!=$MainPID)
    {
	$H->Insert(new Error("You have no control over chosen planet"));
	$H->Draw();
	die;
    }

$buy=false;

if ($makeChanges)
{
post("ccode","string");
if ($POST['ccode']=='Change' and $P['Gateway']!="")
{
    post("gcode","string");
    if ($POST['gcode']=="")
	$H->Insert(new Error("Cannot apply null code to gateway"));
    else
	planet_gateway_change_code($sql, $MainPID, $P['PLID'], $POST['gcode']);
    $buy=true;
}
}

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

if ($makeChanges && $_POST['spend']=="spend PP")
{
B('Farm');
B('Factory');
B('Cybernet');
B('Lab');
B('Refinery');
B('Starbase');

    S('Vpr');
S('Int');
if (tech_check_name($Techs,'Fr'))
    S('Fr');
if (tech_check_name($Techs,'Bs'))
    S('Bs');
if (tech_check_name($Techs,'Drn'))
    S('Drn');
S('Tr');
S('CS');

post("embassy","integer");
post("gateway","integer");
post("spacestation","integer");
if ($POST['gateway']==1 and tech_check_name($Techs,'WHole'))
{
    $R=planet_gateway_build($sql, $MainPID, $P['PLID']);
    if ($R!="")
	$H->Insert(new Error($R));
    else
	$H->Insert(new Info("Gateway constructed"));
    $buy=true;
}

if ($POST['embassy']==1)	//build embassy
{
    $R=planet_construction_build($sql, $MainPID, $P['PLID'], "Embassy", 512);
    if ($R!="")
	$H->Insert(new Error($R));
    else
	$H->Insert(new Info("Embassy constructed"));
    $buy=true;
}

if ($POST['spacestation']==1)	//build embassy
{
    $R=planet_construction_build($sql, $MainPID, $P['PLID'], "SpaceStation", 256);
    if ($R!="")
	$H->Insert(new Error($R));
    else
	$H->Insert(new Info("Space Station constructed"));
    $buy=true;
}
}
elseif ($makeChanges && $_POST['spend']=="spend RP")
{
BRP('Farm');
BRP('Factory');
BRP('Cybernet');
BRP('Lab');
BRP('Refinery');
}

elseif ($makeChanges && $_POST['spend']=="spend CS")
{
    planet_decompose_colony_ships($sql, $MainPID, $P['PLID']);
    $buy=true;
}

if ($buy)
    $P=planet_get_all($sql, $Index['here']);



/////////////////////////////////////////
// SUMMARY OF A PLANET
/////////////////////////////////////////


$SummaryT=new Table();
$SummaryT->sClass='title';
$SummaryT->Insert(2,1,"{$P['Name']} {$P['Ring']}");
$SummaryT->Insert(1,2,new Link("detail.php?id={$P['SID']}","View starsystem"));
$prvid=$GET['id']-1;
$nxtid=$GET['id']+1;
if ($Index['prev']>0)
    $SummaryT->Insert(1,1,new Link("planet.php?id=$prvid" . $sitAddition,"<< Previous"));
if ($Index['next']>0)
    $SummaryT->Insert(3,1,new Link("planet.php?id=$nxtid" . $sitAddition,"Next >>"));
$SummaryT->Insert(2,2,"{$P['CustomName']}");
get("nmch","integer");
if ($GET['nmch']==1)
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

$SummaryT->Insert(1,3,"Type: " . $P['TypeName']);
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
$SummaryT->Insert(2,3,'SB-OV: ' . $P['Attack'] . '%, SB-TV: ' . $P['Defense'] . '%, Culture ' . ($P['CultureSlot']==1?'Yes':'No') . ', Tech: '
			. $TechReq);
$SummaryT->SetClass(2,3,'typeexpl');
$H->Insert($SummaryT);

//////////////////////////////////////
// Environment
//////////////////////////////////////

include("part/planetcompute.php");

planet_mark_output($sql,$P['PLID'],$PopH,$PPH);

$RP=makeinteger(player_get_RP($sql,$MainPID));


$hintstr="hinted" . $_SESSION['Hint'];
$subhint="sublegend " . $hintstr;

$ObjTTemplate=new Table();
$ObjTTemplate->SetCols(3);
$ObjTTemplate->SetRows(4);
$ObjTTemplate->sClass='block object';
$ObjTTemplate->SetClass(1,1,"legend");
$ObjTTemplate->Join(1,1,3,1);
$ObjTTemplate->SetClass(1,2,"levelnum");
$ObjTTemplate->Join(1,2,2,1);
$ObjTTemplate->SetClass(1,4,"additional");
$ObjTTemplate->Join(3,2,1,2);
$ObjTTemplate->SetClass(1,3,"additional");
$ObjTTemplate->Join(1,3,2,1);

////////////////////////////////////
// Population
////////////////////////////////////

$ObjT=clone $ObjTTemplate;

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
$ObjT->Insert(1,1,$DO);

$ObjT->Insert(1,2,"{$Pop}");

$ObjT->Insert(1,3,"{$PopGo}/{$PopMax}");
$ObjT->Insert(1,4,"+$PopRemain");
$ObjT->Insert(3,4,'+'.sprintf("%.1f",$PopH).'/h');
$ObjT->SetClass(3,4,"additional " . ($PopH>0?"positive":"negative"));
if ($PopH>0)
{
    $PopTRemainReadable=time_period_short($PopTRemain);
    $ObjT->Insert(2,4,$PopTRemainReadable);
    $ObjT->SetClass(2,4,"additional positive");
}
else
{
    $ObjT->Insert(2,4,"infinity");
    $ObjT->SetClass(2,4,"additional negative");
}

$ObjT->Set(3,2,InfoBoxCell('Population'));

$ResT=new Table();
$ResT->Insert(1,1,$ObjT);

///////////////////////////////////////
// Toxic
///////////////////////////////////////

$ObjT=clone $ObjTTemplate;

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
$ObjT->Insert(1,1,$DO);

$ObjT->Insert(1,2,"{$Tx}");

$ObjT->Insert(1,3,"{$TxGo}/1000");
$ObjT->Insert(1,4,"+$TxRemain");
$ObjT->Insert(3,4,sprintf("%+.1f",$TxH).'/h');
$ObjT->SetClass(3,4,"additional " . ($TxH>0?"negative":"positive"));
if ($TxTRemain!=0)
{
    $TxTRemainReadable=time_period_short($TxTRemain);
    $ObjT->Insert(2,4,$TxTRemainReadable);
    if ($TxH>0)
        $ObjT->SetClass(2,4,"additional negative");
    else
        $ObjT->SetClass(2,4,"additional positive");
}
else
{
    $ObjT->Insert(2,4,"balanced");
    $ObjT->SetClass(2,4,"additional positive");
}

$ObjT->Set(3,2,InfoBoxCell('Toxic'));

$ResT->Insert(2,1,$ObjT);

///////////////////////////////////////
// Production Points
///////////////////////////////////////

$ObjT=clone $ObjTTemplate;

$DO=new Div();
$DO->Insert("Production Points");
$DO->sClass=$hintstr;
if ($_SESSION['Hint']>0)
    {
    $D=new Div();
    $D->Insert("Production Points is 'money' which you can spend on various buildings and ships on this planet"); $D->Br();
    $DO->Insert($D);
    }
$ObjT->Insert(1,1,$DO);

$ObjT->Insert(1,2,"{$PP}");
$ObjT->Get(1,2)->sId="PPbx";

$ObjT->Insert(1,3,"{$PP}");
$ObjT->Get(1,3)->sId="PPprgbx";
$ObjT->Insert(1,4,"+1");
$ObjT->Get(1,4)->sId="PPrbx";
$ObjT->Insert(3,4,sprintf("%+.1f",$PPH).'/h');
$ObjT->SetClass(3,4,"additional " . ($PPH>0?"positive":"negative"));
$ObjT->Get(3,4)->sId="PPHbx";
if (isset($PP1Time))
{
    $ObjT->Insert(2,4,time_period_short($PP1Time));
    $ObjT->SetClass(2,4,"additional positive");
}
else
{
    $ObjT->Insert(2,4,"infinity");
    $ObjT->SetClass(2,4,"additional negative");
}
$ObjT->Get(2,4)->sId="PPtimebx";

$ObjT->Set(3,2,InfoBoxCell('PP'));

$ResT->Insert(3,1,$ObjT);


$ResT->Get(1,1)->sStyle='vertical-align : top';
$ResT->Get(2,1)->sStyle='vertical-align : top';
$ResT->Get(3,1)->sStyle='vertical-align : top';
$ResT->sClass='block';
$H->Insert($ResT);

////////////////////////////////////////////
// BUILDINGS
////////////////////////////////////////////
$ObjTTemplate->Join(1,1,2,1);
$ObjTTemplate->SetCols(2);
$ObjTTemplate->SetRows(5);
$ObjTTemplate->SetClass(2,4,'additional');
$ObjTTemplate->Join(1,5,2,1);

$BuildT=new Table();
$BuildT->SetCols(6);
$BuildT->Insert(1,1,"Buildings");
$BuildT->SetClass(1,1,'title');
$BuildT->Join(1,1,6,1);

$Field=new Input("text","","0","text number");
$Field->onChange("checkPrice(); count(); show()");
$Inc=new Input("button","","I","incbutton");
$IncX=new Input("button","","X","incbutton");
$IncC=new Input("button","","C","incbutton");
$IncM=new Input("button","","M","incbutton");
$AllB=new Input("button","","A","allbutton");

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


$F=new Form("planet.php?id={$GET['id']}" . $sitAddition,true);

/////////////////////////////////////
// Building info box
/////////////////////////////////////
function BuildingInfoBox($ObjName, $ObjDBName, $ObjShortName, $HintMessage, $UseRP, $Max=NULL, $showButtons=true)
{
    global $ObjTTemplate;
    global $Siege;
    global $P;
      if ($ObjDBName=="Starbase")
	$BaseCost=10;
      else
	$BaseCost=$P['BaseCost'];
    $ObjT=clone $ObjTTemplate;
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
    $ObjT->Insert(1,1,$DO);
    
    $ObjT->Insert(1,2,'' . $P[$ObjDBName]);
    $ObjT->Get(1,2)->sId=$ObjDBName . 'bx';

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
	$ObjT->Insert(1,3,($FMax-$FRemain).'/'.$FMax);
    else
	$ObjT->Insert(1,3,floor(100*($FMax-$FRemain)/$FMax).'%');
    $ObjT->Get(1,3)->sId=$ObjDBName . 'prgbx';
    if ($FRemain<100000 || !$UseRP)
	$ObjT->Insert(1,4,'+'.$FRemain);
    else
	$ObjT->Insert(1,4,'a lot');
    if ($UseRP)
        $ObjT->Insert(2,4,ceil($FRemain*10/$FMax) . 'RP');
    }
    $ObjT->Get(1,4)->sId=$ObjDBName . 'rbx';
    $ObjT->Get(2,4)->sId=$ObjDBName . 'RPbx';
    
    if (!$Siege)
    {
	global $Inc;
	global $AllB;
	global $Field;
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
		global $IncX;
		global $IncC;
		global $IncM;
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


$BuildT->Insert(1,2,
    BuildingInfoBox("Farm","Farm",'f',"Increases growth - population grows faster",true));
$BuildT->Insert(2,2,
    BuildingInfoBox("Factory","Factory",'r',"Produces Production Points",true));
$BuildT->Insert(3,2,
    BuildingInfoBox("Cybernet","Cybernet",'c',"Increases culture level which will allow you to control more planets",true));
$BuildT->Insert(4,2,
    BuildingInfoBox("Laboratory","Lab",'l',"Increases speed of your research",true));
$BuildT->Insert(5,2,
    BuildingInfoBox("Refinery","Refinery",'e',"Depollutes your planet",true));
$BuildT->Insert(6,2,
    BuildingInfoBox("Starbase","Starbase",'sb',"Cheap yet stationary defence",false));

//////////////////////////////////
// ORBIT
//////////////////////////////////

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


$BuildT->Insert(1,2,
    BuildingInfoBox("Vipers","Vpr",'vprs',"Manurevalbe and fast light fighter",false,$VprMax=Vpr_points($Pl['Engineering']),tech_check_name($Techs,'Vpr')));
$BuildT->Insert(2,2,
    BuildingInfoBox("Interceptors","Int",'ints',"Standard light fighter",false,$IntMax=Int_points($Pl['Engineering'])));
$BuildT->Insert(1,3,
    BuildingInfoBox("Frigates","Fr",'frs',"Well-armoured warship",false,$FrMax=Fr_points($Pl['Engineering']),tech_check_name($Techs,'Fr')));
$BuildT->Insert(2,3,
    BuildingInfoBox("Battleships","Bs",'bss',"Big, overpowered ship",false,$BsMax=Bs_points($Pl['Engineering']),tech_check_name($Techs,'Bs')));
$BuildT->Insert(3,2,
    BuildingInfoBox("Dreadnoughts","Drn",'drns',"Strongest of all warhips, yet relatively slow",false,$DrnMax=Drn_points($Pl['Engineering']),tech_check_name($Techs,'Drn')));
$BuildT->Insert(4,2,
    BuildingInfoBox("Transporters","Tr",'trs',"Defenceless ship carrying infrantry for onground desant.<br>Use these to conquer enemy planets.",false,$TrMax=Tr_points($Pl['Engineering'])));
$BuildT->Insert(4,3,
    BuildingInfoBox("Colony Ships","CS",'css',"Defenceless ship carrying settlers for new worlds.<br>Use these to take over free planets",false,$CSMax=CS_points($Pl['Engineering'])));

if ($Siege)
{
    $Enemy=account_get_name_from_pid($sql, $P['FleetOwner']);
    $BuildT->Insert(1,1," ($Enemy)");
}

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
?>
