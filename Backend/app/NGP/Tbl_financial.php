<?php

namespace App\NGP;

use Illuminate\Database\Eloquent\Model;

class Tbl_financial extends Model
{
    //
    protected $connection="pgsql";
    public $table = 'tbl_financial';

    public $fillable = [
        'fkey',
        'body' ,
        'classification',
        'name_of_bank',
        'branch',
        'account_name',
        'account_number',
        'cheque_no',
        'date_of_cheque',
        'financial_performance_report',
        'cip_li_report',
    ];

    protected $casts = [
        'financial_performance_report' => 'array',
        'cip_li_report' => 'array',
        'body' => 'array'
    ];
}
