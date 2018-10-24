<?php

require '../include/AjaxResponse.php';
require '../include/get-config.php';

/**
 * In this script, we want to hit http://www.randomtext.me 100 times, each time grabbing
 * 15 paragraphs of gibberish text.
 *
 * Because it comes back with <p> tags, we want to use regex to only grab
 * the content we want (actual text).
 *
 * This way, we won't have html markup in our comment_text column in the database.
 *
 * See http://php.net/manual/en/ref.pcre.php for references to regex functions.
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	try {
		for ($i = 0; $i < 100; ++$i) {
			$json = file_get_contents($config['random_text_url']);
			$data = json_decode($json);
			$text = $data->text_out ?? '';
			
			if (!empty($text)) {
				preg_match_all('/(?:<p>)(.*?)(?:<\/p>)/', $text, $pMatches);
				
				if ($pMatches && count($pMatches) > 0) {
					$pTags = $pMatches[1]; // second array contains matched groups
					
					for ($j = 0, $count = count($pTags); $j < $count; ++$j) {
						// $pText = $pTags[$j];
						$stmt = $con->prepare("INSERT INTO comments(comment_text)
							VALUES (?)");
						$stmt->bind_param('s', $pTags[$j]);
						
						if (!$stmt->execute()) {
							throw new \Exception($stmt->error);
						}
					}
				}
			}
		}

		$result = $con->query('SELECT COUNT(*) as "count" FROM comments');

		if ($result) {
			$count = (int)$result->fetch_object()->count;
			$response = new AjaxResponse($count, 0, 0, ResponseStatus::Ok);
		} else {
			$response = new AjaxResponse(0, 0, 0, ResponseStatus::Error, null,
				$con->error);
		}
	} catch (\Throwable $ex) {
		$response = new AjaxResponse(0, 0, 0, ResponseStatus::Error, null,
			$ex->getMessage());
	} finally {
		$response->send();
	}
}
