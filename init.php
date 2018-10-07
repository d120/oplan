<?php
include ".htconfig.php";
if (!$DB_USER) die("Please copy .htconfig.php.template to .htconfig.php and fill in the settings");

$db = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8", $DB_USER, $DB_PASS);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);


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
    if (!bind_user_basicauth()) fail(401, "Authorization required. Please log in using your D120 LDAP account.", "");
}



$ds=ldap_connect($ldapHost);
ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);


function bind_user_basicauth() {
  if ($_SERVER["PHP_AUTH_USER"] && $_SERVER["PHP_AUTH_PW"]) {
    if (bind_user($_SERVER["PHP_AUTH_USER"],  $_SERVER["PHP_AUTH_PW"])) return true;
  }
  header("WWW-Authenticate: Basic realm=\"Please authenticate with your LDAP Account\"");
  header("HTTP/1.1 401 Unauthorized");
  return FALSE;
}

function bind_user($user,$pw){
global $ds;
  $user = strtolower(trim($user));
  $userDN = get_user_dn($user);
  $ok = ldap_bind($ds, $userDN, $pw);
  if ($ok) $GLOBALS["boundUserDN"] = $userDN; else $GLOBALS["boundUserDN"] = FALSE;
  return $ok;
}

function get_user_dn($username) {
global $peopleBase;
  return "uid=$username,$peopleBase";
}

function is_group_member($userdn, $groupdn) {
global $ds;
  $result = ldap_read($ds, $groupdn, "(member={$userdn})", ['cn']);
  if ($result === FALSE) { return FALSE; };
  $entries = ldap_get_entries($ds, $result);
  return ($entries['count'] > 0);
}


