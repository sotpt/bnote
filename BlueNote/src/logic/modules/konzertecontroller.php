<?php

/**
 * Controller of the concert module.
 * @author matti
 *
 */
class KonzerteController extends DefaultController {
	
	function start() {
		if(isset($_GET["mode"]) && $_GET["mode"] == "wizzard") {
			$this->wizzard();
		}
		else if(isset($_GET["mode"]) && $_GET["mode"] == "programs") {
			$this->programs();
		}
		else {
			parent::start();
		}
	}
	
	/*
	 * These are the following steps for the user to add a concert:
	 * 1) Basic concert data such as date/time and notes
	 * 2) Choose or add a location
	 * 3) Choose or add a contact person
	 * 4) Choose or create a program
	 * 5) summary and saving
	 */
	private function wizzard() {
		$this->getView()->showAddTitle();
		
		// progress bar
		if(isset($_GET["progress"])) {
			$progress = $_GET["progress"];
		}
		else {
			$progress = 1;
		}
		$this->getView()->showProgressBar($progress);
		
		// save data when done
		if($progress == 5) {
			if(isset($_POST["program"]) && $_POST["program"] == 0) {
				unset($_POST["program"]);
			}
			$this->getData()->saveConcert();
		}
		
		// views
		$func = "step" . $progress;
		$action = "wizzard&progress=" . ($progress+1);
		$this->getView()->$func($action);
		
		// always show abort option
		if($progress < 5) $this->getView()->abortButton();
	}
	
	private function programs() {
		require_once $GLOBALS["DIR_DATA_MODULES"] . "programdata.php";
		require_once $GLOBALS["DIR_PRESENTATION_MODULES"] . "programview.php";
		require_once $GLOBALS["DIR_LOGIC_MODULES"] . "programcontroller.php";
		
		$ctrl = new ProgramController();
		$data = new ProgramData();
		$view = new ProgramView($ctrl);
		$ctrl->setData($data);
		$ctrl->setView($view);
		$ctrl->start();
	}
	
}