/**
 * note: see http://api.jquery.com/ and http://www.w3schools.com/jsref/default.asp
 * for good documentation on the jquery/javascript used here.
 */

function handleAjaxError(xhr, status, error) {
	// show error msg
	var html = '<p>' + error.toString() + '</p>';
	$('#msg').html(html);
}

function populateCallback(data) {
	if (typeof data === 'object') {
		// update count
		$('#comment-count').html(data.inserted.toLocaleString());
	}
	// remove loading icon
	$('#msg').html('');
}

function clearCallback(data) {
	// update count (should be zero)
	var count,
		$span = $('#comment-count');

	count = Number($span.text().replace(/[^0-9\.]+/g, ''));
	if (!isNaN(count)) {
		$('#comment-count').text((count - data.deleted).toLocaleString());
	}
}

function searchCallback(data) {
	var html;
	if (typeof data === 'object' && data.length > 0) {
		// add some table headers
		html = '<tr><th>Comment</th><th>Search Score</th></tr>';
		data.forEach(function(row) {
			var commentText = row[0],
				score = row[1];
			html += '<tr><td>' + commentText + '</td><td>' + score + '</td></tr>';
		});
		$('#search-results').html(html);
		$('#msg').html(''); // hide loading icon
	} else {
		$('#msg').html('<p>No Results</p>');
	}
}

function clearComments() {
	clearResults(true);
	$.get('ajax/clear-comments.php')
		.done(clearCallback).fail(handleAjaxError);
}

function populateComments() {
	clearResults(true);
	showLoadingIcon('Populating comments table...');

	// disable search while populate job is running
	$('#search-text, #search-button').attr('disabled', true);

	$.get('ajax/populate-comments.php')
		.done(populateCallback).fail(handleAjaxError).always(function() {
			$('#search-text, #search-button').removeAttr('disabled');
		});
}

function showLoadingIcon(msg) {
	var html = '<p><i class="fa fa-cog fa-spin fa-lg '
		+ 'fa-fw"></i> '+ msg + '</p>';

	$('#msg').html(html);
}

function searchComments() {
	var $searchTextbox,
		text,
		data,
		searchType;

	// get search text
	$searchTextbox = $('#search-text');
	text = $searchTextbox.val();

	// get search type
	searchType = $('input[name=search-type]')
		.filter(function(){ return this.checked }).val();

	if (typeof text === 'string' && text.length > 0) {
		text = encodeURIComponent(text);
		data = { text: text, type: searchType };

		// new search...let's clear any previous results, show loading icon
		clearResults(true);
		showLoadingIcon('Searching...');

		$.get('ajax/search-comments.php', data)
			.done(searchCallback).fail(handleAjaxError);
	}
}

function clearResults(dontFocus) {
	$('#search-results, #msg').html('');
	if (!dontFocus) {
		$('#search-text').val('').focus();
	}
}

function searchKeyPressed(e) {
	// if enter (keyCode == 13) is pressed, run search
	if (e.keyCode === 13) {
		searchComments();
	}
}
