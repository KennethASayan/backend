<?php

namespace App\NGP;

use Illuminate\Database\Eloquent\Model;

class Tbl_profile extends Model
{
    //
    protected $connection="pgsql";
    public $table = 'tbl_profile';


    public $fillable = [
        'year',
        'sitecode',
        'name_of_po',
        'po_representative',
        'mobile_no',
        'fund_source',
        'project_location',
        'watershed',
        'mailing_address',
        'land_tenure',
        'forest_zone',
        'no_of_po_members_male',
        'no_of_po_members_female',
        'registering_agency',
        'commodity',
        'no_of_seedlings_contracted',
        'total_contracted_area',
        'total_amount_contracted_original',
        'total_amount_contracted_supplemental',
        'total_project_cost',
        'pictures_of_the_po',
        'shapefile_and_kml',
        'moa_loa_year1',
        'moa_loa_year2',
        'moa_loa_year3',
        'supplemental_moa',
        'created_by',
        'penro',
        'cenro',
        'cenro_id',
        'penro_id',
        'no_of_polygon',
        'moa_loa_no',
        'sec_reg_no',
        'date_of_sec_reg',
        'sec_reg_attachment',
        'penro_implemented',
    ];
    protected $casts = [
        'project_location' => 'array',
        'pictures_of_the_po' => 'array',
        'shapefile_and_kml' => 'array',
        'moa_loa_year1' => 'array',
        'moa_loa_year2' => 'array',
        'moa_loa_year3' => 'array',
        'supplemental_moa' => 'array',
        'watershed' => 'array',
        'sec_reg_attachment' => 'array',
        'commodity' => 'array',
    ];

}
