<?php

include_once("./internal/fleetnew.php");
include_once("./internal/fleetgroup.php");

class BattleCoefficients {
  public $AAV = array('AE' => 1.0, 'AU' => 1.0, 'R' => 1.0, '=' => 1.0, 'DU' => 1.0, 'DE' => 1.0);
  public $ADV = array('AE' => 1.0, 'AU' => 1.0, 'R' => 1.0, '=' => 1.0, 'DU' => 1.0, 'DE' => 1.0);
  public $DAV = array('AE' => 1.0, 'AU' => 1.0, 'R' => 1.0, '=' => 1.0, 'DU' => 1.0, 'DE' => 1.0);
  public $DDV = array('AE' => 1.0, 'AU' => 1.0, 'R' => 1.0, '=' => 1.0, 'DU' => 1.0, 'DE' => 1.0);

  public $loseCoefficient = 1.5;
  public $aggressiveness = 0.3;
  public $crushingWinAttModifier = 2.5;

  public function allMul($field, $value) {
    foreach ($this->$field as &$V) {
      $V = $V*$value;
    }
    unset($V);
  }

  public function applyMission($mission) {
    switch ($mission) {
      case 2: //Kamikaze
        $this->allMul('AAV',1.2);
        $this->allMul('ADV',0.85);
        $this->aggressiveness *= 0.5;
        break;
      case 3: //Raid
        $this->AAV['DE']*=0.85;
        $this->AAV['DU']*=0.85;
        $this->AAV['AU']*=1.4;
        $this->AAV['AE']*=1.4;

        $this->ADV['AU']*=1.3;
        $this->ADV['AE']*=1.3;
        $this->DDV['AU']*=1.3;
        $this->DDV['AE']*=1.3;
        break;
      case 4: //Scout
        $this->aggressiveness *= 0.2;
        $this->AAV['DE']*=0.7;
        $this->AAV['DU']*=0.7;
        $this->AAV['AU']*=1.5;
        $this->AAV['AE']*=1.8;

        $this->ADV['AU']*=1.4;
        $this->ADV['AE']*=1.4;

        $this->allMul('DDV',1.05);
        $this->DDV['AU']*=1.05;
        $this->DDV['AE']*=1.05;
        break;
      case 5: //Retreat
        $this->allMul('AAV',0.8);
        $this->allMul('ADV',0.8);
        $this->allMul('DDV',1.2);
        break;
    }
  }
};

function battle_get_default_coefficients() {
  $coef['AAttWinmod']=1.0;
  $coef['ADefWinmod']=1.0;
  $coef['DDefWinmod']=1.0;
  $coef['AAttLosemod']=1.0;
  $coef['ADefLosemod']=1.0;
}

function battle_compute($Attackers, $Defenders) {
  $A = fleetgroup_compute_combat($Attackers);
  $D = fleetgroup_compute_combat($Defenders);


}

?>
