<?php

namespace App\NGP;

use Illuminate\Database\Eloquent\Model;

class Tbl_logs extends Model
{
    //
    protected $connection="pgsql";
    public $table = 'tbl_logs';

    public $fillable = [
        'user',
        'activity',
        'table'
    ];
}
