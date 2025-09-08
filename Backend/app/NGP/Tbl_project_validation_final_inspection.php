<?php

namespace App\NGP;

use Illuminate\Database\Eloquent\Model;

class Tbl_project_validation_final_inspection extends Model
{
    //
    protected $connection="pgsql";
    public $table = 'tbl_project_validation_final_inspection';

    public $fillable = [
        'fkey',
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
        'geotagged_photos',
        'validation_report',
    ];

    protected $casts = [
      'geotagged_photos' => 'array',
      'validation_report' => 'array',
    ];
}
