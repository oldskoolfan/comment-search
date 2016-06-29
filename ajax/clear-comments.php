<?php
namespace Ajax;
include 'ajax-response.php';

/**
 * all we're doing here is deleting all records in our comments table
 */

$response = null;
$con = new \mysqli('localhost','root','','employees');

$result = $con->query('delete from comments');

if ($result) {
	$response = new AjaxResponse(0, 0, $con->affected_rows,
		ResponseStatus::Ok);
} else {
	$response = new AjaxResponse(0, 0, 0, ResponseStatus::Error,
		$con->error);
}

$response->send();
