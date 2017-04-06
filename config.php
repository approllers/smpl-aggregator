<?php
session_start();

// PHP Error Reporting
error_reporting(E_ALL);

// PHP Time Zone
date_default_timezone_set('America/New_York');

// MySQL Database Credentials
$db_user = 'approl5_smpl_agg';
$db_pass = 'approl5_smpl_agg';
$db_name = 'approl5_smpl_aggregator';
$db_host = 'localhost';

// MySQL Connect
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) { die('Connection failed: '. $conn->connect_error); }

// MySQL Charset
$conn->set_charset('utf8');

// MySQL Time Zone
if (!$conn->query('SET time_zone = "-02:00"')) {
    die('There was an error running the query ['. $conn->error .']');
}

// Public File Path from Root
$publicfp = '/home/approl5/public_html/aggregator/';

// Admin File Path from Root
$adminfp = $publicfp .'admin/';

// Public URL
$publicurl = 'http://approllers.com/aggregator/';

// Admin URL
$adminurl = $publicurl .'admin/';

// Cron Jobs File Path
$cronfp = '/home/approl5/linkgoat-cronjobs/';

// Backup Directory File Path
$backupfp = '/home/approl5/backups/';

// CloudFlare Credentials
$cf_key = 'f8736a6e00e1e7bd14a9ad5f08375f7428cbe';
$cf_email = 'cloudflare@linkgoat.com';
$purge_urls = array('http://linkgoat.com/index.html');
$cf_domain = 'linkgoat.com';

// Current URL Request
//$request = 'http://'. $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];

$debugmode = true;
?>