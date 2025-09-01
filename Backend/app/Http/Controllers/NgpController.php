<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use App\NGP\Users;
use App\NGP\R10municipalities;
use App\NGP\R10munbrgys;
use App\NGP\R10watershed;
use App\NGP\Tbl_component_commodity_species;
use App\NGP\Tbl_profile;
use App\NGP\Tbl_seedlings_planted;
use App\NGP\Tbl_area_developed_commodity;
use App\NGP\Tbl_project_validation_year;
use App\NGP\Tbl_project_validation_year_contract;
use App\NGP\Tbl_project_validation_final_inspection;
use App\NGP\Tbl_financial;
use App\NGP\Tbl_volunteer_planters;
use App\NGP\Tbl_logs;
use App\NGP\View_completed_forms;
use App\NGP\View_database_status;
use App\NGP\View_quick_data_summary;
use App\NGP\View_quick_data_survival_rate;
use App\NGP\View_quick_data_number_of_po;
use Illuminate\Support\Facades\Auth;

class NgpController extends Controller
{
    //

    public function test() {
        return json_encode('test');
    }

    public function login(Request $request) {
        $model = new Users;
        $data = $model->where('username', $request->username)->first();
        if($data != null) {
            if(Auth::attempt(['username' => $request->username, 'password' => $request->password], true)) {
                $session_var = [
                    'id' => $data->id,
                    'username' => $data->username,
                    'name' => $data->name,
                    'office' => $data->office,
                    'usertype' => $data->usertype,
                    'userstatus' => $data->userstatus,
                    'cenro_id' => $data->cenro_id,
                    'penro_id' => $data->penro_id,
                    'penro' => $data->penro,
                ];
                // $model->find($data->id)->update(['status' => 'in']);
                $request->session()->put($session_var);
                $this->insert_logs($data->name, 'Logged In', 'N/A');

                $message = 'success';

                // $logsModel = new Logs([
                //     'FKey' => $data->id,
                //     'activity' => $data->firstName.' '.$data->lastName.' Logged In',
                //     'table' => 'users',
                // ]);
                // $logsModel->save();
                // broadcast(new LoginEvent('logged_in'));

            }else {
                $message = 'error';
            }
            // if(@password_verify($request->password, $data->password)) {
            //     $model->find($data->id)->update(['status' => 'in']);


            //     $request->session()->put($session_var);
            //     $message = 'success';
            // }else {
            //     $message = 'fail';
            // }
                
        }else {
            $message = 'fail';
        }

        return json_encode($message);
    }

    public function logout() {
        $logs = $this->insert_logs(Auth::user()->name, 'Logged Out', 'N/A');

        if($logs) {
            Auth::logout();
            Session::flush();

        }

        return json_encode('success');
    }

    public function getMunicipalities($id, $penro_id) {
        $model = new R10municipalities;

        $result = $model->where('cenro_id', $id)->get();
        if($id == 0 && $penro_id == 0) {
            $result = $model->orderBy('Municipality', 'ASC')->get();
        }
        if($id == 0 && $penro_id != 0) {
            if($penro_id == 1) {
                $result = $model->where('cenro_id', 1)->orWhere('cenro_id', 2)->orWhere('cenro_id', 3)->orWhere('cenro_id', 4)->get();

            }else if($penro_id == 2) {
                $result = $model->where('cenro_id', 11)->get();

            }else if($penro_id == 3) {
                $result = $model->where('cenro_id', 5)->orWhere('cenro_id', 6)->get();

            }else if($penro_id == 4) {
                $result = $model->where('cenro_id', 7)->orWhere('cenro_id', 8)->get();

            }else if($penro_id == 5) {
                $result = $model->where('cenro_id', 9)->orWhere('cenro_id', 10)->get();

            }
        }
        return json_encode($result);

    }

    public function getBarangays($id) {
        $model = new R10munbrgys;

        return json_encode ($model->where('MunID', $id)->get());
    }
    
    public function getWatersheds($id) {
        $model = new R10watershed;

        $result = $model->where('province_id', $id)->get();

        if($id == 0) {
            $result = $model->orderBy('name_of_watershed', 'ASC')->get();
        }
        
        return json_encode($result);
    }

    public function getCommodities() {
        $model = new Tbl_component_commodity_species;

        return json_encode($model->select('commodity_name')->groupBy('commodity_name')->orderBy('commodity_name')->get());
    }

    public function getSpecies() {
        $model = new Tbl_component_commodity_species;

        return json_encode($model->select('species_name')->groupBy('species_name')->orderBy('species_name')->get());
    }

    public function saveData(Request $request, $table) {

        $penro_implemented = $request->penro_implemented;
        if($penro_implemented == 'Yes') {
            if($request->penro == 'Bukidnon') {
                $penro_implemented = 222;
            }else if($request->penro == 'Lanao del Norte') {
                $penro_implemented = 333;
            }

        }else {
            $penro_implemented = 0;
        }
        if($table == 'tbl_profile') {
            $data = new Tbl_profile([
                'year' => $request->year,
                'sitecode' => $request->sitecode,
                'name_of_po' => $request->name_of_po,
                'po_representative' => $request->po_representative,
                'mobile_no' => $request->mobile_no,
                'fund_source' => $request->fund_source,
                'project_location' => $request->project_location,
                'watershed' => $request->watershed,
                'mailing_address' => $request->mailing_address,
                'land_tenure' => $request->land_tenure,
                'forest_zone' => $request->forest_zone,
                'no_of_po_members_male' => $request->no_of_po_members_male,
                'no_of_po_members_female' => $request->no_of_po_members_female,
                'registering_agency' => $request->registering_agency,
                'commodity' => $request->commodity,
                'no_of_seedlings_contracted' => $request->no_of_seedlings_contracted,
                'total_contracted_area' => $request->total_contracted_area,
                'total_amount_contracted_original' => $request->total_amount_contracted_original,
                'total_amount_contracted_supplemental' => $request->total_amount_contracted_supplemental,
                'total_project_cost' => $request->total_project_cost,
                'pictures_of_the_po' => $request->pictures_of_the_po,
                'shapefile_and_kml' => $request->shapefile_and_kml,
                'moa_loa_year1' => $request->moa_loa_year1,
                'moa_loa_year2' => $request->moa_loa_year2,
                'moa_loa_year3' => $request->moa_loa_year3,
                'supplemental_moa' => $request->supplemental_moa,
                'created_by' => $request->created_by,
                'penro' => $request->penro,
                'cenro' => $request->cenro,
                'cenro_id' => $request->cenro_id,
                'penro_id' => $request->penro_id,
                'no_of_polygon' => $request->no_of_polygon,
                'moa_loa_no' => $request->moa_loa_no,
                'sec_reg_no' => $request->sec_reg_no,
                'date_of_sec_reg' => $request->date_of_sec_reg,
                'sec_reg_attachment' => $request->sec_reg_attachment,
                'penro_implemented' => $penro_implemented,
            ]);


            if($data->save()) {
                $result = [
                    'message' => 'success',
                    'id' => $data->id
                ];
                $this->insert_logs(Auth::user()->name, 'Added (Profile): '.$request->name_of_po. ', Year: '.$request->year. ', id: '. $data->id, $table);
            }else {
                $result = [
                    'message' => 'error'
                ];
            }

        }else if($table == 'tbl_seedlings_planted') {
            for($index = 0; $index < count($request->all()); $index++) {
                $data = new Tbl_seedlings_planted([
                    'fkey' => $request[$index]['fkey'],
                    'species_name' => $request[$index]['species_name'],
                    'replanted' => $request[$index]['replanted'],
                    'commodity' => $request[$index]['commodity'],
                    'seedlings_produced' => $request[$index]['seedlings_produced'],
                    'seedlings_planted' => $request[$index]['seedlings_planted'],
                    'area_planted' => $request[$index]['area_planted'],
                    'spacing' => $request[$index]['spacing'],
                    'mode_of_propagation' => $request[$index]['mode_of_propagation'],
                    'fertilizer_applied' => $request[$index]['fertilizer_applied'],
                    'start_date' => $request[$index]['start_date'],
                    'end_date' => $request[$index]['end_date'],
                ]);


                if($data->save()) {
                    $result = [
                        'message' => 'success',
                    ];
                    $this->insert_logs(Auth::user()->name, 'Added (Seedlings Planted) Species: '.$request[$index]['species_name']. ', Commodity: '.$request[$index]['commodity']. ', id: '. $data->id, $table);
                }else {
                    $result = [
                        'message' => 'error'
                    ];
                }
            }
        }else if($table == 'tbl_area_developed_commodity') {
            $data = new Tbl_area_developed_commodity([
                'fkey' => $request->fkey,
                'fuelwood_seedling' => $request->fuelwood_seedling,
                'fuelwood_cutting' => $request->fuelwood_cutting,
                'timber_mmfn' => $request->timber_mmfn,
                'timber' => $request->timber,
                'indigenous' => $request->indigenous,
                'indigenous_clonal_propagation' => $request->indigenous_clonal_propagation,
                'coffee' => $request->coffee,
                'cacao_root_stock' => $request->cacao_root_stock,
                'cacao_grafted' => $request->cacao_grafted,
                'rubber_root_stock' => $request->rubber_root_stock,
                'rubber_budded' => $request->rubber_budded,
                'bamboo' => $request->bamboo,
                'rattan' => $request->rattan,
                'mangrove_propagule' => $request->mangrove_propagule,
                'mangrove_potted' => $request->mangrove_potted,
                'nipa' => $request->nipa,
                'other_fruit_trees' => $request->other_fruit_trees,
            ]);

            if($data->save()) {
                $result = [
                    'message' => 'success',
                ];
                $this->insert_logs(Auth::user()->name, 'Added Area Developed Commodity, id: '. $data->id, $table);

            }else {
                $result = [
                    'message' => 'error'
                ];
            }

        }else if($table == 'tbl_project_validation_year') {
            for($index = 0; $index < count($request->all()); $index++) {
                $fkey = preg_replace('/\s+/', '', $request[$index]['fkey']);
                $data = new Tbl_project_validation_year([
                    'fkey' => $fkey,
                    'year' => $request[$index]['year'],
                    'type_of_validation' => $request[$index]['type_of_validation'],
                    'date_conducted' => $request[$index]['date_conducted'],
                    'conducted_by' => $request[$index]['conducted_by'],
                    'seedlings_alive' => $request[$index]['seedlings_alive'],
                    'seedlings_dead' => $request[$index]['seedlings_dead'],
                    'total_seedlings_validated' => $request[$index]['total_seedlings_validated'],
                    'survival_rate' => $request[$index]['survival_rate'],
                    'average_height' => $request[$index]['average_height'],
                    'average_diameter' => $request[$index]['average_diameter'],
                    'average_no_of_leaves' => $request[$index]['average_no_of_leaves'],
                    'species_replanted' => $request[$index]['species_replanted'],
                    'spacing_and_stocking' => $request[$index]['spacing_and_stocking'],
                    'pest_and_diseases' => $request[$index]['pest_and_diseases'],
                    'remarks' => $request[$index]['remarks'],
                    'photos_seedlings_production' => $request[$index]['photos_seedlings_production'],
                    'photos_plantation_establishment' => $request[$index]['photos_plantation_establishment'],
                    'photos_m_and_p' => $request[$index]['photos_m_and_p'],
                    'photos_validation_report_billing' => $request[$index]['photos_validation_report_billing'],
                    'photos_csd' => $request[$index]['photos_csd'],
                    'area_damaged' => $request[$index]['area_damaged'],
                    'seedling_damaged' => $request[$index]['seedling_damaged'],
                    'amount_damaged' => $request[$index]['amount_damaged'],
                    'damaged_remarks' => $request[$index]['damaged_remarks'],
                    'geotagged_photos' => $request[$index]['geotagged_photos'],
                    'validation_report' => $request[$index]['validation_report'],
                    'request_for_relief' => $request[$index]['request_for_relief'],
                    'request_for_relief_approved' => $request[$index]['request_for_relief_approved'],
                    'impaired_or_damage' => $request[$index]['impaired_or_damage'],
                ]);
                if($data->save()) {
                    $result = [
                        'message' => 'success',
                    ];
                    $this->insert_logs(Auth::user()->name, 'Added (Project Validation) Year: '.$request[$index]['year'].', id: '. $data->id, $table);
                }else {
                    $result = [
                        'message' => 'error'
                    ];
                }
            }

        }else if($table == 'tbl_project_validation_year_contract') {
            $data = new Tbl_project_validation_year_contract([
                'fkey' => $request->fkey,
                'year_damaged' => $request->year_damaged,
                'area_damaged' => $request->area_damaged,
                'seedling_damaged' => $request->seedling_damaged,
                'amount_damaged' => $request->amount_damaged,
                'geotagged_photos' => $request->project_validation_year_contract_data_geotagged_photos,
                'validation_report' => $request->project_validation_year_contract_data_validation_report,
                'request_for_relief' => $request->project_validation_year_contract_data_request_for_relief,
                'request_for_relief_approved' => $request->project_validation_year_contract_data_request_for_relief_approved,
            ]);

            if($data->save()) {
                $result = [
                    'message' => 'success',
                ];
                $this->insert_logs(Auth::user()->name, 'Added (Project Validation) Year Damaged: '.$request->year_damaged.', id: '. $data->id, $table);
            }else {
                $result = [
                    'message' => 'error'
                ];
            }
        }else if($table == 'tbl_project_validation_final_inspection') {
            $data = new Tbl_project_validation_final_inspection([
                'fkey' => $request->fkey,
                'type_of_validation' => $request->type_of_validation,
                'date_conducted' => $request->date_conducted,
                'conducted_by' => $request->conducted_by,
                'seedlings_alive' => $request->seedlings_alive,
                'seedlings_dead' => $request->seedlings_dead,
                'total_seedlings_validated' => $request->total_seedlings_validated,
                'survival_rate' => $request->survival_rate,
                'average_height' => $request->average_height,
                'average_diameter' => $request->average_diameter,
                'average_no_of_leaves' => $request->average_no_of_leaves,
                'species_replanted' => $request->species_replanted,
                'spacing_and_stocking' => $request->spacing_and_stocking,
                'pest_and_diseases' => $request->pest_and_diseases,
                'remarks' => $request->remarks,
                'geotagged_photos' => $request->project_validation_final_inspection_data_geotagged_photos,
                'validation_report' => $request->project_validation_final_inspection_data_validation_report,
            ]);

            if($data->save()) {
                $result = [
                    'message' => 'success',
                ];
                $this->insert_logs(Auth::user()->name, 'Added (Project Validation Final Inspection) Date Conducted: '.$request->date_conducted.', id: '. $data->id, $table);
            }else {
                $result = [
                    'message' => 'error'
                ];
            }
        }else if($table == 'tbl_financial') {
            $data = new Tbl_financial([
                'fkey' => $request->fkey,
                'body' => $request->body,
                'classification' => $request->classification,
                'name_of_bank' => $request->name_of_bank,
                'branch' => $request->branch,
                'account_name' => $request->account_name,
                'account_number' => $request->account_number,
                'cheque_no' => $request->cheque_no,
                'date_of_cheque' => $request->date_of_cheque,
                'financial_performance_report' => $request->financial_performance_report,
                'cip_li_report' => $request->cip_li_report,
            ]);

            if($data->save()) {
                $result = [
                    'message' => 'success',
                ];
                $this->insert_logs(Auth::user()->name, 'Added (Financial) Account Number: '.$request->account_number.', id: '. $data->id, $table);
            }else {
                $result = [
                    'message' => 'error'
                ];
            }
        }else if($table == 'tbl_volunteer_planters') {
            $data = new Tbl_volunteer_planters([
                'fkey' => $request->fkey,
                'name_of_school_organization' => $request->name_of_school_organization,
                'male' => $request->male,
                'female' => $request->female,
                'encoded_by' => $request->encoded_by,
            ]);

            if($data->save()) {
                $result = [
                    'message' => 'success',
                ];
                $this->insert_logs(Auth::user()->name, 'Added (Volunteer Planters) Name of School/Org.: '.$request->name_of_school_organization.', id: '. $data->id, $table);
            }else {
                $result = [
                    'message' => 'error'
                ];
            }
        }
       

        return json_encode($result);


    }

