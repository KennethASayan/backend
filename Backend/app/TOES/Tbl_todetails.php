<?php

namespace App\TOES;

use Illuminate\Database\Eloquent\Model;

class Tbl_todetails extends Model
{
    //
    protected $connection="pgsql_second";
    public $table = 'tbl_todetails';

    public $fillable = [
        'destination',
        'purpose',
        'emp_id',
        'to_status',
        'otime',
        'perdiem',
        'laborers',
        'travelchrg',
        'outsidero',
        'outsideaor',
        'airtravel',
        'numberoftraveldays',
        'to_id',
        'depart',
        'arrival',
        'date_filed'
    ];
    
}
