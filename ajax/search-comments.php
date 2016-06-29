<?php
namespace Ajax;

/**
 * our search script. we should always get two query parameters:
 * text (search text) and type (search type - natural or boolean)
 */

$response = [];
$searchText = urldecode($_GET['text']);
$searchType = $_GET['type'];
$con = new \mysqli('localhost','root','','employees');

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

	// get regex for highlighting search words
	if ($searchType === 'natural') {
		// if it's a natural search, we want to break the search text into
		// an array of all words in the search, then build our regex so we
		// match any of those words
		$regex = '/(';
		$searchTermArray = explode(' ', $searchText);
		$length = count($searchTermArray);
		$i = 0;
		// loop through our search terms
		foreach($searchTermArray as $term) {
			$regex .= $term; // always add our search term
			if (++$i !== $length) {
				$regex .= '|'; // if not the last one, add an OR
			} else {
				$regex .= ')/i'; // if the last one, close our regex
			}
		}
	} else {
		// if it's a boolean search, we only need to highlight the exact phrase
		$regex = "/($searchText)/i";
	}

	$response = $stmt->get_result()->fetch_all();
	foreach ($response as $key => $val) {
		// this will replace any text matching our search term(s) with a span
		// wrapped around the text, so we can highlight it with CSS
		$response[$key][0] = preg_replace(
			$regex,
			'<span class="highlight">${1}</span>',
			$val[0]);
	}
}
header('Content-Type: application/json');
echo json_encode($response);
