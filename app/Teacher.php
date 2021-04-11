<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use SoftDeletes; //trash user 

    protected $guard_name = 'web';

    public function student() // children    
    {
        // one teacher has many students (group of students)
        return $this->hasMany('App\Student'); // id refer to student.id
    }

    /** 
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'name'
    ];

    protected $table = "teachers";
    protected $primaryKey = "id";
}
