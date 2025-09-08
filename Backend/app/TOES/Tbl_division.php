<?php

namespace App\TOES;

use Illuminate\Database\Eloquent\Model;

class Tbl_division extends Model
{
    protected $connection="pgsql_second";
    public $table = 'tbl_division';

    public $fillable = [
        'div_name',
        'off_id',
        'emp_id',
        'deputy_cenro'
    ];
}
