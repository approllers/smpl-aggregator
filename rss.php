<?php
include 'config.php';
include 'simplepie.php';

$params = false;
$source_sql_append = '';

if (PHP_SAPI === 'cli') {
	// parameters passed from command line
	unset($argv[0]);
    $params = $argv;
    $params = array_filter($params);
}
if ($_GET) {
	// parameters passed using $_GET
	$names = (isset($_GET['source'])) ? (explode(',', $_GET['source'])) : array();
	$ids = (isset($_GET['id'])) ? (explode(',', $_GET['id'])) : array();
    $params = array_merge($names, $ids);
    $params = array_filter($params);
}

if ($params && count($params) > 0) {
	$source_sql_append .= ' AND ';
	$source_ids = $source_names = array();
	
	foreach($params as $k => $v) {
		if (is_numeric($v)) {
			$source_ids[] = $v;
		}
		else { $source_names[] = $v; }
	}
	if (count($source_ids) > 0) { $source_sql_append .= ' `id` IN ( '. implode(',', $source_ids) .' ) '; }
	if (count($source_ids) > 0 && count($source_names) > 0) { $source_sql_append .= ' OR '; }
	if (count($source_names) > 0) { $source_sql_append .= ' `source` IN ( "'. implode('","', $source_names) .'" ) '; }
}
//print '<pre>'; print_r($params); print '</pre>';
/**
  * Script Overview
	
	1. Create new batch ID and set it as a local variable
	2. Get all active rss sources from db and insert into new simplepie object
	3. Iterate through each source
		4. Iterate through each link $item
			5. Set query variables title, url, description, content, images json string, initial image, source_id, author, batch_id, publish_date, future publish_date, active=0
			6. Insert new link
			7. Insert tags from sources into link_tags table
		- end link loop
	- end source loop
	8. Remove tags from tags table where duplicate links by url
	9. Remove tags from tags table where duplicate links by title
	10. Remove links from links table where duplicate links by url
	11. Remove links from links table where duplicate links by title
	12. Activate remaining links from new batch
	13. Update batches table with total num new links, total num sources scanned, end time
**/



//if ($debugmode) { print '<p>/* Step 1. Create new batch ID and set it as a local variable */</p>'; }
$start_date_time = date('Y-m-d H:i:s');
$batch_sql = '
		INSERT INTO
			`batches` (
					`start`
			)
			VALUES (
				"'. $start_date_time .'"
			)
';
if (!$conn->query($batch_sql)) {
    die('There was an error running the query ['. $conn->error .']');
};
$batch_id = $conn->insert_id;

//if ($debugmode) { print '<p>/* Batch ID: '. $batch_id .'</p>'; }

//if ($debugmode) { print '<p>/* Step 2. Get all active rss sources from db and insert into new simplepie object */</p>'; }

$source_sql = '
		SELECT
			*
		FROM
			`sources`
		WHERE
			`active` = 1
		'. $source_sql_append .'
