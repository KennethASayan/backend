<?php

namespace App\TOES;

use Illuminate\Database\Eloquent\Model;

class Tbl_approveto extends Model
{
    protected $connection="pgsql_second";
    public $table = 'tbl_approveto';

    public $fillable = [
        'to_id',
        'to_year',
        'to_month',
        'to_no',
        'to_seen',
        'cancel'
    ];
}
