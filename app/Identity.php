<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Identity extends Model
{
    use SoftDeletes; //trash user  

    protected $guard_name = 'web';

    public function person() // parent 
    {
        // one identity only belongs to one person
        return $this->belongsTo('App\Person', 'person_id');
    }

    /** 
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'no_ktp'
    ];

    protected $table = "identities";
    protected $primaryKey = "id";
}