    public function saveFiles(Request $request, $table, $id, $index_2 = 0) {
        $files = request()->file('pictures_of_the_po');
        if(request()->hasFile('pictures_of_the_po')) {
            $index = 0;
            foreach($files as $file):
                $file->storeAs('public\NGP\\'.$id, 'pictures_of_the_po_'.$index.'.'.$file->extension());

                $this->insert_logs(Auth::user()->name, 'Added (Profile) Pictures of PO: pictures_of_the_po_'.$index.'.'.$file->extension(), $table);

                $index++;
            endforeach;
        }

        $shapefile_and_kml = request()->file('shapefile_and_kml');
        if(request()->hasFile('shapefile_and_kml')) {
            $index = 0;
            foreach($shapefile_and_kml as $file):
                $file->storeAs('public\NGP\\'.$id, 'shapefile_and_kml_'.$index.'.'.$file->extension());

                $this->insert_logs(Auth::user()->name, 'Added (Profile) Shapefile and KML: shapefile_and_kml_'.$index.'.'.$file->extension(), $table);

                $index++;
            endforeach;
        }

        $moa_loa_year1 = request()->file('moa_loa_year1');
        if(request()->hasFile('moa_loa_year1')) {
            $index = 0;
            foreach($moa_loa_year1 as $file):
                $file->storeAs('public\NGP\\'.$id, 'moa_loa_year1_'.$index.'.'.$file->extension());

                $this->insert_logs(Auth::user()->name, 'Added (Profile) MOA/LOA Year 1: moa_loa_year1_'.$index.'.'.$file->extension(), $table);

                $index++;
            endforeach;
        }

        $moa_loa_year2 = request()->file('moa_loa_year2');
        if(request()->hasFile('moa_loa_year2')) {
            $index = 0;
            foreach($moa_loa_year2 as $file):
                $file->storeAs('public\NGP\\'.$id, 'moa_loa_year2_'.$index.'.'.$file->extension());

                $this->insert_logs(Auth::user()->name, 'Added (Profile) MOA/LOA Year 2: moa_loa_year2_'.$index.'.'.$file->extension(), $table);

                $index++;
            endforeach;
        }

        $moa_loa_year3 = request()->file('moa_loa_year3');
        if(request()->hasFile('moa_loa_year3')) {
            $index = 0;
            foreach($moa_loa_year3 as $file):
                $file->storeAs('public\NGP\\'.$id, 'moa_loa_year3_'.$index.'.'.$file->extension());

                $this->insert_logs(Auth::user()->name, 'Added (Profile) MOA/LOA Year 3: moa_loa_year3_'.$index.'.'.$file->extension(), $table);

                $index++;
            endforeach;
        }

        $supplemental_moa = request()->file('supplemental_moa');
        if(request()->hasFile('supplemental_moa')) {
            $index = 0;
            foreach($supplemental_moa as $file):
                $file->storeAs('public\NGP\\'.$id, 'supplemental_moa_'.$index.'.'.$file->extension());

                $this->insert_logs(Auth::user()->name, 'Added (Profile) Supplemental Moa: supplemental_moa_'.$index.'.'.$file->extension(), $table);

                $index++;
            endforeach;
        }

        $sec_reg_attachment = request()->file('sec_reg_attachment');
        if(request()->hasFile('sec_reg_attachment')) {
            $index = 0;
            foreach($sec_reg_attachment as $file):
                $file->storeAs('public\NGP\\'.$id, 'sec_reg_attachment_'.$index.'.'.$file->extension());

                $this->insert_logs(Auth::user()->name, 'Added (Profile) Sec. Registration Attachment: sec_reg_attachment_'.$index.'.'.$file->extension(), $table);

                $index++;
            endforeach;
        }

        if($table == 'project_validation_year') {
            $yearData = explode(',', $request->year);
            for($index = 0; $index < $index_2; $index++) {
                $photos_plantation_establishment = request()->file('photos_plantation_establishment_'.$index);
                if(request()->hasFile('photos_plantation_establishment_'.$index)){
                    $fileIndex = 0;
                    foreach($photos_plantation_establishment as $file):
                        $file->storeAs('public\NGP\\'.$id, 'photos_plantation_establishment_'.$fileIndex.'_'.$yearData[$index].'.'.$file->extension());

                        $this->insert_logs(Auth::user()->name, 'Added (Project Validation) Year: '.$yearData[$index].' Attachment: photos_plantation_establishment_'.$fileIndex.'_'.$yearData[$index].'.'.$file->extension(), $table);

                        $fileIndex++;
                    endforeach;
                }
                
                $photos_seedlings_production = request()->file('photos_seedlings_production_'.$index);
                if(request()->hasFile('photos_seedlings_production_'.$index)){
                    $fileIndex = 0;
                    foreach($photos_seedlings_production as $file):
                        $file->storeAs('public\NGP\\'.$id, 'photos_seedlings_production_'.$fileIndex.'_'.$yearData[$index].'.'.$file->extension());

                        $this->insert_logs(Auth::user()->name, 'Added (Project Validation) Year: '.$yearData[$index].' Attachment: photos_seedlings_production_'.$fileIndex.'_'.$yearData[$index].'.'.$file->extension(), $table);
                        
                        $fileIndex++;
                    endforeach;
                }
                
                $photos_m_and_p = request()->file('photos_m_and_p_'.$index);
                if(request()->hasFile('photos_m_and_p_'.$index)){
                    $fileIndex = 0;
                    foreach($photos_m_and_p as $file):
                        $file->storeAs('public\NGP\\'.$id, 'photos_m_and_p_'.$fileIndex.'_'.$yearData[$index].'.'.$file->extension());

                        $this->insert_logs(Auth::user()->name, 'Added (Project Validation) Year: '.$yearData[$index].' Attachment: photos_m_and_p_'.$fileIndex.'_'.$yearData[$index].'.'.$file->extension(), $table);
                        
                        $fileIndex++;
                    endforeach;
                }

                $photos_validation_report_billing = request()->file('photos_validation_report_billing_'.$index);
                if(request()->hasFile('photos_validation_report_billing_'.$index)){
                    $fileIndex = 0;
                    foreach($photos_validation_report_billing as $file):
                        $file->storeAs('public\NGP\\'.$id, 'photos_validation_report_billing_'.$fileIndex.'_'.$yearData[$index].'.'.$file->extension());

                        $this->insert_logs(Auth::user()->name, 'Added (Project Validation) Year: '.$yearData[$index].' Attachment: photos_validation_report_billing_'.$fileIndex.'_'.$yearData[$index].'.'.$file->extension(), $table);
                        
                        $fileIndex++;
                    endforeach;
                }
                
                $geotagged_photos = request()->file('geotagged_photos_'.$index);
                if(request()->hasFile('geotagged_photos_'.$index)){
                    $fileIndex = 0;
                    foreach($geotagged_photos as $file):
                        $file->storeAs('public\NGP\\'.$id, 'geotagged_photos_'.$fileIndex.'_'.$yearData[$index].'.'.$file->extension());

                        $this->insert_logs(Auth::user()->name, 'Added (Project Validation) Year: '.$yearData[$index].' Attachment: geotagged_photos_'.$fileIndex.'_'.$yearData[$index].'.'.$file->extension(), $table);
                        
                        $fileIndex++;
                    endforeach;
                }

                $validation_report = request()->file('validation_report_'.$index);
                if(request()->hasFile('validation_report_'.$index)){
                    $fileIndex = 0;
                    foreach($validation_report as $file):
                        $file->storeAs('public\NGP\\'.$id, 'validation_report_'.$fileIndex.'_'.$yearData[$index].'.'.$file->extension());

                        $this->insert_logs(Auth::user()->name, 'Added (Project Validation) Year: '.$yearData[$index].' Attachment: validation_report_'.$fileIndex.'_'.$yearData[$index].'.'.$file->extension(), $table);
                        
                        $fileIndex++;
                    endforeach;
                }

                $request_for_relief = request()->file('request_for_relief_'.$index);
                if(request()->hasFile('request_for_relief_'.$index)){
                    $fileIndex = 0;
                    foreach($request_for_relief as $file):
                        $file->storeAs('public\NGP\\'.$id, 'request_for_relief_'.$fileIndex.'_'.$yearData[$index].'.'.$file->extension());

                        $this->insert_logs(Auth::user()->name, 'Added (Project Validation) Year: '.$yearData[$index].' Attachment: request_for_relief_'.$fileIndex.'_'.$yearData[$index].'.'.$file->extension(), $table);

                        $fileIndex++;
                    endforeach;
                }

                $request_for_relief_approved = request()->file('request_for_relief_approved_'.$index);
                if(request()->hasFile('request_for_relief_approved_'.$index)){
                    $fileIndex = 0;
                    foreach($request_for_relief_approved as $file):
                        $file->storeAs('public\NGP\\'.$id, 'request_for_relief_approved_'.$fileIndex.'_'.$yearData[$index].'.'.$file->extension());

                        $this->insert_logs(Auth::user()->name, 'Added (Project Validation) Year: '.$yearData[$index].' Attachment: request_for_relief_approved_'.$fileIndex.'_'.$yearData[$index].'.'.$file->extension(), $table);
                        
                        $fileIndex++;
                    endforeach;
                }



            }

            $project_validation_year_contract_data_geotagged_photos = request()->file('project_validation_year_contract_data_geotagged_photos');
            if(request()->hasFile('project_validation_year_contract_data_geotagged_photos')) {
                $index = 0;
                foreach($project_validation_year_contract_data_geotagged_photos as $file):
                    $file->storeAs('public\NGP\\'.$id, 'project_validation_year_contract_data_geotagged_photos_'.$index.'.'.$file->extension());

                    $this->insert_logs(Auth::user()->name, 'Added (Project Validation Contract) Attachment: project_validation_year_contract_data_geotagged_photos_'.$index.'.'.$file->extension(), $table);
                   
                    $index++;
                endforeach;
            }

            $project_validation_year_contract_data_validation_report = request()->file('project_validation_year_contract_data_validation_report');
            if(request()->hasFile('project_validation_year_contract_data_validation_report')) {
                $index = 0;
                foreach($project_validation_year_contract_data_validation_report as $file):
                    $file->storeAs('public\NGP\\'.$id, 'project_validation_year_contract_data_validation_report_'.$index.'.'.$file->extension());

                    $this->insert_logs(Auth::user()->name, 'Added (Project Validation Contract) Attachment: project_validation_year_contract_data_validation_report_'.$index.'.'.$file->extension(), $table);

                    $index++;
                endforeach;
            }

            $project_validation_year_contract_data_request_for_relief = request()->file('project_validation_year_contract_data_request_for_relief');
            if(request()->hasFile('project_validation_year_contract_data_request_for_relief')) {
                $index = 0;
                foreach($project_validation_year_contract_data_request_for_relief as $file):
                    $file->storeAs('public\NGP\\'.$id, 'project_validation_year_contract_data_request_for_relief_'.$index.'.'.$file->extension());

                    $this->insert_logs(Auth::user()->name, 'Added (Project Validation Contract) Attachment: project_validation_year_contract_data_request_for_relief_'.$index.'.'.$file->extension(), $table);

                    $index++;
                endforeach;
            }

            $project_validation_year_contract_data_request_for_relief_approved = request()->file('project_validation_year_contract_data_request_for_relief_approved');
            if(request()->hasFile('project_validation_year_contract_data_request_for_relief_approved')) {
                $index = 0;
                foreach($project_validation_year_contract_data_request_for_relief_approved as $file):
                    $file->storeAs('public\NGP\\'.$id, 'project_validation_year_contract_data_request_for_relief_approved_'.$index.'.'.$file->extension());

                    $this->insert_logs(Auth::user()->name, 'Added (Project Validation Contract) Attachment: project_validation_year_contract_data_request_for_relief_approved_'.$index.'.'.$file->extension(), $table);

                    $index++;
                endforeach;
            }

            $project_validation_final_inspection_data_geotagged_photos = request()->file('project_validation_final_inspection_data_geotagged_photos');
            if(request()->hasFile('project_validation_final_inspection_data_geotagged_photos')) {
                $index = 0;
                foreach($project_validation_final_inspection_data_geotagged_photos as $file):
                    $file->storeAs('public\NGP\\'.$id, 'project_validation_final_inspection_data_geotagged_photos_'.$index.'.'.$file->extension());

                    $this->insert_logs(Auth::user()->name, 'Added (Project Validation Final Inspection) Attachment: project_validation_final_inspection_data_geotagged_photos_'.$index.'.'.$file->extension(), $table);
                   
                    $index++;
                endforeach;
            }

            $project_validation_final_inspection_data_validation_report = request()->file('project_validation_final_inspection_data_validation_report');
            if(request()->hasFile('project_validation_final_inspection_data_validation_report')) {
                $index = 0;
                foreach($project_validation_final_inspection_data_validation_report as $file):
                    $file->storeAs('public\NGP\\'.$id, 'project_validation_final_inspection_data_validation_report_'.$index.'.'.$file->extension());

                    $this->insert_logs(Auth::user()->name, 'Added (Project Validation Final Inspection) Attachment: project_validation_final_inspection_data_validation_report_'.$index.'.'.$file->extension(), $table);

                    $index++;
                endforeach;
            }

            
        }else if($table == 'project_validation_year_contract') {
            $project_validation_year_contract_data_geotagged_photos = request()->file('project_validation_year_contract_data_geotagged_photos');
            if(request()->hasFile('project_validation_year_contract_data_geotagged_photos')) {
                $index = 0;
                foreach($project_validation_year_contract_data_geotagged_photos as $file):
                    $file->storeAs('public\NGP\\'.$id, 'project_validation_year_contract_data_geotagged_photos_'.$index.'.'.$file->extension());

                    $this->insert_logs(Auth::user()->name, 'Added (Project Validation Contract) Attachment: project_validation_year_contract_data_geotagged_photos_'.$index.'.'.$file->extension(), $table);
                   
                    $index++;
                endforeach;
            }

            $project_validation_year_contract_data_validation_report = request()->file('project_validation_year_contract_data_validation_report');
            if(request()->hasFile('project_validation_year_contract_data_validation_report')) {
                $index = 0;
                foreach($project_validation_year_contract_data_validation_report as $file):
                    $file->storeAs('public\NGP\\'.$id, 'project_validation_year_contract_data_validation_report_'.$index.'.'.$file->extension());

                    $this->insert_logs(Auth::user()->name, 'Added (Project Validation Contract) Attachment: project_validation_year_contract_data_validation_report_'.$index.'.'.$file->extension(), $table);

                    $index++;
                endforeach;
            }

            $project_validation_year_contract_data_request_for_relief = request()->file('project_validation_year_contract_data_request_for_relief');
            if(request()->hasFile('project_validation_year_contract_data_request_for_relief')) {
                $index = 0;
                foreach($project_validation_year_contract_data_request_for_relief as $file):
                    $file->storeAs('public\NGP\\'.$id, 'project_validation_year_contract_data_request_for_relief_'.$index.'.'.$file->extension());

                    $this->insert_logs(Auth::user()->name, 'Added (Project Validation Contract) Attachment: project_validation_year_contract_data_request_for_relief_'.$index.'.'.$file->extension(), $table);

                    $index++;
                endforeach;
            }

            $project_validation_year_contract_data_request_for_relief_approved = request()->file('project_validation_year_contract_data_request_for_relief_approved');
            if(request()->hasFile('project_validation_year_contract_data_request_for_relief_approved')) {
                $index = 0;
                foreach($project_validation_year_contract_data_request_for_relief_approved as $file):
                    $file->storeAs('public\NGP\\'.$id, 'project_validation_year_contract_data_request_for_relief_approved_'.$index.'.'.$file->extension());

                    $this->insert_logs(Auth::user()->name, 'Added (Project Validation Contract) Attachment: project_validation_year_contract_data_request_for_relief_approved_'.$index.'.'.$file->extension(), $table);

                    $index++;
                endforeach;
            }

        }else if($table == 'tbl_project_validation_final_inspection') {

            $project_validation_final_inspection_data_geotagged_photos = request()->file('project_validation_final_inspection_data_geotagged_photos');
            if(request()->hasFile('project_validation_final_inspection_data_geotagged_photos')) {
                $index = 0;
                foreach($project_validation_final_inspection_data_geotagged_photos as $file):
                    $file->storeAs('public\NGP\\'.$id, 'project_validation_final_inspection_data_geotagged_photos_'.$index.'.'.$file->extension());

                    $this->insert_logs(Auth::user()->name, 'Added (Project Validation Final Inspection) Attachment: project_validation_final_inspection_data_geotagged_photos_'.$index.'.'.$file->extension(), $table);
                   
                    $index++;
                endforeach;
            }

            $project_validation_final_inspection_data_validation_report = request()->file('project_validation_final_inspection_data_validation_report');
            if(request()->hasFile('project_validation_final_inspection_data_validation_report')) {
                $index = 0;
                foreach($project_validation_final_inspection_data_validation_report as $file):
                    $file->storeAs('public\NGP\\'.$id, 'project_validation_final_inspection_data_validation_report_'.$index.'.'.$file->extension());

                    $this->insert_logs(Auth::user()->name, 'Added (Project Validation Final Inspection) Attachment: project_validation_final_inspection_data_validation_report_'.$index.'.'.$file->extension(), $table);

                    $index++;
                endforeach;
            }
        }else if($table == 'tbl_financial') {
            $financial_performance_report = request()->file('financial_performance_report');
            if(request()->hasFile('financial_performance_report')) {
                $index = 0;
                foreach($financial_performance_report as $file):
                    $file->storeAs('public\NGP\\'.$id, 'financial_performance_report_'.$index.'.'.$file->extension());

                    $this->insert_logsc(Auth::user()->name, 'Added (Financial) Attachment: financial_performance_report_'.$index.'.'.$file->extension(), $table);
                   
                    $index++;
                endforeach;
            }

            $cip_li_report = request()->file('cip_li_report');
            if(request()->hasFile('cip_li_report')) {
                $index = 0;
                foreach($cip_li_report as $file):
                    $file->storeAs('public\NGP\\'.$id, 'cip_li_report_'.$index.'.'.$file->extension());

                    $this->insert_logs(Auth::user()->name, 'Added (Financial) Attachment: cip_li_report_'.$index.'.'.$file->extension(), $table);

                    $index++;
                endforeach;
            }
        }
        // return 
        return json_encode('success');
    }

