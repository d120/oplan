<?php
require_once "init.php";

header("Content-Type: application/json");

auth_required();
echo json_encode(array("success" => "true"));
