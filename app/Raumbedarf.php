<?php

namespace Oplan;

use Illuminate\Database\Eloquent\Model;

class Raumbedarf extends Model
{
    protected $table = "raumbedarf";
    public $timestamps = false;

    public function termin() {
        return $this->belongsTo('Oplan\Termin');
    }

    public function raum() {
        return $this->belongsTo('Oplan\Raum', 'nummer', 'raum');
    }
}
