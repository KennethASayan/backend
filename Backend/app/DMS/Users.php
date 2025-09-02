<?php

namespace App\DMS;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Users extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'user_id';
    protected $connection = 'pgsql_third';

    protected $fillable = [
        'department',
        'password',
        'name',
        'user_dept',
        'email',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

}
