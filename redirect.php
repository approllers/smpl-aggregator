<?php

include 'config.php';

if ($_GET['id']) {
	$increment_click_sql = '
		UPDATE
			`links`
		SET
			`clicks` = clicks +1
		WHERE
			`id` = '. $_GET['id'] .'
		AND
			`active` = 1
	';
	$conn->query($increment_click_sql) OR die('Query error at line '. __LINE__ .': '. mysqli_error($conn));
	
	$get_url_sql = '
		SELECT
			`url`
		FROM
			`links`
		WHERE
			`id` = '. $_GET['id'] .'
	';
	$result = $conn->query($get_url_sql) OR die('Query error at line '. __LINE__ .': '. mysqli_error($conn));
		// if affiliate ID exists append to URL before redirect
	while($link = $result->fetch_assoc()) { $url = $link['url']; }
	if (isset($url)) { header('Location: '. $url); } else { print 'could not get the url'; }
	
} else { print 'invalid id'; }
?>