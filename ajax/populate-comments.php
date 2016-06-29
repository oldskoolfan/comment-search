<?php
namespace Ajax;
include 'ajax-response.php';

/**
 * in this script, we want to hit http://hipsum.co 100 times, each time grabbing
 * 100 paragraphs of hipster ipsum text.
 *
 * because it comes back as an html document, we want to use regex to only grab
 * the content we want.
 *
 * first we grab the <div class="hipsum"> section, then grab each paragraph
 * (<p>) and extract the actual text from it.
 *
 * this way, we won't have html markup in our comment_text column in the database.
 *
 * see http://php.net/manual/en/ref.pcre.php for references to these functions.
 */

$response = null;

$con = new \mysqli('localhost', 'root', '', 'employees');

for ($i = 0; $i < 100; ++$i) {
	$html = file_get_contents('http://hipsum.co/?paras=100&type=hipster-centric');
	preg_match('/(<div class="hipsum">)(.*)(<\/div>)/', $html, $matches);
	if ($matches && count($matches) > 0) {
		$text = $matches[2];
		preg_match_all('/(?:<p>)(.*?)(?:<\/p>)/', $text, $pMatches);
		if ($pMatches && count($pMatches) > 0) {
			$pTags = $pMatches[1]; // second array contains matched groups
			for ($j = 0, $count = count($pTags); $j < $count; ++$j) {
				$pText = $con->real_escape_string($pTags[$j]);
				$result = $con->query("INSERT INTO comments(comment_text)
				VALUES ('$pText')");
				if (!$result) {
					echo 'Error: ' . $con->error;
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
	$response = new AjaxResponse(0, 0, $result->num_rows,
		ResponseStatus::Error, $con->error);
}

$response->send();

?>
