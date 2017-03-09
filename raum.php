<?php
require_once "init.php";
$db->exec("SET lc_time_names = 'de_DE';");

header("Content-Type: application/json");

if (count($_POST)>0) {
  auth_required();
  if (isset($_POST["id"]) && isset($_POST["delete"])) {
    $db->prepare("DELETE FROM raumfrei  WHERE id=?")
    ->execute(array($_POST["id"]));
  } else if (isset($_POST["id"])) {
    $db->prepare("UPDATE raumfrei SET raum_nummer=?,von=?,bis=?,kommentar=?,status=? WHERE id=?")
      ->execute(array($_POST["raum_nummer"], $_POST["von"], $_POST["bis"], $_POST["kommentar"], $_POST["status"], $_POST["id"]));
  } else {
    $db->prepare("INSERT INTO raumfrei SET raum_nummer=?,von=?,bis=?,kommentar=?,status=? ")
      ->execute(array($_POST["raum_nummer"], $_POST["von"], $_POST["bis"], $_POST["kommentar"], $_POST["status"]));
  }
  return;
}

if (isset($_GET["kleingruppen"])) {
    $sql = 'select kommentar,group_concat(concat(date_format(von,"%W"),",",raum) order by von) raeume from raumbedarf
         where   kommentar like "%Gruppe%" or  kommentar like "%Group%"
         group by kommentar order by kommentar ;  ';
    $res = $db->query($sql);
    echo json_encode($res->fetchAll());
    exit;
}

if (isset($_GET["nummer"])) {
    $q = $db->prepare("SELECT * FROM raum WHERE nummer = ?");
    $q->execute(array($_GET["nummer"]));
    echo json_encode(array("info" => $q->fetch()));
    exit;
}

if (isset($_GET["all"])) {
    echo json_encode(array( "raumliste"=>$db->query("SELECT * FROM raum;")->fetchAll(PDO::FETCH_ASSOC)));
    return;
}

$order = "date(f.von) asc,f.raum_nummer asc";
if (isset($_GET["order"])) $order = "f.von, f.bis, f.raum_nummer";

$freiResult = $db->query("SELECT f.id,f.raum_nummer,f.von,
    date_format(f.von,'%d.%m %a ') von_day,date_format(f.von,'%H:%i') von_time,date_format(f.bis,'%H:%i') bis_time,
    f.status,f.kommentar,
    group_concat(time(b.von),'-',time(b.bis),' (',b.kommentar,')' separator ', ') belegt
    FROM raumfrei  f
    LEFT OUTER JOIN raumbedarf b ON f.raum_nummer=b.raum AND (
        (b.von < f.bis) AND (b.bis > f.von)
    )
    WHERE f.blocked=0 and f.bis>NOW()
    group by f.id
    order by $order");
if(!$freiResult) {header("HTTP/1.1 404 not found"); die("error(2): ".$db->errorInfo()[2]);}

echo json_encode(array( "frei"=>$freiResult->fetchAll(PDO::FETCH_ASSOC)));
?>
