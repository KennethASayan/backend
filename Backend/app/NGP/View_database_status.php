<?php

namespace App\NGP;

use Illuminate\Database\Eloquent\Model;

class View_database_status extends Model
{
    //
    protected $connection="pgsql";
    protected $table = 'view_database_status';
}
