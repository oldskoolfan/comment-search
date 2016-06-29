<?php
namespace Ajax;

/**
 * just some simple classes declared so we can return a standard response
 * from our php scripts called via ajax, telling how many SQL rows where
 * inserted/updated/deleted
 */

error_reporting(0); // turn off error reporting so we can always send json

abstract class ResponseStatus {
	const Ok = 0;
	const Error = 1;
}

class AjaxResponse {
	public $inserted;
	public $updated;
	public $deleted;
	public $status;
	public $error;

	public function __construct($i = 0, $u = 0, $d = 0, $s = ResponseStatus::Ok, $e = null) {
		$this->inserted = $i;
		$this->updated = $u;
		$this->deleted = $d;
		$this->status = $s;
		$this->error = $e;
	}
	public function send() {
		header('Content-Type: application/json');
		echo json_encode($this);
	}
}
