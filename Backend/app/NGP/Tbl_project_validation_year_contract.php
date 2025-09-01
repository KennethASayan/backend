<?php

namespace App\NGP;

use Illuminate\Database\Eloquent\Model;

class Tbl_project_validation_year_contract extends Model
{
    //
    protected $connection="pgsql";
    public $table = 'tbl_project_validation_year_contract';

    public $fillable = [
        'fkey',
        'year_damaged',
        'area_damaged',
        'seedling_damaged',
        'amount_damaged',
        'geotagged_photos',
        'validation_report',
        'request_for_relief',
        'request_for_relief_approved',
    ];

    protected $casts = [
        'geotagged_photos' => 'array',
        'validation_report' => 'array',
        'request_for_relief' => 'array',
        'request_for_relief_approved' => 'array',
    ];
}
