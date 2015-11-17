<?php

namespace Oplan;

use Illuminate\Database\Eloquent\Model;

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
}
