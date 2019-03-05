<?php

class SearchHelper 
{

	const PAGE_TOTAL = 20;

	public static function searchComments(mysqli $con) : AjaxResponse
	{
		$searchText = urldecode($_GET['text']);
		$searchType = $_GET['type'];

		$regex = self::getSearchRegex($searchType,
			$searchText);

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

		if (!$success) {
			throw new Exception($stmt->error);
		}

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

		list($results, $meta) = self::paginateResults($results,
			$queryResult->num_rows);
		$response = new AjaxResponse(0, 0, 0, ResponseStatus::Ok, $results);
		$response->meta = $meta;

		return $response;
	}

	public static function getSearchRegex(string $searchType, string $searchText) : string
	{
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
		
		return $regex;
	}

	public static function paginateResults(array $results, int $numRows) : array
	{
		$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
		$limit = self::PAGE_TOTAL;
		$totalPages = ceil($numRows / $limit);
		$offset = ($page - 1) * $limit;
		$meta = [
			'start' => $offset + 1,
			'end' => $numRows > $limit ? $offset + $limit : $numRows,
			'total' => $numRows,
			'pages' => $totalPages,
			'currentPage' => $page,
		];
		
		return [
			array_slice($results, $offset, $limit),
			$meta
		];
	}

	public static function getCommentCount(mysqli $con) : string
	{
		$result = $con->query('select count(*) as count from comments');
		
		return number_format($result->fetch_object()->count);
	}
}
