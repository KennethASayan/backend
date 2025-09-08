<?php

namespace App\TOES;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
class Tbl_accounts extends Authenticatable
{
    //
    protected $connection="pgsql_second";
    public $table = 'tbl_accounts';

    public $fillable = [
        'username',
        'password',
        'acct_type',
        'group',
        'oic',
        'offc'
    ];
}
