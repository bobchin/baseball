<?php
App::uses('AppController', 'Controller');
/**
 * Manage Controller
 *
 * @property Manage $Manage
 */
class ManageController extends AppController {

	public $name = 'Manage';
	public $uses = array('NPB');

	public function index()
	{

	}

	public function update()
	{
		/*
		set_time_limit(300);
		$teams = $this->NPB->getTeam();
		$types = $this->NPB->getType();

		$result = array();
		foreach ($teams as $team) {
			foreach ($types as $type) {
				echo sprintf('Team %s の %s を実行中', $team, $type);
				if ($this->NPB->savePlayers($team, $type) === false) {
					$result[] = array($team, $type);
				}
			}
		}

		if (!empty($result)) {
			// error
			debug($result);
		}
		*/

		$this->NPB->savePlayers(NPB::$BAYSTARS, NPB::$BATTER);
		$this->NPB->savePlayers(NPB::$BAYSTARS, NPB::$PITCHER);
		$this->NPB->savePlayers(NPB::$BAYSTARS, NPB::$DEFENCE);
	}
}
