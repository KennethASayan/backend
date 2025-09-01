<?php

namespace App\NGP;

use Illuminate\Database\Eloquent\Model;

class Tbl_seedlings_planted extends Model
{
    //
    protected $connection="pgsql";
    public $table = 'tbl_seedlings_planted';

    public $fillable = [
        'fkey',
        'species_name',
        'replanted',
        'commodity',
        'seedlings_produced',
        'seedlings_planted',
        'area_planted',
        'spacing',
        'mode_of_propagation',
        'fertilizer_applied',
        'start_date',
        'end_date'
    ];
}
