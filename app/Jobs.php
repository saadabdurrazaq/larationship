<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jobs extends Model
{
    use SoftDeletes; //trash user 

    protected $guard_name = 'web';

    public function job_types() // parent 
    {
        // one job only belongs to one type of job
        return $this->belongsToMany('App\jobTypes');
    }

    /** 
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
    ];

    protected $table = "jobs";
    protected $primaryKey = "id";
}
