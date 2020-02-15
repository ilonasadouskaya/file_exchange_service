﻿<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{DV="title"}</title>
    <link rel="stylesheet" type="text/css" href="styles/login_page.css" />
</head>
<body>

    <div id="header">
        <p>{LBL="header_not_logged"}</p>
    </div>

    <div id="login">
        <div id="left_block"></div>

        <div id="center_block">
            <form id="login_form" action="index.php" method="post">
				<fieldset>
						<p>{DV="error_message"}</p>
						<label for="sf_login">{LBL="login"}:</label>
						<input type="text" name="login" id="sf_login"><br>
						<label for="sf_pass">{LBL="password"}:</label>
						<input type="password" name="password" id="sf_pass"><br>
						<label for="sf_remember">{LBL="remember"}</label>
						<input type="checkbox" name="remember" id="sf_remember"><br/>
						<input type="submit" name="enter" value="Log In"><br/>
				</fieldset>
			</form>
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