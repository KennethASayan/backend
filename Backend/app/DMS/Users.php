<?php
namespace App\DMS;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Users extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $connection = 'pgsql_third';
    protected $table = 'users';
    protected $primaryKey = 'user_id';


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
        'remember_token',
    ];
}