    public function getDataByOffice($id) {
        $profile_model = new Tbl_profile();
        $completed_forms_model = new View_completed_forms();
        $user_model = new Users;
        $query = '';
        
        if($id != 0) {
            if($id == 222 || $id == 333) {
                $query = $profile_model->where('penro_implemented', $id)->get();
                $completed_forms_query = $completed_forms_model->get();
    
                foreach($completed_forms_query as $completed_forms_row) {
                    foreach ($query as $profile_index => $profile_row) {
                        if($profile_row->id == $completed_forms_row->id) {
                            $profile_row->database_status = '';
                        }else {
                            $profile_row->database_status = '';
                        }
                    }
                }

            }else {
                $query = $profile_model->where('cenro_id', $id)->get();
                $completed_forms_query = $completed_forms_model->where('cenro_id', $id)->get();
    
                foreach($completed_forms_query as $completed_forms_row) {
                    foreach ($query as $profile_row) {
                        if($profile_row->id == $completed_forms_row->id) {
                            $profile_row->database_status = '';
                        }else {
                            $profile_row->database_status = '';
                        }
                    }
                }

            }
        }else {
            $query = $profile_model->select('*')->get();
        }

        if($id == 222 || $id == 333) {
            $data = [
                'office' => 'PENRO Implemented'
            ];
            $result = [
                'result' => $query,
                'office_name' => $data
            ];

        }else {
            $result = [
                'result' => $query,
                'office_name' => $user_model->where('cenro_id', $id)->first()
            ];

        }

        return json_encode($result);
    }

    public function getDataById($id) {
        $Tbl_profile_model = new Tbl_profile;
        $Tbl_seedlings_planted_model = new Tbl_seedlings_planted;
        $Tbl_area_developed_commodity_model = new Tbl_area_developed_commodity;
        $Tbl_project_validation_year_model = new Tbl_project_validation_year;
        $Tbl_project_validation_year_contract_model = new Tbl_project_validation_year_contract;
        $Tbl_project_validation_final_inspection_model = new Tbl_project_validation_final_inspection;
        $Tbl_financial_model = new Tbl_financial;
        $Tbl_volunteer_planters_model = new Tbl_volunteer_planters;

        $result = [
            'Tbl_profile_model' => $Tbl_profile_model->where('id', $id)->first(),
            'Tbl_seedlings_planted_model' => $Tbl_seedlings_planted_model->where('fkey', $id)->get(),
            'Tbl_area_developed_commodity_model' => $Tbl_area_developed_commodity_model->where('fkey', $id)->first(),
            'Tbl_project_validation_year_model' => $Tbl_project_validation_year_model->where('fkey', $id)->get(),
            'Tbl_project_validation_year_contract_model' => $Tbl_project_validation_year_contract_model->where('fkey', $id)->first(),
            'Tbl_project_validation_final_inspection_model' => $Tbl_project_validation_final_inspection_model->where('fkey', $id)->first(),
            'Tbl_financial_model' => $Tbl_financial_model->where('fkey', $id)->first(),
            'Tbl_volunteer_planters_model' => $Tbl_volunteer_planters_model->where('fkey', $id)->first(),
        ];

        return json_encode($result);


    }

    public function updateData(Request $request, $table, $id) {
        if($table == 'tbl_profile') {

            $penro_implemented = $request->penro_implemented;
            if($penro_implemented == 'Yes') {
                if($request->penro == 'Bukidnon') {
                    $penro_implemented = 222;
                }else if($request->penro == 'Lanao del Norte') {
                    $penro_implemented = 333;
                }

            }else {
                $penro_implemented = 0;
            }
            $model = new Tbl_profile;
            $data = $model->find($request->id)->update([
                    'year' => $request->year,
                    'sitecode' => $request->sitecode,
                    'name_of_po' => $request->name_of_po,
                    'po_representative' => $request->po_representative,
                    'mobile_no' => $request->mobile_no,
                    'fund_source' => $request->fund_source,
                    'project_location' => $request->project_location,
                    'watershed' => $request->watershed,
                    'mailing_address' => $request->mailing_address,
                    'land_tenure' => $request->land_tenure,
                    'forest_zone' => $request->forest_zone,
                    'no_of_po_members_male' => $request->no_of_po_members_male,
                    'no_of_po_members_female' => $request->no_of_po_members_female,
                    'registering_agency' => $request->registering_agency,
                    'commodity' => $request->commodity,
                    'no_of_seedlings_contracted' => $request->no_of_seedlings_contracted,
                    'total_contracted_area' => $request->total_contracted_area,
                    'total_amount_contracted_original' => $request->total_amount_contracted_original,
                    'total_amount_contracted_supplemental' => $request->total_amount_contracted_supplemental,
                    'total_project_cost' => $request->total_project_cost,
                    'pictures_of_the_po' => $request->pictures_of_the_po,
                    'shapefile_and_kml' => $request->shapefile_and_kml,
                    'moa_loa_year1' => $request->moa_loa_year1,
                    'moa_loa_year2' => $request->moa_loa_year2,
                    'moa_loa_year3' => $request->moa_loa_year3,
                    'supplemental_moa' => $request->supplemental_moa,
                    'created_by' => $request->created_by,
                    'penro' => $request->penro,
                    'cenro' => $request->cenro,
                    'cenro_id' => $request->cenro_id,
                    'penro_id' => $request->penro_id,
                    'no_of_polygon' => $request->no_of_polygon,
                    'moa_loa_no' => $request->moa_loa_no,
                    'sec_reg_no' => $request->sec_reg_no,
                    'date_of_sec_reg' => $request->date_of_sec_reg,
                    'sec_reg_attachment' => $request->sec_reg_attachment,
                    'penro_implemented' => $penro_implemented,
                ]);

            if($data) {
                $result = [
                    'message' => 'success',
                ];
                $this->insert_logs(Auth::user()->name, 'Updated (Profile): '.$request->name_of_po. ', Year: '.$request->year. ', id: '. $request->id, $table);
            }else {
                $result = [
                    'message' => 'error'
                ];
            }
        }else if($table == 'tbl_seedlings_planted') {
            $model = new Tbl_seedlings_planted;
            $model->where('fkey', $id)->delete();

            for($index = 0; $index < count($request->all()); $index++) {
                $data = new Tbl_seedlings_planted([
                    'fkey' => $request[$index]['fkey'],
                    'species_name' => $request[$index]['species_name'],
                    'replanted' => $request[$index]['replanted'],
                    'commodity' => $request[$index]['commodity'],
                    'seedlings_produced' => $request[$index]['seedlings_produced'],
                    'seedlings_planted' => $request[$index]['seedlings_planted'],
                    'area_planted' => $request[$index]['area_planted'],
                    'spacing' => $request[$index]['spacing'],
                    'mode_of_propagation' => $request[$index]['mode_of_propagation'],
                    'fertilizer_applied' => $request[$index]['fertilizer_applied'],
                    'start_date' => $request[$index]['start_date'],
                    'end_date' => $request[$index]['end_date'],
                ]);

                if($data->save()) {
                    $result = [
                        'message' => 'success',
                    ];
                    $this->insert_logs(Auth::user()->name, 'Updated (Seedlings Planted) Species: '.$request[$index]['species_name']. ', Commodity: '.$request[$index]['commodity']. ', id: '. $data->id, $table);
                }else {
                    $result = [
                        'message' => 'error'
                    ];
                }
            }
        }else if($table == 'tbl_area_developed_commodity') {
            $model = new Tbl_area_developed_commodity;
            $data = $model->find($request->id)->update([
                'fuelwood_seedling' => $request->fuelwood_seedling,
                'fuelwood_cutting' => $request->fuelwood_cutting,
                'timber_mmfn' => $request->timber_mmfn,
                'timber' => $request->timber,
                'indigenous' => $request->indigenous,
                'indigenous_clonal_propagation' => $request->indigenous_clonal_propagation,
                'coffee' => $request->coffee,
                'cacao_root_stock' => $request->cacao_root_stock,
                'cacao_grafted' => $request->cacao_grafted,
                'rubber_root_stock' => $request->rubber_root_stock,
                'rubber_budded' => $request->rubber_budded,
                'bamboo' => $request->bamboo,
                'rattan' => $request->rattan,
                'mangrove_propagule' => $request->mangrove_propagule,
                'mangrove_potted' => $request->mangrove_potted,
                'nipa' => $request->nipa,
                'other_fruit_trees' => $request->other_fruit_trees,
            ]);

            if($data) {
                $result = [
                    'message' => 'success',
                ];
                $this->insert_logs(Auth::user()->name, 'Updated Area Developed Commodity, id: '. $request->id, $table);
            }else {
                $result = [
                    'message' => 'error'
                ];
            }

        }else if($table == 'tbl_project_validation_year') {
            $model = new Tbl_project_validation_year;
            $model->where('fkey', $id)->delete();

            $file_id = [];
            for($index = 0; $index < count($request->all()); $index++) {
                // $fkey = preg_replace('/\s+/', '', $request[$index]['fkey']);
                $data = new Tbl_project_validation_year([
                    'year' => $request[$index]['year'],
                    'fkey' => $request[$index]['fkey'],
                    'type_of_validation' => $request[$index]['type_of_validation'],
                    'date_conducted' => $request[$index]['date_conducted'],
                    'conducted_by' => $request[$index]['conducted_by'],
                    'seedlings_alive' => $request[$index]['seedlings_alive'],
                    'seedlings_dead' => $request[$index]['seedlings_dead'],
                    'total_seedlings_validated' => $request[$index]['total_seedlings_validated'],
                    'survival_rate' => $request[$index]['survival_rate'],
                    'average_height' => $request[$index]['average_height'],
                    'average_diameter' => $request[$index]['average_diameter'],
                    'average_no_of_leaves' => $request[$index]['average_no_of_leaves'],
                    'species_replanted' => $request[$index]['species_replanted'],
                    'spacing_and_stocking' => $request[$index]['spacing_and_stocking'],
                    'pest_and_diseases' => $request[$index]['pest_and_diseases'],
                    'remarks' => $request[$index]['remarks'],
                    'photos_seedlings_production' => $request[$index]['photos_seedlings_production'],
                    'photos_plantation_establishment' => $request[$index]['photos_plantation_establishment'],
                    'photos_m_and_p' => $request[$index]['photos_m_and_p'],
                    'photos_validation_report_billing' => $request[$index]['photos_validation_report_billing'],
                    'photos_csd' => $request[$index]['photos_csd'],
                    'area_damaged' => $request[$index]['area_damaged'],
                    'seedling_damaged' => $request[$index]['seedling_damaged'],
                    'amount_damaged' => $request[$index]['amount_damaged'],
                    'damaged_remarks' => $request[$index]['damaged_remarks'],
                    'geotagged_photos' => $request[$index]['geotagged_photos'],
                    'validation_report' => $request[$index]['validation_report'],
                    'request_for_relief' => $request[$index]['request_for_relief'],
                    'request_for_relief_approved' => $request[$index]['request_for_relief_approved'],
                    'impaired_or_damage' => $request[$index]['impaired_or_damage'],
                ]);
                $data->save();
                if($data) {
                    array_push($file_id, $data->id);
                    $result = [
                        'message' => 'success',
                        'new_data' => $file_id
                        
                    ];
                    $this->insert_logs(Auth::user()->name, 'Updated (Project Validation) Year: '.$request[$index]['year'].', id: '. $data->id, $table);
                }else {
                    $result = [
                        'message' => 'error',
                        'id' => null
                    ];
                }
            }
        }else if($table == 'tbl_project_validation_year_contract') {
            $model = new Tbl_project_validation_year_contract;
            $data = $model->find($request->id)->update([
                'year_damaged' => $request->year_damaged,
                'area_damaged' => $request->area_damaged,
                'seedling_damaged' => $request->seedling_damaged,
                'amount_damaged' => $request->amount_damaged,
                'geotagged_photos' => $request->geotagged_photos,
                'validation_report' => $request->validation_report,
                'request_for_relief' => $request->request_for_relief,
                'request_for_relief_approved' => $request->request_for_relief_approved,
            ]);

            if($data) {
                $result = [
                    'message' => 'success',
                ];
                $this->insert_logs(Auth::user()->name, 'Updated (Project Validation) Year Damaged: '.$request->year_damaged.', id: '. $request->id, $table);
            }else {
                $result = [
                    'message' => 'error'
                ];
            }
        }else if($table == 'tbl_project_validation_final_inspection') {
            $model = new Tbl_project_validation_final_inspection;
            $data = $model->find($request->id)->update([
                'type_of_validation' => $request->type_of_validation,
                'date_conducted' => $request->date_conducted,
                'conducted_by' => $request->conducted_by,
                'seedlings_alive' => $request->seedlings_alive,
                'seedlings_dead' => $request->seedlings_dead,
                'total_seedlings_validated' => $request->total_seedlings_validated,
                'survival_rate' => $request->survival_rate,
                'average_height' => $request->average_height,
                'average_diameter' => $request->average_diameter,
                'average_no_of_leaves' => $request->average_no_of_leaves,
                'species_replanted' => $request->species_replanted,
                'spacing_and_stocking' => $request->spacing_and_stocking,
                'pest_and_diseases' => $request->pest_and_diseases,
                'remarks' => $request->remarks,
                'geotagged_photos' => $request->geotagged_photos,
                'validation_report' => $request->validation_report,
            ]);

            if($data) {
                $result = [
                    'message' => 'success',
                ];
                $this->insert_logs(Auth::user()->name, 'Updated (Project Validation Final Inspection) Date Conducted: '.$request->date_conducted.', id: '. $request->id, $table);
            }else {
                $result = [
                    'message' => 'error'
                ];
            }
        }else if($table == 'tbl_financial') {
            $model = new Tbl_financial;
            $data = $model->find($request->id)->update([
                'body' => $request->body,
                'classification' => $request->classification,
                'name_of_bank' => $request->name_of_bank,
                'branch' => $request->branch,
                'account_name' => $request->account_name,
                'account_number' => $request->account_number,
                'cheque_no' => $request->cheque_no,
                'date_of_cheque' => $request->date_of_cheque,
                'financial_performance_report' => $request->financial_performance_report,
                'cip_li_report' => $request->cip_li_report,
            ]);

            if($data) {
                $result = [
                    'message' => 'success',
                ];
                $this->insert_logs(Auth::user()->name, 'Updated (Financial) Account Number: '.$request->account_number.', id: '. $request->id, $table);
            }else {
                $result = [
                    'message' => 'error'
                ];
            }
        }else if($table == 'tbl_volunteer_planters') {
            $model = new Tbl_volunteer_planters;
            $data = $model->find($request->id)->update([
                'name_of_school_organization' => $request->name_of_school_organization,
                'male' => $request->male,
                'female' => $request->female,
                'encoded_by' => $request->encoded_by,
            ]);

            if($data) {
                $result = [
                    'message' => 'success',
                ];
                $this->insert_logs(Auth::user()->name, 'Updated (Volunteer Planters) Name of School/Org.: '.$request->name_of_school_organization.', id: '. $request->id, $table);
            }else {
                $result = [
                    'message' => 'error'
                ];
            }
        }

        return json_encode($result);
    }

