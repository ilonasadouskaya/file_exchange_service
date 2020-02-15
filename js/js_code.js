$(document).ready(function(){
	// Hide fields for error message (will be shown if necessary)
	$('#upload_message').hide();
	$('#table_message').hide();
	// Set event handlers
	$('#filesTable').on('click', '.delete', deleteFile); // Delete file 
	$('#filesTable').on('click', '.download', checkBeforeFileDownload); // Check if file exist and, if yes, download it from storage
	$('#upload_button').bind ('click', uploadFile); // Upload new file to storage
	$('#filesTable').on('click', '#name', sortName); // Sort by file name column
	$('#filesTable').on('click', '#size', sortSize); // Sort by file size column
	$('#filesTable').on('click', '#date', sortDate); // Sort by file upload date column
	
});

function deleteFile() {
	event.preventDefault();
	
	// Form and send request to server
	var fileId = $(this).attr('id');
	
	var request = $.ajax({
		url: 'server.php',
		type: 'GET',
		contentType: false,
        processData: false,
		data: 'delete=true&file=' + fileId,
		dataType: 'json'
	});
	
	// If request is successful
	request.done(function(response) {
		// If response is empty, show error message and exit
		if (typeof response == 'undefined' || response == null) {
			$('#table_message').show();
			$('#table_message').addClass('error_message_table');
			$('#table_message').text('Undefined problem.');
			return false;
		}
		
		// If some server error while deleting file, show error message and exit
		if (response.code != 0) {
			$('#table_message').show();
			$('#table_message').addClass('error_message_table');
			$('#table_message').text(response.message);
			return false;
		}
		
		// Delete row with deleted file
		deleteRow(fileId);
		$('#table_message').hide();
			
		// Check if statistics received and, if yes, update it
		var stat = response.statistics;
		if (typeof stat != 'undefined' && stat != null) {
			updateStatisticsAndUsage(stat);
			$('#table_message').hide();
		} else {
			$('#table_message').show();
			$('#table_message').addClass("error_message_table");
			$('#table_message').text('File is deleted. App statistics is missing!');
		}
		
	});
	
	// If request is not successful show error message
	request.fail(function(jqXHR, textStatus) {
		$('#table_message').show();
		$('#table_message').addClass('error_message_table');
		$('#table_message').text('Request failed: ' + textStatus);
	});
}

function checkBeforeFileDownload() {
	event.preventDefault();
	
	// Form and send request to server
	var fileId = $(this).attr('id');
	
	var request = $.ajax({
		url: 'server.php',
		type: 'GET',
		contentType: false,
        processData: false,
		data: 'save=check&file=' + fileId,
		dataType: 'json'
	});
	
	// If request is successful
	request.done(function(response) {
		// If response is empty, show error message and exit
		if (typeof response == 'undefined' || response == null) {
			$('#table_message').show();
			$('#table_message').addClass('error_message_table');
			$('#table_message').text('Undefined problem.');
			return false;
		}
		
		// If some server error with file, show error message and exit
		if (response.code == -1) {
			$('#table_message').show();
			$('#table_message').addClass('error_message_table');
			$('#table_message').text(response.message);
		}
		// If no errors, download file
		else {
			$('#table_message').hide();
			window.location = 'server.php?save=true&file=' + fileId;
		}
	})
	
	// If request is not successful show error message
	request.fail(function(jqXHR, textStatus) {
		$('#table_message').show();
		$('#table_message').addClass('error_message_table');
		$('#table_message').text('Request failed: ' + textStatus);
	});
}

