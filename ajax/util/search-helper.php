<?php
namespace Ajax;

class SearchHelper {

	const PAGE_TOTAL = 20;

	public static function getSearchRegex($searchType, $searchText) {
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

	public static function paginateResults($results, $numRows) {
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
}
