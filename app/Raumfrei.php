<?php

namespace Oplan;

use Illuminate\Database\Eloquent\Model;

class Raumbuchung extends Model
{
    protected $table = "raumfrei";

    public function raum() {
        return $this->belongsTo('Oplan\Raum', 'nummer', 'raum_nummer');
    }
    
}
