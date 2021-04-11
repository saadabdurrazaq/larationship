<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use SoftDeletes; //trash user 

    protected $guard_name = 'web';

    public function identity() // children  
    {
        // one person has one identity 
        return $this->hasOne('App\Identity', 'person_id', 'id'); // id refer to identity.id
    }

    /** 
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'name'
    ];

    protected $table = "persons";
    protected $primaryKey = "id";
}