    public function updateFiles(Request $request, $table, $id, $index_2 = 0) {
        $Tbl_profile_model = new Tbl_profile;
        $Tbl_project_validation_year_model = new Tbl_project_validation_year;
        $Tbl_project_validation_year_contract_model = new Tbl_project_validation_year_contract;
        $Tbl_project_validation_final_inspection_model = new Tbl_project_validation_final_inspection;
        $Tbl_financial_model = new Tbl_financial;
        

        $files = request()->file('pictures_of_the_po');
        if(request()->hasFile('pictures_of_the_po')) {

            if($Tbl_profile_model->where('id', $id)->first()->pictures_of_the_po != null) {
                $index = count($Tbl_profile_model->where('id', $id)->first()->pictures_of_the_po) - 1;
            }else {
                $index = 0;

            }
            foreach($files as $file):
                $file->storeAs('public\NGP\\'.$id, 'pictures_of_the_po_'.$index.'.'.$file->extension());
                    $this->insert_logs(Auth::user()->name, 'Added (Profile) Pictures of PO: pictures_of_the_po_'.$index.'.'.$file->extension(), $table);
                $index++;
            endforeach;
        }

        $shapefile_and_kml = request()->file('shapefile_and_kml');
        if(request()->hasFile('shapefile_and_kml')) {

            if($Tbl_profile_model->where('id', $id)->first()->shapefile_and_kml != null) {
                $index = count($Tbl_profile_model->where('id', $id)->first()->shapefile_and_kml) - 1;
            }else {
                $index = 0;

            }

            foreach($shapefile_and_kml as $file):
                $file->storeAs('public\NGP\\'.$id, 'shapefile_and_kml_'.$index.'.'.$file->extension());
                $this->insert_logs(Auth::user()->name, 'Added (Profile) Shapefile and KML: shapefile_and_kml_'.$index.'.'.$file->extension(), $table);
                $index++;
            endforeach;
        }

        $moa_loa_year1 = request()->file('moa_loa_year1');
        if(request()->hasFile('moa_loa_year1')) {

            if($Tbl_profile_model->where('id', $id)->first()->moa_loa_year1 != null) {
                $index = count($Tbl_profile_model->where('id', $id)->first()->moa_loa_year1) - 1;
            }else {
                $index = 0;

            }

            foreach($moa_loa_year1 as $file):
                $file->storeAs('public\NGP\\'.$id, 'moa_loa_year1_'.$index.'.'.$file->extension());
                $this->insert_logs(Auth::user()->name, 'Added (Profile) MOA/LOA Year 1: moa_loa_year1_'.$index.'.'.$file->extension(), $table);
                $index++;
            endforeach;
        }

        $moa_loa_year2 = request()->file('moa_loa_year2');
        if(request()->hasFile('moa_loa_year2')) {

            if($Tbl_profile_model->where('id', $id)->first()->moa_loa_year2 != null) {
                $index = count($Tbl_profile_model->where('id', $id)->first()->moa_loa_year2) - 1;
            }else {
                $index = 0;

            }

            foreach($moa_loa_year2 as $file):
                $file->storeAs('public\NGP\\'.$id, 'moa_loa_year2_'.$index.'.'.$file->extension());
                $this->insert_logs(Auth::user()->name, 'Added (Profile) MOA/LOA Year 2: moa_loa_year2_'.$index.'.'.$file->extension(), $table);
                $index++;
            endforeach;
        }

        $moa_loa_year3 = request()->file('moa_loa_year3');
        if(request()->hasFile('moa_loa_year3')) {

            if($Tbl_profile_model->where('id', $id)->first()->moa_loa_year3 != null) {
                $index = count($Tbl_profile_model->where('id', $id)->first()->moa_loa_year3) - 1;
            }else {
                $index = 0;

            }

            foreach($moa_loa_year3 as $file):
                $file->storeAs('public\NGP\\'.$id, 'moa_loa_year3_'.$index.'.'.$file->extension());
                $this->insert_logs(Auth::user()->name, 'Added (Profile) MOA/LOA Year 3: moa_loa_year3_'.$index.'.'.$file->extension(), $table);
                $index++;
            endforeach;
        }

        $supplemental_moa = request()->file('supplemental_moa');
        if(request()->hasFile('supplemental_moa')) {

            if($Tbl_profile_model->where('id', $id)->first()->supplemental_moa != null) {
                $index = count($Tbl_profile_model->where('id', $id)->first()->supplemental_moa) - 1;
            }else {
                $index = 0;

            }

            foreach($supplemental_moa as $file):
                $file->storeAs('public\NGP\\'.$id, 'supplemental_moa_'.$index.'.'.$file->extension());
                $this->insert_logs(Auth::user()->name, 'Added (Profile) Supplemental Moa: supplemental_moa_'.$index.'.'.$file->extension(), $table);
                $index++;
            endforeach;
        }

        $sec_reg_attachment = request()->file('sec_reg_attachment');
        if(request()->hasFile('sec_reg_attachment')) {

            if($Tbl_profile_model->where('id', $id)->first()->sec_reg_attachment != null) {
                $index = count($Tbl_profile_model->where('id', $id)->first()->sec_reg_attachment) - 1;
            }else {
                $index = 0;

            }

            foreach($sec_reg_attachment as $file):
                $file->storeAs('public\NGP\\'.$id, 'sec_reg_attachment_'.$index.'.'.$file->extension());
                $this->insert_logs(Auth::user()->name, 'Added (Profile) Sec. Registration Attachment: sec_reg_attachment_'.$index.'.'.$file->extension(), $table);
                $index++;
            endforeach;
        }

        if($table == 'project_validation_year') {
            $yearData = explode(',', $request->year);
            $id_data = explode(',', $request->id);
            for($index = 0; $index < $index_2; $index++) {
                $photos_plantation_establishment = request()->file('photos_plantation_establishment_'.$index);
                if(request()->hasFile('photos_plantation_establishment_'.$index)){
                    if($Tbl_project_validation_year_model->where('id', $id_data[$index])->first()->photos_plantation_establishment != null) {
                        $fileIndex = count($Tbl_project_validation_year_model->where('id', $id_data[$index])->first()->photos_plantation_establishment) - 1;
                    }else {
                        $fileIndex = 0;
        
                    }

                    foreach($photos_plantation_establishment as $file):
                        $file->storeAs('public\NGP\\'.$id, 'photos_plantation_establishment_'.$fileIndex.'_'.$yearData[$index].'.'.$file->extension());
                        $this->insert_logs(Auth::user()->name, 'Added (Project Validation) Year: '.$yearData[$index].' Attachment: photos_plantation_establishment_'.$fileIndex.'_'.$yearData[$index].'.'.$file->extension(), $table);
                        $fileIndex++;
                    endforeach;
                }
                
                $photos_seedlings_production = request()->file('photos_seedlings_production_'.$index);
                if(request()->hasFile('photos_seedlings_production_'.$index)){
                    // return json_encode($id_data);
                    // return json_encode(count($Tbl_project_validation_year_model->where('id', $id_data[$index])->first()->photos_seedlings_production));

                    if($Tbl_project_validation_year_model->where('id', $id_data[$index])->first()->photos_seedlings_production != null) {
                        $fileIndex = count($Tbl_project_validation_year_model->where('id', $id_data[$index])->first()->photos_seedlings_production) - 1;
                    }else {
                        $fileIndex = 0;
        
                    }

                    foreach($photos_seedlings_production as $file):
                        $file->storeAs('public\NGP\\'.$id, 'photos_seedlings_production_'.$fileIndex.'_'.$yearData[$index].'.'.$file->extension());
                        $this->insert_logs(Auth::user()->name, 'Added (Project Validation) Year: '.$yearData[$index].' Attachment: photos_seedlings_production_'.$fileIndex.'_'.$yearData[$index].'.'.$file->extension(), $table);
                        $fileIndex++;
                    endforeach;
                }
                
                $photos_m_and_p = request()->file('photos_m_and_p_'.$index);
                if(request()->hasFile('photos_m_and_p_'.$index)){

                    if($Tbl_project_validation_year_model->where('id', $id_data[$index])->first()->photos_m_and_p != null) {
                        $fileIndex = count($Tbl_project_validation_year_model->where('id', $id_data[$index])->first()->photos_m_and_p) - 1;
                    }else {
                        $fileIndex = 0;
        
                    }

                    foreach($photos_m_and_p as $file):
                        $file->storeAs('public\NGP\\'.$id, 'photos_m_and_p_'.$fileIndex.'_'.$yearData[$index].'.'.$file->extension());
                        $this->insert_logs(Auth::user()->name, 'Added (Project Validation) Year: '.$yearData[$index].' Attachment: photos_m_and_p_'.$fileIndex.'_'.$yearData[$index].'.'.$file->extension(), $table);
                        $fileIndex++;
                    endforeach;
                }

                $photos_validation_report_billing = request()->file('photos_validation_report_billing_'.$index);
                if(request()->hasFile('photos_validation_report_billing_'.$index)){

                    if( $Tbl_project_validation_year_model->where('id', $id_data[$index])->first()->photos_validation_report_billing != null) {
                        $fileIndex = count($Tbl_project_validation_year_model->where('id', $id_data[$index])->first()->photos_validation_report_billing) - 1;
                    }else {
                        $fileIndex = 0;
        
                    }

                    foreach($photos_validation_report_billing as $file):
                        $file->storeAs('public\NGP\\'.$id, 'photos_validation_report_billing_'.$fileIndex.'_'.$yearData[$index].'.'.$file->extension());
                        $this->insert_logs(Auth::user()->name, 'Added (Project Validation) Year: '.$yearData[$index].' Attachment: photos_validation_report_billing_'.$fileIndex.'_'.$yearData[$index].'.'.$file->extension(), $table);
                        $fileIndex++;
                    endforeach;
                }
                
                $geotagged_photos = request()->file('geotagged_photos_'.$index);
                if(request()->hasFile('geotagged_photos_'.$index)){

                    if($Tbl_project_validation_year_model->where('id', $id_data[$index])->first()->geotagged_photos != null) {
                        $fileIndex = count($Tbl_project_validation_year_model->where('id', $id_data[$index])->first()->geotagged_photos) - 1;
                    }else {
                        $fileIndex = 0;
        
                    }

                    foreach($geotagged_photos as $file):
                        $file->storeAs('public\NGP\\'.$id, 'geotagged_photos_'.$fileIndex.'_'.$yearData[$index].'.'.$file->extension());
                        $this->insert_logs(Auth::user()->name, 'Added (Project Validation) Year: '.$yearData[$index].' Attachment: geotagged_photos_'.$fileIndex.'_'.$yearData[$index].'.'.$file->extension(), $table);
                        $fileIndex++;
                    endforeach;
                }

                $validation_report = request()->file('validation_report_'.$index);
                if(request()->hasFile('validation_report_'.$index)){
                    
                    if($Tbl_project_validation_year_model->where('id', $id_data[$index])->first()->validation_report != null) {
                        $fileIndex = count($Tbl_project_validation_year_model->where('id', $id_data[$index])->first()->validation_report) - 1;
                    }else {
                        $fileIndex = 0;
        
                    }

                    foreach($validation_report as $file):
                        $file->storeAs('public\NGP\\'.$id, 'validation_report_'.$fileIndex.'_'.$yearData[$index].'.'.$file->extension());
                        $this->insert_logs(Auth::user()->name, 'Added (Project Validation) Year: '.$yearData[$index].' Attachment: validation_report_'.$fileIndex.'_'.$yearData[$index].'.'.$file->extension(), $table);
                        $fileIndex++;
                    endforeach;
                }

                $request_for_relief = request()->file('request_for_relief_'.$index);
                if(request()->hasFile('request_for_relief_'.$index)){

                    if($Tbl_project_validation_year_model->where('id', $id_data[$index])->first()->request_for_relief != null) {
                        $fileIndex = count($Tbl_project_validation_year_model->where('id', $id_data[$index])->first()->request_for_relief) - 1;
                    }else {
                        $fileIndex = 0;
        
                    }

                    foreach($request_for_relief as $file):
                        $file->storeAs('public\NGP\\'.$id, 'request_for_relief_'.$fileIndex.'_'.$yearData[$index].'.'.$file->extension());
                        $this->insert_logs(Auth::user()->name, 'Added (Project Validation) Year: '.$yearData[$index].' Attachment: request_for_relief_'.$fileIndex.'_'.$yearData[$index].'.'.$file->extension(), $table);
                        $fileIndex++;
                    endforeach;
                }

                $request_for_relief_approved = request()->file('request_for_relief_approved_'.$index);
                if(request()->hasFile('request_for_relief_approved_'.$index)){

                    if($Tbl_project_validation_year_model->where('id', $id_data[$index])->first()->request_for_relief_approved != null) {
                        $fileIndex = count($Tbl_project_validation_year_model->where('id', $id_data[$index])->first()->request_for_relief_approved) - 1;
                    }else {
                        $fileIndex = 0;
        
                    }

                    foreach($request_for_relief_approved as $file):
                        $file->storeAs('public\NGP\\'.$id, 'request_for_relief_approved_'.$fileIndex.'_'.$yearData[$index].'.'.$file->extension());
                        $this->insert_logs(Auth::user()->name, 'Added (Project Validation) Year: '.$yearData[$index].' Attachment: request_for_relief_approved_'.$fileIndex.'_'.$yearData[$index].'.'.$file->extension(), $table);
                        $fileIndex++;
                    endforeach;
                }



            }

            
        }else if($table == 'project_validation_year_contract') {
            $project_validation_year_contract_data_geotagged_photos = request()->file('project_validation_year_contract_data_geotagged_photos');
            if(request()->hasFile('project_validation_year_contract_data_geotagged_photos')) {

                if($Tbl_project_validation_year_contract_model->where('fkey', $id)->first()->geotagged_photos != null) {
                    $index = count($Tbl_project_validation_year_contract_model->where('fkey', $id)->first()->geotagged_photos) - 1;
                }else {
                    $index = 0;
    
                }

                foreach($project_validation_year_contract_data_geotagged_photos as $file):
                    $file->storeAs('public\NGP\\'.$id, 'project_validation_year_contract_data_geotagged_photos_'.$index.'.'.$file->extension());
                    $this->insert_logs(Auth::user()->name, 'Added (Project Validation Contract) Attachment: project_validation_year_contract_data_geotagged_photos_'.$index.'.'.$file->extension(), $table);
                    $index++;
                endforeach;
            }

            $project_validation_year_contract_data_validation_report = request()->file('project_validation_year_contract_data_validation_report');
            if(request()->hasFile('project_validation_year_contract_data_validation_report')) {

                if($Tbl_project_validation_year_contract_model->where('fkey', $id)->first()->validation_report != null) {
                    $index = count($Tbl_project_validation_year_contract_model->where('fkey', $id)->first()->validation_report) - 1;
                }else {
                    $index = 0;
    
                }

                foreach($project_validation_year_contract_data_validation_report as $file):
                    $file->storeAs('public\NGP\\'.$id, 'project_validation_year_contract_data_validation_report_'.$index.'.'.$file->extension());
                    $this->insert_logs(Auth::user()->name, 'Added (Project Validation Contract) Attachment: project_validation_year_contract_data_validation_report_'.$index.'.'.$file->extension(), $table);
                    $index++;
                endforeach;
            }

            $project_validation_year_contract_data_request_for_relief = request()->file('project_validation_year_contract_data_request_for_relief');
            if(request()->hasFile('project_validation_year_contract_data_request_for_relief')) {

                if($Tbl_project_validation_year_contract_model->where('fkey', $id)->first()->request_for_relief != null) {
                    $index = count($Tbl_project_validation_year_contract_model->where('fkey', $id)->first()->request_for_relief) - 1;
                }else {
                    $index = 0;
    
                }

                foreach($project_validation_year_contract_data_request_for_relief as $file):
                    $file->storeAs('public\NGP\\'.$id, 'project_validation_year_contract_data_request_for_relief_'.$index.'.'.$file->extension());
                    $this->insert_logs(Auth::user()->name, 'Added (Project Validation Contract) Attachment: project_validation_year_contract_data_request_for_relief_'.$index.'.'.$file->extension(), $table);
                    $index++;
                endforeach;
            }

            $project_validation_year_contract_data_request_for_relief_approved = request()->file('project_validation_year_contract_data_request_for_relief_approved');
            if(request()->hasFile('project_validation_year_contract_data_request_for_relief_approved')) {

                if($Tbl_project_validation_year_contract_model->where('fkey', $id)->first()->request_for_relief != null) {
                    $index = count($Tbl_project_validation_year_contract_model->where('fkey', $id)->first()->request_for_relief) - 1;
                }else {
                    $index = 0;
    
                }

                foreach($project_validation_year_contract_data_request_for_relief_approved as $file):
                    $file->storeAs('public\NGP\\'.$id, 'project_validation_year_contract_data_request_for_relief_approved_'.$index.'.'.$file->extension());
                    $this->insert_logs(Auth::user()->name, 'Added (Project Validation Contract) Attachment: project_validation_year_contract_data_request_for_relief_approved_'.$index.'.'.$file->extension(), $table);
                    $index++;
                endforeach;
            }

        }else if($table == 'tbl_project_validation_final_inspection') {

            $project_validation_final_inspection_data_geotagged_photos = request()->file('project_validation_final_inspection_data_geotagged_photos');
            if(request()->hasFile('project_validation_final_inspection_data_geotagged_photos')) {

                if($Tbl_project_validation_final_inspection_model->where('fkey', $id)->first()->geotagged_photos != null) {
                    $index = count($Tbl_project_validation_final_inspection_model->where('fkey', $id)->first()->geotagged_photos) - 1;
                }else {
                    $index = 0;
    
                }

                foreach($project_validation_final_inspection_data_geotagged_photos as $file):
                    $file->storeAs('public\NGP\\'.$id, 'project_validation_final_inspection_data_geotagged_photos_'.$index.'.'.$file->extension());
                    $this->insert_logs(Auth::user()->name, 'Added (Project Validation Final Inspection) Attachment: project_validation_final_inspection_data_geotagged_photos_'.$index.'.'.$file->extension(), $table);
                    $index++;
                endforeach;
            }

            $project_validation_final_inspection_data_validation_report = request()->file('project_validation_final_inspection_data_validation_report');
            if(request()->hasFile('project_validation_final_inspection_data_validation_report')) {

                if($Tbl_project_validation_final_inspection_model->where('fkey', $id)->first()->validation_report != null) {
                    $index = count($Tbl_project_validation_final_inspection_model->where('fkey', $id)->first()->validation_report) - 1;
                }else {
                    $index = 0;
    
                }
                
                foreach($project_validation_final_inspection_data_validation_report as $file):
                    $file->storeAs('public\NGP\\'.$id, 'project_validation_final_inspection_data_validation_report_'.$index.'.'.$file->extension());
                    $this->insert_logs(Auth::user()->name, 'Added (Project Validation Final Inspection) Attachment: project_validation_final_inspection_data_validation_report_'.$index.'.'.$file->extension(), $table);
                    $index++;
                endforeach;
            }
        }else if($table == 'tbl_financial') {
            $financial_performance_report = request()->file('financial_performance_report');
            if(request()->hasFile('financial_performance_report')) {

                if($Tbl_financial_model->where('fkey', $id)->first()->financial_performance_report != null) {
                    $index = count($Tbl_financial_model->where('fkey', $id)->first()->financial_performance_report) - 1;

                }else {
                    $index = 0;

                }
                foreach($financial_performance_report as $file):
                    $file->storeAs('public\NGP\\'.$id, 'financial_performance_report_'.$index.'.'.$file->extension());
                    $this->insert_logs(Auth::user()->name, 'Added (Project Validation Final Inspection) Attachment: project_validation_final_inspection_data_validation_report_'.$index.'.'.$file->extension(), $table);
                    $index++;
                endforeach;
            }

            $cip_li_report = request()->file('cip_li_report');
            if(request()->hasFile('cip_li_report')) {
                
                if($Tbl_financial_model->where('fkey', $id)->first()->cip_li_report != null) {
                    $index = count($Tbl_financial_model->where('fkey', $id)->first()->cip_li_report) - 1;

                }else {
                    $index = 0;

                }

                foreach($cip_li_report as $file):
                    $file->storeAs('public\NGP\\'.$id, 'cip_li_report_'.$index.'.'.$file->extension());
                    $this->insert_logs(Auth::user()->name, 'Added (Financial) Attachment: cip_li_report_'.$index.'.'.$file->extension(), $table);
                    $index++;
                endforeach;
            }
        }
        // return 
        return json_encode('success');
    }

