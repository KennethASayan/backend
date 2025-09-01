<?php

namespace App\TOES;

use Illuminate\Database\Eloquent\Model;

class Tbl_track extends Model
{
    //
    protected $connection="pgsql_second";
    public $table = 'tbl_track';

    public $fillable = [
        'to_id',
        '1st',
        '2nd',
        '3rd',
        '4th',
        '5th'
    ];
}
