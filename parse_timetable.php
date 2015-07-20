<?php
require_once "init.php";
header("Content-Type: text/plain");
auth_required();

$startTs = intval($_POST["start"]);
$q = $db->prepare("SELECT * FROM raum WHERE nummer = ?");
$q->execute(array($_POST["nummer"]));
$raum = $q->fetch();

switch($raum['verw_mit']) {
    case "tucan":
        parse_tucan($startTs, $raum);
        break;
    default:
        die(json_encode(array("success"=>false,"error"=>"Unbekannte Raumverwaltung")));
}

function parse_tucan($startTs, $raum) {
    global $tucan_root, $tucan_myid, $db;
    $startDate = date("d.m.Y", $startTs);
    $url = $tucan_root.'/scripts/mgrqcgi?APPNAME=CampusNet&PRGNAME=SCHEDULER&ARGUMENTS=-N000000000000001,-N000385,-A'.$startDate.',-A,-N1,-N'.$raum['verw_id'];
    $result = file_get_contents($url);
    #echo $result;
    $regex = '|'.
    '<td class="appointment".*abbr="(?P<weekday>[A-Za-z]+) .*class="timePeriod">.*' .
    '(?P<von>[0-9][0-9]:[0-9][0-9])[ -]+(?P<bis>[0-9][0-9]:[0-9][0-9])'.
    '(?P<crap>.*)'.
    'title="(?P<title>[^"]+)">\s*?(?P<name>[^>]+)\s*?<|sU';
    preg_match_all($regex, $result, $matches, PREG_SET_ORDER);
    
    $tag = array("Montag" => 0, "Dienstag" => 1, "Mittwoch" => 2, "Donnerstag" => 3, "Freitag" => 4, "Samstag" => 5, "Sonntag" => 6);
    $db->exec("DELETE FROM raumfrei WHERE raum_nummer=" . $db->quote($raum["nummer"]) . " AND von >= '".date("Y-m-d", $startTs)."'
    AND bis <= '".date("Y-m-d", $startTs + 3600*24*7)."'  AND status='tucan'");
    $q = $db->prepare("INSERT INTO raumfrei SET raum_nummer=?, von=?, bis=?, status='tucan', kommentar=?, platz=?, blocked=?");
    foreach($matches as $item) {
        if (preg_match('/-N([0-9]+)"/', $item['crap'], $matchId))
          $id = $matchId[1];
        else $id = "";
        //echo substr($item["weekday"],0,2)."\t$item[von] - $item[bis]\t$id\t$item[name]\t$item[title]\n";
        $date = $startTs + $tag[$item["weekday"]] * 24*3600;
        $date = date("Y-m-d", $date);
        $data = array($raum["nummer"], "$date $item[von]:00", "$date $item[bis]:00", $item["name"], $raum["platz"], $id==$tucan_myid?0:1);
        //print_r($data);
        $q->execute($data);
        
    }
    echo json_encode(array("success"=>true));
    
}