    public function delete_data(Request $request) {
        $tbl_profile = new Tbl_profile;
        $tbl_seedlings_planted = new Tbl_seedlings_planted;
        $tbl_area_developed_commodity = new Tbl_area_developed_commodity;
        $tbl_project_validation_year = new Tbl_project_validation_year;
        $tbl_project_validation_year_contract = new Tbl_project_validation_year_contract;
        $tbl_project_validation_final_inspection = new Tbl_project_validation_final_inspection;
        $tbl_financial = new Tbl_financial;
        $tbl_volunteer_planters = new Tbl_volunteer_planters;

        $data = $tbl_profile->find($request->id)->delete();
        $tbl_seedlings_planted->where('fkey', $request->id)->delete();
        $tbl_area_developed_commodity->where('fkey', $request->id)->delete();
        $tbl_project_validation_year->where('fkey', $request->id)->delete();
        $tbl_project_validation_year_contract->where('fkey', $request->id)->delete();
        $tbl_project_validation_final_inspection->where('fkey', $request->id)->delete();
        $tbl_financial->where('fkey', $request->id)->delete();
        $tbl_volunteer_planters->where('fkey', $request->id)->delete();

        $profileModel = $tbl_profile->find($request->id)->row();
        if($data) {
            $result = [
                'message' => 'success',
            ];
            $this->insert_logs(Auth::user()->name, 'Deleted PO: '.$profileModel->name_of_po.', Year: '.$profileModel->year, 'tbl_profile');
        }else {
            $result = [
                'message' => 'error'
            ];
        }
        
        return json_encode($result);
    }

    public function insert_logs($user, $activity, $table) {
        $logsModel = new Tbl_logs([
            'user' => $user,
            'activity' => $activity,
            'table' => $table,
        ]);
        $logsModel->save();

        return $logsModel;
    }

