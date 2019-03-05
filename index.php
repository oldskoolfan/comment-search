<?php
require 'include/get-config.php';
require 'include/AjaxResponse.php';
require 'include/SearchHelper.php';

if (isset($_GET['text']) && isset($_GET['type'])) {
	$response = SearchHelper::searchComments($con);
}
?>

<!doctype html>
<html>
<head>
	<meta charset="utf-8">

	<!-- for mobile viewports -->
	<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">

	<title>Comment Search Example</title>

	<!-- our css file -->
	<link href="assets/styles.css" rel="stylesheet" type="text/css">

	<!-- fontawesome for the rotating cog loading icon -->
	<script src="https://use.fontawesome.com/11acce7723.js"></script>
</head>
<body>
	<h1>Comment Search</h1>
	<section id="info">
		<p>Number of comments: <span id="comment-count"><?=SearchHelper::getCommentCount($con)?></span></p>
	</section>
	<section id="controls">
		<div>
			<button id="populate-button" onclick="populateComments()">Populate Table</button>
			<button id="clear-button" onclick="clearComments()">Clear Table</button>
		</div>
		<br>
		<div>
			<input id="search-text" type="text" onkeypress="searchKeyPressed(event)"
				value="<?=isset($_GET['text']) ? urldecode($_GET['text']) : ''?>"/>
			<button id="search-button" onclick="searchComments()">Search</button>
			<button id="clear-results" onclick="clearResults()">Clear Results</button>
			<div>
				<span>Search Type:</span>
				<input id="natural" type="radio" name="search-type" value="natural" 
					<?=isset($_GET['type']) && $_GET['type'] === 'boolean' ? '' : 'checked'?> />
				<label for="natural">Natural</label>
				<input id="boolean" type="radio" name="search-type" value="boolean" 
					<?=isset($_GET['type']) && $_GET['type'] === 'boolean' ? 'checked' : ''?> />
				<label for="boolean">Boolean</label>
			</div>
		</div>
	</section>
	<section id="msg">
	</section>

	<?php if (isset($response)): ?>				
	<h3 id="meta">Displaying <?=$response->meta['start']?> thru <?=$response->meta['end']?>
		of <?=$response->meta['total']?> results</h3>
	<?php endif; ?>
	
	<table id="search-results">
	<?php if (isset($response)): ?>
		<?php if (count($response->data) > 0):?>
			<tr><th>Comment</th><th>Search Score</th></tr>
			<?php foreach ($response->data as $row): ?>
				<tr><td><?=$row[0]?></td><td><?=is_numeric($row[1]) && floor($row[1]) === $row[1] ? $row[1] : number_format($row[1], 4)?></td></tr>
			<?php endforeach; ?>
		<?php else: ?>
			<p>No Results</p>
		<?php endif; ?>
	<?php endif; ?>
	</table>
	<div id="pagination">
	<?php if (isset($response)):?>
		<?php if ($response->meta['pages'] > 1):?>	
			<?php if ($response->meta['currentPage'] > 1):?>
				<button onclick="searchComments(<?=$response->meta['currentPage'] - 1?>)">Prev</button>
			<?php endif;?>
			<span>Page <?=$response->meta['currentPage']?></span>
			<button onclick="searchComments(<?=$response->meta['currentPage'] + 1?>)">Next</button>
		<?php endif;?>
	<?php endif;?>
	</div>
	
	<!-- BEGIN JS assets -->

	<!-- jquery for its magical DOM manipulation powers -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>

	<!-- jquery pagination plugin (http://flaviusmatis.github.io/simplePagination.js/) -->
	<link type="text/css" rel="stylesheet" href="assets/simplePagination.css"/>
	<script type="text/javascript" src="assets/jquery.simplePagination.js"></script>

	<script src="assets/scripts.js"></script>
	<!-- END assets -->
</body>
</html>
