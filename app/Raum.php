<?php

namespace Oplan;

use Illuminate\Database\Eloquent\Model;

class Raum extends Model
{
    
    protected $table = "raum";
    
    protected $primaryKey = "nummer";
    public $timestamps = false;
    
    public function nutzungen() {
        return $this->hasMany('Oplan\Raumbedarf', 'raum');
    }
    public function buchungen() {
        return $this->hasMany('Oplan\Raumbuchung', 'raum_nummer');
    }
    
}