    public function getDatabaseStatus() {
        $completed_forms_model = new View_database_status();
        $query = $completed_forms_model->get();

        $targets = [
            [
                'id' => 1,
                'name' => 'PENRO Bukidnon',
                'cenro' => [
                    [   'name' => 'PENRO Implemented', 
                        'id' => 222,
                        'data' => [
                            [ 'year' => 2011, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2012, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2013, 'target' => 2, 'accomplishment' => 0 ],
                            [ 'year' => 2014, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2015, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2016, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2017, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2018, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2019, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2020, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2021, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2022, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2023, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2024, 'target' => 1, 'accomplishment' => 0 ],
                        ]
                    ],
                    [   'name' => 'Don Carlos', 
                        'id' => 1,
                        'data' => [
                            [ 'year' => 2011, 'target' => 2, 'accomplishment' => 0 ],
                            [ 'year' => 2012, 'target' => 9, 'accomplishment' => 0 ],
                            [ 'year' => 2013, 'target' => 27, 'accomplishment' => 0 ],
                            [ 'year' => 2014, 'target' => 14, 'accomplishment' => 0 ],
                            [ 'year' => 2015, 'target' => 15, 'accomplishment' => 0 ],
                            [ 'year' => 2016, 'target' => 37, 'accomplishment' => 0 ],
                            [ 'year' => 2017, 'target' => 21, 'accomplishment' => 0 ],
                            [ 'year' => 2018, 'target' => 46, 'accomplishment' => 0 ],
                            [ 'year' => 2019, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2020, 'target' => 37, 'accomplishment' => 0 ],
                            [ 'year' => 2021, 'target' => 45, 'accomplishment' => 0 ],
                            [ 'year' => 2022, 'target' => 32, 'accomplishment' => 0 ],
                            [ 'year' => 2023, 'target' => 4, 'accomplishment' => 0 ],
                            [ 'year' => 2024, 'target' => 5, 'accomplishment' => 0 ],
                        ]
                    ],
                    [   'name' => 'Manolo Fortich', 
                        'id' => 2,
                        'data' => [
                            [ 'year' => 2011, 'target' => 11, 'accomplishment' => 0 ],
                            [ 'year' => 2012, 'target' => 33, 'accomplishment' => 0 ],
                            [ 'year' => 2013, 'target' => 20, 'accomplishment' => 0 ],
                            [ 'year' => 2014, 'target' => 11, 'accomplishment' => 0 ],
                            [ 'year' => 2015, 'target' => 9, 'accomplishment' => 0 ],
                            [ 'year' => 2016, 'target' => 24, 'accomplishment' => 0 ],
                            [ 'year' => 2017, 'target' => 23, 'accomplishment' => 0 ],
                            [ 'year' => 2018, 'target' => 4, 'accomplishment' => 0 ],
                            [ 'year' => 2019, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2020, 'target' => 10, 'accomplishment' => 0 ],
                            [ 'year' => 2021, 'target' => 8, 'accomplishment' => 0 ],
                            [ 'year' => 2022, 'target' => 10, 'accomplishment' => 0 ],
                            [ 'year' => 2023, 'target' => 7, 'accomplishment' => 0 ],
                            [ 'year' => 2024, 'target' => 6, 'accomplishment' => 0 ],
                        ]
                    ],
                    [   'name' => 'Talakag', 
                        'id' => 3,
                        'data' => [
                            [ 'year' => 2011, 'target' => 6, 'accomplishment' => 0 ],
                            [ 'year' => 2012, 'target' => 25, 'accomplishment' => 0 ],
                            [ 'year' => 2013, 'target' => 27, 'accomplishment' => 0 ],
                            [ 'year' => 2014, 'target' => 10, 'accomplishment' => 0 ],
                            [ 'year' => 2015, 'target' => 13 , 'accomplishment' => 0 ],
                            [ 'year' => 2016, 'target' => 34, 'accomplishment' => 0 ],
                            [ 'year' => 2017, 'target' => 22, 'accomplishment' => 0 ],
                            [ 'year' => 2018, 'target' => 11, 'accomplishment' => 0 ],
                            [ 'year' => 2019, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2020, 'target' => 13, 'accomplishment' => 0 ],
                            [ 'year' => 2021, 'target' => 9, 'accomplishment' => 0 ],
                            [ 'year' => 2022, 'target' => 6, 'accomplishment' => 0 ],
                            [ 'year' => 2023, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2024, 'target' => 4, 'accomplishment' => 0 ],
                        ]
                    ],
                    [   'name' => 'Valencia City', 
                        'id' => 4,
                        'data' => [
                            [ 'year' => 2011, 'target' => 29, 'accomplishment' => 0 ],
                            [ 'year' => 2012, 'target' => 92, 'accomplishment' => 0 ],
                            [ 'year' => 2013, 'target' => 45, 'accomplishment' => 0 ],
                            [ 'year' => 2014, 'target' => 23, 'accomplishment' => 0 ],
                            [ 'year' => 2015, 'target' => 28, 'accomplishment' => 0 ],
                            [ 'year' => 2016, 'target' => 45, 'accomplishment' => 0 ],
                            [ 'year' => 2017, 'target' => 34, 'accomplishment' => 0 ],
                            [ 'year' => 2018, 'target' => 14, 'accomplishment' => 0 ],
                            [ 'year' => 2019, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2020, 'target' => 14, 'accomplishment' => 0 ],
                            [ 'year' => 2021, 'target' => 42, 'accomplishment' => 0 ],
                            [ 'year' => 2022, 'target' => 26, 'accomplishment' => 0 ],
                            [ 'year' => 2023, 'target' => 12, 'accomplishment' => 0 ],
                            [ 'year' => 2024, 'target' => 6, 'accomplishment' => 0 ],
                        ]
                    ],
                    [   'name' => 'Mount Kitanglad', 
                        'id' => 15,
                        'data' => [
                            [ 'year' => 2011, 'target' => 24, 'accomplishment' => 0 ],
                            [ 'year' => 2012, 'target' => 13, 'accomplishment' => 0 ],
                            [ 'year' => 2013, 'target' => 18, 'accomplishment' => 0 ],
                            [ 'year' => 2014, 'target' => 6, 'accomplishment' => 0 ],
                            [ 'year' => 2015, 'target' => 6, 'accomplishment' => 0 ],
                            [ 'year' => 2016, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2017, 'target' => 6, 'accomplishment' => 0 ],
                            [ 'year' => 2018, 'target' => 2, 'accomplishment' => 0 ],
                            [ 'year' => 2019, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2020, 'target' => 4, 'accomplishment' => 0 ],
                            [ 'year' => 2021, 'target' => 3, 'accomplishment' => 0 ],
                            [ 'year' => 2022, 'target' => 2, 'accomplishment' => 0 ],
                            [ 'year' => 2023, 'target' => 3, 'accomplishment' => 0 ],
                            [ 'year' => 2024, 'target' => 3, 'accomplishment' => 0 ],
                        ]
                    ],
                    [   'name' => 'Mount Kalatungan', 
                        'id' => 14,
                        'data' => [
                            [ 'year' => 2011, 'target' => 14, 'accomplishment' => 0 ],
                            [ 'year' => 2012, 'target' => 13, 'accomplishment' => 0 ],
                            [ 'year' => 2013, 'target' => 18, 'accomplishment' => 0 ],
                            [ 'year' => 2014, 'target' => 2, 'accomplishment' => 0 ],
                            [ 'year' => 2015, 'target' => 3, 'accomplishment' => 0 ],
                            [ 'year' => 2016, 'target' => 9, 'accomplishment' => 0 ],
                            [ 'year' => 2017, 'target' => 10, 'accomplishment' => 0 ],
                            [ 'year' => 2018, 'target' => 3, 'accomplishment' => 0 ],
                            [ 'year' => 2019, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2020, 'target' => 3, 'accomplishment' => 0 ],
                            [ 'year' => 2021, 'target' => 3, 'accomplishment' => 0 ],
                            [ 'year' => 2022, 'target' => 2, 'accomplishment' => 0 ],
                            [ 'year' => 2023, 'target' => 2, 'accomplishment' => 0 ],
                            [ 'year' => 2024, 'target' => 2, 'accomplishment' => 0 ],
                        ]
                    ],
                    [   'name' => 'Mount Pantaron', 
                        'id' => 13,
                        'data' => [
                            [ 'year' => 2011, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2012, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2013, 'target' => 2, 'accomplishment' => 0 ],
                            [ 'year' => 2014, 'target' => 2, 'accomplishment' => 0 ],
                            [ 'year' => 2015, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2016, 'target' => 5, 'accomplishment' => 0 ],
                            [ 'year' => 2017, 'target' => 3, 'accomplishment' => 0 ],
                            [ 'year' => 2018, 'target' => 4, 'accomplishment' => 0 ],
                            [ 'year' => 2019, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2020, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2021, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2022, 'target' => 3, 'accomplishment' => 0 ],
                            [ 'year' => 2023, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2024, 'target' => 2, 'accomplishment' => 0 ],
                        ]
                    ],
                    [   'name' => 'Mount Tago', 
                        'id' => 12,
                        'data' => [
                            [ 'year' => 2011, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2012, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2013, 'target' => 2, 'accomplishment' => 0 ],
                            [ 'year' => 2014, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2015, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2016, 'target' => 3, 'accomplishment' => 0 ],
                            [ 'year' => 2017, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2018, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2019, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2020, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2021, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2022, 'target' => 2, 'accomplishment' => 0 ],
                            [ 'year' => 2023, 'target' => 3, 'accomplishment' => 0 ],
                            [ 'year' => 2024, 'target' => 1, 'accomplishment' => 0 ],
                        ]
                    ],
                    
                ],
                'data' => [
                    [ 'year' => 2011, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2012, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2013, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2014, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2015, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2016, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2017, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2018, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2019, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2020, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2021, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2022, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2023, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2024, 'total_accomplishment' => 0, 'total_target' => 0 ],
                ]
            ],
            [
                'id' => 2,
                'name' => 'PENRO Camiguin',
                'cenro' => [
                    [   'name' => 'PENRO Camiguin',
                        'id' => 11,
                        'data' => [
                            [ 'year' => 2011, 'target' => 12, 'accomplishment' => 0 ],
                            [ 'year' => 2012, 'target' => 12, 'accomplishment' => 0 ],
                            [ 'year' => 2013, 'target' => 11, 'accomplishment' => 0 ],
                            [ 'year' => 2014, 'target' => 4, 'accomplishment' => 0 ],
                            [ 'year' => 2015, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2016, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2017, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2018, 'target' => 2, 'accomplishment' => 0 ],
                            [ 'year' => 2019, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2020, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2021, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2022, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2023, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2024, 'target' => 0, 'accomplishment' => 0 ],
                        ]
                    ],
                ],
                'data' => [
                    [ 'year' => 2011, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2012, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2013, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2014, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2015, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2016, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2017, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2018, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2019, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2020, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2021, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2022, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2023, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2024, 'total_accomplishment' => 0, 'total_target' => 0 ],
                ]
            ],
            [
                'id' => 3,
                'name' => 'PENRO Lanao del Norte',
                'cenro' => [
                    [   'name' => 'PENRO Implemented', 
                        'id' => 333,
                        'data' => [
                            [ 'year' => 2011, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2012, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2013, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2014, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2015, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2016, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2017, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2018, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2019, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2020, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2021, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2022, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2023, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2024, 'target' => 0, 'accomplishment' => 0 ],
                        ]
                    ],
                    [   'name' => 'CENRO Iligan City', 
                        'id' => 5,
                        'data' => [
                            [ 'year' => 2011, 'target' => 13, 'accomplishment' => 0 ],
                            [ 'year' => 2012, 'target' => 29, 'accomplishment' => 0 ],
                            [ 'year' => 2013, 'target' => 46, 'accomplishment' => 0 ],
                            [ 'year' => 2014, 'target' => 12, 'accomplishment' => 0 ],
                            [ 'year' => 2015, 'target' => 10, 'accomplishment' => 0 ],
                            [ 'year' => 2016, 'target' => 18, 'accomplishment' => 0 ],
                            [ 'year' => 2017, 'target' => 16, 'accomplishment' => 0 ],
                            [ 'year' => 2018, 'target' => 15, 'accomplishment' => 0 ],
                            [ 'year' => 2019, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2020, 'target' => 10, 'accomplishment' => 0 ],
                            [ 'year' => 2021, 'target' => 9, 'accomplishment' => 0 ],
                            [ 'year' => 2022, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2023, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2024, 'target' => 0, 'accomplishment' => 0 ],
                        ]
                    ],
                    [   'name' => 'CENRO Kolambugan', 
                        'id' => 6,
                        'data' => [
                            [ 'year' => 2011, 'target' => 6, 'accomplishment' => 0 ],
                            [ 'year' => 2012, 'target' => 12, 'accomplishment' => 0 ],
                            [ 'year' => 2013, 'target' => 54, 'accomplishment' => 0 ],
                            [ 'year' => 2014, 'target' => 5, 'accomplishment' => 0 ],
                            [ 'year' => 2015, 'target' => 11, 'accomplishment' => 0 ],
                            [ 'year' => 2016, 'target' => 13, 'accomplishment' => 0 ],
                            [ 'year' => 2017, 'target' => 17, 'accomplishment' => 0 ],
                            [ 'year' => 2018, 'target' => 26, 'accomplishment' => 0 ],
                            [ 'year' => 2019, 'target' => 2, 'accomplishment' => 0 ],
                            [ 'year' => 2020, 'target' => 8, 'accomplishment' => 0 ],
                            [ 'year' => 2021, 'target' => 8, 'accomplishment' => 0 ],
                            [ 'year' => 2022, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2023, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2024, 'target' => 0, 'accomplishment' => 0 ],
                        ]
                    ],
                    [   'name' => 'Mount Inayawan RNP', 
                        'id' => 20,
                        'data' => [
                            [ 'year' => 2011, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2012, 'target' => 2, 'accomplishment' => 0 ],
                            [ 'year' => 2013, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2014, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2015, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2016, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2017, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2018, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2019, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2020, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2021, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2022, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2023, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2024, 'target' => 0, 'accomplishment' => 0 ],
                        ]
                    ],
                ],
                'data' => [
                    [ 'year' => 2011, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2012, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2013, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2014, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2015, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2016, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2017, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2018, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2019, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2020, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2021, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2022, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2023, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2024, 'total_accomplishment' => 0, 'total_target' => 0 ],
                ]
            ],
            [
                'id' => 4,
                'name' => 'PENRO Misamis Occidental',
                'cenro' => [
                    [   'name' => 'CENRO Oroquieta City', 
                        'id' => 7,
                        'data' => [
                            [ 'year' => 2011, 'target' => 9, 'accomplishment' => 0 ],
                            [ 'year' => 2012, 'target' => 31, 'accomplishment' => 0 ],
                            [ 'year' => 2013, 'target' => 16, 'accomplishment' => 0 ],
                            [ 'year' => 2014, 'target' => 22, 'accomplishment' => 0 ],
                            [ 'year' => 2015, 'target' => 8, 'accomplishment' => 0 ],
                            [ 'year' => 2016, 'target' => 15, 'accomplishment' => 0 ],
                            [ 'year' => 2017, 'target' => 5, 'accomplishment' => 0 ],
                            [ 'year' => 2018, 'target' => 12, 'accomplishment' => 0 ],
                            [ 'year' => 2019, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2020, 'target' => 2, 'accomplishment' => 0 ],
                            [ 'year' => 2021, 'target' => 4, 'accomplishment' => 0 ],
                            [ 'year' => 2022, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2023, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2024, 'target' => 0, 'accomplishment' => 0 ],
                        ]
                    ],
                    [   'name' => 'CENRO Ozamiz City', 
                        'id' => 8,
                        'data' => [
                            [ 'year' => 2011, 'target' => 11, 'accomplishment' => 0 ],
                            [ 'year' => 2012, 'target' => 17, 'accomplishment' => 0 ],
                            [ 'year' => 2013, 'target' => 29, 'accomplishment' => 0 ],
                            [ 'year' => 2014, 'target' => 7, 'accomplishment' => 0 ],
                            [ 'year' => 2015, 'target' => 12, 'accomplishment' => 0 ],
                            [ 'year' => 2016, 'target' => 12, 'accomplishment' => 0 ],
                            [ 'year' => 2017, 'target' => 8, 'accomplishment' => 0 ],
                            [ 'year' => 2018, 'target' => 5, 'accomplishment' => 0 ],
                            [ 'year' => 2019, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2020, 'target' => 3, 'accomplishment' => 0 ],
                            [ 'year' => 2021, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2022, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2023, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2024, 'target' => 0, 'accomplishment' => 0 ],
                        ]
                    ],
                    [   'name' => 'Mount Malindang', 
                        'id' => 17,
                        'data' => [
                            [ 'year' => 2011, 'target' => 14, 'accomplishment' => 0 ],
                            [ 'year' => 2012, 'target' => 11, 'accomplishment' => 0 ],
                            [ 'year' => 2013, 'target' => 15, 'accomplishment' => 0 ],
                            [ 'year' => 2014, 'target' => 9, 'accomplishment' => 0 ],
                            [ 'year' => 2015, 'target' => 7, 'accomplishment' => 0 ],
                            [ 'year' => 2016, 'target' => 9, 'accomplishment' => 0 ],
                            [ 'year' => 2017, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2018, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2019, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2020, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2021, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2022, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2023, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2024, 'target' => 0, 'accomplishment' => 0 ],
                        ]
                    ],
                    [   'name' => 'Baliangao Protected Landscape and Seascape', 
                        'id' => 19,
                        'data' => [
                            [ 'year' => 2011, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2012, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2013, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2014, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2015, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2016, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2017, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2018, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2019, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2020, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2021, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2022, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2023, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2024, 'target' => 0, 'accomplishment' => 0 ],
                        ]
                    ],
                ],
                'data' => [
                    [ 'year' => 2011, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2012, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2013, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2014, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2015, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2016, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2017, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2018, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2019, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2020, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2021, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2022, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2023, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2024, 'total_accomplishment' => 0, 'total_target' => 0 ],
                ]
            ],
            [
                'id' => 5,
                'name' => 'PENRO Misamis Oriental',
                'cenro' => [
                    [   'name' => 'CENRO Gingoog City', 
                        'id' => 9,
                        'data' => [
                            [ 'year' => 2011, 'target' => 30, 'accomplishment' => 0 ],
                            [ 'year' => 2012, 'target' => 33, 'accomplishment' => 0 ],
                            [ 'year' => 2013, 'target' => 25, 'accomplishment' => 0 ],
                            [ 'year' => 2014, 'target' => 10, 'accomplishment' => 0 ],
                            [ 'year' => 2015, 'target' => 33, 'accomplishment' => 0 ],
                            [ 'year' => 2016, 'target' => 11, 'accomplishment' => 0 ],
                            [ 'year' => 2017, 'target' => 16, 'accomplishment' => 0 ],
                            [ 'year' => 2018, 'target' => 14, 'accomplishment' => 0 ],
                            [ 'year' => 2019, 'target' => 2, 'accomplishment' => 0 ],
                            [ 'year' => 2020, 'target' => 26, 'accomplishment' => 0 ],
                            [ 'year' => 2021, 'target' => 17, 'accomplishment' => 0 ],
                            [ 'year' => 2022, 'target' => 2, 'accomplishment' => 0 ],
                            [ 'year' => 2023, 'target' => 10, 'accomplishment' => 0 ],
                            [ 'year' => 2024, 'target' => 2, 'accomplishment' => 0 ],
                        ]
                    ],
                    [   'name' => 'CENRO Initao', 
                        'id' => 10,
                        'data' => [
                            [ 'year' => 2011, 'target' => 27, 'accomplishment' => 0 ],
                            [ 'year' => 2012, 'target' => 37, 'accomplishment' => 0 ],
                            [ 'year' => 2013, 'target' => 26, 'accomplishment' => 0 ],
                            [ 'year' => 2014, 'target' => 15, 'accomplishment' => 0 ],
                            [ 'year' => 2015, 'target' => 13, 'accomplishment' => 0 ],
                            [ 'year' => 2016, 'target' => 9, 'accomplishment' => 0 ],
                            [ 'year' => 2017, 'target' => 13, 'accomplishment' => 0 ],
                            [ 'year' => 2018, 'target' => 9, 'accomplishment' => 0 ],
                            [ 'year' => 2019, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2020, 'target' => 6, 'accomplishment' => 0 ],
                            [ 'year' => 2021, 'target' => 9, 'accomplishment' => 0 ],
                            [ 'year' => 2022, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2023, 'target' => 5, 'accomplishment' => 0 ],
                            [ 'year' => 2024, 'target' => 2, 'accomplishment' => 0 ],
                        ]
                    ],
                    [   'name' => 'Initao-Libertad PLS', 
                        'id' => 22,
                        'data' => [
                            [ 'year' => 2011, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2012, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2013, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2014, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2015, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2016, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2017, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2018, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2019, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2020, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2021, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2022, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2023, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2024, 'target' => 0, 'accomplishment' => 0 ],
                        ]
                    ],
                    [   'name' => 'Mount Balatukan', 
                        'id' => 18,
                        'data' => [
                            [ 'year' => 2011, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2012, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2013, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2014, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2015, 'target' => 3, 'accomplishment' => 0 ],
                            [ 'year' => 2016, 'target' => 6, 'accomplishment' => 0 ],
                            [ 'year' => 2017, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2018, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2019, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2020, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2021, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2022, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2023, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2024, 'target' => 1, 'accomplishment' => 0 ],
                        ]
                    ],
                    [   'name' => 'Mimbilisan Protected Landscape', 
                        'id' => 23,
                        'data' => [
                            [ 'year' => 2011, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2012, 'target' => 1, 'accomplishment' => 0 ],
                            [ 'year' => 2013, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2014, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2015, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2016, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2017, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2018, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2019, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2020, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2021, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2022, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2023, 'target' => 0, 'accomplishment' => 0 ],
                            [ 'year' => 2024, 'target' => 0, 'accomplishment' => 0 ],
                        ]
                    ],
                ],
                'data' => [
                    [ 'year' => 2011, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2012, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2013, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2014, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2015, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2016, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2017, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2018, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2019, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2020, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2021, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2022, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2023, 'total_accomplishment' => 0, 'total_target' => 0 ],
                    [ 'year' => 2024, 'total_accomplishment' => 0, 'total_target' => 0 ],
                ]
            ]
        ];

        $regional_total = [
            'data' => [ [ 'year' => 2011, 'total_accomplishment' => 0, 'total_target' => 0 ],
                [ 'year' => 2012, 'total_accomplishment' => 0, 'total_target' => 0 ],
                [ 'year' => 2013, 'total_accomplishment' => 0, 'total_target' => 0 ],
                [ 'year' => 2014, 'total_accomplishment' => 0, 'total_target' => 0 ],
                [ 'year' => 2015, 'total_accomplishment' => 0, 'total_target' => 0 ],
                [ 'year' => 2016, 'total_accomplishment' => 0, 'total_target' => 0 ],
                [ 'year' => 2017, 'total_accomplishment' => 0, 'total_target' => 0 ],
                [ 'year' => 2018, 'total_accomplishment' => 0, 'total_target' => 0 ],
                [ 'year' => 2019, 'total_accomplishment' => 0, 'total_target' => 0 ],
                [ 'year' => 2020, 'total_accomplishment' => 0, 'total_target' => 0 ],
                [ 'year' => 2021, 'total_accomplishment' => 0, 'total_target' => 0 ],
                [ 'year' => 2022, 'total_accomplishment' => 0, 'total_target' => 0 ],
                [ 'year' => 2023, 'total_accomplishment' => 0, 'total_target' => 0 ],
                [ 'year' => 2024, 'total_accomplishment' => 0, 'total_target' => 0 ]
            ]
        ];
        
        foreach($query as $row) {
            foreach($targets as $penro_index => $penro) {
                foreach($penro['cenro'] as $cenro_index => $cenro) {
                    if($row->penro_implemented == $cenro['id'] || $row->cenro_id == $cenro['id']) {
                        foreach($cenro['data'] as $data_index => $data) {
                            if($row->year == $data['year']) {
                                $targets[$penro_index]['cenro'][$cenro_index]['data'][$data_index]['accomplishment'] += (int)$row->accomplishment;
                                
                                foreach($penro['data'] as $penro_data_index => $penro_data) {
                                    if($penro_data['year'] == $data['year']) {
                                        $targets[$penro_index]['data'][$penro_data_index]['total_accomplishment'] += (int)$row->accomplishment;
                                    }
                                }
                            }
                        }
                    }
                    


                }
               
                
            }
        }

        foreach($targets as $penro_index => $penro) {
            foreach($penro['cenro'] as $cenro_index => $cenro) {
                foreach($cenro['data'] as $data_index => $data) {
                    foreach($penro['data'] as $penro_data_index => $penro_data) {
                        if($penro_data['year'] == $data['year']) {
                            $targets[$penro_index]['data'][$penro_data_index]['total_target'] += (int)$data['target'];
                        }
                    }
                }
            }
            foreach($regional_total['data'] as $regional_total_index => $regional_total_row) {
                foreach($penro['data'] as $penro_data_index => $penro_data) {
                    if($regional_total_row['year'] == $penro_data['year']) {
                        $regional_total['data'][$regional_total_index]['total_accomplishment'] += (int)$targets[$penro_index]['data'][$penro_data_index]['total_accomplishment'];
                        $regional_total['data'][$regional_total_index]['total_target'] += (int)$targets[$penro_index]['data'][$penro_data_index]['total_target'];
                        
                    }
                }
            }
        }


        $result = [
            'targets_and_accomplishment' => $targets,
            'regional_total' => $regional_total['data']
        ];
        
        return json_encode($result);

    }   

