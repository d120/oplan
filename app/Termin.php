<?php

namespace Oplan;

use Illuminate\Database\Eloquent\Model;

class Termin extends Model
{
    protected $table = "termin";
    
    protected $fillable = ["kurztitel", "langtitel", "beschreibung", "wieviele_min", "wieviele_max", "wieviele_freitext",
    "wann_freitext", "dauer", "dauer_freitext", "zielgruppe"];
    
    /**
     * Leiter dieses AKs
    **/
    public function leiter() {
       return $this->belongsToMany('Oplan\User', 'termin_leiter'); 
    }
    
    /**
     * Veranstaltung, zu der dieser Termin gehört
    **/
    public function veranstaltung() {
       return $this->belongsTo('Oplan\Veranstaltung'); 
    }

    /**
     * Benötigte / Zugeteilte Räume für diesen Termin/AK
     * Dies ist oft nur ein Element, könnten aber mehrere sein
    **/
    public function raumbedarf() {
        return $this->hasMany('Oplan\Raumbedarf');
    }
    
    public function updateSlug() {
        
    }
    
}
