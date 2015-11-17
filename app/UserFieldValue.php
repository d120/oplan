<?php

namespace Oplan;

use Illuminate\Database\Eloquent\Model;

class UserFieldValue extends Model
{
    public function user() {
        return $this->belongsTo('Oplan\User');
    }
}
