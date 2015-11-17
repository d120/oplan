<?php

namespace Oplan;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'pwhash'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['pwhash'];

    public function getAuthPassword() {
        return $this->pwhash;
    }

    public function fieldValues() {
        return $this->hasMany('Oplan\UserFieldValue');
    }

    /**
     * Termine, für die dieser Benutzer als Veranstalter eingetragen ist
     * z.B. von diesem Benutzer geleitete AKs
    **/
    public function geleiteteTermine() {
       return $this->belongsToMany('Oplan\Termin', 'termin_leiter'); 
    }
    
}
