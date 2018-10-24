<?php

require '../include/AjaxResponse.php';
require '../include/SearchHelper.php';
require '../include/get-config.php';

/**
 * our search script. we should always get two query parameters:
 * text (search text) and type (search type - natural or boolean)
 */

try {
	$response = SearchHelper::searchComments($con);
} catch (\Throwable $ex) {
	$response = new AjaxResponse(0, 0, 0, ResponseStatus::Error, null,
		$ex->getMessage());
} finally {
	$response->send();
}