function uploadFile() {
	event.preventDefault();
	
	// Delete class 'new', if any, to show animation only for newly uploaded file
	$('.new').removeAttr('class');
	
	// Form and send request to server
	var data = new FormData();
	var files = $('#sf_upload')[0].files[0];
	data.append('file', files);

	var request = $.ajax({
		url: 'server.php',
		type: 'POST',
		contentType: false,
        processData: false,
		data: data,
		dataType: 'json'
	});

	// If request is successful
	request.done(function(response) {
		// If response is empty, show error message and exit
		if (typeof response == 'undefined' || response == null) {
			$('#upload_message').show();
			$('#upload_message').addClass('error_message_form');
			$('#upload_message').text('Undefined problem.');
			return false;
		}
		
		// If some server error while uploading file, show error message and exit
		if (response.code != 0) {
			$('#upload_message').show();
			$('#upload_message').addClass('error_message_form');
			$('#upload_message').text(response.message);
			return false;
		}
		
		// Check of file data is receives and, if yes, add new row with uploaded file
		var data = response.fileData;
		if (typeof data != 'undefined' && data != null) {
			$('#upload_message').hide();
			addRow(data);
		} else {
			$('#upload_message').show();
			$('#upload_message').addClass('error_message_form');
			$('#upload_message').text('File is uploaded. File data is not defined!');
			return false;
		}
		
		// Check if updated statistics received and, if yes, update it
		var stat = response.statistics;
		if (typeof stat != "undefined" && stat != null) {
			$('#upload_message').hide();
			updateStatisticsAndUsage(stat);			
		} else {
			$('#upload_message').show();
			$('#upload_message').addClass('error_message_form');
			$('#upload_message').text('File is uploaded. App statistics is missing!');
			return false;
		}
	});

	// If request is not successful show error message
	request.fail(function(jqXHR, textStatus) {
		$('#upload_message').show();
		$('#upload_message').addClass('error_message');
		$('#upload_message').text('Request failed: ' + textStatus);
	});
}

function addRow(fileData) {
	// Get names of classes for new td
	var className = $('#filesTable tr th:nth-child(1)').attr('class');
	var classSize = $('#filesTable tr th:nth-child(2)').attr('class');
	var classTime = $('#filesTable tr th:nth-child(3)').attr('class');
	var classDelete = $('#filesTable tr th:nth-child(4)').attr('class');
	
	// Form table cells for added file
	var cellName = "<td class=\"" + className + "\"><a href=\"" + fileData.get_link + "\" id=\"" + fileData.id + "\">" + fileData.name + "</a></td>";
	var cellSize = "<td class=\"" + classSize + "\">" + fileData.size + "</td>";
	var cellTime = "<td class=\"" + classTime + "\">" + fileData.upload_time + "</td>";
	var cellDelete = "<td class=\"" + classDelete + "\"><a class=\"delete\" href=\"" + fileData.delete_link + "\" id=\"" + fileData.id + "\">" + fileData.delete_label + "</a></td>";
	
	// Form table row for added file
	var row = "<tr class=\"new\">" + cellName + cellSize + cellTime + cellDelete + "</tr>";
	
	// Insert new row in the table
	$('#filesTable tr:last').after(row);
	// Add attributes for new row
	$('.new').attr({
		row_id: fileData.id,
		row_name: fileData.name,
		row_size: fileData.size,
		row_time: fileData.upload_time	
	});
	
	$('.new').attr({
		row_id: fileData.id,
		row_name: fileData.name,
		row_size: fileData.size,
		row_time: fileData.upload_time	
	});
	
	// Set background color for animation
	$('.new').css('background-color', 'rgba(50, 205, 50, 0.9)');
	// Background animation (from green to default color)
	setTimeout(function() {$('.new').css('background-color', 'rgba(50, 205, 50, 0.8)');}, 200);
	setTimeout(function() {$('.new').css('background-color', 'rgba(50, 205, 50, 0.7)');}, 300);
	setTimeout(function() {$('.new').css('background-color', 'rgba(50, 205, 50, 0.6)');}, 400);
	setTimeout(function() {$('.new').css('background-color', 'rgba(50, 205, 50, 0.5)');}, 500);
	setTimeout(function() {$('.new').css('background-color', 'rgba(50, 205, 50, 0.4)');}, 600);
	setTimeout(function() {$('.new').css('background-color', 'rgba(50, 205, 50, 0.3)');}, 700);
	setTimeout(function() {$('.new').css('background-color', 'rgba(50, 205, 50, 0.2)');}, 800);
	setTimeout(function() {$('.new').css('background-color', 'rgba(50, 205, 50, 0.1)');}, 900);
	// Set initial table row color after animation
	setTimeout(function() {$('.new').css('background-color', 'initial');}, 1000);
}

