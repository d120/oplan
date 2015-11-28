<?php

namespace Oplan;

use Illuminate\Database\Eloquent\Model;
use DB;
use DomDocument;
class Veranstaltung extends Model
{
    protected $table = "veranstaltung";

    public function termine() {
        return $this->hasMany('Oplan\Termin');
    }

    public function ausrichtendeOrganisation() {
        return $this->belongsTo('Oplan\Organisation');
    }
    
    public static function byKey($kuerzel) {
        return Veranstaltung::where('kuerzel', $kuerzel)->first();
    }
    
    public function generatePentabarfXML() {
        $data = DB::select('SELECT date(t.von) day_date, r.nummer room_name, t.id event_id, 
        t.von event_start, t.dauer event_duration, kurztitel, langtitel, beschreibung, zielgruppe 
        FROM termin t INNER JOIN raumbedarf b ON t.id = b.termin_id INNER JOIN raum r ON r.nummer = b.raum
        WHERE t.veranstaltung_id = ?', [ $this->id ]);
        $doc = new DomDocument("1.0", "utf-8");
        
        $schedule = $doc->createElement('schedule');
        $doc->appendChild($schedule);
        
        $conference = $doc->createElement('conference');
        $schedule->appendChild($conference);
        $this->addXmlChildren($doc, $conference, array(
            "title" => $this->kuerzel,
            "subtitle" => "",
            "venue" => '',//$this->ausrichtendeOrganisation->name,
            "city" => "",
            "start" => "",
            "end" => "",
            "days" => "",
            "release" => "",
            "day_change" => "",
            "timeslot_duration" => ""
        ));
        
        $currentDay = "xxxxxx"; $currentRoom = "xxxxxx";
        $day = null; $room;
        foreach($data as $d) {
            if ($currentDay != $d->day_date) {
                $day = $doc->createElement('day');
                $schedule->appendChild($day);
                $currentDay = $d->day_date;
                $day->setAttribute('index', ++$dayIndex);
                $day->setAttribute('date', $currentDay);
            }
            if ($currentRoom != $d->room_name) {
                $room = $doc->createElement('room');
                $day->appendChild($room);
                $currentRoom = $d->room_name;
                $room->setAttribute('name', $currentRoom);
            }
            $event = $doc->createElement('event');
            $room->appendChild($event);
            $event->setAttribute('id', $d->event_id);
            $this->addXmlChildren($doc, $event, [
                "start" => $d->event_start,
                "duration" => $d->event_duration,
                "room" => $d->room_name,
                "tag" => "",
                "title" => $d->kurztitel,
                "subtitle" => $d->langtitel,
                "track" => $d->zielgruppe,
                "type" => "",
                "language" => "",
                "abstract" => "",
                "description" => $d->beschreibung
            ]);
            
        }
        return $doc;
    }
    private function addXmlChildren($doc, $el, $values) {
        foreach($values as $k=>$v) {
            $sub = $doc->createElement($k);
            $el->appendChild($sub);
            $value = $doc->createTextNode($v);
            $sub->appendChild($value);
        }
    }
}