';
print '<pre>'; print_r($source_sql); print '</pre>';
$source_results = $conn->query($source_sql) OR die('Query error at line '. __LINE__ .': '. mysqli_error($conn));
if ($source_results->num_rows < 1) { die('No active sources'); }
else { $total_sources = $source_results->num_rows; }
$source_id_list = array();
$sources = array();
$sp_rss_list = array();
while ($source = $source_results->fetch_assoc()) {
	$source_id_list[] = $source['id'];
	$source_id = $source['id'];
	$source_rss_url = $source['rss_url'];
	
	$sp_source = new SimplePie();
	$sp_source->handle_content_type();
	$sp_source->set_feed_url($source_rss_url);
	$sp_source->init();
	
	$total_duplicate_urls = 0;
	$total_duplicate_titles = 0;
	$total_future_publish_dates = 0;
	$total_links_scanned = 0;
	foreach ($sp_source->get_items() as $item) {
		// set link variables
		$duplicate = false;

		$publish_date = $item->get_date('Y-m-d H:i:s');
		$url = $original_url = $item->get_permalink();
		$author = $item->get_author();
		if (is_object($author)) { $author_name = $author->get_name(); }
		else { $author_name = ''; }
		if (strtotime(date('Y-m-d H:i:s')) < strtotime($item->get_date('Y-m-d H:i:s'))) {
			$future = 1; $total_future_publish_dates++;
		} else { $future = 0; }
		$title = $original_title = $item->get_title();
		$title = strip_tags($title);
		$content = $description = $item->get_content();
		$description = strip_tags($description);
		$dom = new DOMDocument;
		$dom->loadHTML($content);
		$dom_images = $dom->getElementsByTagName('img');
		$images_array = array();
		$image_file_types = array('jpg', 'jpeg', 'gif', 'png');
		foreach ($dom_images as $image) {
			$image_str = $image->getAttribute('src');
			$image_a = explode('?', $image_str);
			$img = explode('.', $image_a[0]);
			$ext = array_pop($img);
			if (in_array($ext, $image_file_types)) {
				$images_array[] = $image_a[0];
			}
		}
		$images_array = array_unique($images_array);
		$images = implode(',', $images_array);
		$image = (count($images_array) > 0) ? ($images_array[0]) : ('');

		
		// Check if URL is a duplicate
		$duplicate_url_check_sql = '
			SELECT
				`original_url`
			FROM
				`links`
			WHERE
				`original_url` = "'. $url .'"
		';
		$result = $conn->query($duplicate_url_check_sql);
		if ($result->num_rows > 0) { $duplicate = true; $total_duplicate_urls++; }
		
		$duplicate_title_check_sql = '
			SELECT
				`original_title`
			FROM
				`links`
			WHERE
				`original_title` = "'. $title .'"
		';
		$result = $conn->query($duplicate_title_check_sql);
		if ($result->num_rows > 0) { $duplicate = true; $total_duplicate_titles++; }
		
		$total_links_scanned++;
		// insert into links: title, url, description, content, images json string, initial image, source_id, author, batch_id, publish_date, future publish_date, active=0
		if (!$duplicate) {
			$insert_new_link_sql = '
				INSERT INTO
					`links` (
						`title`,
						`original_title`,
						`url`,
						`original_url`,
						`description`,
						`content`,
						`image`,
						`images`,
						`author`,
						`source_id`,
						`submission_date`,
						`publish_date`,
						`future_publish_date`,
						`batch_id`,
						`active`
					)
				VALUES (
						"'. htmlspecialchars($title, ENT_COMPAT, 'UTF-8', false) .'", 
						"'. htmlspecialchars($original_title, ENT_COMPAT, 'UTF-8', false) .'", 
						"'. $url .'",
						"'. $original_url .'",
						"'. htmlspecialchars($description, ENT_COMPAT, 'UTF-8', false) .'",
						"'. htmlspecialchars($content, ENT_COMPAT, 'UTF-8', false) .'",
						"'. $image .'",
						"'. $images .'",
						"'. $author_name .'",
						'. $source_id .',
						"'. date('Y-m-d H:i:s') .'",
						"'. $publish_date .'",
						'. $future .',
						'. $batch_id .',
						1
				)
			';
			//print '<pre>'; print_r($insert_new_link_sql); print '</pre>';
			$conn->query($insert_new_link_sql) OR die('Query error at line '. __LINE__ .': '. mysqli_error($conn));
			$link_id = $conn->insert_id; // get last link id
			
			// insert into tags: link_id, tag_id
			$insert_source_tags_sql = '
				INSERT INTO
					`link_tags` (
						`tag_id`,
						`link_id`
					)
				SELECT
					DISTINCT `tag_id`,
					'. $link_id .'
				FROM
					`source_tags`
				WHERE
					`source_id` = '. $source_id .'
			';
			$conn->query($insert_source_tags_sql) OR die('Query error at line '. __LINE__ .': '. mysqli_error($conn));
		}
	} // end iterate through source links loop
	
} // end iterate through sources loop





// Update url in links table from original_url by going through url hops and finding the end result




// Update batches table with total num new links, total num sources scanned, end time
$end_date_time = date('Y-m-d H:i:s');
$duration = date('Y-m-d H:i:s', strtotime(abs(strtotime($end_date_time) - strtotime($start_date_time))));
$update_batch = '
	UPDATE
		`batches`
	SET
		`total_new_links` = (
			SELECT
				COUNT(id)
			FROM
				`links`
			WHERE
				`batch_id` = '. $batch_id .'
			AND
				`active` = 1
		),
		`total_sources` = '. $total_sources .',
		`total_duplicate_urls` = '. $total_duplicate_urls .',
		`total_duplicate_titles` = '. $total_duplicate_titles .',
		`total_links_scanned` = '. $total_links_scanned .',
		`total_future_publish_dates` = '. $total_future_publish_dates .',
		`end` = "'. $end_date_time .'",
		`duration` = "'. $duration .'",
		`source_ids` = "'. implode(',', $source_id_list) .'"
	WHERE
		`id` = '. $batch_id .'
';
$conn->query($update_batch) OR die('Query error at line '. __LINE__ .': '. mysqli_error($conn));
//print '<pre>'; print_r($update_batch); print '</pre>';


?>