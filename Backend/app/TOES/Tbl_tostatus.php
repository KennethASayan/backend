<?php

namespace App\TOES;

use Illuminate\Database\Eloquent\Model;

class Tbl_tostatus extends Model
{
    //
    protected $connection="pgsql_second";
    public $table = 'tbl_tostatus';

    public $fillable = [
        'emp_id',
        'to_id',
        'date_action',
        'remarks',
        'stat_act'
    ];
}
