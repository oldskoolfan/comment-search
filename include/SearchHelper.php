<?php

class SearchHelper 
{

	const PAGE_TOTAL = 20;

	public static function searchComments(mysqli $con) : AjaxResponse
	{
		$searchText = urldecode($_GET['text']);
		$searchType = $_GET['type'];

		// regular expression for searching and highlighting comment text (see foreach loop below)
		$regex = self::getSearchRegex($searchType,
			$searchText);

		// VARIABLES YOU WILL BE WORKING WITH
		$sql = '';
		$result = (object) ['num_rows' => 0]; // initialized this way to prevent fatal errors
		$rows = [];

		/**
		 * 1. $searchType can be 'natural' or 'boolean'
		 * In either case, we want the $sql variable to contain
		 * our SELECT SQL query for displaying the results.
		 * Column one should contain the comment text, and column two should contain the matching score.
		 * 
		 * NOTE: for boolean search, make sure to put double quotes around $searchText in the AGAINST clause
		 * to match the entire string (look at the MySQL contact() function for use with escaped double quotes)
		 * 
		 * 2. Once you have the query as $sql, use the $con->query() method to execute it to $result.
		 * 
		 * 3. Lastly, after running the query and getting the mysqli_result, use the $result->fetch_all() method to set 
		 * $rows equal to all the rows as a multidimensional array
		 * 
		 * NOTE: make sure to check if the query ran successfully
		 */

		// CODE HERE

		foreach ($rows as $key => $val) {
			// this will replace any text matching our search term(s) with a span
			// wrapped around the text, so we can highlight it with CSS
			$rows[$key][0] = preg_replace(
				$regex,
				'<span class="highlight">${1}</span>',
				$val[0]);
			// this will make sure score is always numeric
			$rows[$key][1] = (float) $val[1]; 
		}

		// get paginated results
		list($rows, $meta) = self::paginateResults($rows,
			$result->num_rows);

		// populate response object
		$response = new AjaxResponse(0, 0, 0, ResponseStatus::Ok, $rows);
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
