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

function fail($code, $message, $details = null) {
    switch($code) {
        case 400: header("HTTP/1.1 400 Bad Request"); break;
        case 401: header("HTTP/1.1 401 Unauthorized"); break;
        case 403: header("HTTP/1.1 403 Forbidden"); break;
        case 404: header("HTTP/1.1 404 Not Found"); break;
        case 409: header("HTTP/1.1 409 Conflict"); break;
        default:  header("HTTP/1.1 500 Internal Error"); break;
    }
    echo json_encode(array("success" => false, "error_code" => $code, "error" => $message, "error_details" => $details));
    exit;
}

function auth_required() {
    global $db, $login;
    if (isset($_SERVER["PHP_AUTH_USER"]) && isset($_SERVER["PHP_AUTH_PW"])) {
        $q = $db->prepare("SELECT * FROM users WHERE name = ? AND pwhash = ?");
        $q->execute(array($_SERVER["PHP_AUTH_USER"], md5($_SERVER["PHP_AUTH_PW"])));
        $result = $q->fetchAll();
        if (count($result) == 1) $login = $result[0];
    }
    if (!$login) fail(401, "Authorization required", "");
}
