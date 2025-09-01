<?php

namespace App\TOES;

use Illuminate\Database\Eloquent\Model;

class Tbl_office extends Model
{
    protected $connection="pgsql_second";
    public $table = 'tbl_office';

    public $fillable = [
        'office_name',
        'emp_id',
        'tgroup'
    ];
}
