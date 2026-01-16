<?php
require('./wp-load.php');
global $wpdb;
$sql = "SELECT domain, path FROM $wpdb->blogs WHERE archived='0' AND deleted ='0'";

$blogs = $wpdb->get_results($sql);

foreach ($blogs as $blog) {
    $command = "https://" . $blog->domain . ($blog->path ? $blog->path : '/') . 'wp-cron.php';
    $ch = curl_init($command);
    $rc = curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
    $rc = curl_exec($ch);
    curl_close($ch);
}
