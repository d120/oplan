<?php
require_once "init.php";
header("Content-Type: text/plain");

$download =false;
$download=true;

if ($download) {
  //$postdata = http_build_query(
  //    array(
  //        'APPNAME' => 'CampusNet',
  //        'PRGNAME' => 'SEARCHROOM'
  //    )
  //);
  
  $room_size = '0'; //alle
  $room_site = '335533336389043'; // nur Campus Stadtmitte
  $postdata = 'APPNAME=CampusNet&PRGNAME=SEARCHROOM&ARGUMENTS=sessionno%2Cmenuid%2Croom_site%2Cbuilding%2Croom_type%2Cseats_from%2Cseats_to%2Carea_from%2Carea_to%2Cdisabled_access%2Cfaculty%2Cdate_from%2Cdate_to%2Ctime_from%2Ctime_to%2Cduration%2Cappointment_type&sessionno=000000000000001&menuid=000385&campusnet_submit=submit&campusnet_submit=Suche&room_site='.$room_site.'&building=0&room_type=0&seats_from=&seats_to=&area_from=&area_to=&appointment_type=S&date_from=&date_to=&time_from=&time_to=&duration=&faculty=0';


  $opts = array('http' =>
      array(
          'method'  => 'POST',
          'header'  => 'Content-type: application/x-www-form-urlencoded',
          'content' => $postdata
      )
  );

  $context  = stream_context_create($opts);

  $result = file_get_contents($tucan_root.'/scripts/mgrqcgi', false, $context);
  file_put_contents("/tmp/tucan.txt", $result);
} else {
  $result = file_get_contents("/tmp/tucan.txt");
}
//echo $result;
$regex1 = '|<tr class="tbdata">.*?</tr>\s+<tr>.*?</tr>|s';
$regex2 = '|<td name="roomBuilding">.*href="(?P<verw_link>[^"]+)"[^>]*>(?P<nummer>[^>]+)</a>.*' .
  '<td name="roomType">(?P<raumtyp>[^<(]+)(?:\\((?P<kommentar2>[^)]+)\\)?)?</td>.*' .
  '<td name="seats">(?P<platz>[^<]*)</td>.*' .
  'roomAppointmentsLink" href="[^"]*-N1,-N(?P<verw_id>[0-9]+)".*' .
  '<span class="elementDescription">(?P<kommentar1>[^<]+)</span>' .
  '|smU';
  
preg_match_all($regex1, $result, $rows);
//var_dump($rows);
echo count($rows[0]);


$source = "tucan";
$db->exec ("DELETE FROM raum WHERE verw_mit = " . $db->quote($source));

$stmt = $db->prepare("INSERT INTO raum SET nummer=?,platz=?,verw_id=?,verw_link=?,verw_kommentar=?,raumtyp=?,verw_mit=" . $db->quote($source) . "");

foreach($rows[0] as $row) {
  preg_match($regex2, $row, $room);
  if(!$room) {
    echo "\n\n\nPARSE ERROR: ";
    var_dump($row); 
    #die("parse error");
  } else {
    //var_dump($matches);
    $nr = str_replace("/", " ", $room['nummer']);
    $room['verw_link'] = $tucan_root.html_entity_decode($room['verw_link']);
    $komm = $room['kommentar1'] . '; ' . $room['kommentar2'];
    $stmt->execute(array($nr, $room['platz'], $room['verw_id'], $room['verw_link'], $komm, $room['raumtyp']));
  }
}



/*
$regex = '|
  <td name="roomBuilding">.*?href="(?P<verw_link>[^"]+)"[^>]*>(?P<nummer>[^>]+)</a>.*?
  <td name="roomType">(?P<raumtyp>[^>]+)</td>.*?
  <td name="seats">(?P<platz>[^>]+)</td>.*?
  roomAppointmentsLink" href="[^"]*?-N1,-N(?P<verw_id>[0-9]+)"
  |sx';*/