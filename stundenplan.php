<?php
include "init.php";

if (isset($_GET["do"]) && $_GET["do"] == "list") {
  $res = $db->query("SELECT DISTINCT zielgruppe FROM raumbedarf");
  $list = array();
  while ($zgstring = $res->fetchColumn()) {
    $items = explode(" ", $zgstring);
    $list += array_flip($items);
  }
  header("Content-Type: application/json");
  foreach($list as $k=>&$v) $v = ucwords(str_replace("_", " ", $k));
  echo json_encode($list);
  exit;
}

if (!(isset($_GET["w"]) && strlen($_GET["w"]) == 10)) die ("Bitte Wochenbeginn w=yyyy-mm-dd angeben");
$dateParam = $_GET["w"];

if (isset($_GET["g"])) {
    $title = $_GET["g"];
    $kw = strtotime($dateParam);
    $start = date("Y-m-d",$kw);
    $end = date("Y-m-d",$kw+3600*24*7);
    header("X-Zeitbereich: $start -- $end");
    $res = $db->query("
    SELECT von,bis,id,concat(count(if(raum<>'',1,null)),'/',count(id)) anz,if(count(if(raum = '',1,null))>0,'wunsch','ok') typ ,sum(min_platz) min_platz,zielgruppe,kommentar,
    group_concat(concat(kommentar,'|',praeferenz,'|',raum) separator '<br>') zuteilung 
    FROM raumbedarf WHERE zielgruppe LIKE " . $db->quote("%$_GET[g]%") . " AND von > '$start' AND von < '$end' GROUP BY von,bis
    ORDER BY von");
    if(!$res) echo $db->errorInfo()[2];


} else if (isset($_GET["raum"])) {
    $title = $_GET["raum"];
    $kw = strtotime($dateParam);
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

if (isset($_GET["format"]) && $_GET["format"] == "ics") {
  header("Content-Type: text/plain; charset=utf-8");
  echo "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//de/d120/mweller/oplan//NONSGML v1.0//DE
X-WR-CALNAME:$title
";
while($event = $res->fetch(PDO::FETCH_ASSOC)) {
$dt_from = "TZID=Europe/Berlin:".date('Ymd\THis', strtotime($event["von"]));
$dt_to = "TZID=Europe/Berlin:".date('Ymd\THis', strtotime($event["bis"]));
echo "BEGIN:VEVENT
UID:".$event["id"]."@oplan.www2.fachschaft.informatik.tu-darmstadt.de
DTSTAMP;$dt_from
DTSTART;$dt_from
DTEND;$dt_to
SUMMARY:$event[kommentar]
LOCATION:".str_replace("<br>","\\n",$event["zuteilung"])."
END:VEVENT
";
}
echo "END:VCALENDAR
";
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
define("MIN_HEIGHT",32);

echo "<div class=\"absplan\">";
$wochentage=array("Montag","Dienstag","Mittwoch","Donnerstag","Freitag");
for($i=0;$i<5;$i++) {
  echo "<div class='dayheader' style='width: ".(WIDTH-20)."px; left: ".(OFFSET_X+($i*WIDTH))."px; '>" . $wochentage[$i]/*date("D", $kw+$i*DAY)*/ . "</div>\n";
}
$pos = array();
$delta=0; $deltaDay=0;
foreach($res as $row) {
  $von = strtotime($row["von"]);
  $ts = $von - $kw;
  $ts -= 60*60*4;
  $height = strtotime($row["bis"]) - $von;
  $top = $ts % DAY;
  $left = floor($ts / DAY)*WIDTH;
  if ($left != $deltaDay) {
    $deltaDay = $left; $delta=0;
  }
  $top+=$delta;
  if ($height/ZOOM < MIN_HEIGHT) {$delta+=MIN_HEIGHT*ZOOM-$height; $height=MIN_HEIGHT*ZOOM; }

  // echo "$ts,$top,$left<br>";
  $off = isset($pos[$ts]) ? $pos[$ts] : 0;
  if(isset($row["anz"]))$anz=$row["anz"]; else $anz="";
  echo "<table class='termin $row[typ]' style='top: ".($top/ZOOM+$off/5)."px; left: ".(OFFSET_X+($left)+$off)."px; height: ".($height/ZOOM)."px; width: ".(WIDTH-$off-10)."px;'>\n";
  $zz = explode("<br>",$row["zuteilung"]);
  $zzz = substr($zz[0], strrpos($zz[0],"|")+1) ;
  echo "<tr><td class='header'><a href='slot.html#?id=$row[id]'><time>".date("H:i",$von)."</time></a> <small>$zzz</small></td></tr>";
  echo "<tr><td class='text'> <b>$row[kommentar]</b> </td></tr>\n";
  $pos[$ts]=$off+40;
}
echo "</table>";




?>