    public function getYears() {
        $model = new Tbl_profile;
        
        $result = $model->selectRaw('year')
                        ->groupBy('year')
                        ->orderBy('year', 'DESC')
                        ->get();
        
        return json_encode($result);
    }

    public function getQuickData($year, $fund) {
        $model = new View_quick_data_summary;
        $survival_rate_model = new View_quick_data_survival_rate;
        $profile_model = new View_quick_data_number_of_po;


        $officeSeedlingsAndPOQuery = $model->selectRaw('cenro, year, SUM(seedlings_produced::float) as seedlings_produced,
                                SUM(seedlings_planted::float) as seedlings_planted, SUM(area_planted::float) as area_planted,
                                SUM(no_of_po_members_female::float) + SUM(no_of_po_members_male::float) as total_no_po')
                                ->where('fund_source', $fund)
                                ->where('year', $year)
                                ->groupBy('year')
                                ->groupBy('cenro')
                                ->get();

        $surivalRateQuery = $survival_rate_model->selectRaw('cenro, year, AVG(survival_rate::float) as survival_rate ')
                            ->where('fund_source', $fund)
                            ->where('year', $year)
                            ->groupBy('year')
                            ->groupBy('cenro')
                            ->get();
        
        $commodityQuery = $model->selectRaw('year, commodity, SUM(seedlings_produced::float) as seedlings_produced,
                                SUM(seedlings_planted::float) as seedlings_planted, SUM(area_planted::float) as area_planted')
                                ->where('fund_source', $fund)
                                ->where('year', $year)
                                ->groupBy('year')
                                ->groupBy('commodity')
                                ->get();

        $numberOfPOQuery = $profile_model->selectRaw('SUM(number_of_po) as number_of_po, cenro, year')
                                ->where('fund_source', $fund)
                                ->where('year', $year)
                                ->groupBy('cenro')
                                ->groupBy('year')
                                ->get();

        if($year == '2011-to-Date') {
            $officeSeedlingsAndPOQuery = $model->selectRaw('cenro, SUM(seedlings_produced::float) as seedlings_produced,
                                SUM(seedlings_planted::float) as seedlings_planted, SUM(area_planted::float) as area_planted,
                                SUM(no_of_po_members_female::float) + SUM(no_of_po_members_male::float) as total_no_po')
                                ->where('fund_source', $fund)
                                ->groupBy('cenro')
                                ->get();

            $surivalRateQuery = $survival_rate_model->selectRaw('cenro, AVG(survival_rate::float) as survival_rate ')
                            ->where('fund_source', $fund)
                            ->groupBy('cenro')
                            ->get();

            $commodityQuery = $model->selectRaw('commodity, SUM(seedlings_produced::float) as seedlings_produced,
                                SUM(seedlings_planted::float) as seedlings_planted, SUM(area_planted::float) as area_planted')
                                ->where('fund_source', $fund)
                                ->groupBy('commodity')
                                ->get();

            $numberOfPOQuery = $profile_model->selectRaw('COUNT(UPPER(name_of_po)) as number_of_po, cenro')
                                ->where('fund_source', $fund)
                                ->groupBy('cenro')
                                ->get();
        }

        $offices = [
            [
                'name' => 'PENRO Bukidnon',
                'cenro' => [
                    [   'name' => 'Don Carlos', 
                        'data' => [
                            [ 'year' => $year, 'area_planted' => 0, 'seedlings_planted' => 0, 'seedlings_produced' => 0, 'total_no_po' => 0, 'survival_rate' => 0, 'number_of_po' => 0 ],
                        ]
                    ],
                    [   'name' => 'Manolo Fortich', 
                        'data' => [
                            [ 'year' => $year, 'area_planted' => 0, 'seedlings_planted' => 0, 'seedlings_produced' => 0, 'total_no_po' => 0, 'survival_rate' => 0, 'number_of_po' => 0 ],
                        ]
                    ],
                    [   'name' => 'Talakag', 
                        'data' => [
                            [ 'year' => $year, 'area_planted' => 0, 'seedlings_planted' => 0, 'seedlings_produced' => 0, 'total_no_po' => 0, 'survival_rate' => 0, 'number_of_po' => 0 ],
                        ]
                    ],
                    [   'name' => 'Valencia City', 
                        'data' => [
                            [ 'year' => $year, 'area_planted' => 0, 'seedlings_planted' => 0, 'seedlings_produced' => 0, 'total_no_po' => 0, 'survival_rate' => 0, 'number_of_po' => 0 ],
                        ]
                    ],
                    [   'name' => 'Mount Kitanglad', 
                        'data' => [
                            [ 'year' => $year, 'area_planted' => 0, 'seedlings_planted' => 0, 'seedlings_produced' => 0, 'total_no_po' => 0, 'survival_rate' => 0, 'number_of_po' => 0 ],
                        ]
                    ],
                    [   'name' => 'Mount Kalatungan', 
                        'data' => [
                            [ 'year' => $year, 'area_planted' => 0, 'seedlings_planted' => 0, 'seedlings_produced' => 0, 'total_no_po' => 0, 'survival_rate' => 0, 'number_of_po' => 0 ],
                        ]
                    ],
                    [   'name' => 'Mount Pantaron', 
                        'data' => [
                            [ 'year' => $year, 'area_planted' => 0, 'seedlings_planted' => 0, 'seedlings_produced' => 0, 'total_no_po' => 0, 'survival_rate' => 0, 'number_of_po' => 0 ],
                        ]
                    ],
                    [   'name' => 'Mount Tago', 
                        'data' => [
                            [ 'year' => $year, 'area_planted' => 0, 'seedlings_planted' => 0, 'seedlings_produced' => 0, 'total_no_po' => 0, 'survival_rate' => 0, 'number_of_po' => 0 ],
                        ]
                    ],
                    
                ],
                'data' => [
                    [ 'year' => $year, 'area_planted' => 0, 'seedlings_planted' => 0, 'seedlings_produced' => 0, 'total_no_po' => 0, 'survival_rate' => 0, 'number_of_po' => 0 ],
                ]
            ],
            [
                'name' => 'PENRO Camiguin',
                'cenro' => [
                    [   'name' => 'Camiguin',
                        'data' => [
                            [ 'year' => $year, 'area_planted' => 0, 'seedlings_planted' => 0, 'seedlings_produced' => 0, 'total_no_po' => 0, 'survival_rate' => 0, 'number_of_po' => 0 ],
                        ]
                    ],
                ],
                'data' => [
                    [ 'year' => $year, 'area_planted' => 0, 'seedlings_planted' => 0, 'seedlings_produced' => 0, 'total_no_po' => 0, 'survival_rate' => 0, 'number_of_po' => 0 ],
                ]
            ],
            [
                'name' => 'PENRO Lanao del Norte',
                'cenro' => [
                    [   'name' => 'Iligan City', 
                        'data' => [
                            [ 'year' => $year, 'area_planted' => 0, 'seedlings_planted' => 0, 'seedlings_produced' => 0, 'total_no_po' => 0, 'survival_rate' => 0, 'number_of_po' => 0 ],
                        ]
                    ],
                    [   'name' => 'Kolambugan', 
                        'data' => [
                            [ 'year' => $year, 'area_planted' => 0, 'seedlings_planted' => 0, 'seedlings_produced' => 0, 'total_no_po' => 0, 'survival_rate' => 0, 'number_of_po' => 0 ],
                        ]
                    ],
                    [   'name' => 'Mount Inayawan RNP', 
                        'data' => [
                            [ 'year' => $year, 'area_planted' => 0, 'seedlings_planted' => 0, 'seedlings_produced' => 0, 'total_no_po' => 0, 'survival_rate' => 0, 'number_of_po' => 0 ],
                        ]
                    ],
                ],
                'data' => [
                    [ 'year' => $year, 'area_planted' => 0, 'seedlings_planted' => 0, 'seedlings_produced' => 0, 'total_no_po' => 0, 'survival_rate' => 0, 'number_of_po' => 0 ],
                ]
            ],
            [
                'name' => 'PENRO Misamis Occidental',
                'cenro' => [
                    [   'name' => 'Oroquieta City', 
                        'data' => [
                            [ 'year' => $year, 'area_planted' => 0, 'seedlings_planted' => 0, 'seedlings_produced' => 0, 'total_no_po' => 0, 'survival_rate' => 0, 'number_of_po' => 0 ],
                        ]
                    ],
                    [   'name' => 'Ozamiz City', 
                        'data' => [
                            [ 'year' => $year, 'area_planted' => 0, 'seedlings_planted' => 0, 'seedlings_produced' => 0, 'total_no_po' => 0, 'survival_rate' => 0, 'number_of_po' => 0 ],
                        ]
                    ],
                    [   'name' => 'Mount Malindang', 
                        'data' => [
                            [ 'year' => $year, 'area_planted' => 0, 'seedlings_planted' => 0, 'seedlings_produced' => 0, 'total_no_po' => 0, 'survival_rate' => 0, 'number_of_po' => 0 ],
                        ]
                    ],
                    [   'name' => 'Baliangao Protected Landscape and Seascape', 
                        'data' => [
                            [ 'year' => $year, 'area_planted' => 0, 'seedlings_planted' => 0, 'seedlings_produced' => 0, 'total_no_po' => 0, 'survival_rate' => 0, 'number_of_po' => 0 ],
                        ]
                    ],
                ],
                'data' => [
                    [ 'year' => $year, 'area_planted' => 0, 'seedlings_planted' => 0, 'seedlings_produced' => 0, 'total_no_po' => 0, 'survival_rate' => 0, 'number_of_po' => 0 ],
                ]
            ],
            [
                'name' => 'PENRO Misamis Oriental',
                'cenro' => [
                    [   'name' => 'Gingoog City', 
                        'data' => [
                            [ 'year' => $year, 'area_planted' => 0, 'seedlings_planted' => 0, 'seedlings_produced' => 0, 'total_no_po' => 0, 'survival_rate' => 0, 'number_of_po' => 0 ],
                        ]
                    ],
                    [   'name' => 'Initao', 
                        'data' => [
                            [ 'year' => $year, 'area_planted' => 0, 'seedlings_planted' => 0, 'seedlings_produced' => 0, 'total_no_po' => 0, 'survival_rate' => 0, 'number_of_po' => 0 ],
                        ]
                    ],
                    [   'name' => 'Initao-Libertad PLS', 
                        'data' => [
                            [ 'year' => $year, 'area_planted' => 0, 'seedlings_planted' => 0, 'seedlings_produced' => 0, 'total_no_po' => 0, 'survival_rate' => 0, 'number_of_po' => 0 ],
                        ]
                    ],
                    [   'name' => 'Mount Balatukan', 
                        'data' => [
                            [ 'year' => $year, 'area_planted' => 0, 'seedlings_planted' => 0, 'seedlings_produced' => 0, 'total_no_po' => 0, 'survival_rate' => 0, 'number_of_po' => 0 ],
                        ]
                    ],
                    [   'name' => 'Mimbilisan Protected Landscape', 
                        'data' => [
                            [ 'year' => $year, 'area_planted' => 0, 'seedlings_planted' => 0, 'seedlings_produced' => 0, 'total_no_po' => 0, 'survival_rate' => 0, 'number_of_po' => 0 ],
                        ]
                    ],
                ],
                'data' => [
                    [ 'year' => $year, 'area_planted' => 0, 'seedlings_planted' => 0, 'seedlings_produced' => 0, 'total_no_po' => 0, 'survival_rate' => 0, 'number_of_po' => 0 ],
                ]
            ]
        ];

        $commodities = [
            [
                'name' => 'Agroforestry',
                'data' => [
                    'seedlings_produced' => 0, 'seedlings_planted' => 0, 'area_planted' => 0
                ]
            ],
            [
                'name' => 'Bamboo',
                'data' => [
                    'seedlings_produced' => 0, 'seedlings_planted' => 0, 'area_planted' => 0
                ]
            ],
            [
                'name' => 'Beach Forest',
                'data' => [
                    'seedlings_produced' => 0, 'seedlings_planted' => 0, 'area_planted' => 0
                ]
            ],
            [
                'name' => 'Cacao',
                'data' => [
                    'seedlings_produced' => 0, 'seedlings_planted' => 0, 'area_planted' => 0
                ]
            ],
            [
                'name' => 'Cacao',
                'data' => [
                    'seedlings_produced' => 0, 'seedlings_planted' => 0, 'area_planted' => 0
                ]
            ],
            [
                'name' => 'Coffee',
                'data' => [
                    'seedlings_produced' => 0, 'seedlings_planted' => 0, 'area_planted' => 0
                ]
            ],
            [
                'name' => 'Fruit Trees',
                'data' => [
                    'seedlings_produced' => 0, 'seedlings_planted' => 0, 'area_planted' => 0
                ]
            ],
            [
                'name' => 'Fuelwood',
                'data' => [
                    'seedlings_produced' => 0, 'seedlings_planted' => 0, 'area_planted' => 0
                ]
            ],
            [
                'name' => 'Hedgerows',
                'data' => [
                    'seedlings_produced' => 0, 'seedlings_planted' => 0, 'area_planted' => 0
                ]
            ],
            [
                'name' => 'High Value Crop',
                'data' => [
                    'seedlings_produced' => 0, 'seedlings_planted' => 0, 'area_planted' => 0
                ]
            ],
            [
                'name' => 'Indigenous',
                'data' => [
                    'seedlings_produced' => 0, 'seedlings_planted' => 0, 'area_planted' => 0
                ]
            ],
            [
                'name' => 'Mangrove',
                'data' => [
                    'seedlings_produced' => 0, 'seedlings_planted' => 0, 'area_planted' => 0
                ]
            ],
            [
                'name' => 'Nipa',
                'data' => [
                    'seedlings_produced' => 0, 'seedlings_planted' => 0, 'area_planted' => 0
                ]
            ],
            [
                'name' => 'Rattan',
                'data' => [
                    'seedlings_produced' => 0, 'seedlings_planted' => 0, 'area_planted' => 0
                ]
            ],
            [
                'name' => 'Rubber',
                'data' => [
                    'seedlings_produced' => 0, 'seedlings_planted' => 0, 'area_planted' => 0
                ]
            ],
            [
                'name' => 'Timber',
                'data' => [
                    'seedlings_produced' => 0, 'seedlings_planted' => 0, 'area_planted' => 0
                ]
            ],
            [
                'name' => 'Urban Greening',
                'data' => [
                    'seedlings_produced' => 0, 'seedlings_planted' => 0, 'area_planted' => 0
                ]
            ],
            [
                'name' => 'TOTAL',
                'data' => [
                    'seedlings_produced' => 0, 'seedlings_planted' => 0, 'area_planted' => 0
                ]
            ],

        ];

        $regional_total = [
            'data' => [ 
                [ 'year' => $year, 'area_planted' => 0, 'seedlings_planted' => 0, 'seedlings_produced' => 0, 'total_no_po' => 0, 'survival_rate' => 0, 'number_of_po' => 0 ]
            ]
        ];

        foreach($officeSeedlingsAndPOQuery as $row) {
            foreach($offices as $penro_index => $penro) {
                foreach($penro['cenro'] as $cenro_index => $cenro) {
                    if($row->cenro == $cenro['name'] ) {
                        foreach($cenro['data'] as $data_index => $data) {
                            if($row->year == $data['year'] || $year == '2011-to-Date') {
                                $offices[$penro_index]['cenro'][$cenro_index]['data'][$data_index]['area_planted'] = (int)$row->area_planted;
                                $offices[$penro_index]['cenro'][$cenro_index]['data'][$data_index]['seedlings_produced'] = (int)$row->seedlings_produced;
                                $offices[$penro_index]['cenro'][$cenro_index]['data'][$data_index]['seedlings_planted'] = (int)$row->seedlings_planted;
                                $offices[$penro_index]['cenro'][$cenro_index]['data'][$data_index]['total_no_po'] = (int)$row->total_no_po;
                                
                                foreach($penro['data'] as $penro_data_index => $penro_data) {
                                    if($penro_data['year'] == $data['year']) {
                                        $offices[$penro_index]['data'][$penro_data_index]['area_planted'] += (int)$row->area_planted;
                                        $offices[$penro_index]['data'][$penro_data_index]['seedlings_produced'] += (int)$row->seedlings_produced;
                                        $offices[$penro_index]['data'][$penro_data_index]['seedlings_planted'] += (int)$row->seedlings_planted;
                                        $offices[$penro_index]['data'][$penro_data_index]['total_no_po'] += (int)$row->total_no_po;
                                    }
                                }
                            }
                            
                        }
                    }
                }
            }
        }

        foreach($numberOfPOQuery as $row) {
            foreach($offices as $penro_index => $penro) {
                foreach($penro['cenro'] as $cenro_index => $cenro) {
                    if($row->cenro == $cenro['name'] ) {
                        foreach($cenro['data'] as $data_index => $data) {
                            if($row->year == $data['year'] || $year == '2011-to-Date') {
                                $offices[$penro_index]['cenro'][$cenro_index]['data'][$data_index]['number_of_po'] = (int)$row->number_of_po;
                                
                                foreach($penro['data'] as $penro_data_index => $penro_data) {
                                    if($penro_data['year'] == $data['year']) {
                                        $offices[$penro_index]['data'][$penro_data_index]['number_of_po'] += (int)$row->number_of_po;
                                    }
                                }
                            }
                            
                        }
                    }
                }
            }
        }

        foreach($surivalRateQuery as $row) {
            foreach($offices as $penro_index => $penro) {
                foreach($penro['cenro'] as $cenro_index => $cenro) {
                    if($row->cenro == $cenro['name'] ) {
                        foreach($cenro['data'] as $data_index => $data) {
                            if($row->year == $data['year'] || $year == '2011-to-Date') {
                                $offices[$penro_index]['cenro'][$cenro_index]['data'][$data_index]['survival_rate'] = (int)$row->survival_rate;
                                
                                foreach($penro['data'] as $penro_data_index => $penro_data) {
                                    if($penro_data['year'] == $data['year']) {
                                        $offices[$penro_index]['data'][$penro_data_index]['survival_rate'] += (int)$row->survival_rate;
                                    }
                                }
                            }
                            
                        }
                    }
                }
            }
        }

        foreach($offices as $penro_index => $penro) {
            foreach($regional_total['data'] as $regional_total_index => $regional_total_row) {
                foreach($penro['data'] as $penro_data_index => $penro_data) {
                    if($regional_total_row['year'] == $penro_data['year'] || $year == '2011-to-Date') {
                        $regional_total['data'][$regional_total_index]['seedlings_produced'] += (int)$offices[$penro_index]['data'][$penro_data_index]['seedlings_produced'];
                        $regional_total['data'][$regional_total_index]['seedlings_planted'] += (int)$offices[$penro_index]['data'][$penro_data_index]['seedlings_planted'];
                        $regional_total['data'][$regional_total_index]['area_planted'] += (int)$offices[$penro_index]['data'][$penro_data_index]['area_planted'];
                        $regional_total['data'][$regional_total_index]['total_no_po'] += (int)$offices[$penro_index]['data'][$penro_data_index]['total_no_po'];
                        $regional_total['data'][$regional_total_index]['survival_rate'] += (int)$offices[$penro_index]['data'][$penro_data_index]['survival_rate'];
                        $regional_total['data'][$regional_total_index]['number_of_po'] += (int)$offices[$penro_index]['data'][$penro_data_index]['number_of_po'];

                    }
                    
                }
            }
        }

        foreach($commodityQuery as $row) {
            foreach($commodities as $commodity_index => $commodity) {
                foreach($commodity['data'] as  $data) {
                    if($row->commodity == $commodity['name']) {
                        $commodities[$commodity_index]['data']['area_planted'] = (int)$row->area_planted;
                        $commodities[$commodity_index]['data']['seedlings_produced'] = (int)$row->seedlings_produced;
                        $commodities[$commodity_index]['data']['seedlings_planted'] = (int)$row->seedlings_planted;

                    }else if($commodity['name'] == 'TOTAL') {
                        $commodities[$commodity_index]['data']['area_planted'] += (int)$row->area_planted;
                        $commodities[$commodity_index]['data']['seedlings_produced'] += (int)$row->seedlings_produced;
                        $commodities[$commodity_index]['data']['seedlings_planted'] += (int)$row->seedlings_planted;
                    }
                    
                }
            }
        }


        
        $result = [
            'seedlings_and_po_query' => $offices,
            'seedlings_and_po_regional_total' => $regional_total['data'],
            'commodities_data' => $commodities
        ];
        
        return json_encode($result);
    }

    public function getSystemUpdatesFromGitlab() {
        $cURLConnection = curl_init('https://gitlab.com/api/v4/projects/51193791/repository/commits?per_page=500');
        curl_setopt($cURLConnection, CURLOPT_HTTPHEADER, array(

            'PRIVATE-TOKEN: gq4-JcU2h5As3e2aDx9a',
        ));
        curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, 1);
        
        $gitlabData = curl_exec($cURLConnection);
        $data = json_decode($gitlabData, true);


        curl_close($cURLConnection);

        // return substr($gitlabData, 0, -1);

        return json_encode($data);
    }

    public function getSystemLogs() {
        $model = new Tbl_logs;

        $result = $model->orderBy('created_at', 'DESC')->get();
        return json_encode($result);
        
    }

    public function changePassword(Request $request) {
        $model = new Users;

        $query = $model->find($request->id)->update([
            'password' => password_hash($request->password, PASSWORD_BCRYPT, array("cost" => 10))
        ]);

        if($query) {
            $message = 'success';
        }else {
            $message = 'fail';
        }

        return json_encode($message);
    }

    public function getNameOfPOs() {
        $model = new Tbl_profile();

        $query = $model->selectRaw('name_of_po')->take(30)->orderBy('name_of_po', 'ASC')->get();

        return json_encode($query);
    }

    public function getNameOfPOsLoad($number) {
        $model = new Tbl_profile();

        $query = $model->selectRaw('name_of_po')->skip($number)->take(30)->groupBy('name_of_po')->orderBy('name_of_po', 'ASC')->get();

        return json_encode($query);
    }
}
