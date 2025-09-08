<?php

namespace App\NGP;

use Illuminate\Database\Eloquent\Model;

class Tbl_project_validation_year extends Model
{
    //
    protected $connection="pgsql";
    public $table = 'tbl_project_validation_year';

    public $fillable = [
        'fkey',
        'year',
        'type_of_validation',
        'date_conducted',
        'conducted_by',
        'seedlings_alive',
        'seedlings_dead',
        'total_seedlings_validated',
        'survival_rate',
        'average_height',
        'average_diameter',
        'average_no_of_leaves',
        'species_replanted',
        'spacing_and_stocking',
        'pest_and_diseases',
        'remarks',
        'photos_seedlings_production',
        'photos_plantation_establishment',
        'photos_m_and_p',
        'photos_validation_report_billing',
        'photos_csd',
        'area_damaged',
        'seedling_damaged',
        'amount_damaged',
        'damaged_remarks',
        'geotagged_photos',
        'validation_report',
        'request_for_relief',
        'request_for_relief_approved',
        'impaired_or_damage',
    ];

    protected $casts = [
        'photos_seedlings_production' => 'array',
        'photos_plantation_establishment' => 'array',
        'photos_m_and_p' => 'array',
        'photos_validation_report_billing' => 'array',
        'photos_csd' => 'array',
        'geotagged_photos' => 'array',
        'validation_report' => 'array',
        'request_for_relief' => 'array',
        'request_for_relief_approved' => 'array',
    ];
}
