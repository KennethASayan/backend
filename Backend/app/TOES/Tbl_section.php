<?php

namespace App\TOES;

use Illuminate\Database\Eloquent\Model;

class Tbl_section extends Model
{
    //
    protected $connection="pgsql_second";
    public $table = 'tbl_section';

    public $fillable = [
        'sec_name',
        'div_id',
        'emp_id',
        'office'
    ];
}
