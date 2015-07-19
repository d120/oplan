<?php
require_once "init.php";

if (!isset($_GET["id"])) {header("HTTP/1.1 400 bad request"); die("Bitte id angeben");}

header("Content-Type: application/json");

$id = intval($_GET["id"]);
$slotResult = $db->query("SELECT von,bis,raum FROM raumbedarf WHERE id = $id");
if (!$slotResult || $slotResult->rowCount()!=1) {header("HTTP/1.1 404 not found"); die("slot not found");}
$slot = $slotResult->fetch(PDO::FETCH_ASSOC);



if (count($_POST)>0) {
  
  if (isset($_POST["apply"])) {
    $raum = $db->quote($_POST["apply"]);
    
    
    if ($_POST["apply"] != "") {
        $frei = $db->query("SELECT * FROM raumfrei  WHERE raum_nummer = $raum AND
            (date_add(bis,interval 10 minute) >= '$slot[bis]') AND (date_add(von,interval -10 minute) <= '$slot[von]')
            AND blocked=0 ");
        
        $overlap = $db->query("SELECT * FROM raumbedarf b WHERE b.raum = $raum AND 
            (b.von < '$slot[bis]') AND (b.bis > '$slot[von]')");
        if (!$overlap) {header("HTTP/1.1 500 SQL Error"); die("error: ".$db->errorInfo()[2]);}

        if ($frei->rowCount() == 0) {
            header("HTTP/1.1 404 Not found");
            echo json_encode(array("success" => false, "not_free" => true));
            return;
        } else if ($overlap->rowCount() > 0) {
            header("HTTP/1.1 409 Conflict");
            echo json_encode(array("success" => false, "overlaps" => $overlap->fetchAll(PDO::FETCH_ASSOC)));
            return;
        }
    }
    
    
    $ok = $db->exec("UPDATE raumbedarf SET raum = $raum WHERE id = $id");
    if (!$ok) {header("HTTP/1.1 500 SQL Error"); die("error(2): ".$db->errorInfo()[2]);}
    echo json_encode(array("success" => true));
    
    exit;
  }

  if (isset($_POST["von"]) && isset($_POST["bis"])) {
      $db->prepare("UPDATE raumbedarf SET von=?,bis=?,min_platz=?,kommentar=?,praeferenz=?,zielgruppe=? WHERE id=?")
        ->execute(array($_POST["von"], $_POST["bis"], $_POST["min_platz"], $_POST["kommentar"], $_POST["praeferenz"], $_POST["zielgruppe"], $_GET["id"]));
  }
  return;
}



$res = $db->query("SELECT id,min_platz,zielgruppe,kommentar,praeferenz,raum FROM raumbedarf WHERE von='$slot[von]' and bis='$slot[bis]'");
if(!$res) {header("HTTP/1.1 500 SQL Error"); die("error(1): ".$db->errorInfo()[2]);}

$freiResult = $db->query("SELECT f.raum_nummer,f.von,f.bis,f.status,f.kommentar,b.kommentar belegt FROM raumfrei  f
    LEFT OUTER JOIN raumbedarf b ON f.raum_nummer=b.raum AND (
        (b.von < '$slot[bis]') AND (b.bis > '$slot[von]')
    )
    WHERE f.von<=date_add('$slot[von]',interval 10 minute) AND f.bis>=date_add('$slot[bis]',interval -10 minute)
    AND f.blocked=0
    order by if(belegt is null,0,1) asc,raum_nummer");
if(!$freiResult) {header("HTTP/1.1 500 SQL Error"); die("error(2): ".$db->errorInfo()[2]);}

echo json_encode(array("slot"=>$slot, "raumbedarf"=>$res->fetchAll(PDO::FETCH_ASSOC), "frei"=>$freiResult->fetchAll(PDO::FETCH_ASSOC)));
?>
