<?php

namespace App\NGP;

use Illuminate\Database\Eloquent\Model;

class Tbl_volunteer_planters extends Model
{
    //
    protected $connection="pgsql";
    public $table = 'tbl_volunteer_planters';

    public $fillable = [
        'fkey',
        'name_of_school_organization',
        'male',
        'female',
        'encoded_by',
    ];
}
