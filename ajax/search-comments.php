<?php
namespace Ajax;
require 'ajax-response.php';
require 'util/search-helper.php';
require '../include/mysql-connect.php';

/**
 * our search script. we should always get two query parameters:
 * text (search text) and type (search type - natural or boolean)
 */

try {
	$searchText = urldecode($_GET['text']);
	$searchType = $_GET['type'];

	if ($searchType === 'natural') {
		// "regular" (i.e. natural language) search query
		$query = "
			select comment_text,
				match(comment_text) against(?) as score
			from comments
			where match(comment_text) against(?)";
	} else {
		// boolean mode search query (in this case we are match the entire search string
		// by adding double quotes around it in the AGAINST clause).
		// also notice the 'in boolean mode' in the AGAINST clause.
		$query = "
			select comment_text,
				match(comment_text) against(concat('\"', ?, '\"') in boolean mode) as score
			from comments
			where match(comment_text) against(concat('\"', ?, '\"') in boolean mode)";
	}

	$stmt = $con->prepare($query);
	$stmt->bind_param('ss', $searchText, $searchText);
	$success = $stmt->execute();

	if ($success) {

		$regex = SearchHelper::getSearchRegex($searchType,
			$searchText);

		$queryResult = $stmt->get_result();
		$results = $queryResult->fetch_all();

		foreach ($results as $key => $val) {
			// this will replace any text matching our search term(s) with a span
			// wrapped around the text, so we can highlight it with CSS
			$results[$key][0] = preg_replace(
				$regex,
				'<span class="highlight">${1}</span>',
				$val[0]);
		}
		list($results, $meta) = SearchHelper::paginateResults($results,
			$queryResult->num_rows);
		$response = new AjaxResponse(0, 0, 0, ResponseStatus::Ok, $results);
		$response->meta = $meta;
	}
} catch (\Throwable $ex) {
	$response = new AjaxResponse(0, 0, 0, ResponseStatus::Error, null,
		$ex->getMessage());
} finally {
	$response->send();
}
