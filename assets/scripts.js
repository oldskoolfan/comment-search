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

function searchCallback(response) {
	var html,
		data = response.data;
	if (response.status === 0) {
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

			if (typeof response.meta === 'object') {
				displayPagination(response.meta);
			}
		} else {
			$('#msg').html('<p>No Results</p>');
		}
	} else {
		$('#msg').html('<p>' + data.error + '</p>');
	}
}

function displayPagination(meta) {
	var html = '',
		prevPage,
		nextPage;

	// display result meta info
	$('#search-results').before('<h3 id="meta">Displaying ' +
		meta.start + ' thru ' + meta.end + ' of ' +
		meta.total + ' results</h3>');

	// display pagination buttons if we have more than
	// one page
	if (meta.pages > 1) {
		if (meta.currentPage > 1) {
			prevPage = meta.currentPage - 1;
			html += '<button onclick="searchComments(' +
				prevPage + ')">Prev</button>';
		}
		nextPage = meta.currentPage + 1;
		html += '<span>Page ' + meta.currentPage + '</span>' +
			'<button onclick="searchComments(' +
				nextPage + ')">Next</button>';
		$('#pagination').html(html);
	}
}

function clearComments() {
	// make sure we really want to do this...
	var sure = confirm('Warning: this will clear entire comments table. Are you sure?');
	if (!sure) {
		return;
	}
	clearResults(true);
	$.post('ajax/clear-comments.php')
		.done(clearCallback).fail(handleAjaxError);
}

function populateComments() {
	// make sure we want to
	var sure = confirm('Are you sure you want to populate more comments?');
	if (!sure) {
		return;
	}
	clearResults(true);
	showLoadingIcon('Populating comments table...');

	// disable buttons/inputs while populate job is running
	$('button, input').attr('disabled', true);

	$.post('ajax/populate-comments.php')
		.done(populateCallback).fail(handleAjaxError).always(function() {
			$('button, input').removeAttr('disabled');
		});
}

function showLoadingIcon(msg) {
	var html = '<p><i class="fa fa-cog fa-spin fa-lg '
		+ 'fa-fw"></i> '+ msg + '</p>';

	$('#msg').html(html);
}

function searchComments(page) {
	var $searchTextbox,
		text,
		data = { },
		searchType;

	if (page) {
		data.page = page;
	}

	// get search text
	$searchTextbox = $('#search-text');
	text = $searchTextbox.val();

	// get search type
	searchType = $('input[name=search-type]')
		.filter(function(){ return this.checked }).val();

	if (typeof text === 'string' && text.length > 0) {
		text = encodeURIComponent(text);
		data.text = text
		data.type = searchType;

		// append search params to URL
		var newUrl = location.href.substr(0, location.href.indexOf('?'))
			+ '?text=' + text + '&type=' + searchType;

		if (page) {
			newUrl += ('&page=' + page);
		}
		
		history.pushState(data, null, newUrl);

		// new search...let's clear any previous results, show loading icon
		clearResults(true);
		showLoadingIcon('Searching...');

		$.get('ajax/search-comments.php', data)
			.done(searchCallback).fail(handleAjaxError);
	}
}

function clearResults(dontFocus) {
	$('#search-results, #msg, #pagination').html('');
	$('#meta').remove();

	if (!dontFocus) {
		$('#search-text').val('').focus();
		$('input[name=search-type][value=natural]').prop('checked', true);

		// remove query params from URL
		var newUrl = location.href.substr(0, location.href.indexOf('?'));
		history.pushState(null, null, newUrl);
	}
}

function searchKeyPressed(e) {
	// if enter (keyCode == 13) is pressed, run search
	if (e.keyCode === 13) {
		searchComments();
	}
}
