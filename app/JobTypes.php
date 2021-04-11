<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobTypes extends Model
{
    use SoftDeletes; //trash user

    protected $guard_name = 'web';

    // The jobs belong to the job types

    public function jobs() // children  
    {
        // one type of job has many jobs
        return $this->belongsToMany('App\Jobs'); // id refer to jobs.id
    }

    /** 
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'name'
    ];

    protected $table = "job_types";
    protected $primaryKey = "id";
}