function deleteRow(fileId) {
	// Background animation (from default color to red)
	setTimeout(function() {$("[row_id=\"" + fileId + "\"]").css('background-color', 'rgba(220, 20, 60, 0.1)');}, 100);
	setTimeout(function() {$("[row_id=\"" + fileId + "\"]").css('background-color', 'rgba(220, 20, 60, 0.2)');}, 200);
	setTimeout(function() {$("[row_id=\"" + fileId + "\"]").css('background-color', 'rgba(220, 20, 60, 0.3)');}, 300);
	setTimeout(function() {$("[row_id=\"" + fileId + "\"]").css('background-color', 'rgba(220, 20, 60, 0.4)');}, 400);
	setTimeout(function() {$("[row_id=\"" + fileId + "\"]").css('background-color', 'rgba(220, 20, 60, 0.5)');}, 500);
	setTimeout(function() {$("[row_id=\"" + fileId + "\"]").css('background-color', 'rgba(220, 20, 60, 0.6)');}, 600);
	setTimeout(function() {$("[row_id=\"" + fileId + "\"]").css('background-color', 'rgba(220, 20, 60, 0.7)');}, 700);
	setTimeout(function() {$("[row_id=\"" + fileId + "\"]").css('background-color', 'rgba(220, 20, 60, 0.8)');}, 800);
	setTimeout(function() {$("[row_id=\"" + fileId + "\"]").css('background-color', 'rgba(220, 20, 60, 0.9)');}, 900);
	// Delete row after animation
	setTimeout(function() {$("[row_id=\"" + fileId + "\"]").remove();}, 1000);
}

function updateStatisticsAndUsage(stat) {
	// Set updated statistics after file deletion or upload
	$('#total_users').html(stat.total_users);
	$('#total_files').html(stat.total_files);
	$('#total_size').html(stat.total_size);
	$('#per_user').html(stat.per_user);
	$('#used.value').html(stat.used);
}

function sortName() {
	$('#filesTable tbody tr').data('sort-by', 'name'); // Set data for compareRowsAsc and compareRowsDesc
	sortTable(); // Call sort function
}

function sortSize() {
	$('#filesTable tbody tr').data('sort-by', 'size');
	sortTable();
}

function sortDate() {
	$('#filesTable tbody tr').data('sort-by', 'date');
	sortTable();
}

function sortTable() {
	// Get sort attribute, if any
	var sort = $(this).attr('sort');
	// Get all table body rows
	var rows = $('#filesTable').children('tbody').children('tr');
	// Set default sort order in table header
	$('thead th').children('span').text('=');
	
	// If no sort attribute set, sort A-Z
	if (typeof sort == 'undefined' || sort == null) {
		$(this).attr({'sort': 'asc'}); // Set sort attribute
		rows = $(rows).sort(compareRowsAsc); // Call sort A-Z function
	} 
	// If sort attribute already set, change to the opposite and sort
	else {
		if (sort == 'asc') {
		$(this).attr({'sort': 'desc'});
		rows = $(rows).sort(compareRowsDesc); // Call sort Z-A function
		} else {
			$(this).attr({'sort': 'asc'});
			rows = $(rows).sort(compareRowsAsc);
		}
	}
	
	// Set new table body with sorted rows
	var new_tbody = $('<tbody sort=\"' + $(this).attr('sort') + '\">');
	$(new_tbody).append(rows);
	
	// Delete old non-sorted table body
	$('#filesTable tbody').remove();
	// Insert new sorted table body
	$('#filesTable').append(new_tbody);
}

function compareRowsAsc(a, b) {
	// Get column id, by which the table should be sorted
	var sort_by = $(a).data('sort-by');
	// Set sort order in table header
	$('#' + sort_by).children('span').text('A-Z');
	
	// Sort by file name
	if (sort_by == 'name') {
		if ($(a).attr('row_name') > $(b).attr('row_name')) {
		console.log('1');
		} else {
			return -1;
		}
	}
	
	// Sort by file size
	if (sort_by == 'size') {
		if ($(a).attr('row_size') > $(b).attr('row_size')) {
		return 1;
		} else {
			return -1;
		}
	}
	
	// Sort by file upload time
	if (sort_by == 'date') {
		if ($(a).attr('row_time') > $(b).attr('row_time')) {
		return 1;
		} else {
			return -1;
		}
	}
}

function compareRowsDesc(a, b) {
	var sort_by = $(a).data('sort-by');
	$('#' + sort_by).children('span').text('Z-A');
	
	if (sort_by == 'name') {
		if ($(a).attr('row_name') > $(b).attr('row_name')) {
			return -1;
		} else {
			return 1;
		}
	}
	
	if (sort_by == 'size') {
		if ($(a).attr('row_size') > $(b).attr('row_size')) {
			return -1;
		} else {
			return 1;
		}
	}
	
	if (sort_by == 'date') {
		if ($(a).attr('row_time') > $(b).attr('row_time')) {
			return -1;
		} else {
			return 1;
		}
	}
}