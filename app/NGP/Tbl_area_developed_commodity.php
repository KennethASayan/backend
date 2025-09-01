<?php

namespace App\NGP;

use Illuminate\Database\Eloquent\Model;

class Tbl_area_developed_commodity extends Model
{
    //
    protected $connection="pgsql";
    public $table = 'tbl_area_developed_commodity';

    public $fillable = [
        'fkey',
        'fuelwood_seedling',
        'fuelwood_cutting',
        'timber_mmfn',
        'timber',
        'indigenous',
        'indigenous_clonal_propagation',
        'coffee',
        'cacao_root_stock',
        'cacao_grafted',
        'rubber_root_stock',
        'rubber_budded',
        'bamboo',
        'rattan',
        'mangrove_propagule',
        'mangrove_potted',
        'nipa',
        'other_fruit_trees',
    ];
}
