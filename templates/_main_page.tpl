﻿<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{DV="title"}</title>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script src="js/js_code.js"></script>		
    <link rel="stylesheet" type="text/css" href="styles/main_page.css"></link>
</head>
<body>

    <div id="header">
        <div><a href="?logout=go">&#160;{LBL="logout"}&#160;</a></div>
		<div>{LBL="header_logged"} {DV="user_name"}&#160;&#160;</div>
    </div>

    <div id="main">
        <div id="left_block">
            <ul id="allowed_formats">
				<li><p>{LBL="allowed_formats"}:</p></li>
				{DV="allowed_extension"}
			</ul>
			<br/>
			<ul id="total_used">
				<li><p>{LBL="used"}:</p></li>
				<li><div class="value" id="used">{DV="files_used"}</div> {LBL="file_size_unit"} {LBL="out_of"} <div class="value">{DV="files_limit"}</div> {LBL="file_size_unit"}</li>
			</ul>
        </div>

        <div id="center_block">
			<fieldset id="upload_form">
				<legend>{LBL="upload_form"}</legend>
				<form id="upload">
					<div id="upload_message">{DV="upload_message"}</div>
					<input type="file" name="file" id="sf_upload">
					<input type="submit" name="upload" value="Upload" id="upload_button"><br/>
				</form>
			</fieldset>
            <fieldset id="user_files">
				<p id="table_message">{DV="table_message"}</p>
				<table id="filesTable">
					<thead>
						<tr>
							<th class="col_file" id="name"><span><small>=</small></span> {LBL="file"}</th>
							<th class="col_size" id="size"><span><small>=</small></span> {LBL="size"} ({LBL="file_size_unit_table"})</th>
							<th class="col_time" id="date"><span><small>=</small></span> {LBL="upload_time"}</th>
							<th class="col_delete">{LBL="delete"}</th>
						</tr>
					</thead>
					<tbody>
					{DV="user_files_table_row"}
					</tbody>
				</table>
			</fieldset>
        </div>

        <div id="right_block">
            {FILE="statistics.tpl"}
        </div>
    </div>

    <div id="footer">
        {FILE="footer.tpl"}
    </div>

</body>
</html>