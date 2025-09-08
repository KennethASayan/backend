<?php

namespace App\NGP;

use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    //
    protected $connection="pgsql";
    protected $table = 'users';

    public $fillable = [
        'password'
    ];
}
