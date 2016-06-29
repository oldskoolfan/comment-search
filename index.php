<!doctype html>
<html>
<head>
	<meta charset="utf-8">

	<!-- for mobile viewports -->
	<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">

	<title>Comment Search Example</title>

	<!-- all our css and js assets -->
	<link href="assets/styles.css" rel="stylesheet" type="text/css">

	<!-- fontawesome for the rotating cog loading icon -->
	<script src="https://use.fontawesome.com/11acce7723.js"></script>

	<!-- jquery for its magical DOM manipulation powers -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
	<script src="assets/scripts.js"></script>
	<!-- END assets -->
</head>
<body>
	<h1>Comment Search</h1>
	<section id="info">
		<?php
			$con = new mysqli('localhost','root','','employees');
			$result = $con->query('select count(*) as count from comments');
			$count = number_format($result->fetch_object()->count);
			echo "<p>Number of comments: <span id=\"comment-count\">$count</span></p>";
		?>
	</section>
	<section id="controls">
		<div>
			<button id="populate-button" onclick="populateComments()">Populate Table</button>
			<button id="clear-button" onclick="clearComments()">Clear Table</button>
		</div>
		<br>
		<div>
			<input id="search-text" type="text" />
			<button id="search-button" onclick="searchComments()">Search</button>
			<button id="clear-results" onclick="clearResults()">Clear Results</button>
			<div>
				<label for="search-type">Search Type:
					<input type="radio" name="search-type" value="natural" checked="checked" /> Natural
					<input type="radio" name="search-type" value="boolean"/> Boolean
				</label>
			</div>
		</div>
	</section>
	<section id="msg">
	</section>
	<table id="search-results">
	</table>
</body>
</html>