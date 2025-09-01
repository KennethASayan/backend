<?php

namespace App\TOES;

use Illuminate\Database\Eloquent\Model;

class Tbl_empdetails extends Model
{
    protected $connection="pgsql_second";
    public $table = 'tbl_empdetails';

    public $fillable = [
        'emp_id',
        'f_name',
        'm_name',
        'l_name',
        'sal',
        'emp_pos',
        'email_address',
        'sec_id'
    ];
}
