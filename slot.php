<?php
require_once "init.php";

header("Content-Type: application/json");


if (isset($_POST["create_von"]) && isset($_POST["create_bis"])) {
    auth_required();
    
    $ok = $db->prepare("INSERT INTO raumbedarf SET von=?,bis=?,min_platz=-1,kommentar=?,praeferenz='',zielgruppe=? ")
      ->execute(array($_POST["create_von"], $_POST["create_bis"], $_POST["kommentar"], $_POST["zielgruppe"]));
    echo json_encode(array("success" => $ok, "id" => $db->lastInsertId()));
    return;
}

if (!isset($_GET["id"])) fail(400, "Bitte id angeben");

$id = intval($_GET["id"]);
$slotResult = $db->query("SELECT id,von,bis,raum,zielgruppe FROM raumbedarf WHERE id = $id");
if (!$slotResult || $slotResult->rowCount()!=1) fail(400, "slot not found");
$slot = $slotResult->fetch(PDO::FETCH_ASSOC);


if (count($_POST)>0) {
  auth_required();

  if (isset($_POST["apply"])) {
      $raum = $db->quote($_POST["apply"]);
      
      
      if ($_POST["apply"] != "") {
          $frei = $db->query("SELECT * FROM raumfrei  WHERE raum_nummer = $raum AND
              (date_add(bis,interval 10 minute) >= '$slot[bis]') AND (date_add(von,interval -10 minute) <= '$slot[von]')
              AND blocked=0 ");
          
          $overlap = $db->query("SELECT * FROM raumbedarf b WHERE b.raum = $raum AND 
              (b.von < '$slot[bis]') AND (b.bis > '$slot[von]')");
          if (!$overlap) fail(500, "sql_failed", $db->errorInfo()[2]);

          if ($frei->rowCount() == 0) {
              fail(404, "not_free");
              return;
          } else if ($overlap->rowCount() > 0) {
              fail(409, "overlaps", $overlap->fetchAll(PDO::FETCH_ASSOC));
              return;
          }
      }
      
      
      $ok = $db->exec("UPDATE raumbedarf SET raum = $raum WHERE id = $id");
      if (!$ok) {header("HTTP/1.1 500 SQL Error"); die("error(2): ".$db->errorInfo()[2]);}
      echo json_encode(array("success" => true));
      
      exit;
    
    
  } else if (isset($_POST["min_platz"]) && isset($_POST["kommentar"])) {
      $ok = $db->prepare("UPDATE raumbedarf SET min_platz=?,kommentar=?,praeferenz=?,zielgruppe=? WHERE id=?")
        ->execute(array($_POST["min_platz"], $_POST["kommentar"], $_POST["praeferenz"], $_POST["zielgruppe"], $_GET["id"]));
      if (!$ok) fail(500, "sql_failed");
      echo json_encode(array("success" => true));
      
      
  } else if (isset($_POST["von"]) && isset($_POST["bis"])) {
      if ($_POST["all"]) {
          $s = $db->prepare("UPDATE raumbedarf SET von=?,bis=? WHERE von=? AND bis=? AND zielgruppe=?");
          $ok = $s->execute(array($_POST["von"], $_POST["bis"], $slot["von"], $slot["bis"], $slot["zielgruppe"]));
      } else {
          $s = $db->prepare("UPDATE raumbedarf SET von=?,bis=? WHERE id=?");
          $ok = $s->execute(array($_POST["von"], $_POST["bis"], $slot["id"]));
      }
      if (!$ok) fail(500, "sql_failed");
      echo json_encode(array("success" => true, "modifications" => $s->rowCount()));
      
      
  } else if (isset($_POST["delete"])) {
      $ok = $db->exec("DELETE FROM raumbedarf WHERE id = $id");
      if (!$ok) fail(500, "sql_failed");
      echo json_encode(array("success" => true));
      
  } else {
      fail(400, "missing_parameters");
  }
  return;
}



$res = $db->query("SELECT id,min_platz,zielgruppe,kommentar,praeferenz,raum FROM raumbedarf WHERE von='$slot[von]' and bis='$slot[bis]'");
if(!$res) fail(500, "sql_failed", $db->errorInfo()[2]);

$freiResult = $db->query("SELECT f.raum_nummer,f.von,f.bis,f.status,f.kommentar,b.kommentar belegt FROM raumfrei  f
    LEFT OUTER JOIN raumbedarf b ON f.raum_nummer=b.raum AND (
        (b.von < '$slot[bis]') AND (b.bis > '$slot[von]')
    )
    WHERE f.von<=date_add('$slot[von]',interval 10 minute) AND f.bis>=date_add('$slot[bis]',interval -10 minute)
    AND f.blocked=0
    order by if(belegt is null,0,1) asc,raum_nummer");
if(!$freiResult) fail(500, "sql_failed", $db->errorInfo()[2]);

echo json_encode(array("slot"=>$slot, "raumbedarf"=>$res->fetchAll(PDO::FETCH_ASSOC), "frei"=>$freiResult->fetchAll(PDO::FETCH_ASSOC)));
?>
