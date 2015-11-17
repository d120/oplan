<?php

namespace Oplan;

use Illuminate\Database\Eloquent\Model;

class Organisation extends Model
{
    protected $table = "organisation";

    public function veranstaltungen() {
        return $this->hasMany('Oplan\Veranstaltung');
    }
    
}
