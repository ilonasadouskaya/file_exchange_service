$(document).ready(function(){
	$('#proxy_message').hide();
	$('#upload_message').hide();
	$('#table_message').hide();
	$('.delete').bind ('click', deleteFile); // Delets a file which was uploaded before the last page refresh
	$('#filesTable').on('click', '.delete', deleteFile); // Deletes a file which was uploaded after the last page refresh
	$('.download').bind('click', checkBeforeFileDownload); // Checks if file exist and, if yes, downloads it from storage
	$('#upload_button').bind ('click', uploadFile); // Uploads a new file to storage
});

function deleteFile() {
	event.preventDefault();
	var fileId = $(this).attr('id');
	
	var request = $.ajax({
		url: 'server.php',
		type: 'GET',
		contentType: false,
        processData: false,
		data: 'delete=true&file=' + fileId,
		dataType: 'json'
	});
	
	request.done(function(response) {
		if (typeof response == 'undefined' || response == null) {
			$('#table_message').show();
			$('#table_message').addClass('error_message');
			$('#table_message').text('Undefined problem.');
			return false;
		}
		
		if (response.code != 0) {
			$('#table_message').show();
			$('#table_message').addClass('error_message');
			$('#table_message').text(response.message);
		} else {
			deleteRow(fileId);
			$('#table_message').hide();
			
			var stat = response.statistics;
			if (typeof stat != 'undefined' && stat != null) {
				updateStatistics(stat);
				$('#table_message').hide();
			} else {
				$('#table_message').show();
				$('#table_message').addClass("error_message");
				$('#table_message').text('File is deleted. App statistics is missing!');
			}
		}
	});
	
	request.fail(function(jqXHR, textStatus) {
		$('#table_message').show();
		$('#table_message').addClass('error_message');
		$('#table_message').text('Request failed: ' + textStatus);
	});
}

function checkBeforeFileDownload() {
	event.preventDefault();
	var fileId = $(this).attr('id');
	console.log(fileId);
	
	var request = $.ajax({
		url: 'server.php',
		type: 'GET',
		contentType: false,
        processData: false,
		data: 'save=check&file=' + fileId,
		dataType: 'json'
	});
	
	request.done(function(response) {
		if (typeof response == 'undefined' || response == null) {
			$('#table_message').show();
			$('#table_message').addClass('error_message');
			$('#table_message').text('Undefined problem.');
			return false;
		}
		
		if (response.code == -1) {
			$('#table_message').show();
			$('#table_message').addClass('error_message');
			$('#table_message').text(response.message);
		} else {
			$('#table_message').hide();
			window.location = 'server.php?save=true&file=' + fileId;
		}
	})
	
	request.fail(function(jqXHR, textStatus) {
		$('#table_message').show();
		$('#table_message').addClass('error_message');
		$('#table_message').text('Request failed: ' + textStatus);
	});
}

function uploadFile() {
	event.preventDefault();
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

	request.done(function(response) {
		if (typeof response == 'undefined' || response == null) {
			$('#upload_message').show();
			$('#upload_message').addClass('error_message');
			$('#upload_message').text('Undefined problem.');
			return false;
		}
		
		if (response.code != 0) {
			$('#upload_message').show();
			$('#upload_message').addClass('error_message');
			$('#upload_message').text(response.message);
			return false;
		}
		
		var stat = response.statistics;
		if (typeof stat != "undefined" && stat != null) {
			updateStatistics(stat);
			$('#upload_message').hide();
		} else {
			$('#upload_message').show();
			$('#upload_message').addClass('error_message');
			$('#upload_message').text('File is uploaded. App statistics is missing!');
			return false;
		}
			
		var data = response.fileData;
		if (typeof data != 'undefined' && data != null) {
			addRow(data);
			$('#upload_message').hide();
		} else {
			$('#upload_message').show();
			$('#upload_message').addClass('error_message');
			$('#upload_message').text('File is uploaded. File data is not defined!');
			return false;
		}
	});

	request.fail(function(jqXHR, textStatus) {
		$('#upload_message').show();
		$('#upload_message').addClass('error_message');
		$('#upload_message').text('Request failed: ' + textStatus);
	});
}

function addRow(fileData) {
	var className = $('#filesTable tr th:nth-child(1)').attr('class');
	var classSize = $('#filesTable tr th:nth-child(2)').attr('class');
	var classTime = $('#filesTable tr th:nth-child(3)').attr('class');
	var classDelete = $('#filesTable tr th:nth-child(4)').attr('class');
	
	var cellName = "<td class=\"" + className + "\"><a href=\"" + fileData.get_link + "\" id=\"" + fileData.id + "\">" + fileData.name + "</a></td>";
	var cellSize = "<td class=\"" + classSize + "\">" + fileData.size + "</td>";
	var cellTime = "<td class=\"" + classTime + "\">" + fileData.upload_time + "</td>";
	var cellDelete = "<td class=\"" + classDelete + "\"><a class=\"delete\" href=\"" + fileData.delete_link + "\" id=\"" + fileData.id + "\">" + fileData.delete_label + "</a></td>";
	
	var row = "<tr class=\"new\">" + cellName + cellSize + cellTime + cellDelete + "</tr>";
	
	
	
	$('#filesTable tr:first').after(row);
	$('.new').attr({
		row_id: fileData.id,
		row_name: fileData.name,
		row_size: fileData.size,
		row_time: fileData.upload_time	
	});
	$('.new').css('background-color', 'rgba(50, 205, 50, 0.9)');

	setTimeout(function() {$('.new').css('background-color', 'rgba(50, 205, 50, 0.8)');}, 200);
	setTimeout(function() {$('.new').css('background-color', 'rgba(50, 205, 50, 0.7)');}, 300);
	setTimeout(function() {$('.new').css('background-color', 'rgba(50, 205, 50, 0.6)');}, 400);
	setTimeout(function() {$('.new').css('background-color', 'rgba(50, 205, 50, 0.5)');}, 500);
	setTimeout(function() {$('.new').css('background-color', 'rgba(50, 205, 50, 0.4)');}, 600);
	setTimeout(function() {$('.new').css('background-color', 'rgba(50, 205, 50, 0.3)');}, 700);
	setTimeout(function() {$('.new').css('background-color', 'rgba(50, 205, 50, 0.2)');}, 800);
	setTimeout(function() {$('.new').css('background-color', 'rgba(50, 205, 50, 0.1)');}, 900);
	setTimeout(function() {$('.new').css('background-color', 'initial');}, 1000);
}

function deleteRow(fileId) {
	$("[row_id=\"" + fileId + "\"]").css('background-color', 'rgba(220, 20, 60, 0.8)');
	
	$.when(
		$("[row_id=\"" + fileId + "\"]").hide(1000, 'linear', $("[row_id=\"" + fileId + "\"]"))
	).then(function() {
		$("[row_id=\"" + fileId + "\"]").remove();
	});
}

function updateStatistics(stat) {
	$('#total_users').html(stat.total_users);
	$('#total_files').html(stat.total_files);
	$('#total_size').html(stat.total_size);
	$('#per_user').html(stat.per_user);
	$('#used').html(stat.used);
}