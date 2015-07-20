<?php
include "init.php";

if (!(isset($_GET["w"]) && strlen($_GET["w"]) == 2)) die ("Bitte Kalenderwoche w=NN angeben");

if (isset($_GET["g"])) {
    $title = $_GET["g"];
    $kw = strtotime(date("Y")."W".$_GET["w"]);
    $start = date("Y-m-d",$kw);
    $end = date("Y-m-d",$kw+3600*24*7);
    $res = $db->query("
    SELECT von,bis,id,concat(count(if(raum<>'',1,null)),'/',count(id)) anz,if(count(if(raum = '',1,null))>0,'wunsch','ok') typ ,sum(min_platz) min_platz,zielgruppe,kommentar,
    group_concat(concat(kommentar,'|',praeferenz,'|',raum) separator '<br>') zuteilung 
    FROM raumbedarf WHERE zielgruppe LIKE " . $db->quote("%$_GET[g]%") . " AND von > '$start' AND von < '$end' GROUP BY von,bis");
    if(!$res) echo $db->errorInfo()[2];

} else if (isset($_GET["raum"])) {
    $title = $_GET["raum"];
    $kw = strtotime(date("Y")."W".$_GET["w"]);
    $start = date("Y-m-d",$kw);
    $end = date("Y-m-d",$kw+3600*24*7);
    $raum = $db->quote($_GET["raum"]);
    $res = $db->query("
    SELECT id,von,bis,kommentar,if(blocked=0,'frei','block') typ,status FROM raumfrei WHERE raum_nummer = $raum AND von > '$start' AND von < '$end'
    UNION
    SELECT id,von,bis,kommentar ,'ok' typ, '' status
    FROM raumbedarf WHERE raum = $raum AND von > '$start' AND von < '$end'
    UNION
    SELECT id,von,bis,kommentar ,'wunsch' typ, '' status
    FROM raumbedarf WHERE praeferenz = $raum AND raum<>praeferenz AND von > '$start' AND von < '$end'");
    if(!$res) echo $db->errorInfo()[2];

} else {
    die("Bitte Gruppe g=STR oder raum=STR angeben");
}

if(isset($_GET["format"]) && $_GET["format"] == "json") {
  header("Content-Type: application/json");
  echo json_encode($res->fetchAll(PDO::FETCH_ASSOC));
  return;
}

?>
  <title><?=htmlentities($title)?></title>
  <meta charset=utf8>
  <link rel="stylesheet" href="style.css">
  <h1><?=htmlentities($title)?></h1>

<?php

define("DAY",3600*24);
define("ZOOM", 100);
define("WIDTH",200);
define("OFFSET_X",10);

echo "<div class=\"absplan\">";
for($i=0;$i<7;$i++) {
  echo "<div style='position: absolute; top: 200px; width: ".(WIDTH-20)."px; padding:5px; background: #999; color: white; left: ".(OFFSET_X+($i*WIDTH))."px; '>" . date("D d", $kw+$i*DAY) . "</div>\n";
}
$pos = array();
foreach($res as $row) {
  $von = strtotime($row["von"]);
  $ts = $von - $kw;
  $height = strtotime($row["bis"]) - $von;
  $top = $ts % DAY;
  $left = floor($ts / DAY)*WIDTH;
  #echo "$ts,$top,$left<br>";
  $off = isset($pos[$ts]) ? $pos[$ts] : 0;
  if(isset($row["anz"]))$anz=$row["anz"]; else $anz="";
  echo "<div class='termin $row[typ]' style='top: ".($top/ZOOM+$off)."px; left: ".(OFFSET_X+($left)+$off)."px; height: ".($height/ZOOM)."px; width: ".(WIDTH-10)."px;'>\n";
  echo "<a href='slot.html#?id=$row[id]'><small>".date("H:i",$von)."</small></a> <b>$row[kommentar]</b> $anz</div>\n";
  $pos[$ts]=$off+10;echo $off,$ts;
}
echo "</div>";




?>
