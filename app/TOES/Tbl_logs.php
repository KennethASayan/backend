<?php

namespace App\TOES;

use Illuminate\Database\Eloquent\Model;

class Tbl_logs extends Model
{
    //
    protected $connection="pgsql_second";
    public $table = 'tbl_logs';

    public $fillable = [
        'emp_id',
        'log_status',
        'depart',
        'arrival',
        'destination',
        'date_created'
    ];

}
