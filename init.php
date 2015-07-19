<?php
include ".htconfig.php";
if (!$DB_USER) die("Please copy .htconfig.php.template to .htconfig.php and fill in the settings");

$db = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8", $DB_USER, $DB_PASS);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

function getdata($params) {
    global $API_KEY;
    $url = "https://creativecommons.tankerkoenig.de/json/";
    $result = file_get_contents($url.$params."&apikey=$API_KEY");
    $json = json_decode($result, true);
    return $json;
}

