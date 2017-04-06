<?php
session_start();

// PHP Error Reporting
error_reporting(E_ALL);

// PHP Time Zone
date_default_timezone_set('America/New_York');

// MySQL Database Credentials
$db_user = '';
$db_pass = '';
$db_name = '';
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
$publicfp = '';

// Admin File Path from Root
$adminfp = $publicfp .'admin/';

// Public URL
$publicurl = '';

// Admin URL
$adminurl = $publicurl .'admin/';

// Cron Jobs File Path
$cronfp = '';

// Backup Directory File Path
$backupfp = '';

// CloudFlare Credentials
$cf_key = '';
$cf_email = '';
$purge_urls = array('');
$cf_domain = '';

// Current URL Request
//$request = 'http://'. $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];

$debugmode = true;
?>
