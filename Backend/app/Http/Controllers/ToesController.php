<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use Illuminate\Support\Facades\DB;
use App\TOES\Tbl_accounts;
use App\TOES\Tbl_logs;
use App\TOES\Tbl_todetails;
use App\TOES\Tbl_tostatus;
use App\TOES\Tbl_empdetails;
use App\TOES\Tbl_division;
use App\TOES\Tbl_approveto;
use App\TOES\Tbl_office;
use App\TOES\Tbl_section;
use App\TOES\Tbl_track;
use App\TOES\View_office;
use App\TOES\View_approveto;
use App\TOES\View_empdetails_with_to;
use App\TOES\View_empdetails_with_office;
use Illuminate\Support\Facades\Auth;
use Storage;
class ToesController extends Controller
{
    //

    public function login(Request $request) {
        $model = new Tbl_accounts;
        $data = $model->where('username', $request->username)->first();
        if($data != null) {
            if(Auth::guard('second_db')->attempt(['username' => $request->username, 'password' => $request->password], true)) {
                $session_var = [
                    'id' => $data->id,
                    'username' => $data->username,
                    'acct_type' => $data->acct_type,
                    'group' => $data->group,
                    'poc' => $data->poc,
                    'offc' => $data->offc,
                    'role' => $data->role,
                ];
                // $model->find($data->id)->update(['status' => 'in']);
                $request->session()->put($session_var);
                // $this->insert_logs($data->name, 'Logged In', 'N/A');

                $message = 'success';
                $this->insert_logs(Auth::guard('second_db')->user()->username, 'Logged In', 'N/A', 'N/A', 'N/A');
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
        $this->insert_logs(Auth::guard('second_db')->user()->username, 'Logged Out', 'N/A', 'N/A', 'N/A');

        // if($logs) {
            Auth::guard('second_db')->logout();
            Session::flush();

        // }

        return json_encode('success');
    }

    public function insert_logs($user, $activity, $depart, $arrival, $destination) {
        $logsModel = new Tbl_logs([
            'emp_id' => $user,
            'log_status' => $activity,
            'depart' => $depart,
            'arrival' => $arrival,
            'destination' => $destination,
        ]);
        $logsModel->save();

        return $logsModel;
    }

    public function getPendingTravel($emp_id) {
        $model = new Tbl_todetails;
        $approveToModel = new View_approveto;
        $officeModel = new View_office;
        $empDetailsModel = new Tbl_empdetails;
        $accountsModel = new Tbl_accounts;

        $pending = $model->where('emp_id', $emp_id)->where('to_status', '!=', 'APPROVED')->orderBy('depart', 'DESC')->get();
        $approved = $approveToModel->where('emp_id', $emp_id)->where('to_status', '=', 'APPROVED')->orderBy('depart', 'DESC')->get();
        $userDetails = json_decode($this->getUserDetails(Auth::guard('second_db')->user()->username));
        $officeDetails = $officeModel->where('sec_id', $userDetails->sec_id)->first();
/**0*/  $sectionIndorserDetails = $empDetailsModel->where('emp_id', @$officeDetails->sec_emp_id)->first();
/**1*/  $divisionIndorserDetails = $empDetailsModel->where('emp_id', @$officeDetails->div_emp_id)->first();
/**2*/  $officialsIndorserDetails = $empDetailsModel->where('emp_id', @$officeDetails->emp_id)->first();
/**4*/  $deputyCenroIndorserDetails = $empDetailsModel->where('emp_id', @$officeDetails->deputy_cenro)->first();

        $sectionIndorserName = @$sectionIndorserDetails->f_name.' '.@$sectionIndorserDetails->m_name.' '.@$sectionIndorserDetails->l_name;
        $divisionIndorserName = @$divisionIndorserDetails->f_name.' '.@$divisionIndorserDetails->m_name.' '.@$divisionIndorserDetails->l_name;
        $officialsIndorserName = @$officialsIndorserDetails->f_name.' '.@$officialsIndorserDetails->m_name.' '.@$officialsIndorserDetails->l_name;

        $deputyCenroIndorserName = null;
        if($deputyCenroIndorserDetails != null) {
            $deputyCenroIndorserName = $deputyCenroIndorserDetails->f_name.' '.$deputyCenroIndorserDetails->m_name.' '.$deputyCenroIndorserDetails->l_name;

        }

        $oicQuery = $accountsModel->where('username', @$sectionIndorserDetails->emp_id)->first();

        if($oicQuery != null) {
            $oicIndorserDetails = $empDetailsModel->where('emp_id', $oicQuery->username)->first();
            $oicIndorserName = $oicIndorserDetails->f_name.' '.$oicIndorserDetails->m_name.' '.$oicIndorserDetails->l_name;

        }

        $oicVerifierQuery = $accountsModel->where('username', @$divisionIndorserDetails->emp_id)->first();

        if($oicVerifierQuery != null) {
            $oicVerifierIndorserDetails = $empDetailsModel->where('emp_id', $oicVerifierQuery->username)->first();
            $oicVerifierIndorserName = $oicVerifierIndorserDetails->f_name.' '.$oicVerifierIndorserDetails->m_name.' '.$oicVerifierIndorserDetails->l_name;

        }

        $msd_emp_id = '';

        if(@$divisionIndorserDetails->emp_id == 'dennisa_juab' || @$divisionIndorserDetails->emp_id == 'guilryd_cabatuan') {
            $msd_emp_id = 'rozele_torres';

          }else if(@$divisionIndorserDetails->emp_id == 'merlital_tabamo'  || @$divisionIndorserDetails->emp_id == 'moritob_estifano' || @$divisionIndorserDetails->emp_id == 'edgardog_agbayani' || @$divisionIndorserDetails->emp_id == 'albertoa_ecuacion') {
            $msd_emp_id = 'ferddiev_sususco';

          }else if(@$divisionIndorserDetails->emp_id == 'saibenp_mampao' || @$divisionIndorserDetails->emp_id == 'bucharya_dimaporo') {
            $msd_emp_id = 'hermosillac_flores';

          }else if(@$divisionIndorserDetails->emp_id == 'jeromeg_pioquinto' || @$divisionIndorserDetails->emp_id == 'jamela_tiburon') {
            $msd_emp_id = 'hdaensencio';

          }

        $msdIndorserDetails = $empDetailsModel->where('emp_id', $msd_emp_id)->first();
        $msdIndorserName = @$msdIndorserDetails->f_name.' '.@$msdIndorserDetails->m_name.' '.@$msdIndorserDetails->l_name;

        foreach($pending as $row):
            $row->to_status_original = $row->to_status;

            if($row->to_status == 'ARD' && $row->numberoftraveldays >= 8) {
                $row->to_status = 'FOR RECOMMENDING APPROVAL OF ARD MS';

            }else if($row->to_status == 'ARD-P-APPROVAL' && $row->numberoftraveldays <= 7 && $userDetails->sec_id < 47) {
                $row->to_status = 'FOR APPROVAL OF '.$officialsIndorserName;

            }else if($row->to_status == 'S. Chief' && $row->numberoftraveldays <= 7 && $userDetails->sec_id >= 47 && $row->outsideaor == 1) {
                $row->to_status = 'FOR RECOMMENDING APPROVAL OF '.$sectionIndorserName;

            }else if($row->to_status == '1st Verifier' && $row->numberoftraveldays <= 7 && $userDetails->sec_id >= 47 && $row->outsideaor == 1) {
                $name = $deputyCenroIndorserName;
                if($name == null) {
                    $name = $divisionIndorserName;
                }
                $row->to_status = 'FOR RECOMMENDING APPROVAL OF '.$name;

            }else if($row->to_status == 'CENRO' && $row->numberoftraveldays <= 7 && $userDetails->sec_id >= 47) {
                $row->to_status = 'FOR CENRO\'s APPROVAL';

            }elseif ($row->to_status == 'S. Chief') {
                $name = $sectionIndorserName;
                if($oicQuery != null) {
                    $name = $oicIndorserName;
                }

                $row->to_status = 'FOR RECOMMENDING APPROVAL OF '.$name;

            }else if($row->to_status == '1st Verifier' && $row->numberoftraveldays <= 7 && $userDetails->sec_id >= 47 && $row->outsideaor != 1 && $userDetails->acct_type == 'CENRO') {
                $row->to_status = 'FOR RECOMMENDING APPROVAL OF '.$msdIndorserName;

            }elseif ($row->to_status == '1st Verifier') {
                $name = $divisionIndorserName;
                if($oicVerifierQuery != null) {
                    $name = $oicVerifierIndorserName;
                }
                if($deputyCenroIndorserName != null) {
                    $name = $deputyCenroIndorserName;
                }

                $row->to_status = 'FOR RECOMMENDING APPROVAL OF '.$name;

            }else if($row->to_status == 'ARD' && $row->numberoftraveldays <= 7 && $row->outsideaor == 1 && $userDetails->acct_type == 'PENRO') {
                $row->to_status = 'FOR RECOMMENDING APPROVAL OF '.$officialsIndorserName;

            }else if($row->to_status == 'ARD') {
                $row->to_status = 'FOR RECOMMENDING APPROVAL OF '.$officialsIndorserName;

            }else if($row->to_status == 'HEA') {
                $row->to_status = 'FOR RECOMMENDING APPROVAL OF HEA';

            }else if($row->to_status == 'RED') {
                $row->to_status = 'FOR RED\'s APPROVAL';

            }else if($row->to_status == 'PENRO') {
                $row->to_status = 'FOR PENRO\'s APPROVAL';
            }


            $attachments = [];
            if(Storage::exists('public\TOES\\'.$row->id.'\report_of_previous_travel.pdf')) {
                array_push($attachments, 'report_of_previous_travel.pdf');

            }
            if(Storage::exists('public\TOES\\'.$row->id.'\travel_plan.pdf')) {
                array_push($attachments, 'travel_plan.pdf');

            }
            if(Storage::exists('public\TOES\\'.$row->id.'\others.pdf')) {
                array_push($attachments, 'others.pdf');
            }

            $row->attachment = $attachments;

        endforeach;

        foreach($approved as $row):
            $attachments = [];
            if(Storage::exists('public\TOES\\'.$row->id.'\report_of_previous_travel.pdf')) {
                array_push($attachments, 'report_of_previous_travel.pdf');

            }
            if(Storage::exists('public\TOES\\'.$row->id.'\travel_plan.pdf')) {
                array_push($attachments, 'travel_plan.pdf');

            }
            if(Storage::exists('public\TOES\\'.$row->id.'\others.pdf')) {
                array_push($attachments, 'others.pdf');

            }
            if(Storage::exists('public\TOES\\'.$row->id.'\lasttravelreport.pdf')) {
                array_push($attachments, 'lasttravelreport.pdf');

            }
            if(Storage::exists('public\TOES\\'.$row->id.'\travelplan.pdf')) {
                array_push($attachments, 'travelplan.pdf');
            }

            $row->attachment = $attachments;
        endforeach;

        $result = [
            'pending' => $pending,
            'approved' => $approved
        ];

        return json_encode($result);
    }

    public function keepAlive() {
        return json_encode('keepalive');
    }

    public function viewApprovedTO($to_id) {

        $employeeWithTODetailsModel = new View_empdetails_with_to;
        $officeModel = new View_office;
        $toStatusModel = new Tbl_tostatus;

        $employeeWithTODetailsModelResult = $employeeWithTODetailsModel->where('id', $to_id)->first();
        $office =  $officeModel->where('sec_id', '=', $employeeWithTODetailsModelResult->sec_id)->first()->office_name;
        $division =  $officeModel->where('sec_id', '=', $employeeWithTODetailsModelResult->sec_id)->first()->div_name;
        $employeeWithTODetailsModelResult['office_name'] = $division. ', '.$office;
        // $users = $divisionModel->join('view_empdetails_with_to', 'tbl_division.id',
        //                             '=', 'view_empdetails_with_to.id')
        // ->select('*')->get();

        // $toStatusResult = $toStatusModel->where('to_id', '=', $to_id)->get();
        $toStatusResult = $toStatusModel->join('tbl_empdetails', 'tbl_tostatus.emp_id', '=', 'tbl_empdetails.emp_id')
                                ->where('tbl_tostatus.to_id', '=', $to_id)
                                ->select(
                                    'tbl_empdetails.*',
                                    'tbl_tostatus.id as status_id',
                                    'tbl_tostatus.emp_id as status_emp_id',
                                    'tbl_tostatus.to_id',
                                    'tbl_tostatus.date_action',
                                    'tbl_tostatus.remarks',
                                    'tbl_tostatus.stat_act',
                                    'tbl_tostatus.created_at as to_status_created_at'
                                )
                                ->get();
        $approverDetails = [];
        foreach($toStatusResult as $row):
            if(strtolower($row->stat_act) == 'r. approved') {
                $approverDetails['recommending'] = $row;
            }else if(strtolower($row->stat_act) == 'approved') {
                $approverDetails['approver'] = $row;
            }
        endforeach;

        $depart = date('D, M d Y', strtotime($employeeWithTODetailsModelResult->depart));
        $arrival = date('D, M d Y', strtotime($employeeWithTODetailsModelResult->arrival));
        $date_prepared = date('D, M d Y', strtotime($employeeWithTODetailsModelResult->date_filed));

        $qr = "Name: ". $employeeWithTODetailsModelResult->f_name. ' '. $employeeWithTODetailsModelResult->m_name. ' '.$employeeWithTODetailsModelResult->l_name.
                ' '. 'TO No: '. $employeeWithTODetailsModelResult->full_to_no. ' Date Travel: '.$depart. '-'.$arrival. ' Purpose: '.$employeeWithTODetailsModelResult->purpose. ' Date Prepared: '.$date_prepared;
        $qr = str_replace("\\", "", $qr);
        $result = [
            'employeeDetails' => $employeeWithTODetailsModelResult,
            'approverDetails' => $approverDetails,
            'qr' => $qr
        ];
        return json_encode($result);
    }

    public function getUserDetails($user_id) {
        $model = new Tbl_empdetails;
        $officeModel = new View_office;
        $accountModel = new Tbl_accounts;

        $result = $model->where('emp_id', $user_id)->first();

        $officeResult = $officeModel->where('sec_id', @$result->sec_id)->first();
        $result['office_name'] = @$officeResult->office_name;
        $result['div_name'] = @$officeResult->div_name;
        $result['sec_name'] = @$officeResult->sec_name;
        $result['sec_office'] = @$officeResult->office;

        $accountResult = $accountModel->where('username', $result->emp_id)->first();
        $result['acct_type'] = @$accountResult->acct_type;
        $result['group'] = @$accountResult->group;
        $result['role'] = @$accountResult->role;

        $divisionResult = $officeModel->where('div_id', @$officeResult->div_id)->get();
        $result['offices'] = @$divisionResult;

        $username = Auth::guard('second_db')->user()->username;
        if($username == 'rios_bagundol' || $username == 'gleej_buta' || $username == 'mariloum_garrido') {
            $customOffices = [];

            $customOffices[] = (object) [
                'sec_id' => 48,
                'sec_name' => 'Monitoring and Enforcement Section'
            ];

            $customOffices[] = (object) [
                'sec_id' => 49,
                'sec_name' => 'Regulatory and Permitting Section'
            ];

            $customOffices[] = (object) [
                'sec_id' => 50,
                'sec_name' => 'Conservation and Development Section'
            ];

            $customOffices[] = (object) [
                'sec_id' => 52,
                'sec_name' => 'Admin and Finance Section'
            ];

            $result['offices'] = $customOffices;
        }


        return json_encode($result);
    }

    public function getListOfOffices() {
        $model = new View_office;

        $result = $model->get();

        return json_encode($result);
    }

    public function saveProfileInformation(Request $request, $type) {
        $empDetailsModel = new Tbl_empdetails;
        $accountModel = new Tbl_accounts;

        if($type == 'profile') {
            $data = $empDetailsModel->where('emp_id', $request->emp_id)->update([
                'f_name' => strtoupper($request->f_name),
                'm_name' => strtoupper($request->m_name),
                'l_name' => strtoupper($request->l_name),
                'sal' => strtoupper($request->sal),
                'emp_pos' => strtoupper($request->emp_pos),
                'sec_id' => strtoupper($request->sec_id),
            ]);
            $this->insert_logs(Auth::guard('second_db')->user()->username, 'Updated Profile Information', 'N/A', 'N/A', 'N/A');

        }else if($type == 'password') {
            $data = $accountModel->where('username', $request->emp_id)->update([
                'password' => password_hash($request->password, PASSWORD_BCRYPT, array("cost" => 10))
            ]);
            $this->insert_logs(Auth::guard('second_db')->user()->username, 'Updated Password', 'N/A', 'N/A', 'N/A');
        }

        if($data) {
            $result = [
                'message' => 'success',
            ];
        }else {
            $result = [
                'message' => 'error'
            ];
        }

        return json_encode($result);
    }

    public function countTravelOrderThisYear($emp_id) {
        $model = new Tbl_todetails;
        $yearToday = date('Y');

        $result = $model->where('emp_id', $emp_id)
                        ->whereRaw('EXTRACT(YEAR FROM depart::date) = ?', [$yearToday])
                        ->selectRaw('COUNT(*) as countdata')->first();

        return json_encode($result);
    }

    public function saveTravelInformation(Request $request) {
        $todetails_model = new Tbl_todetails;
        $tbldivision_model = new Tbl_division;
        $tblaccounts_model = new Tbl_accounts;
        $tblapproveto_model = new Tbl_approveto;

        $getDeputyCenroQuery = $tbldivision_model->where('deputy_cenro', $request->emp_id)->first();
        $getOicQuery = $tblaccounts_model->where('oic', $request->emp_id)->first();
        $getMySectionQuery = json_decode($this->getUserDetails($request->emp_id));

        $depart = date_create($request->depart);
        $arrival = date_create($request->arrival);
        $date_difference = date_diff($depart, $arrival);
        $date_difference = $date_difference->format("%a");
        $date_difference += 1;

        $message_result = '';
        $id = '';
        $to_status = '';

        if($request->acct_type == 'ARD MS' || $request->acct_type == 'ARD TS') {
            if($date_difference > 7 && $request->outsideaor == 1) {
                $message_result = 'The System does not support Travel more than 30 days and Travel Outside AOR (8-30 days) for ARDs';
            }else {
                $to_status = 'RED';

            }

        }else if($request->acct_type == 'MSD' || $request->acct_type == 'TSD' || $request->acct_type == 'PASU') {
            $to_status = 'PENRO';
            if($date_difference >= 8 && $date_difference <= 30) {
                $to_status = 'ARD';

            }else if($date_difference > 30) {
                $to_status = 'PENRO-Verifier';

            }else if($date_difference <= 7 && $request->emp_id == 'ismailc_ambola') {
                $to_status = 'CENRO';

            }

        }else if($request->acct_type == 'S. Chief' && $getDeputyCenroQuery) {
            $to_status = 'CENRO';
            if($date_difference > 30) {
                $to_status = 'CENRO-Verifier';

            }else if($date_difference >= 8) {
                $to_status = 'ARD';

            }

        }else if($request->acct_type == 'S. Chief') {
            $to_status == 'ARD';

            if($getOicQuery == null) {
                if($getMySectionQuery->sec_id == 42) {
                    $to_status = 'RED';

                }else if($request->outsideaor == 1 && $date_difference <= 7) {
                    if($getMySectionQuery->sec_id == 151) {
                        $to_status = 'PENRO';

                    }else {
                        $to_status = '1st Verifier';

                    }



                }else if($date_difference > 30 ) {
                    $to_status = '1st Verifier';

                }else if($date_difference >= 8) {
                    $to_status = 'ARD';

                }else {
                    if($request->emp_id == 'richeo_silva' || $request->emp_id == 'royr_aguanta' || $request->emp_id == 'nvcalipusan_cenro') {
                        $to_status = 'CENRO';

                    }else if($date_difference <= 7 && $request->outsideaor == 0
                        && ($getMySectionQuery->sec_id == 143 || $getMySectionQuery->sec_id == 118
                        || $getMySectionQuery->sec_id == 108 || $getMySectionQuery->sec_id == 98) ) {

                        $to_status = 'CENRO';

                    }else if($getMySectionQuery->sec_id == 86) {
                        $to_status = 'CENRO';

                    }else {
                        $to_status = '1st Verifier';

                        if($getMySectionQuery->sec_id == 82) {
                            $to_status = 'PASU';

                        }else if($getMySectionQuery->sec_id == 83 || $getMySectionQuery->sec_id == 151) {
                            $to_status = 'PENRO';

                        }
                    }



                }


            }
            if($getMySectionQuery->sec_id == 42) {
                $to_status = 'RED';

            }

            // CENRO OROQUIETA CITY NO DEPUTY CENRO, S.CHIEF RECOMMENDS

            if(($getMySectionQuery->sec_id == 90 || $getMySectionQuery->sec_id == 100 || $getMySectionQuery->sec_id == 110) && $date_difference <= 7) {
                $to_status = 'CENRO';

            }

            // CENRO OROQUIETA CITY NO DEPUTY CENRO, S.CHIEF RECOMMENDS

        }else if($request->acct_type == 'D. Chief' || $request->acct_type == 'PENRO' || $request->acct_type == 'CENRO') {

            if($getOicQuery == null) {
                $to_status = 'ARD';

                if($request->group == 12) {
                    $to_status = 'RED';

                }else if($request->group == 88 && $request->acct_type != 'PENRO') {
                    if($date_difference <= 7 && $getMySectionQuery->sec_id >= 47 && $request->outsideaor != 1) {
                        $to_status = '1st Verifier';

                    }else if($date_difference <= 7 && $getMySectionQuery->sec_id >= 47 && $request->outsideaor == 1) {
                        $to_status = 'PENRO';

                    }else if($date_difference >= 8 && $date_difference <= 30) {
                        $to_status = 'ARD';

                    }else if($date_difference > 30) {
                        $to_status = 'ARD';

                    }

                }else if($request->group == 88 && $request->acct_type == 'PENRO') {
                    if($date_difference <= 7 && $request->outsideaor == 1) {
                        $to_status = 'RED';

                    }else if($date_difference <= 7 && $request->outsideaor != 1) {
                        $to_status = 'ARD';

                    }else if($date_difference >= 8) {
                        $to_status = 'ARD';

                    }

                }else if($request->group == 501) {
                    if($date_difference <= 7 && $request->outsideaor == 1) {
                        $to_status = 'ARD-P-APPROVAL';

                    }else if($date_difference <= 7 && $request->outsideaor != 1) {
                        $to_status = 'ARD-P-APPROVAL';

                    }else if($date_difference >= 8) {
                        $to_status = 'ARD';

                    }
                }

                if($getMySectionQuery->sec_id == 42) {
                    $to_status = 'RED';

                }



            }else {
                if($date_difference > 7 && $request->outsideaor == 1) {
                    $message_result = 'The System does not support Travel more than 30 days and Travel Outside AOR (8-30 days) for ARDs';

                }else {
                    $to_status = 'RED';
                }
            }

        }else if($request->acct_type == 'RED') {
            $to_status = 'APPROVED';

            $todetails_model = new Tbl_todetails([
                'destination' => $request->destination,
                'purpose' => $request->purpose,
                'emp_id' => $request->emp_id,
                'to_status' => $to_status,
                'perdiem' => $request->perdiem,
                'laborers' => $request->laborers,
                'travelchrg' => $request->travelchrg,
                'outsideaor' => $request->outsideaor,
                'airtravel' => $request->airtravel,
                'numberoftraveldays' => $date_difference,
                'depart' => $request->depart,
                'arrival' => $request->arrival,
                'date_filed' => date("D, M d Y h:i:s A"),
            ]);
            $todetails_model->save();

            $last_id = $todetails_model->id;
            $year = date("Y");
            $month = date("m");
            $to_no = 1;
            $to_seen = 0;
            $cancel = 0;

            $approveToYearQuery = $tblapproveto_model->where('to_year', $year)->get();


            if(count($approveToYearQuery) >= 1) {
                $approveToMonthQuery = $tblapproveto_model->where('to_year', $year)->where('to_month', $month) ->get();

                if(count($approveToMonthQuery) >= 1) {
                    $to_no = count($approveToMonthQuery) + 1;
                }
            }

            $approveToInsert = new Tbl_approveto([
                'to_id' => $last_id,
                'to_year' => $year,
                'to_month' => $month,
                'to_no' => $to_no,
                'to_seen' => $to_seen,
                'cancel' => $cancel
            ]);
            $approveToInsert->save();

            $tbltostatus_model = new Tbl_tostatus([
                'emp_id' => $request->emp_id,
                'to_id' => $last_id,
                'remarks' => '',
                'stat_act' => 'APPROVED',

            ]);

            $tbltostatus_model->save();
        }else {
            $to_status = 'S. Chief';

            if($getMySectionQuery->sec_name == 'PENRO Staff') {
                $to_status = 'PENRO';

            }else if($getMySectionQuery->sec_name == 'Administrative and Support Unit') {
                $to_status = '1st Verifier';

            }else if($getMySectionQuery->sec_id == 110) {
                $to_status = '1st Verifier';

            }

            // office of the PENRO bukidnon
            if($getMySectionQuery->sec_id == 159 && $date_difference <= 7) {
                $to_status = 'PENRO';

            }else if($date_difference <= 7 && $getMySectionQuery->sec_id >= 47 && $request->outsideaor == 1) {
                $to_status = 'S. Chief';

            }else if($date_difference <= 7 && $getMySectionQuery->sec_id >= 47) {
                $to_status = '1st Verifier';

                if($getMySectionQuery->sec_id == 91 || $getMySectionQuery->sec_id == 101) {
                    $to_status = 'S. Chief';

                }

            }else if($date_difference <= 7 && $getMySectionQuery->sec_id >= 47 && $getMySectionQuery->sec_id < 90 && $request->outsideaor == 1) {
                $to_status = '1st Verifier';

            }else if($date_difference <= 7 && $getMySectionQuery->sec_id < 47) {
                $to_status = '1st Verifier';

            }else if($date_difference >= 8 && $date_difference <= 30 && $getMySectionQuery->sec_id >= 47) {
                $to_status = 'ARD';

            }else if($date_difference >= 8 && $date_difference <= 30) {
                $to_status = 'ARD';

            }else if($date_difference > 30) {
                $to_status = '1st Verifier';

            }

            if ($date_difference <= 7 && ($getMySectionQuery->emp_id == 'ritchief_villarta' || $getMySectionQuery->emp_id == 'rosellea_baluat' || $getMySectionQuery->emp_id == 'leac_yandug' || $getMySectionQuery->emp_id == 'susanl_galanido' || $getMySectionQuery->emp_id == 'cliffordd_gocela' || $getMySectionQuery->emp_id == 'ellenm_baldado' || $getMySectionQuery->emp_id == 'richmonb_halasan'  || $getMySectionQuery->emp_id == 'jersonl_meliston' || $getMySectionQuery->emp_id == 'litoa_paculba')) {
                $to_status = 'PENRO';
            }

            if ($getMySectionQuery->sec_id == 149 || $getMySectionQuery->sec_id == 82 || $getMySectionQuery->sec_id == 78) {
                $to_status = '1st Verifier';
            }

            if ($getMySectionQuery->sec_id == 147) {
                $to_status = 'PENRO';
            }

            if ($getMySectionQuery->sec_id == 27 || $getMySectionQuery->sec_id == 28) {
                $to_status = 'ARD-P-APPROVAL';
            }

            if ($date_difference <= 7 && ($getMySectionQuery->sec_id == 86 || $getMySectionQuery->sec_id == 88)) {
                $to_status = 'CENRO';
            }

            if ($getMySectionQuery->sec_id == 82 && $date_difference <= 7) {
                $to_status = 'PASU';
            }

            if($request->acct_type == 'HEA') {
                $to_status = 'RED';
            }

            // CENRO OROQUIETA CITY NO DEPUTY CENRO, S.CHIEF RECOMMENDS

            if(($getMySectionQuery->sec_id == 90 || $getMySectionQuery->sec_id == 100 || $getMySectionQuery->sec_id == 110) && $date_difference <= 7) {
                $to_status = 'S. Chief';

            }
            if($getMySectionQuery->sec_id == 138 && $date_difference <= 7) {
                $to_status = 'CENRO';
            }

            // CENRO OROQUIETA CITY NO DEPUTY CENRO, S.CHIEF RECOMMENDS


            // PENRO MISAMIS OCCIDENTAL PENRO STAFF DIRECT PENRO
            if($getMySectionQuery->sec_id == 87 && $date_difference <= 7) {
                $to_status = 'PENRO';
            }
            // PENRO MISAMIS OCCIDENTAL PENRO STAFF DIRECT PENRO

        }

        // TEMPORARY PENRO MISAMIS OCCIDENTAL ADMIN AND FINANCE TO DIRECT TO MSD
        if($date_difference <= 7 && $getMySectionQuery->sec_id == 52) {
            $to_status = '1st Verifier';
        }

        // TEMPORARY PENRO CAMIGUIN ADMIN AND FINANCE TO DIRECT TO MSD
        if($date_difference <= 7 && $getMySectionQuery->sec_id == 59) {
            $to_status = '1st Verifier';
        }

        if($request->acct_type != 'RED') {
            $todetails_model = new Tbl_todetails([
                'destination' => $request->destination,
                'purpose' => $request->purpose,
                'emp_id' => $request->emp_id,
                'to_status' => $to_status,
                'perdiem' => $request->perdiem,
                'laborers' => $request->laborers,
                'travelchrg' => $request->travelchrg,
                'outsideaor' => $request->outsideaor,
                'airtravel' => $request->airtravel,
                'numberoftraveldays' => $date_difference,
                'depart' => $request->depart,
                'arrival' => $request->arrival,
                'date_filed' => date("D, M d Y h:i:s A"),
            ]);
            $todetails_model->save();

        }
        $this->insert_logs($request->emp_id, 'applied', $request->depart, $request->arrival, $request->destination);
        $id = $todetails_model->id;
        $message_result = 'success';

        $result = [
            'id' => $id,
            'message' => $message_result
        ];

        return json_encode($result);

    }

    public function saveFiles(Request $request, $id) {
        $report_of_previous_travel_files = request()->file('report_of_previous_travel');
        $travel_plan_files = request()->file('travel_plan');
        $others_files = request()->file('others');
        $esig_files = request()->file('esig');

        if(request()->hasFile('report_of_previous_travel')) {
            $report_of_previous_travel_files->storeAs('public\TOES\\'.$id, 'report_of_previous_travel'.'.'.$report_of_previous_travel_files->extension());

        }
        if(request()->hasFile('travel_plan')) {
            $travel_plan_files->storeAs('public\TOES\\'.$id, 'travel_plan'.'.'.$travel_plan_files->extension());

        }
        if(request()->hasFile('others')) {
            $others_files->storeAs('public\TOES\\'.$id, 'others'.'.'.$others_files->extension());
        }

        if(request()->hasFile('esig')) {
            foreach($esig_files as $file):

                $file->storeAs('public\TOES\esig',$file->getClientOriginalName());
                $tblaccounts_model = new Tbl_accounts([
                    'username' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    'password' => password_hash('Windows7', PASSWORD_BCRYPT, array("cost" => 10)),
                    'acct_type' => 'user'
                ]);
                $tblaccounts_model->save();

                $tblempdetails_model = new Tbl_empdetails([
                    'emp_id' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                ]);
                $tblempdetails_model->save();

            endforeach;
            return var_dump($esig_files);

        }

        return json_encode('success');
    }

    public function deleteTo(Request $request, $id) {
        $model = new Tbl_todetails;

        $result = $model->find($id)->delete();
        $this->insert_logs($request->emp_id, 'deleted', $request->depart, $request->arrival, $request->destination);

        if($result) {
            $message = [
                'message' => 'success'
            ];
        }else {
            $message = [
                'message' => 'error'
            ];
        }

        return json_encode($message);
    }

    public function viewPendingTO($to_id) {
        $toStatusModel = new Tbl_tostatus;
        $empDetailsModel = new Tbl_empdetails;

        $toStatusQuery = $toStatusModel->where('to_id', $to_id)->get();

        foreach($toStatusQuery as $row):
            $empDetailsQuery = $empDetailsModel->where('emp_id', $row->emp_id)->first();

            $row->name = $empDetailsQuery->f_name.' '.$empDetailsQuery->m_name.' '.$empDetailsQuery->l_name;

        endforeach;

        return json_encode($toStatusQuery);
    }

    public function updateTravelInformation(Request $request) {
        $todetails_model = new Tbl_todetails;
        $tbldivision_model = new Tbl_division;
        $tblaccounts_model = new Tbl_accounts;
        $tblapproveto_model = new Tbl_approveto;

        $getDeputyCenroQuery = $tbldivision_model->where('deputy_cenro', $request->emp_id)->first();
        $getOicQuery = $tblaccounts_model->where('oic', $request->emp_id)->first();
        $getMySectionQuery = json_decode($this->getUserDetails($request->emp_id));

        $depart = date_create($request->depart);
        $arrival = date_create($request->arrival);
        $date_difference = date_diff($depart, $arrival);
        $date_difference = $date_difference->format("%a");
        $date_difference += 1;

        $message_result = '';
        $id = '';
        $to_status = $request->to_status_original;

        if($request->acct_type == 'ARD MS' || $request->acct_type == 'ARD TS') {
            if($date_difference > 7 && $request->outsideaor == 1) {
                $message_result = 'The System does not support Travel more than 30 days and Travel Outside AOR (8-30 days) for ARDs';
            }else {
                $to_status = 'RED';

            }

        }else if($request->acct_type == 'MSD' || $request->acct_type == 'TSD' || $request->acct_type == 'PASU') {
            $to_status = 'PENRO';
            if($date_difference >= 8 && $date_difference <= 30) {
                $to_status = 'ARD';

            }else if($date_difference > 30) {
                $to_status = 'PENRO-Verifier';

            }else if($date_difference <= 7 && $request->emp_id == 'ismailc_ambola') {
                $to_status = 'CENRO';

            }

        }else if($request->acct_type == 'S. Chief' && $getDeputyCenroQuery) {
            $to_status = 'CENRO';
            if($date_difference > 30) {
                $to_status = 'CENRO-Verifier';

            }else if($date_difference >= 8) {
                $to_status = 'ARD';

            }

        }else if($request->acct_type == 'S. Chief') {
            $to_status == 'ARD';

            if($getOicQuery == null) {
                if($getMySectionQuery->sec_id == 42) {
                    $to_status = 'RED';

                }else if($request->outsideaor == 1 && $date_difference <= 7) {
                    if($getMySectionQuery->sec_id == 151) {
                        $to_status = 'PENRO';

                    }else {
                        $to_status = '1st Verifier';

                    }



                }else if($date_difference > 30 ) {
                    $to_status = '1st Verifier';

                }else if($date_difference >= 8) {
                    $to_status = 'ARD';

                }else {
                    if($request->emp_id == 'richeo_silva' || $request->emp_id == 'royr_aguanta' || $request->emp_id == 'nvcalipusan_cenro') {
                        $to_status = 'CENRO';

                    }else if($date_difference <= 7 && $request->outsideaor == 0
                        && ($getMySectionQuery->sec_id == 143 || $getMySectionQuery->sec_id == 118
                        || $getMySectionQuery->sec_id == 108 || $getMySectionQuery->sec_id == 98) ) {

                        $to_status = 'CENRO';

                    }else if($getMySectionQuery->sec_id == 86) {
                        $to_status = 'CENRO';

                    }else {
                        $to_status = '1st Verifier';

                        if($getMySectionQuery->sec_id == 82) {
                            $to_status = 'PASU';

                        }else if($getMySectionQuery->sec_id == 83 || $getMySectionQuery->sec_id == 151) {
                            $to_status = 'PENRO';

                        }
                    }

                }




            }
            if($getMySectionQuery->sec_id == 42) {
                $to_status = 'RED';

            }

            // CENRO OROQUIETA CITY NO DEPUTY CENRO, S.CHIEF RECOMMENDS

            if(($getMySectionQuery->sec_id == 90 || $getMySectionQuery->sec_id == 100 || $getMySectionQuery->sec_id == 110) && $date_difference <= 7) {
                $to_status = 'CENRO';

            }

            // CENRO OROQUIETA CITY NO DEPUTY CENRO, S.CHIEF RECOMMENDS

        }else if($request->acct_type == 'D. Chief' || $request->acct_type == 'PENRO' || $request->acct_type == 'CENRO') {

            if($getOicQuery == null) {
                $to_status = 'ARD';

                if($request->group == 12) {
                    $to_status = 'RED';

                }else if($request->group == 88 && $request->acct_type != 'PENRO') {
                    if($date_difference <= 7 && $getMySectionQuery->sec_id >= 47 && $request->outsideaor != 1) {
                        $to_status = '1st Verifier';

                    }else if($date_difference <= 7 && $getMySectionQuery->sec_id >= 47 && $request->outsideaor == 1) {
                        $to_status = 'PENRO';

                    }else if($date_difference >= 8 && $date_difference <= 30) {
                        $to_status = 'ARD';

                    }else if($date_difference > 30) {
                        $to_status = 'ARD';

                    }

                }else if($request->group == 88 && $request->acct_type == 'PENRO') {
                    if($date_difference <= 7 && $request->outsideaor == 1) {
                        $to_status = 'RED';

                    }else if($date_difference <= 7 && $request->outsideaor != 1) {
                        $to_status = 'ARD';

                    }else if($date_difference >= 8) {
                        $to_status = 'ARD';

                    }

                }else if($request->group == 501) {
                    if($date_difference <= 7 && $request->outsideaor == 1) {
                        $to_status = 'ARD-P-APPROVAL';

                    }else if($date_difference <= 7 && $request->outsideaor != 1) {
                        $to_status = 'ARD-P-APPROVAL';

                    }else if($date_difference >= 8) {
                        $to_status = 'ARD';

                    }
                }

                if($getMySectionQuery->sec_id == 42) {
                    $to_status = 'RED';

                }



            }else {
                if($date_difference > 7 && $request->outsideaor == 1) {
                    $message_result = 'The System does not support Travel more than 30 days and Travel Outside AOR (8-30 days) for ARDs';

                }else {
                    $to_status = 'RED';
                }
            }

        }else if($request->acct_type == 'RED') {
            $to_status = 'APPROVED';

            $todetails_model = new Tbl_todetails([
                'destination' => $request->destination,
                'purpose' => $request->purpose,
                'emp_id' => $request->emp_id,
                'to_status' => $to_status,
                'perdiem' => $request->perdiem,
                'laborers' => $request->laborers,
                'travelchrg' => $request->travelchrg,
                'outsideaor' => $request->outsideaor,
                'airtravel' => $request->airtravel,
                'numberoftraveldays' => $date_difference,
                'depart' => $request->depart,
                'arrival' => $request->arrival,
                'date_filed' => date("D, M d Y h:i:s A"),
            ]);
            $todetails_model->save();

            $last_id = $todetails_model->id;
            $year = date("Y");
            $month = date("m");
            $to_no = 1;
            $to_seen = 0;
            $cancel = 0;

            $approveToYearQuery = $tblapproveto_model->where('to_year', $year)->get();


            if(count($approveToYearQuery) >= 1) {
                $approveToMonthQuery = $tblapproveto_model->where('to_year', $year)->where('to_month', $month) ->get();

                if(count($approveToMonthQuery) >= 1) {
                    $to_no = count($approveToMonthQuery) + 1;
                }
            }

            $approveToInsert = new Tbl_approveto([
                'to_id' => $last_id,
                'to_year' => $year,
                'to_month' => $month,
                'to_no' => $to_no,
                'to_seen' => $to_seen,
                'cancel' => $cancel
            ]);
            $approveToInsert->save();

            $tbltostatus_model = new Tbl_tostatus([
                'emp_id' => $request->emp_id,
                'to_id' => $last_id,
                'remarks' => '',
                'stat_act' => 'APPROVED',

            ]);

            $tbltostatus_model->save();
        }else {
            $to_status = 'S. Chief';

            if($getMySectionQuery->sec_name == 'PENRO Staff') {
                $to_status = 'PENRO';

            }else if($getMySectionQuery->sec_name == 'Administrative and Support Unit') {
                $to_status = '1st Verifier';

            }else if($getMySectionQuery->sec_id == 110) {
                $to_status = '1st Verifier';

            }


            // office of the PENRO bukidnon
            if($getMySectionQuery->sec_id == 159 && $date_difference <= 7) {
                $to_status = 'PENRO';

            }else if($date_difference <= 7 && $getMySectionQuery->sec_id >= 47 && $request->outsideaor == 1) {
                $to_status = 'S. Chief';

            }else if($date_difference <= 7 && $getMySectionQuery->sec_id >= 47) {
                $to_status = '1st Verifier';

                if($getMySectionQuery->sec_id == 91 || $getMySectionQuery->sec_id == 101) {
                    $to_status = 'S. Chief';

                }

            }else if($date_difference <= 7 && $getMySectionQuery->sec_id >= 47 && $getMySectionQuery->sec_id < 90 && $request->outsideaor == 1) {
                $to_status = '1st Verifier';

            }else if($date_difference <= 7 && $getMySectionQuery->sec_id < 47) {
                $to_status = '1st Verifier';

            }else if($date_difference >= 8 && $date_difference <= 30 && $getMySectionQuery->sec_id >= 47) {
                $to_status = 'ARD';

            }else if($date_difference >= 8 && $date_difference <= 30) {
                $to_status = 'ARD';

            }else if($date_difference > 30) {
                $to_status = '1st Verifier';

            }

            if ($date_difference <= 7 && ($getMySectionQuery->emp_id == 'rosellea_baluat' || $getMySectionQuery->emp_id == 'leac_yandug' || $getMySectionQuery->emp_id == 'susanl_galanido' || $getMySectionQuery->emp_id == 'cliffordd_gocela' || $getMySectionQuery->emp_id == 'ellenm_baldado' || $getMySectionQuery->emp_id == 'richmonb_halasan'  || $getMySectionQuery->emp_id == 'jersonl_meliston' || $getMySectionQuery->emp_id == 'litoa_paculba')) {
                $to_status = 'PENRO';
            }

            if ($getMySectionQuery->sec_id == 149 || $getMySectionQuery->sec_id == 82 || $getMySectionQuery->sec_id == 78) {
                $to_status = '1st Verifier';
            }

            if ($getMySectionQuery->sec_id == 147) {
                $to_status = 'PENRO';
            }

            if ($getMySectionQuery->sec_id == 27 || $getMySectionQuery->sec_id == 28) {
                $to_status = 'ARD-P-APPROVAL';
            }

            if ($date_difference <= 7 && ($getMySectionQuery->sec_id == 86 || $getMySectionQuery->sec_id == 88)) {
                $to_status = 'CENRO';
            }

            if ($getMySectionQuery->sec_id == 82 && $date_difference <= 7) {
                $to_status = 'PASU';
            }

            if($request->acct_type == 'HEA') {
                $to_status = 'RED';
            }

            if($date_difference >= 8 && $getMySectionQuery->sec_id >= 47) {
                $to_status = 'ARD';

            }



            // CENRO OROQUIETA CITY NO DEPUTY CENRO, S.CHIEF RECOMMENDS

            if(($getMySectionQuery->sec_id == 90 || $getMySectionQuery->sec_id == 100 || $getMySectionQuery->sec_id == 110) && $date_difference <= 7) {
                $to_status = 'S. Chief';

            }
            if($getMySectionQuery->sec_id == 138 && $date_difference <= 7) {
                $to_status = 'CENRO';
            }

            // CENRO OROQUIETA CITY NO DEPUTY CENRO, S.CHIEF RECOMMENDS


            // PENRO MISAMIS OCCIDENTAL PENRO STAFF DIRECT PENRO
            if($getMySectionQuery->sec_id == 87 && $date_difference <= 7) {
                $to_status = 'PENRO';
            }
            // PENRO MISAMIS OCCIDENTAL PENRO STAFF DIRECT PENRO

        }

        // TEMPORARY PENRO MISAMIS OCCIDENTAL ADMIN AND FINANCE TO DIRECT TO MSD
        if($date_difference <= 7 && $getMySectionQuery->sec_id == 52) {
            $to_status = '1st Verifier';
        }

        // TEMPORARY PENRO CAMIGUIN ADMIN AND FINANCE TO DIRECT TO MSD
        if($date_difference <= 7 && $getMySectionQuery->sec_id == 59) {
            $to_status = '1st Verifier';
        }

        $data = $todetails_model->find($request->id)->update([
            'destination' => $request->destination,
            'purpose' => $request->purpose,
            'emp_id' => $request->emp_id,
            'perdiem' => $request->perdiem,
            'laborers' => $request->laborers,
            'travelchrg' => $request->travelchrg,
            'outsideaor' => $request->outsideaor,
            'airtravel' => $request->airtravel,
            'numberoftraveldays' => $date_difference,
            'depart' => $request->depart,
            'arrival' => $request->arrival,
            'to_status' => $to_status
        ]);

        $tbltostatus_model = new Tbl_tostatus([
            'emp_id' => $request->emp_id,
            'to_id' => $request->id,
            'remarks' => '',
            'stat_act' => 'Updated Information',

        ]);
        $tbltostatus_model->save();

        $this->insert_logs($request->emp_id, 'Updated Information', $request->depart, $request->arrival, $request->destination);

        $id = $request->id;
        $message_result = 'fail';

        if($data) {
            $message_result = 'success';
        }

        $result = [
            'id' => $id,
            'message' => $message_result
        ];

        return json_encode($result);
    }

    public function getOfficesUnderArdForRecommendation($user, $acct_type) {
        $todetails_model = new Tbl_todetails;
        $tbldivision_model = new Tbl_division;
        $tblaccounts_model = new Tbl_accounts;
        $tbloffice_model = new View_office;

        $getDeputyCenroQuery = $tbldivision_model->where('deputy_cenro', $user)->first();
        $getOicQuery = $tblaccounts_model->where('oic', $user)->first();

        $result_query = [];
        $cenrosConditions = '';

        $officeDetails = $tbloffice_model->where('emp_id', $user)->first();
        if($acct_type == 'CENRO' ) {

            $officeDetails = $tbloffice_model->where('div_emp_id', $user)->first();

        }

        $listOfOfficesDetails = $tbloffice_model->select('div_id')
                                                ->select('div_name')
                                                ->where('tgroup', $officeDetails->tgroup)
                                                ->groupBy('div_id')
                                                ->groupBy('div_name')
                                                ->get();
        $whereCondition1st = 'tbl_office.tgroup';
        $whereCondition2nd = $officeDetails->tgroup;
        $groupByCondition = 'tbl_division.id';
        $selectQuery = 'tbl_division.div_name, tbl_division.id, COUNT(*) as countdata';
        $status = '';
        if($acct_type == 'PENRO') {
            $status = 'PENRO';
        }else if($acct_type == 'CENRO' || $acct_type == 'PASU'){
            $status = 'CENRO';
        }else if($acct_type == 'ARD MS' || $acct_type == 'ARD TS') {
            $status = 'ARD';
        }

        if($user == 'nvcalipusan' || $user == 'alsond_potutan') {
            $status = 'PASU';
        }

        if($acct_type == 'ARD MS') {
            $listOfFieldOfficesDetails = $tbloffice_model->select('div_id')
                                                        ->select('div_name')
                                                        ->where('div_id', '>=', 17)
                                                        ->groupBy('div_id')
                                                        ->groupBy('div_name')
                                                        ->orderBy('div_id', 'ASC')
                                                        ->get();

            $tsOfficesDetails = $tbloffice_model
                                                ->select('div_id')
                                                ->select('div_name')
                                                ->where('div_id', 5)
                                                ->orWhere('div_id', 6)
                                                ->orWhere('div_id', 7)
                                                ->orWhere('div_id', 8)
                                                ->orWhere('div_id', 10)
                                                ->orWhere('div_id', 11)
                                                ->orWhere('div_id', 12)
                                                ->groupBy('div_id')
                                                ->groupBy('div_name')
                                                ->orderBy('div_id', 'ASC')
                                                ->get();

             $countDataFieldOfficeResultQuery = $todetails_model
                                    ->join('tbl_empdetails', 'tbl_todetails.emp_id', '=', 'tbl_empdetails.emp_id')
                                    ->join('tbl_section', 'tbl_empdetails.sec_id', '=', 'tbl_section.id')
                                    ->join('tbl_division', 'tbl_division.id', '=', 'tbl_section.div_id')
                                    ->join('tbl_office', DB::raw('CAST(tbl_office.id AS TEXT)'), '=', 'tbl_division.off_id')
                                    ->where('div_id', '>=', 17)
                                    ->where('tbl_todetails.to_status', $status)
                                    ->selectRaw('tbl_division.div_name, tbl_division.id, COUNT(*) as countdata')
                                    ->groupBy('tbl_division.id')
                                    ->get();
            $div_ids = [5, 6, 7, 8, 10, 11, 12];
            $countDataTsOfficeResultQuery = $todetails_model
                                    ->join('tbl_empdetails', 'tbl_todetails.emp_id', '=', 'tbl_empdetails.emp_id')
                                    ->join('tbl_section', 'tbl_empdetails.sec_id', '=', 'tbl_section.id')
                                    ->join('tbl_division', 'tbl_division.id', '=', 'tbl_section.div_id')
                                    ->join('tbl_office', DB::raw('CAST(tbl_office.id AS TEXT)'), '=', 'tbl_division.off_id')
                                    ->where('tbl_todetails.to_status', $status)
                                    ->whereIn('div_id', $div_ids) // Dynamically apply multiple values
                                    ->where('numberoftraveldays', '>=', 8)
                                    ->selectRaw('tbl_division.div_name, tbl_division.id, COUNT(*) as countdata')
                                    ->groupBy('tbl_division.id')
                                    ->get();

            foreach($tsOfficesDetails as $row):
                $row->countdata = 0;
                foreach($countDataTsOfficeResultQuery as $countRow):
                    if($row->div_name == $countRow->div_name) {
                        $row->countdata = $countRow->countdata;
                    }
                endforeach;
                $listOfOfficesDetails->push($row);
            endforeach;


            foreach($listOfFieldOfficesDetails as $row):
                $row->countdata = 0;
                foreach($countDataFieldOfficeResultQuery as $countRow):
                    if($row->div_name == $countRow->div_name) {
                        $row->countdata = $countRow->countdata;
                    }
                endforeach;
                $listOfOfficesDetails->push($row);
            endforeach;

        }

        if($acct_type == 'CENRO' ) {

            $listOfOfficesDetails = $tbloffice_model->where('div_emp_id', $user)->get();
            $whereCondition1st = 'tbl_division.emp_id';
            $whereCondition2nd = $user;
            $groupByCondition = 'tbl_section.id';
            $selectQuery = 'tbl_section.sec_name, tbl_section.id, COUNT(*) as countdata';

        }




        $countDataResultQuery = $todetails_model
                                    ->join('tbl_empdetails', 'tbl_todetails.emp_id', '=', 'tbl_empdetails.emp_id')
                                    ->join('tbl_section', 'tbl_empdetails.sec_id', '=', 'tbl_section.id')
                                    ->join('tbl_division', 'tbl_division.id', '=', 'tbl_section.div_id')
                                    ->join('tbl_office', DB::raw('CAST(tbl_office.id AS TEXT)'), '=', 'tbl_division.off_id')
                                    ->where($whereCondition1st, $whereCondition2nd)
                                    ->where('tbl_todetails.to_status', $status)
                                    ->selectRaw($selectQuery)
                                    ->groupBy($groupByCondition)
                                    ->get();

        $employeeDetailsQuery = $todetails_model
                                    ->join('tbl_empdetails', 'tbl_todetails.emp_id', '=', 'tbl_empdetails.emp_id')
                                    ->join('tbl_section', 'tbl_empdetails.sec_id', '=', 'tbl_section.id')
                                    ->join('tbl_division', 'tbl_division.id', '=', 'tbl_section.div_id')
                                    ->join('tbl_office', DB::raw('CAST(tbl_office.id AS TEXT)'), '=', 'tbl_division.off_id')
                                    ->select(
                                        'tbl_todetails.id',
                                        'tbl_empdetails.f_name',
                                        'tbl_empdetails.m_name',
                                        'tbl_empdetails.l_name',
                                        'tbl_empdetails.emp_pos',
                                        'tbl_empdetails.sal',
                                        'tbl_empdetails.emp_id',
                                        'tbl_empdetails.sec_id',
                                        'tbl_section.sec_name',
                                        'tbl_division.div_name',
                                        'tbl_division.id as div_id',
                                        'tbl_division.emp_id as div_emp_id',
                                        'tbl_todetails.depart',
                                        'tbl_todetails.arrival',
                                        'tbl_todetails.destination',
                                        'tbl_todetails.purpose',
                                        'tbl_todetails.otime',
                                        'tbl_todetails.outsideaor',
                                        'tbl_todetails.numberoftraveldays',
                                        'tbl_todetails.airtravel'
                                    )
                                    ->where('tbl_todetails.to_status', $status)
                                    ->get();

        if($acct_type == 'CENRO') {
            $employeeDetailsQuery = $todetails_model
                                    ->join('tbl_empdetails', 'tbl_todetails.emp_id', '=', 'tbl_empdetails.emp_id')
                                    ->join('tbl_section', 'tbl_empdetails.sec_id', '=', 'tbl_section.id')
                                    ->join('tbl_division', 'tbl_division.id', '=', 'tbl_section.div_id')
                                    ->join('tbl_office', DB::raw('CAST(tbl_office.id AS TEXT)'), '=', 'tbl_division.off_id')
                                    ->select(
                                        'tbl_todetails.id',
                                        'tbl_empdetails.f_name',
                                        'tbl_empdetails.m_name',
                                        'tbl_empdetails.l_name',
                                        'tbl_empdetails.emp_pos',
                                        'tbl_empdetails.sal',
                                        'tbl_empdetails.emp_id',
                                        'tbl_empdetails.sec_id',
                                        'tbl_section.sec_name',
                                        'tbl_division.div_name',
                                        'tbl_division.id as div_id',
                                        'tbl_division.emp_id as div_emp_id',
                                        'tbl_todetails.depart',
                                        'tbl_todetails.arrival',
                                        'tbl_todetails.destination',
                                        'tbl_todetails.purpose',
                                        'tbl_todetails.otime',
                                        'tbl_todetails.outsideaor',
                                        'tbl_todetails.numberoftraveldays',
                                        'tbl_todetails.airtravel'
                                    )
                                    ->where($whereCondition1st, $whereCondition2nd)
                                    ->where('tbl_todetails.to_status', $status)
                                    ->get();
        }
        $countDataResultQueryPlanning = [];
        $employeeDetailsQueryPlanning = [];

        if($acct_type == 'ARD TS') {
			//TEMPORARY RECOM PLANNING UNDER MAM MABEL
			$listOfOfficesDetails = $tbloffice_model->select('div_id')
                                                ->select('div_name')
                                                ->where('tgroup', $officeDetails->tgroup)
												// ->orWhere('div_name', 'Planning and Management Division')
                                                ->groupBy('div_id')
                                                ->groupBy('div_name')
                                                ->get();
            $countDataResultQuery = $todetails_model
                                    ->join('tbl_empdetails', 'tbl_todetails.emp_id', '=', 'tbl_empdetails.emp_id')
                                    ->join('tbl_section', 'tbl_empdetails.sec_id', '=', 'tbl_section.id')
                                    ->join('tbl_division', 'tbl_division.id', '=', 'tbl_section.div_id')
                                    ->join('tbl_office', DB::raw('CAST(tbl_office.id AS TEXT)'), '=', 'tbl_division.off_id')
                                    ->where($whereCondition1st, $whereCondition2nd)
                                    ->where('tbl_todetails.numberoftraveldays', '<', 8)
                                    ->where('tbl_todetails.to_status', $status)
                                    ->selectRaw('tbl_division.div_name, tbl_division.id, COUNT(*) as countdata')
                                    ->groupBy('tbl_division.id')
                                    ->get();

			//TEMPORARY RECOM PLANNING UNDER MAM MABEL
			// $countDataResultQueryPlanning = $todetails_model
            //                         ->join('tbl_empdetails', 'tbl_todetails.emp_id', '=', 'tbl_empdetails.emp_id')
            //                         ->join('tbl_section', 'tbl_empdetails.sec_id', '=', 'tbl_section.id')
            //                         ->join('tbl_division', 'tbl_division.id', '=', 'tbl_section.div_id')
            //                         ->join('tbl_office', DB::raw('CAST(tbl_office.id AS TEXT)'), '=', 'tbl_division.off_id')
            //                         ->where('div_name', 'Planning and Management Division')
            //                         ->where('tbl_todetails.numberoftraveldays', '<', 8)
            //                         ->where('tbl_todetails.to_status', '1st Verifier')
            //                         ->selectRaw('tbl_division.div_name, tbl_division.id, COUNT(*) as countdata')
            //                         ->groupBy('tbl_division.id')
            //                         ->get();

            $employeeDetailsQuery = $todetails_model
                                    ->join('tbl_empdetails', 'tbl_todetails.emp_id', '=', 'tbl_empdetails.emp_id')
                                    ->join('tbl_section', 'tbl_empdetails.sec_id', '=', 'tbl_section.id')
                                    ->join('tbl_division', 'tbl_division.id', '=', 'tbl_section.div_id')
                                    ->join('tbl_office', DB::raw('CAST(tbl_office.id AS TEXT)'), '=', 'tbl_division.off_id')
                                    ->select(
                                        'tbl_todetails.id',
                                        'tbl_empdetails.f_name',
                                        'tbl_empdetails.m_name',
                                        'tbl_empdetails.l_name',
                                        'tbl_empdetails.emp_pos',
                                        'tbl_empdetails.sal',
                                        'tbl_empdetails.emp_id',
                                        'tbl_empdetails.sec_id',
                                        'tbl_section.sec_name',
                                        'tbl_division.div_name',
                                        'tbl_division.id as div_id',
                                        'tbl_todetails.depart',
                                        'tbl_todetails.arrival',
                                        'tbl_todetails.destination',
                                        'tbl_todetails.purpose',
                                        'tbl_todetails.otime',
                                        'tbl_todetails.outsideaor',
                                        'tbl_todetails.numberoftraveldays',
                                        'tbl_todetails.airtravel'
                                    )
                                    ->where('tbl_todetails.to_status', $status)
                                    ->where('numberoftraveldays', '<', 8)
                                    ->get();

			//TEMPORARY RECOM PLANNING UNDER MAM MABEL
			// $employeeDetailsQueryPlanning = $todetails_model
            //                         ->join('tbl_empdetails', 'tbl_todetails.emp_id', '=', 'tbl_empdetails.emp_id')
            //                         ->join('tbl_section', 'tbl_empdetails.sec_id', '=', 'tbl_section.id')
            //                         ->join('tbl_division', 'tbl_division.id', '=', 'tbl_section.div_id')
            //                         ->join('tbl_office', DB::raw('CAST(tbl_office.id AS TEXT)'), '=', 'tbl_division.off_id')
            //                         ->select(
            //                             'tbl_todetails.id',
            //                             'tbl_empdetails.f_name',
            //                             'tbl_empdetails.m_name',
            //                             'tbl_empdetails.l_name',
            //                             'tbl_empdetails.emp_pos',
            //                             'tbl_empdetails.sal',
            //                             'tbl_empdetails.emp_id',
            //                             'tbl_empdetails.sec_id',
            //                             'tbl_section.sec_name',
            //                             'tbl_division.div_name',
            //                             'tbl_division.id as div_id',
            //                             'tbl_todetails.depart',
            //                             'tbl_todetails.arrival',
            //                             'tbl_todetails.destination',
            //                             'tbl_todetails.purpose',
            //                             'tbl_todetails.otime',
            //                             'tbl_todetails.outsideaor',
            //                             'tbl_todetails.numberoftraveldays',
            //                             'tbl_todetails.airtravel'
            //                         )
            //                         ->where('tbl_todetails.to_status', '1st Verifier')
            //                         ->where('numberoftraveldays', '<', 8)
            //                         ->get();
        }

        foreach($listOfOfficesDetails as $row):
            if($row->countdata == null) {
                $row->countdata = 0;
            }
            $row->id = $row->div_id;
            if($acct_type == 'CENRO') {
                $row->id = $row->sec_id;
            }
            foreach($countDataResultQuery as $countRow):
                if($row->div_name == $countRow->div_name) {
                    $row->countdata = $countRow->countdata;
                }

                if($acct_type == 'CENRO') {
                    if($row->sec_name == $countRow->sec_name) {
                        $row->countdata = $countRow->countdata;
                    }
                }

            endforeach;

			//TEMPORARY RECOM PLANNING UNDER MAM MABEL
			// foreach($countDataResultQueryPlanning as $countRow):
            //     if($row->div_name == $countRow->div_name) {
            //         $row->countdata = $countRow->countdata;
            //     }
            // endforeach;

        endforeach;
		$employeeDetailsQuery = $employeeDetailsQuery->merge($employeeDetailsQueryPlanning);

        $data = [
            'listOfOfficesDetails' => $listOfOfficesDetails,
            'employeeDetailsQuery' => $employeeDetailsQuery,
        ];
        return json_encode($data);
    }

    public function getTravelsForRecommendation($user, $acct_type) {
        $todetails_model = new Tbl_todetails;
        $tbldivision_model = new Tbl_division;
        $tblaccounts_model = new Tbl_accounts;

        $getDeputyCenroQuery = $tbldivision_model->where('deputy_cenro', $user)->first();
        $getOicQuery = $tblaccounts_model->where('oic', $user)->first();

        $result_query = [];
        $cenrosConditions = '';
        if($getDeputyCenroQuery != null) {
            if($getDeputyCenroQuery->deputy_cenro == $user) {
                $result_query = $todetails_model
                                ->join('tbl_empdetails', 'tbl_todetails.emp_id', '=', 'tbl_empdetails.emp_id')
                                ->join('tbl_section', 'tbl_empdetails.sec_id', '=', 'tbl_section.id')
                                ->join('tbl_division', 'tbl_division.id', '=', 'tbl_section.div_id')
                                ->where(function ($query) {
                                    $query->where('tbl_todetails.to_status', '1st Verifier')
                                        ->orWhere('tbl_todetails.to_status', 'S. Chief');
                                })
                                ->where('tbl_division.deputy_cenro', $user)
                                ->where('tbl_todetails.emp_id', '!=', '')
                                ->whereNotIn('tbl_section.id', [120, 121, 122, 123, 124, 125, 126, 127, 128, 129])
                                ->select(
                                    'tbl_todetails.id',
                                    'tbl_empdetails.f_name',
                                    'tbl_empdetails.m_name',
                                    'tbl_empdetails.l_name',
                                    'tbl_empdetails.emp_pos',
                                    'tbl_empdetails.sal',
                                    'tbl_empdetails.emp_id',
                                    'tbl_empdetails.sec_id',
                                    'tbl_section.sec_name',
                                    'tbl_division.div_name',
                                    'tbl_todetails.depart',
                                    'tbl_todetails.arrival',
                                    'tbl_todetails.destination',
                                    'tbl_todetails.purpose',
                                    'tbl_todetails.otime',
                                    'tbl_todetails.outsideaor',
                                    'tbl_todetails.numberoftraveldays',
                                    'tbl_todetails.airtravel'
                                )
                                ->orderBy('tbl_todetails.date_filed')
                                ->get();
            }

        }else if($getOicQuery != null) {
            $result_query = $todetails_model
                            ->join('tbl_empdetails', 'tbl_todetails.emp_id', '=', 'tbl_empdetails.emp_id')
                            ->join('tbl_section', 'tbl_empdetails.sec_id', '=', 'tbl_section.id')
                            ->join('tbl_division', 'tbl_division.id', '=', 'tbl_section.div_id')
                            ->where('tbl_todetails.to_status', '1st Verifier')
                            ->where('tbl_division.emp_id', $getOicQuery->username)
                            ->select(
                                'tbl_todetails.id',
                                'tbl_empdetails.f_name',
                                'tbl_empdetails.m_name',
                                'tbl_empdetails.l_name',
                                'tbl_empdetails.emp_pos',
                                'tbl_empdetails.sal',
                                'tbl_empdetails.emp_id',
                                'tbl_empdetails.sec_id',
                                'tbl_section.sec_name',
                                'tbl_division.div_name',
                                'tbl_todetails.depart',
                                'tbl_todetails.arrival',
                                'tbl_todetails.destination',
                                'tbl_todetails.purpose',
                                'tbl_todetails.otime',
                                'tbl_todetails.outsideaor',
                                'tbl_todetails.numberoftraveldays',
                                'tbl_todetails.airtravel'
                            )
                            ->orderBy('tbl_todetails.date_filed')
                            ->get();

        }else if($acct_type == 'ARD MS' || $acct_type == 'ARD TS') {

        }else {
            if ($user == 'rozele_torres') {
                $cenrosConditions = [
                    ['tbl_division.id', [36, 37], 'tbl_empdetails.emp_id', ['guilryd_cabatuan', 'dennisa_juab']],
                ];
            } elseif ($user == 'ferddiev_sususco') {
                $cenrosConditions = [
                    ['tbl_division.id', [38, 39, 40, 41], 'tbl_empdetails.emp_id', ['moritob_estifano',  'merlital_tabamo', 'edgardog_agbayani', 'albertoa_ecuacion']],
                ];
            } elseif ($user == 'hermosillac_flores') {
                $cenrosConditions = [
                    ['tbl_division.id', [42, 43], 'tbl_empdetails.emp_id', ['bucharya_dimaporo', 'saibenp_mampao']],
                ];
            } elseif ($user == 'hdaensencio') {
                $cenrosConditions = [
                    ['tbl_division.id', [44, 45], 'tbl_empdetails.emp_id', ['jeromeg_pioquinto', 'jamela_tiburon']],
                ];
            }

            $to_status = $acct_type;
            $table_id = 'tbl_section.emp_id';

            if($acct_type == 'D. Chief' || $user == 'jockeye_oclarit' || $acct_type == 'TSD' ) {
                $to_status = '1st Verifier';
                $table_id = 'tbl_division.emp_id';
            }

            if($acct_type == 'PASU' && $user != 'alsond_potutan') {
                $to_status = '1st Verifier';
                $table_id = 'tbl_division.emp_id';
            }

            $result_query = $todetails_model
                                ->join('tbl_empdetails', 'tbl_todetails.emp_id', '=', 'tbl_empdetails.emp_id')
                                ->join('tbl_section', 'tbl_empdetails.sec_id', '=', 'tbl_section.id')
                                ->join('tbl_division', 'tbl_division.id', '=', 'tbl_section.div_id')
                                ->where('tbl_todetails.to_status', $to_status)
                                ->where($table_id, $user)
                                ->select(
                                    'tbl_todetails.id',
                                    'tbl_empdetails.f_name',
                                    'tbl_empdetails.m_name',
                                    'tbl_empdetails.l_name',
                                    'tbl_empdetails.emp_pos',
                                    'tbl_empdetails.sal',
                                    'tbl_empdetails.emp_id',
                                    'tbl_empdetails.sec_id',
                                    'tbl_section.sec_name',
                                    'tbl_division.div_name',
                                    'tbl_todetails.depart',
                                    'tbl_todetails.arrival',
                                    'tbl_todetails.destination',
                                    'tbl_todetails.purpose',
                                    'tbl_todetails.otime',
                                    'tbl_todetails.outsideaor',
                                    'tbl_todetails.numberoftraveldays',
                                    'tbl_todetails.airtravel'
                                )
                                ->orderBy('tbl_todetails.date_filed')
                                ->get();
            if($user == 'rozele_torres' || $user == 'ferddiev_sususco' || $user == 'hermosillac_flores' || $user == 'hdaensencio') {
                $result_query = $todetails_model
                                ->join('tbl_empdetails', 'tbl_todetails.emp_id', '=', 'tbl_empdetails.emp_id')
                                ->join('tbl_section', 'tbl_empdetails.sec_id', '=', 'tbl_section.id')
                                ->join('tbl_division', 'tbl_division.id', '=', 'tbl_section.div_id')
                                ->where('tbl_todetails.to_status', '1st Verifier')
                                ->where(function ($query) use ($user, $cenrosConditions) {
                                    $query->where('tbl_division.emp_id', $user);

                                    // Add dynamic `OR` conditions for `$cenros`
                                    foreach ($cenrosConditions as $condition) {
                                        [$divIdColumn, $divIds, $empIdColumn, $empIds] = $condition;
                                        $query->orWhere(function ($subQuery) use ($divIdColumn, $divIds, $empIdColumn, $empIds) {
                                            $subQuery->whereIn($divIdColumn, $divIds)
                                                    ->whereIn($empIdColumn, $empIds);
                                        });
                                    }
                                })
                                ->select(
                                    'tbl_todetails.id',
                                    'tbl_empdetails.f_name',
                                    'tbl_empdetails.m_name',
                                    'tbl_empdetails.l_name',
                                    'tbl_empdetails.emp_pos',
                                    'tbl_empdetails.sal',
                                    'tbl_empdetails.emp_id',
                                    'tbl_empdetails.sec_id',
                                    'tbl_section.sec_name',
                                    'tbl_division.div_name',
                                    'tbl_todetails.depart',
                                    'tbl_todetails.arrival',
                                    'tbl_todetails.destination',
                                    'tbl_todetails.purpose',
                                    'tbl_todetails.otime',
                                    'tbl_todetails.outsideaor',
                                    'tbl_todetails.numberoftraveldays',
                                    'tbl_todetails.airtravel'
                                )
                                ->orderBy('tbl_todetails.date_filed')
                                ->get();

            }
        }

        return json_encode($result_query);
    }

    public function fileExists(Request $request) {
        $filePath = $request->filePath;
        $result = response()->json(['exists' => false]);

        if (Storage::exists($request->filePath)) {
            $result = response()->json(['exists' => true]);
        }
        return $result;
    }

    private function updateTrack($tbltrack_model, $toId, $level, $empId) {
        $trackQuery = $tbltrack_model->where('to_id', $toId)
                        ->selectRaw('COUNT(*) as countdata')->first();

        if($trackQuery->countdata >= 1) {
            $tbltrack_model->where('to_id', $toId)->update([
                $level => $empId
            ]);

        }else {
            $tbltrack_model = new Tbl_track([
                'to_id' => $toId,
                $level => $empId
            ]);
            $tbltrack_model->save();
        }
    }

    public function recommendTravel(Request $request) {
        $todetails_model = new Tbl_todetails;
        $tbldivision_model = new Tbl_division;
        $tblaccounts_model = new Tbl_accounts;
        $tblempdetails_model = new Tbl_empdetails;
        $tbltrack_model = new Tbl_track;
        $tblapproveto_model = new Tbl_approveto;

        $userDetails = json_decode($this->getUserDetails($request->travelDetails['emp_id']));
        $getDeputyCenroQuery = $tbldivision_model->where('deputy_cenro', $request->userDetails['emp_id'])->first();

        $yearToday = date('Y');
        $monthToday = date("m");

        $tbltostatus_model = [];
        $data = [];

        if($request->userDetails['acct_type'] == 'D. Chief') {
            if($request->travelDetails['numberoftraveldays'] > 30 && $request->travelDetails['sec_id'] < 47) {
                $tbltostatus_model = new Tbl_tostatus([
                    'emp_id' => $request->userDetails['emp_id'],
                    'to_id' => $request->travelDetails['id'],
                    'date_action' => date("D, M d Y h:i:s A"),
                    'remarks' => $request->travelDetails['remarks'],
                    'stat_act' => 'INDORSED',
                ]);
            }else {
                $tbltostatus_model = new Tbl_tostatus([
                    'emp_id' => $request->userDetails['emp_id'],
                    'to_id' => $request->travelDetails['id'],
                    'date_action' => date("D, M d Y h:i:s A"),
                    'remarks' => $request->travelDetails['remarks'],
                    'stat_act' => 'R. APPROVED',
                ]);
            }
            $tbltostatus_model->save();

            if($request->travelDetails['emp_id'] == 'mmparagas') {
                $todetails_model->find($request->travelDetails['id'])->update([
                    'to_status' => 'RED'
                ]);

            }else {
                if($request->travelDetails['numberoftraveldays'] <= 7 && $request->travelDetails['sec_id'] < 47 && ($userDetails->acct_type == 'S. Chief' || $userDetails->acct_type == 'user') ) {
                    $todetails_model->find($request->travelDetails['id'])->update([
                        'to_status' => 'ARD-P-APPROVAL'
                    ]);
                }else {
                    $todetails_model->find($request->travelDetails['id'])->update([
                        'to_status' => 'ARD'
                    ]);
                }

                $this->updateTrack($tbltrack_model, $request->travelDetails['id'], '2nd', $request->userDetails['emp_id']);

            }

        }else if($request->userDetails['acct_type'] == 'S. Chief') {
            if($request->userDetails['emp_id'] == 'tbabarrientos' || $request->userDetails['emp_id'] == 'ccacta') {
                $todetails_model->find($request->travelDetails['id'])->update([
                    'to_status' => 'S. Chief SAO'
                ]);

            }else if(@$getDeputyCenroQuery->deputy_cenro == $request->userDetails['emp_id']) {
                if($request->travelDetails['numberoftraveldays'] > 30 && $request->travelDetails['sec_id'] >= 47) {
                    $todetails_model->find($request->travelDetails['id'])->update([
                        'to_status' => 'PENRO'
                    ]);

                }else {
                    $todetails_model->find($request->travelDetails['id'])->update([
                        'to_status' => 'CENRO'
                    ]);
                }

            }else {
                if($request->travelDetails['outsideaor'] == 1 && $request->travelDetails['numberoftraveldays'] <= 7 && $request->travelDetails['sec_id'] >= 90) {
                    $todetails_model->find($request->travelDetails['id'])->update([
                        'to_status' => 'CENRO'
                    ]);

                }else if($request->travelDetails['outsideaor'] == 1 && $request->travelDetails['numberoftraveldays'] <= 7 && ($request->travelDetails['sec_id'] >= 47 && $request->travelDetails['sec_id'] < 90)) {
                    $todetails_model->find($request->travelDetails['id'])->update([
                        'to_status' => 'PENRO'
                    ]);

                }else if($request->travelDetails['numberoftraveldays'] > 30 && $request->travelDetails['sec_id'] >= 47) {
                    $todetails_model->find($request->travelDetails['id'])->update([
                        'to_status' => 'PENRO'
                    ]);

                }else {
                    $todetails_model->find($request->travelDetails['id'])->update([
                        'to_status' => '1st Verifier'
                    ]);

                }

            }

            $stat_act = 'R. APPROVED';
            if(@$getDeputyCenroQuery->deputy_cenro == $request->userDetails['emp_id']) {

                if($request->travelDetails['outsideaor'] == 1 && $request->travelDetails['numberoftraveldays'] <= 7) {
                    $stat_act = 'R. APPROVED';

                }else if($request->travelDetails['numberoftraveldays'] > 30 && $request->travelDetails['sec_id'] >= 47) {
                    $stat_act = 'INDORSED';

                }

            }else {
                $stat_act = 'INDORSED';
                if($request->travelDetails['outsideaor'] == 1 && $request->travelDetails['numberoftraveldays'] <= 7 && $request->travelDetails['sec_id'] >= 47) {
                    $stat_act = 'R. APPROVED';
                }
            }
            // CENRO OROQUIETA CITY NO DEPUTY CENRO, S.CHIEF RECOMMENDS

                if(($userDetails->sec_id == 90 || $userDetails->sec_id == 100 || $userDetails->sec_id == 110) && $request->travelDetails['numberoftraveldays'] <= 7) {
                    $todetails_model->find($request->travelDetails['id'])->update([
                        'to_status' => 'CENRO'
                    ]);
                    $stat_act = 'R. APPROVED';

                }

            // CENRO OROQUIETA CITY NO DEPUTY CENRO, S.CHIEF RECOMMENDS

            $tbltostatus_model = new Tbl_tostatus([
                'emp_id' => $request->userDetails['emp_id'],
                'to_id' => $request->travelDetails['id'],
                'remarks' => $request->travelDetails['remarks'],
                'stat_act' => $stat_act
            ]);
            $tbltostatus_model->save();

            $this->updateTrack($tbltrack_model, $request->travelDetails['id'], '1st', $request->userDetails['emp_id']);

        }else if($request->userDetails['acct_type'] == 'ARD MS' || $request->userDetails ['acct_type'] == 'ARD TS') {
            $stat_act = 'R. APPROVED';
            $to_status = 'RED';
            if($request->travelDetails['numberoftraveldays'] <=  7 && $request->travelDetails['sec_id'] < 47 && ($userDetails->acct_type == 'S. Chief' || $userDetails->acct_type == 'user') ) {
                $to_status = 'APPROVED';
                $stat_act = 'APPROVED';

			    //TEMPORARY RECOM PLANNING UNDER MAM MABEL
                // if($request->userDetails ['acct_type'] == 'ARD TS' && ($request->travelDetails['sec_id'] == 1 || $request->travelDetails['sec_id'] == 2 || $request->travelDetails['sec_id'] == 3 )) {
                //     $to_status = 'ARD-P-APPROVAL';
                //     $stat_act = 'R. APPROVED';
                // }

                $approveQuery = $tblapproveto_model->where('to_year', $yearToday)
                            ->selectRaw('COUNT(*) as countdata')->first();

                $total = 1;
                if($approveQuery->countdata >= 1) {
                    $approveQueryWithMonth = $tblapproveto_model->where('to_year', $yearToday)
                            ->where('to_month', $monthToday)
                            ->selectRaw('COUNT(*) as countdata')->first();

                    if($approveQueryWithMonth->countdata >= 1) {
                        $total = $approveQueryWithMonth->countdata + 1;

                    }

                }

                $tblapproveto_model = new Tbl_approveto([
                    'to_id' => $request->travelDetails['id'],
                    'to_year' => $yearToday,
                    'to_month' => $monthToday,
                    'to_no' => $total,
                    'to_seen' => 0,
                    'to_cancel' => 0
                ]);
                $tblapproveto_model->save();

            }

            $todetails_model->find($request->travelDetails['id'])->update([
                'to_status' => $to_status
            ]);

            $tbltostatus_model = new Tbl_tostatus([
                'emp_id' => $request->userDetails['emp_id'],
                'to_id' => $request->travelDetails['id'],
                'remarks' => $request->travelDetails['remarks'],
                'stat_act' => $stat_act
            ]);
            $tbltostatus_model->save();

            $this->updateTrack($tbltrack_model, $request->travelDetails['id'], '3rd', $request->userDetails['emp_id']);


        }else if($request->userDetails['acct_type'] == 'MSD' || $request->userDetails['acct_type'] == 'TSD' || $request->userDetails['acct_type'] == 'PASU') {
            $stat_act = 'R. APPROVED';

            if($request->travelDetails['numberoftraveldays'] > 30) {
                $stat_act = 'INDORSED';
            }

            $todetails_model->find($request->travelDetails['id'])->update([
                'to_status' => 'PENRO'
            ]);

            $tbltostatus_model = new Tbl_tostatus([
                'emp_id' => $request->userDetails['emp_id'],
                'to_id' => $request->travelDetails['id'],
                'remarks' => $request->travelDetails['remarks'],
                'stat_act' => $stat_act
            ]);
            $tbltostatus_model->save();

            $this->updateTrack($tbltrack_model, $request->travelDetails['id'], '4th', $request->userDetails['emp_id']);


        }else if($request->userDetails['acct_type'] == 'HEA') {
            $todetails_model->find($request->travelDetails['id'])->update([
                'to_status' => 'RED'
            ]);

            $tbltostatus_model = new Tbl_tostatus([
                'emp_id' => $request->userDetails['emp_id'],
                'to_id' => $request->travelDetails['id'],
                'remarks' => $request->travelDetails['remarks'],
                'stat_act' => 'R. APPROVED'
            ]);
            $tbltostatus_model->save();

            $this->updateTrack($tbltrack_model, $request->travelDetails['id'], '3rd', $request->userDetails['emp_id']);


        }else if($request->userDetails['acct_type'] == 'PENRO' || $request->userDetails['acct_type'] == 'CENRO') {
            $stat_act = 'APPROVED';
            $to_status = 'APPROVED';

            if($request->travelDetails['numberoftraveldays'] > 30 && ($request->travelDetails['emp_id'] == 'MSD' || $request->travelDetails['emp_id'] == 'TSD') ) {
                $stat_act = 'R. APPROVED';
                $to_status = 'RED';

            }else if($request->travelDetails['numberoftraveldays'] > 30 && $request->userDetails['acct_type'] == 'PENRO') {
                $stat_act = 'INDORSED';
                $to_status = 'ARD';

            }else if($request->travelDetails['numberoftraveldays'] > 30 && $request->userDetails['acct_type'] == 'CENRO') {
                $stat_act = 'INDORSED';
                $to_status = 'PENRO';

            }

            $todetails_model->find($request->travelDetails['id'])->update([
                'to_status' => $to_status
            ]);
            $tbltostatus_model = new Tbl_tostatus([
                'emp_id' => $request->userDetails['emp_id'],
                'to_id' => $request->travelDetails['id'],
                'remarks' => $request->travelDetails['remarks'],
                'stat_act' => $stat_act
            ]);
            $tbltostatus_model->save();

            $approveQuery = $tblapproveto_model->whereRaw('to_year', $yearToday)
                            ->selectRaw('COUNT(*) as countdata')->first();
            $total = 1;
            if($approveQuery->countdata >= 1) {
                $approveQueryWithMonth = $tblapproveto_model->where('to_year', $yearToday)
                        ->where('to_month', $monthToday)
                        ->selectRaw('COUNT(*) as countdata')->first();

                if($approveQueryWithMonth->countdata >= 1) {
                    $total = $approveQueryWithMonth->countdata + 1;

                }

            }

            $tblapproveto_model = new Tbl_approveto([
                'to_id' => $request->travelDetails['id'],
                'to_year' => $yearToday,
                'to_month' => $monthToday,
                'to_no' => $total,
                'to_seen' => 0,
                'to_cancel' => 0
            ]);
            $tblapproveto_model->save();

        }else if($request->userDetails['acct_type'] == 'RED') {
            $todetails_model->find($request->travelDetails['id'])->update([
                'to_status' => 'APPROVED'
            ]);
            $tbltostatus_model = new Tbl_tostatus([
                'emp_id' => $request->userDetails['emp_id'],
                'to_id' => $request->travelDetails['id'],
                'remarks' => $request->travelDetails['remarks'],
                'stat_act' => 'APPROVED'
            ]);
            $tbltostatus_model->save();

            $approveQuery = $tblapproveto_model->whereRaw('to_year', $yearToday)
                            ->selectRaw('COUNT(*) as countdata')->first();
            $total = 1;
            if($approveQuery->countdata >= 1) {
                $approveQueryWithMonth = $tblapproveto_model->where('to_year', $yearToday)
                        ->where('to_month', $monthToday)
                        ->selectRaw('COUNT(*) as countdata')->first();

                if($approveQueryWithMonth->countdata >= 1) {
                    $total = $approveQueryWithMonth->countdata + 1;

                }

            }

        }

        $this->updateTrack($tbltrack_model, $request->travelDetails['id'], '5th', $request->userDetails['emp_id']);

        $result = [
            'message' => 'success'
        ];

        return json_encode($result);
    }

    public function deleteTravel(Request $request) {
        $todetails_model = new Tbl_todetails;
        $userDetails = json_decode($this->getUserDetails($request->travelDetails['emp_id']));

        $to_status = '';
        if($userDetails->acct_type == 'RED') {
            $to_status = 'ARD/DISAPPROVED';
        }else {
            if($request->userDetails['acct_type'] == 'ARD MS' || $request->userDetails['acct_type'] == 'ARD TS' || $request->userDetails['acct_type'] == 'PENRO' || $request->userDetails['acct_type'] == 'CENRO') {
                $to_status = 'ARD/DISAPPROVED';

              }else if($request->userDetails['acct_type'] == 'D. Chief') {
                $to_status = 'D. Chief/DISAPPROVED';

              }else if($request->userDetails['acct_type'] == 'HEA') {
                $to_status = 'HEA/DISAPPROVED';

              }else if($request->userDetails['acct_type'] == 'MSD' || $request->userDetails['acct_type'] == 'TSD') {
                $to_status = $request->userDetails['acct_type'].'/DISAPPROVED';

              }else if($request->userDetails['acct_type'] == 'S. Chief') {
                $to_status = $request->userDetails['acct_type'].'/DISAPPROVED';

              }
        }

        $tbltostatus_model = new Tbl_tostatus([
            'emp_id' => $request->userDetails['emp_id'],
            'to_id' => $request->travelDetails['id'],
            'date_action' => date("D, M d Y h:i:s A"),
            'remarks' => $request->travelDetails['remarks'],
            'stat_act' => $to_status,
        ]);
        $tbltostatus_model->save();

        $todetails_model->find($request->travelDetails['id'])->update([
            'to_status' => $to_status
        ]);


        $result = [
            'message' => 'error'
        ];
        if($tbltostatus_model) {
            $result = [
                'message' => 'success'
            ];
        }

        return json_encode($result);
    }

    public function approveTravel(Request $request) {
        $todetails_model = new Tbl_todetails;
        $tbldivision_model = new Tbl_division;
        $tbltrack_model = new Tbl_track;
        $tblapproveto_model = new Tbl_approveto;


        $yearToday = date('Y');
        $monthToday = date("m");

        $todetails_model->find($request->travelDetails['id'])->update([
            'to_status' => 'APPROVED'
        ]);

        $tbltostatus_model = new Tbl_tostatus([
            'emp_id' => $request->userDetails['emp_id'],
            'to_id' => $request->travelDetails['id'],
            'date_action' => date("D, M d Y h:i:s A"),
            'remarks' => $request->travelDetails['remarks'],
            'stat_act' => 'APPROVED',
        ]);

        $tbltostatus_model->save();

        $approveQuery = $tblapproveto_model->where('to_year', $yearToday)
                        ->selectRaw('COUNT(*) as countdata')->first();
        $total = 1;
        if($approveQuery->countdata >= 1) {
            $approveQueryWithMonth = $tblapproveto_model->where('to_year', $yearToday)
                    ->where('to_month', $monthToday)
                    ->selectRaw('COUNT(*) as countdata')->first();

            if($approveQueryWithMonth->countdata >= 1) {
                $total = $approveQueryWithMonth->countdata + 1;

            }

        }

        $tblapproveto_model = new Tbl_approveto([
            'to_id' => $request->travelDetails['id'],
            'to_year' => $yearToday,
            'to_month' => $monthToday,
            'to_no' => $total,
            'to_seen' => 0,
            'to_cancel' => 0
        ]);
        $tblapproveto_model->save();

        $this->updateTrack($tbltrack_model, $request->travelDetails['id'], '5th', $request->userDetails['emp_id']);

        $result = [
            'message' => 'success'
        ];

        return json_encode($result);

    }

    public function getTravelsForApproval($user, $acct_type) {
        $todetails_model = new Tbl_todetails;
        $tbloffice_model = new View_office;

        $officeDetails = $tbloffice_model->where('emp_id', $user)->first();

        if($acct_type == 'PASU') {
            $data = $todetails_model
                            ->join('tbl_empdetails', 'tbl_todetails.emp_id', '=', 'tbl_empdetails.emp_id')
                            ->join('tbl_section', 'tbl_empdetails.sec_id', '=', 'tbl_section.id')
                            ->join('tbl_division', 'tbl_division.id', '=', 'tbl_section.div_id')
                            ->where('tbl_todetails.to_status', 'PASU')
                            ->where('tbl_division.emp_id', $user)
                            ->select(
                                'tbl_todetails.id',
                                'tbl_empdetails.f_name',
                                'tbl_empdetails.m_name',
                                'tbl_empdetails.l_name',
                                'tbl_empdetails.emp_pos',
                                'tbl_empdetails.sal',
                                'tbl_empdetails.emp_id',
                                'tbl_empdetails.sec_id',
                                'tbl_section.sec_name',
                                'tbl_division.div_name',
                                'tbl_todetails.depart',
                                'tbl_todetails.arrival',
                                'tbl_todetails.destination',
                                'tbl_todetails.purpose',
                                'tbl_todetails.otime',
                                'tbl_todetails.outsideaor',
                                'tbl_todetails.numberoftraveldays',
                                'tbl_todetails.airtravel'
                            )
                            ->orderBy('tbl_todetails.date_filed')
                            ->get();

        }else if($acct_type == 'ARD MS' || $acct_type == 'ARD TS' || $acct_type == 'RED') {
            $listOfOfficesDetails = $tbloffice_model->select('div_id')
                        ->select('div_name')
                        ->where('tgroup', $officeDetails->tgroup)
                        ->groupBy('div_id')
                        ->groupBy('div_name')
                        ->get();
            $whereCondition1st = 'tbl_office.tgroup';
            $whereCondition2nd = $officeDetails->tgroup;
            $groupByCondition = 'tbl_division.id';
            $selectQuery = 'tbl_division.div_name, tbl_division.id, COUNT(*) as countdata';

            $to_status = 'ARD-P-APPROVAL';




            $countDataResultQuery = $todetails_model
                                    ->join('tbl_empdetails', 'tbl_todetails.emp_id', '=', 'tbl_empdetails.emp_id')
                                    ->join('tbl_section', 'tbl_empdetails.sec_id', '=', 'tbl_section.id')
                                    ->join('tbl_division', 'tbl_division.id', '=', 'tbl_section.div_id')
                                    ->join('tbl_office', DB::raw('CAST(tbl_office.id AS TEXT)'), '=', 'tbl_division.off_id')
                                    ->where($whereCondition1st, $whereCondition2nd)
                                    ->where('tbl_todetails.to_status', $to_status)
                                    ->selectRaw($selectQuery)
                                    ->groupBy($groupByCondition)
                                    ->get();

            if($acct_type == 'RED') {
                $to_status = 'RED';
                $listOfOfficesDetails = $tbloffice_model->select('id as div_id')
                        ->select('office_name as div_name')
                        ->groupBy('id')
                        ->groupBy('office_name')
                        ->orderBy('id')
                        ->get();

                $countDataResultQuery = $todetails_model
                ->join('tbl_empdetails', 'tbl_todetails.emp_id', '=', 'tbl_empdetails.emp_id')
                ->join('tbl_section', 'tbl_empdetails.sec_id', '=', 'tbl_section.id')
                ->join('tbl_division', 'tbl_division.id', '=', 'tbl_section.div_id')
                ->join('tbl_office', DB::raw('CAST(tbl_office.id AS TEXT)'), '=', 'tbl_division.off_id')
                ->where('to_status', 'RED')
                ->selectRaw('tbl_office.office_name AS div_name, tbl_office.id, COUNT(*) AS countdata')
                ->groupBy('tbl_office.id', 'tbl_office.office_name')
                ->get();
            }

            $employeeDetailsQuery = $todetails_model
                                        ->join('tbl_empdetails', 'tbl_todetails.emp_id', '=', 'tbl_empdetails.emp_id')
                                        ->join('tbl_section', 'tbl_empdetails.sec_id', '=', 'tbl_section.id')
                                        ->join('tbl_division', 'tbl_division.id', '=', 'tbl_section.div_id')
                                        ->join('tbl_office', DB::raw('CAST(tbl_office.id AS TEXT)'), '=', 'tbl_division.off_id')
                                        ->select(
                                            'tbl_todetails.id',
                                            'tbl_empdetails.f_name',
                                            'tbl_empdetails.m_name',
                                            'tbl_empdetails.l_name',
                                            'tbl_empdetails.emp_pos',
                                            'tbl_empdetails.sal',
                                            'tbl_empdetails.emp_id',
                                            'tbl_empdetails.sec_id',
                                            'tbl_office.office_name',
                                            'tbl_section.sec_name',
                                            'tbl_division.div_name',
                                            'tbl_division.id as div_id',
                                            'tbl_division.emp_id as div_emp_id',
                                            'tbl_todetails.depart',
                                            'tbl_todetails.arrival',
                                            'tbl_todetails.destination',
                                            'tbl_todetails.purpose',
                                            'tbl_todetails.otime',
                                            'tbl_todetails.outsideaor',
                                            'tbl_todetails.numberoftraveldays',
                                            'tbl_todetails.airtravel'
                                        )
                                        ->where('tbl_todetails.to_status', $to_status)
                                        ->get();
            foreach($listOfOfficesDetails as $row):
                if($row->countdata == null) {
                    $row->countdata = 0;
                }
                $row->id = $row->div_id;
                foreach($countDataResultQuery as $countRow):
                    if($row->div_name == $countRow->div_name) {
                        $row->countdata = $countRow->countdata;
                    }

                endforeach;
            endforeach;
            $data = [
                'listOfOfficesDetails' => $listOfOfficesDetails,
                'employeeDetailsQuery' => $employeeDetailsQuery,
            ];
        }

        return json_encode($data);

    }

    public function getOffice() {
        $tbloffice_model = new View_office;

        $data = [
            'office' => [],
            'division' => [],
            'section' => []
        ];

        $data['office'] = $tbloffice_model
                            ->selectRaw('id, office_name')
                            ->groupBy('id')
                            ->groupBy('office_name')
                            ->orderBy('id')
                            ->get();

        $data['division'] = $tbloffice_model
                            ->selectRaw('id, div_id, div_name')
                            ->groupBy('div_id')
                            ->groupBy('div_name')
                            ->groupBy('id')
                            ->orderBy('div_id')
                            ->get();

        $data['section'] = $tbloffice_model
                            ->selectRaw('id, div_id, sec_id, sec_name')
                            ->groupBy('sec_id')
                            ->groupBy('sec_name')
                            ->groupBy('div_id')
                            ->groupBy('id')
                            ->orderBy('sec_id')
                            ->get();

        return json_encode($data);
    }

    // EOLS API

        public function readTo() {
            $todetails_model = new Tbl_todetails;


            $results['records'] = $todetails_model::select(
                DB::raw("
                    CONCAT(
                        LPAD(CAST(FLOOR(EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP))) / 3600) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 3600) / 60) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 60)) AS TEXT), 2, '0')
                    ) AS avgtime
                "),
                'tbl_todetails.id',
                '_ts.date_action',
                DB::raw("CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS datefiled"), // Handle '0000-00-00' as NULL
                '_div.div_name AS division',
                DB::raw("CONCAT(emp.f_name, ' ', emp.l_name) AS fullname"),
                DB::raw("CONCAT(TO_CHAR(CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE), 'FMMonth FMDD YYYY'), ' - ', TO_CHAR(CAST(arrival AS DATE), 'FMMonth FMDD YYYY')) AS dateoftravel"),
                'destination',
                'purpose',
                DB::raw("CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS depart"), // Handle '0000-00-00' as NULL in select
                'airtravel',
                'tbl_todetails.emp_id'
            )
            ->join('tbl_empdetails AS emp', 'emp.emp_id', '=', 'tbl_todetails.emp_id')
            ->join('tbl_section AS _sec', 'emp.sec_id', '=', '_sec.id')
            ->join('tbl_division AS _div', '_div.id', '=', '_sec.div_id')
            ->join('tbl_office AS _off', DB::raw('CAST(_div.off_id AS INTEGER)'), '=', '_off.id')
            ->join('tbl_tostatus AS _ts', function($join) {
                $join->on('tbl_todetails.id', '=', '_ts.to_id')
                     ->where('_ts.stat_act', '=', 'APPROVED');
            })
            ->where('tbl_todetails.to_status', '=', 'APPROVED')
            ->where(DB::raw("EXTRACT(YEAR FROM CURRENT_DATE) = EXTRACT(YEAR FROM CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE))"))
            ->where(DB::raw("EXTRACT(WEEK FROM CURRENT_DATE) = EXTRACT(WEEK FROM CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE))"))
            ->orderByDesc('tbl_todetails.depart')
            ->get();

            return json_encode($results);
        }

        public function weekTOmisoc() {
            $todetails_model = new Tbl_todetails;

            $results['records'] = $todetails_model::select(
                DB::raw("
                    CONCAT(
                        LPAD(CAST(FLOOR(EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP))) / 3600) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 3600) / 60) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 60)) AS TEXT), 2, '0')
                    ) AS avgtime
                "),
                'tbl_todetails.id',
                '_ts.date_action',
                DB::raw("CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS datefiled"), // Handle '0000-00-00' as NULL
                '_div.div_name AS division',
                DB::raw("CONCAT(emp.f_name, ' ', emp.l_name) AS fullname"),
                DB::raw("CONCAT(TO_CHAR(CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE), 'FMMonth FMDD YYYY'), ' - ', TO_CHAR(CAST(arrival AS DATE), 'FMMonth FMDD YYYY')) AS dateoftravel"),
                'destination',
                'purpose',
                DB::raw("CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS depart"), // Handle '0000-00-00' as NULL in select
                'airtravel',
                'tbl_todetails.emp_id'
            )
            ->join('tbl_empdetails AS emp', 'emp.emp_id', '=', 'tbl_todetails.emp_id')
            ->join('tbl_section AS _sec', 'emp.sec_id', '=', '_sec.id')
            ->join('tbl_division AS _div', '_div.id', '=', '_sec.div_id')
            ->join('tbl_office AS _off', DB::raw('CAST(_div.off_id AS INTEGER)'), '=', '_off.id')
            ->join('tbl_tostatus AS _ts', function($join) {
                $join->on('tbl_todetails.id', '=', '_ts.to_id')
                     ->where('_ts.stat_act', '=', 'APPROVED');
            })
            ->where('tbl_todetails.to_status', '=', 'APPROVED')
            ->where('_off.office_name', 'LIKE', '%Misamis Occidental%')
            ->where(DB::raw("EXTRACT(YEAR FROM CURRENT_DATE) = EXTRACT(YEAR FROM CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE))"))
            ->where(DB::raw("EXTRACT(WEEK FROM CURRENT_DATE) = EXTRACT(WEEK FROM CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE))"))
            ->orderByDesc('tbl_todetails.depart')
            ->get();

            return json_encode($results);
        }

        public function weekTOmisor() {
            $todetails_model = new Tbl_todetails;

            $results['records'] = $todetails_model::select(
                DB::raw("
                    CONCAT(
                        LPAD(CAST(FLOOR(EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP))) / 3600) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 3600) / 60) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 60)) AS TEXT), 2, '0')
                    ) AS avgtime
                "),
                'tbl_todetails.id',
                '_ts.date_action',
                DB::raw("CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS datefiled"), // Handle '0000-00-00' as NULL
                '_div.div_name AS division',
                DB::raw("CONCAT(emp.f_name, ' ', emp.l_name) AS fullname"),
                DB::raw("CONCAT(TO_CHAR(CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE), 'FMMonth FMDD YYYY'), ' - ', TO_CHAR(CAST(arrival AS DATE), 'FMMonth FMDD YYYY')) AS dateoftravel"),
                'destination',
                'purpose',
                DB::raw("CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS depart"), // Handle '0000-00-00' as NULL in select
                'airtravel',
                'tbl_todetails.emp_id'
            )
            ->join('tbl_empdetails AS emp', 'emp.emp_id', '=', 'tbl_todetails.emp_id')
            ->join('tbl_section AS _sec', 'emp.sec_id', '=', '_sec.id')
            ->join('tbl_division AS _div', '_div.id', '=', '_sec.div_id')
            ->join('tbl_office AS _off', DB::raw('CAST(_div.off_id AS INTEGER)'), '=', '_off.id')
            ->join('tbl_tostatus AS _ts', function($join) {
                $join->on('tbl_todetails.id', '=', '_ts.to_id')
                     ->where('_ts.stat_act', '=', 'APPROVED');
            })
            ->where('tbl_todetails.to_status', '=', 'APPROVED')
            ->where('_off.office_name', 'LIKE', '%Misamis Oriental%')
            ->where(DB::raw("EXTRACT(YEAR FROM CURRENT_DATE) = EXTRACT(YEAR FROM CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE))"))
            ->where(DB::raw("EXTRACT(WEEK FROM CURRENT_DATE) = EXTRACT(WEEK FROM CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE))"))
            ->orderByDesc('tbl_todetails.depart')
            ->get();

            return json_encode($results);
        }

        public function weekTOlanao() {
            $todetails_model = new Tbl_todetails;

            $results['records'] = $todetails_model::select(
                DB::raw("
                    CONCAT(
                        LPAD(CAST(FLOOR(EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP))) / 3600) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 3600) / 60) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 60)) AS TEXT), 2, '0')
                    ) AS avgtime
                "),
                'tbl_todetails.id',
                '_ts.date_action',
                DB::raw("CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS datefiled"), // Handle '0000-00-00' as NULL
                '_div.div_name AS division',
                DB::raw("CONCAT(emp.f_name, ' ', emp.l_name) AS fullname"),
                DB::raw("CONCAT(TO_CHAR(CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE), 'FMMonth FMDD YYYY'), ' - ', TO_CHAR(CAST(arrival AS DATE), 'FMMonth FMDD YYYY')) AS dateoftravel"),
                'destination',
                'purpose',
                DB::raw("CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS depart"), // Handle '0000-00-00' as NULL in select
                'airtravel',
                'tbl_todetails.emp_id'
            )
            ->join('tbl_empdetails AS emp', 'emp.emp_id', '=', 'tbl_todetails.emp_id')
            ->join('tbl_section AS _sec', 'emp.sec_id', '=', '_sec.id')
            ->join('tbl_division AS _div', '_div.id', '=', '_sec.div_id')
            ->join('tbl_office AS _off', DB::raw('CAST(_div.off_id AS INTEGER)'), '=', '_off.id')
            ->join('tbl_tostatus AS _ts', function($join) {
                $join->on('tbl_todetails.id', '=', '_ts.to_id')
                     ->where('_ts.stat_act', '=', 'APPROVED');
            })
            ->where('tbl_todetails.to_status', '=', 'APPROVED')
            ->where('_off.office_name', 'LIKE', '%Lanao%')
            ->where(DB::raw("EXTRACT(YEAR FROM CURRENT_DATE) = EXTRACT(YEAR FROM CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE))"))
            ->where(DB::raw("EXTRACT(WEEK FROM CURRENT_DATE) = EXTRACT(WEEK FROM CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE))"))
            ->orderByDesc('tbl_todetails.depart')
            ->get();

            return json_encode($results);
        }

        public function weekTOcamiguin() {
            $todetails_model = new Tbl_todetails;


            $results['records'] = $todetails_model::select(
                DB::raw("
                    CONCAT(
                        LPAD(CAST(FLOOR(EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP))) / 3600) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 3600) / 60) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 60)) AS TEXT), 2, '0')
                    ) AS avgtime
                "),
                'tbl_todetails.id',
                '_ts.date_action',
                DB::raw("CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS datefiled"), // Handle '0000-00-00' as NULL
                '_div.div_name AS division',
                DB::raw("CONCAT(emp.f_name, ' ', emp.l_name) AS fullname"),
                DB::raw("CONCAT(TO_CHAR(CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE), 'FMMonth FMDD YYYY'), ' - ', TO_CHAR(CAST(arrival AS DATE), 'FMMonth FMDD YYYY')) AS dateoftravel"),
                'destination',
                'purpose',
                DB::raw("CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS depart"), // Handle '0000-00-00' as NULL in select
                'airtravel',
                'tbl_todetails.emp_id'
            )
            ->join('tbl_empdetails AS emp', 'emp.emp_id', '=', 'tbl_todetails.emp_id')
            ->join('tbl_section AS _sec', 'emp.sec_id', '=', '_sec.id')
            ->join('tbl_division AS _div', '_div.id', '=', '_sec.div_id')
            ->join('tbl_office AS _off', DB::raw('CAST(_div.off_id AS INTEGER)'), '=', '_off.id')
            ->join('tbl_tostatus AS _ts', function($join) {
                $join->on('tbl_todetails.id', '=', '_ts.to_id')
                     ->where('_ts.stat_act', '=', 'APPROVED');
            })
            ->where('tbl_todetails.to_status', '=', 'APPROVED')
            ->where('_off.office_name', 'LIKE', '%Camiguin%')
            ->where(DB::raw("EXTRACT(YEAR FROM CURRENT_DATE) = EXTRACT(YEAR FROM CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE))"))
            ->where(DB::raw("EXTRACT(WEEK FROM CURRENT_DATE) = EXTRACT(WEEK FROM CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE))"))
            ->orderByDesc('tbl_todetails.depart')
            ->get();

            return json_encode($results);
        }

        public function weekTObukidnon() {
            $todetails_model = new Tbl_todetails;

            $results['records'] = $todetails_model::select(
                DB::raw("
                    CONCAT(
                        LPAD(CAST(FLOOR(EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP))) / 3600) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 3600) / 60) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 60)) AS TEXT), 2, '0')
                    ) AS avgtime
                "),
                'tbl_todetails.id',
                '_ts.date_action',
                DB::raw("CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS datefiled"), // Handle '0000-00-00' as NULL
                '_div.div_name AS division',
                DB::raw("CONCAT(emp.f_name, ' ', emp.l_name) AS fullname"),
                DB::raw("CONCAT(TO_CHAR(CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE), 'FMMonth FMDD YYYY'), ' - ', TO_CHAR(CAST(arrival AS DATE), 'FMMonth FMDD YYYY')) AS dateoftravel"),
                'destination',
                'purpose',
                DB::raw("CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS depart"), // Handle '0000-00-00' as NULL in select
                'airtravel',
                'tbl_todetails.emp_id'
            )
            ->join('tbl_empdetails AS emp', 'emp.emp_id', '=', 'tbl_todetails.emp_id')
            ->join('tbl_section AS _sec', 'emp.sec_id', '=', '_sec.id')
            ->join('tbl_division AS _div', '_div.id', '=', '_sec.div_id')
            ->join('tbl_office AS _off', DB::raw('CAST(_div.off_id AS INTEGER)'), '=', '_off.id')
            ->join('tbl_tostatus AS _ts', function($join) {
                $join->on('tbl_todetails.id', '=', '_ts.to_id')
                     ->where('_ts.stat_act', '=', 'APPROVED');
            })
            ->where('tbl_todetails.to_status', '=', 'APPROVED')
            ->where('_off.office_name', 'LIKE', '%Bukidnon%')
            ->where(DB::raw("EXTRACT(YEAR FROM CURRENT_DATE) = EXTRACT(YEAR FROM CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE))"))
            ->where(DB::raw("EXTRACT(WEEK FROM CURRENT_DATE) = EXTRACT(WEEK FROM CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE))"))
            ->orderByDesc('tbl_todetails.depart')
            ->get();

            return json_encode($results);
        }

        public function weekTOregion() {
            $todetails_model = new Tbl_todetails;

            $results['records'] = $todetails_model::select(
                DB::raw("
                    CONCAT(
                        LPAD(CAST(FLOOR(EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP))) / 3600) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 3600) / 60) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 60)) AS TEXT), 2, '0')
                    ) AS avgtime
                "),
                'tbl_todetails.id',
                '_ts.date_action',
                DB::raw("CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS datefiled"), // Handle '0000-00-00' as NULL
                '_div.div_name AS division',
                DB::raw("CONCAT(emp.f_name, ' ', emp.l_name) AS fullname"),
                DB::raw("CONCAT(TO_CHAR(CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE), 'FMMonth FMDD YYYY'), ' - ', TO_CHAR(CAST(arrival AS DATE), 'FMMonth FMDD YYYY')) AS dateoftravel"),
                'destination',
                'purpose',
                DB::raw("CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS depart"), // Handle '0000-00-00' as NULL in select
                'airtravel',
                'tbl_todetails.emp_id'
            )
            ->join('tbl_empdetails AS emp', 'emp.emp_id', '=', 'tbl_todetails.emp_id')
            ->join('tbl_section AS _sec', 'emp.sec_id', '=', '_sec.id')
            ->join('tbl_division AS _div', '_div.id', '=', '_sec.div_id')
            ->join('tbl_office AS _off', DB::raw('CAST(_div.off_id AS INTEGER)'), '=', '_off.id')
            ->join('tbl_tostatus AS _ts', function($join) {
                $join->on('tbl_todetails.id', '=', '_ts.to_id')
                     ->where('_ts.stat_act', '=', 'APPROVED');
            })
            ->where('tbl_todetails.to_status', '=', 'APPROVED')
            ->where('_off.id', '<=', 3)
            ->where(DB::raw("EXTRACT(YEAR FROM CURRENT_DATE) = EXTRACT(YEAR FROM CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE))"))
            ->where(DB::raw("EXTRACT(WEEK FROM CURRENT_DATE) = EXTRACT(WEEK FROM CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE))"))
            ->orderByDesc('tbl_todetails.depart')
            ->get();

            return json_encode($results);
        }

        public function readAllTo() {
            $todetails_model = new Tbl_todetails;
            $year = date('Y');


            $results['records'] = $todetails_model::select(
                DB::raw("
                    CONCAT(
                        LPAD(CAST(FLOOR(EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP))) / 3600) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 3600) / 60) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 60)) AS TEXT), 2, '0')
                    ) AS avgtime
                "),
                'tbl_todetails.id',
                '_ts.date_action',
                DB::raw("CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS datefiled"), // Handle '0000-00-00' as NULL
                '_div.div_name AS division',
                DB::raw("CONCAT(emp.f_name, ' ', emp.l_name) AS fullname"),
                DB::raw("CONCAT(TO_CHAR(CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE), 'FMMonth FMDD YYYY'), ' - ', TO_CHAR(CAST(arrival AS DATE), 'FMMonth FMDD YYYY')) AS dateoftravel"),
                'destination',
                'purpose',
                DB::raw("CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS depart"), // Handle '0000-00-00' as NULL in select
                'airtravel',
                'tbl_todetails.emp_id'
            )
            ->join('tbl_empdetails AS emp', 'emp.emp_id', '=', 'tbl_todetails.emp_id')
            ->join('tbl_section AS _sec', 'emp.sec_id', '=', '_sec.id')
            ->join('tbl_division AS _div', '_div.id', '=', '_sec.div_id')
            ->join('tbl_office AS _off', DB::raw('CAST(_div.off_id AS INTEGER)'), '=', '_off.id')
            ->join('tbl_tostatus AS _ts', function($join) {
                $join->on('tbl_todetails.id', '=', '_ts.to_id')
                     ->where('_ts.stat_act', '=', 'APPROVED');
            })
            ->where('tbl_todetails.to_status', '=', 'APPROVED')
            ->where(DB::raw("EXTRACT(YEAR FROM CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE))"), '=', $year)  // Handle '0000-00-00' in WHERE clause
            ->orderByDesc('tbl_todetails.depart')
            ->get();

            return json_encode($results);
        }

        public function readallByAir() {
            $todetails_model = new Tbl_todetails;
            $year = date('Y');

            $results['records'] = $todetails_model::select(
                DB::raw("
                    CONCAT(
                        LPAD(CAST(FLOOR(EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP))) / 3600) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 3600) / 60) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 60)) AS TEXT), 2, '0')
                    ) AS avgtime
                "),
                'tbl_todetails.id',
                '_ts.date_action',
                DB::raw("CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS datefiled"), // Handle '0000-00-00' as NULL
                '_div.div_name AS division',
                DB::raw("CONCAT(emp.f_name, ' ', emp.l_name) AS fullname"),
                DB::raw("CONCAT(TO_CHAR(CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE), 'FMMonth FMDD YYYY'), ' - ', TO_CHAR(CAST(arrival AS DATE), 'FMMonth FMDD YYYY')) AS dateoftravel"),
                'destination',
                'purpose',
                DB::raw("CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS depart"), // Handle '0000-00-00' as NULL in select
                'airtravel',
                'tbl_todetails.emp_id'
            )
            ->join('tbl_empdetails AS emp', 'emp.emp_id', '=', 'tbl_todetails.emp_id')
            ->join('tbl_section AS _sec', 'emp.sec_id', '=', '_sec.id')
            ->join('tbl_division AS _div', '_div.id', '=', '_sec.div_id')
            ->join('tbl_office AS _off', DB::raw('CAST(_div.off_id AS INTEGER)'), '=', '_off.id')
            ->join('tbl_tostatus AS _ts', function($join) {
                $join->on('tbl_todetails.id', '=', '_ts.to_id')
                     ->where('_ts.stat_act', '=', 'APPROVED');
            })
            ->where('tbl_todetails.to_status', '=', 'APPROVED')
            ->where(DB::raw("EXTRACT(YEAR FROM CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE))"), '=', $year)  // Handle '0000-00-00' in WHERE clause
            ->where('tbl_todetails.airtravel', '=', 1)
            ->orderByDesc('tbl_todetails.depart')
            ->get();

        }

        public function getTOmisoc() {
            $todetails_model = new Tbl_todetails;
            $year = date('Y');

            $results['records'] = $todetails_model::select(
                DB::raw("
                    CONCAT(
                        LPAD(CAST(FLOOR(EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP))) / 3600) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 3600) / 60) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 60)) AS TEXT), 2, '0')
                    ) AS avgtime
                "),
                'tbl_todetails.id',
                '_ts.date_action',
                DB::raw("CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS datefiled"), // Handle '0000-00-00' as NULL
                '_div.div_name AS division',
                DB::raw("CONCAT(emp.f_name, ' ', emp.l_name) AS fullname"),
                DB::raw("CONCAT(TO_CHAR(CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE), 'FMMonth FMDD YYYY'), ' - ', TO_CHAR(CAST(arrival AS DATE), 'FMMonth FMDD YYYY')) AS dateoftravel"),
                'destination',
                'purpose',
                DB::raw("CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS depart"), // Handle '0000-00-00' as NULL in select
                'airtravel',
                'tbl_todetails.emp_id'
            )
            ->join('tbl_empdetails AS emp', 'emp.emp_id', '=', 'tbl_todetails.emp_id')
            ->join('tbl_section AS _sec', 'emp.sec_id', '=', '_sec.id')
            ->join('tbl_division AS _div', '_div.id', '=', '_sec.div_id')
            ->join('tbl_office AS _off', DB::raw('CAST(_div.off_id AS INTEGER)'), '=', '_off.id')
            ->join('tbl_tostatus AS _ts', function($join) {
                $join->on('tbl_todetails.id', '=', '_ts.to_id')
                     ->where('_ts.stat_act', '=', 'APPROVED');
            })
            ->where('tbl_todetails.to_status', '=', 'APPROVED')
            ->where(DB::raw("EXTRACT(YEAR FROM CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE))"), '=', $year)  // Handle '0000-00-00' in WHERE clause
            ->where('_off.office_name', 'LIKE', '%Misamis Occidental%')
            ->orderByDesc('tbl_todetails.depart')
            ->get();

            return json_encode($results);
        }

        public function getTOmisocByAir() {
            $todetails_model = new Tbl_todetails;
            $year = date('Y');


            $results['records'] = $todetails_model::select(
                DB::raw("
                    CONCAT(
                        LPAD(CAST(FLOOR(EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP))) / 3600) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 3600) / 60) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 60)) AS TEXT), 2, '0')
                    ) AS avgtime
                "),
                'tbl_todetails.id',
                '_ts.date_action',
                DB::raw("CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS datefiled"), // Handle '0000-00-00' as NULL
                '_div.div_name AS division',
                DB::raw("CONCAT(emp.f_name, ' ', emp.l_name) AS fullname"),
                DB::raw("CONCAT(TO_CHAR(CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE), 'FMMonth FMDD YYYY'), ' - ', TO_CHAR(CAST(arrival AS DATE), 'FMMonth FMDD YYYY')) AS dateoftravel"),
                'destination',
                'purpose',
                DB::raw("CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS depart"), // Handle '0000-00-00' as NULL in select
                'airtravel',
                'tbl_todetails.emp_id'
            )
            ->join('tbl_empdetails AS emp', 'emp.emp_id', '=', 'tbl_todetails.emp_id')
            ->join('tbl_section AS _sec', 'emp.sec_id', '=', '_sec.id')
            ->join('tbl_division AS _div', '_div.id', '=', '_sec.div_id')
            ->join('tbl_office AS _off', DB::raw('CAST(_div.off_id AS INTEGER)'), '=', '_off.id')
            ->join('tbl_tostatus AS _ts', function($join) {
                $join->on('tbl_todetails.id', '=', '_ts.to_id')
                     ->where('_ts.stat_act', '=', 'APPROVED');
            })
            ->where('tbl_todetails.to_status', '=', 'APPROVED')
            ->where(DB::raw("EXTRACT(YEAR FROM CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE))"), '=', $year)  // Handle '0000-00-00' in WHERE clause
            ->where('_off.office_name', 'LIKE', '%Misamis Occidental%')
            ->where('tbl_todetails.airtravel', '=', 1)
            ->orderByDesc('tbl_todetails.depart')
            ->get();

            return json_encode($results);
        }

        public function getTObukidnon() {
            $todetails_model = new Tbl_todetails;
            $year = date('Y');

            $results['records'] = $todetails_model::select(
                DB::raw("
                    CONCAT(
                        LPAD(CAST(FLOOR(EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP))) / 3600) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 3600) / 60) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 60)) AS TEXT), 2, '0')
                    ) AS avgtime
                "),
                'tbl_todetails.id',
                '_ts.date_action',
                DB::raw("CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS datefiled"), // Handle '0000-00-00' as NULL
                '_div.div_name AS division',
                DB::raw("CONCAT(emp.f_name, ' ', emp.l_name) AS fullname"),
                DB::raw("CONCAT(TO_CHAR(CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE), 'FMMonth FMDD YYYY'), ' - ', TO_CHAR(CAST(arrival AS DATE), 'FMMonth FMDD YYYY')) AS dateoftravel"),
                'destination',
                'purpose',
                DB::raw("CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS depart"), // Handle '0000-00-00' as NULL in select
                'airtravel',
                'tbl_todetails.emp_id'
            )
            ->join('tbl_empdetails AS emp', 'emp.emp_id', '=', 'tbl_todetails.emp_id')
            ->join('tbl_section AS _sec', 'emp.sec_id', '=', '_sec.id')
            ->join('tbl_division AS _div', '_div.id', '=', '_sec.div_id')
            ->join('tbl_office AS _off', DB::raw('CAST(_div.off_id AS INTEGER)'), '=', '_off.id')
            ->join('tbl_tostatus AS _ts', function($join) {
                $join->on('tbl_todetails.id', '=', '_ts.to_id')
                     ->where('_ts.stat_act', '=', 'APPROVED');
            })
            ->where('tbl_todetails.to_status', '=', 'APPROVED')
            ->where(DB::raw("EXTRACT(YEAR FROM CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE))"), '=', $year)  // Handle '0000-00-00' in WHERE clause
            ->where('_off.office_name', 'LIKE', '%Bukidnon%')
            ->orderByDesc('tbl_todetails.depart')
            ->get();

            return json_encode($results);
        }

        public function getTObukidnonByAir() {
            $todetails_model = new Tbl_todetails;
            $year = date('Y');

            $results['records'] = $todetails_model::select(
                DB::raw("
                    CONCAT(
                        LPAD(CAST(FLOOR(EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP))) / 3600) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 3600) / 60) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 60)) AS TEXT), 2, '0')
                    ) AS avgtime
                "),
                'tbl_todetails.id',
                '_ts.date_action',
                DB::raw("CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS datefiled"), // Handle '0000-00-00' as NULL
                '_div.div_name AS division',
                DB::raw("CONCAT(emp.f_name, ' ', emp.l_name) AS fullname"),
                DB::raw("CONCAT(TO_CHAR(CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE), 'FMMonth FMDD YYYY'), ' - ', TO_CHAR(CAST(arrival AS DATE), 'FMMonth FMDD YYYY')) AS dateoftravel"),
                'destination',
                'purpose',
                DB::raw("CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS depart"), // Handle '0000-00-00' as NULL in select
                'airtravel',
                'tbl_todetails.emp_id'
            )
            ->join('tbl_empdetails AS emp', 'emp.emp_id', '=', 'tbl_todetails.emp_id')
            ->join('tbl_section AS _sec', 'emp.sec_id', '=', '_sec.id')
            ->join('tbl_division AS _div', '_div.id', '=', '_sec.div_id')
            ->join('tbl_office AS _off', DB::raw('CAST(_div.off_id AS INTEGER)'), '=', '_off.id')
            ->join('tbl_tostatus AS _ts', function($join) {
                $join->on('tbl_todetails.id', '=', '_ts.to_id')
                     ->where('_ts.stat_act', '=', 'APPROVED');
            })
            ->where('tbl_todetails.to_status', '=', 'APPROVED')
            ->where(DB::raw("EXTRACT(YEAR FROM CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE))"), '=', $year)  // Handle '0000-00-00' in WHERE clause
            ->where('_off.office_name', 'LIKE', '%Bukidnon%')
            ->where('tbl_todetails.airtravel', '=', 1)
            ->orderByDesc('tbl_todetails.depart')
            ->get();

            return json_encode($results);
        }

        public function getTOcamiguin() {
            $todetails_model = new Tbl_todetails;
            $year = date('Y');

            $results['records'] = $todetails_model::select(
                DB::raw("
                    CONCAT(
                        LPAD(CAST(FLOOR(EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP))) / 3600) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 3600) / 60) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 60)) AS TEXT), 2, '0')
                    ) AS avgtime
                "),
                'tbl_todetails.id',
                '_ts.date_action',
                DB::raw("CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS datefiled"), // Handle '0000-00-00' as NULL
                '_div.div_name AS division',
                DB::raw("CONCAT(emp.f_name, ' ', emp.l_name) AS fullname"),
                DB::raw("CONCAT(TO_CHAR(CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE), 'FMMonth FMDD YYYY'), ' - ', TO_CHAR(CAST(arrival AS DATE), 'FMMonth FMDD YYYY')) AS dateoftravel"),
                'destination',
                'purpose',
                DB::raw("CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS depart"), // Handle '0000-00-00' as NULL in select
                'airtravel',
                'tbl_todetails.emp_id'
            )
            ->join('tbl_empdetails AS emp', 'emp.emp_id', '=', 'tbl_todetails.emp_id')
            ->join('tbl_section AS _sec', 'emp.sec_id', '=', '_sec.id')
            ->join('tbl_division AS _div', '_div.id', '=', '_sec.div_id')
            ->join('tbl_office AS _off', DB::raw('CAST(_div.off_id AS INTEGER)'), '=', '_off.id')
            ->join('tbl_tostatus AS _ts', function($join) {
                $join->on('tbl_todetails.id', '=', '_ts.to_id')
                     ->where('_ts.stat_act', '=', 'APPROVED');
            })
            ->where('tbl_todetails.to_status', '=', 'APPROVED')
            ->where(DB::raw("EXTRACT(YEAR FROM CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE))"), '=', $year)  // Handle '0000-00-00' in WHERE clause
            ->where('_off.office_name', 'LIKE', '%Camiguin%')
            ->orderByDesc('tbl_todetails.depart')
            ->get();

            return json_encode($results);
        }

        public function getTOcamiguinByAir() {
            $todetails_model = new Tbl_todetails;
            $year = date('Y');

            $results['records'] = $todetails_model::select(
                DB::raw("
                    CONCAT(
                        LPAD(CAST(FLOOR(EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP))) / 3600) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 3600) / 60) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 60)) AS TEXT), 2, '0')
                    ) AS avgtime
                "),
                'tbl_todetails.id',
                '_ts.date_action',
                DB::raw("CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS datefiled"), // Handle '0000-00-00' as NULL
                '_div.div_name AS division',
                DB::raw("CONCAT(emp.f_name, ' ', emp.l_name) AS fullname"),
                DB::raw("CONCAT(TO_CHAR(CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE), 'FMMonth FMDD YYYY'), ' - ', TO_CHAR(CAST(arrival AS DATE), 'FMMonth FMDD YYYY')) AS dateoftravel"),
                'destination',
                'purpose',
                DB::raw("CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS depart"), // Handle '0000-00-00' as NULL in select
                'airtravel',
                'tbl_todetails.emp_id'
            )
            ->join('tbl_empdetails AS emp', 'emp.emp_id', '=', 'tbl_todetails.emp_id')
            ->join('tbl_section AS _sec', 'emp.sec_id', '=', '_sec.id')
            ->join('tbl_division AS _div', '_div.id', '=', '_sec.div_id')
            ->join('tbl_office AS _off', DB::raw('CAST(_div.off_id AS INTEGER)'), '=', '_off.id')
            ->join('tbl_tostatus AS _ts', function($join) {
                $join->on('tbl_todetails.id', '=', '_ts.to_id')
                     ->where('_ts.stat_act', '=', 'APPROVED');
            })
            ->where('tbl_todetails.to_status', '=', 'APPROVED')
            ->where(DB::raw("EXTRACT(YEAR FROM CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE))"), '=', $year)  // Handle '0000-00-00' in WHERE clause
            ->where('_off.office_name', 'LIKE', '%Camiguin%')
            ->where('tbl_todetails.airtravel', '=', 1)
            ->orderByDesc('tbl_todetails.depart')
            ->get();

            return json_encode($results);
        }

        public function getTOlanao() {
            $todetails_model = new Tbl_todetails;
            $year = date('Y');

            $results['records'] = $todetails_model::select(
                DB::raw("
                    CONCAT(
                        LPAD(CAST(FLOOR(EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP))) / 3600) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 3600) / 60) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 60)) AS TEXT), 2, '0')
                    ) AS avgtime
                "),
                'tbl_todetails.id',
                '_ts.date_action',
                DB::raw("CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS datefiled"), // Handle '0000-00-00' as NULL
                '_div.div_name AS division',
                DB::raw("CONCAT(emp.f_name, ' ', emp.l_name) AS fullname"),
                DB::raw("CONCAT(TO_CHAR(CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE), 'FMMonth FMDD YYYY'), ' - ', TO_CHAR(CAST(arrival AS DATE), 'FMMonth FMDD YYYY')) AS dateoftravel"),
                'destination',
                'purpose',
                DB::raw("CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS depart"), // Handle '0000-00-00' as NULL in select
                'airtravel',
                'tbl_todetails.emp_id'
            )
            ->join('tbl_empdetails AS emp', 'emp.emp_id', '=', 'tbl_todetails.emp_id')
            ->join('tbl_section AS _sec', 'emp.sec_id', '=', '_sec.id')
            ->join('tbl_division AS _div', '_div.id', '=', '_sec.div_id')
            ->join('tbl_office AS _off', DB::raw('CAST(_div.off_id AS INTEGER)'), '=', '_off.id')
            ->join('tbl_tostatus AS _ts', function($join) {
                $join->on('tbl_todetails.id', '=', '_ts.to_id')
                     ->where('_ts.stat_act', '=', 'APPROVED');
            })
            ->where('tbl_todetails.to_status', '=', 'APPROVED')
            ->where(DB::raw("EXTRACT(YEAR FROM CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE))"), '=', $year)  // Handle '0000-00-00' in WHERE clause
            ->where('_off.office_name', 'LIKE', '%Lanao%')
            ->orderByDesc('tbl_todetails.depart')
            ->get();

            return json_encode($results);
        }

        public function getTOlanaoByAir() {
            $todetails_model = new Tbl_todetails;
            $year = date('Y');

            $results['records'] = $todetails_model::select(
                DB::raw("
                    CONCAT(
                        LPAD(CAST(FLOOR(EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP))) / 3600) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 3600) / 60) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 60)) AS TEXT), 2, '0')
                    ) AS avgtime
                "),
                'tbl_todetails.id',
                '_ts.date_action',
                DB::raw("CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS datefiled"), // Handle '0000-00-00' as NULL
                '_div.div_name AS division',
                DB::raw("CONCAT(emp.f_name, ' ', emp.l_name) AS fullname"),
                DB::raw("CONCAT(TO_CHAR(CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE), 'FMMonth FMDD YYYY'), ' - ', TO_CHAR(CAST(arrival AS DATE), 'FMMonth FMDD YYYY')) AS dateoftravel"),
                'destination',
                'purpose',
                DB::raw("CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS depart"), // Handle '0000-00-00' as NULL in select
                'airtravel',
                'tbl_todetails.emp_id'
            )
            ->join('tbl_empdetails AS emp', 'emp.emp_id', '=', 'tbl_todetails.emp_id')
            ->join('tbl_section AS _sec', 'emp.sec_id', '=', '_sec.id')
            ->join('tbl_division AS _div', '_div.id', '=', '_sec.div_id')
            ->join('tbl_office AS _off', DB::raw('CAST(_div.off_id AS INTEGER)'), '=', '_off.id')
            ->join('tbl_tostatus AS _ts', function($join) {
                $join->on('tbl_todetails.id', '=', '_ts.to_id')
                     ->where('_ts.stat_act', '=', 'APPROVED');
            })
            ->where('tbl_todetails.to_status', '=', 'APPROVED')
            ->where(DB::raw("EXTRACT(YEAR FROM CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE))"), '=', $year)  // Handle '0000-00-00' in WHERE clause
            ->where('_off.office_name', 'LIKE', '%Lanao%')
            ->where('tbl_todetails.airtravel', '=', 1)
            ->orderByDesc('tbl_todetails.depart')
            ->get();

            return json_encode($results);
        }

        public function getTOmisor() {
            $todetails_model = new Tbl_todetails;
            $year = date('Y');

            $results['records'] = $todetails_model::select(
                DB::raw("
                    CONCAT(
                        LPAD(CAST(FLOOR(EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP))) / 3600) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 3600) / 60) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 60)) AS TEXT), 2, '0')
                    ) AS avgtime
                "),
                'tbl_todetails.id',
                '_ts.date_action',
                DB::raw("CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS datefiled"), // Handle '0000-00-00' as NULL
                '_div.div_name AS division',
                DB::raw("CONCAT(emp.f_name, ' ', emp.l_name) AS fullname"),
                DB::raw("CONCAT(TO_CHAR(CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE), 'FMMonth FMDD YYYY'), ' - ', TO_CHAR(CAST(arrival AS DATE), 'FMMonth FMDD YYYY')) AS dateoftravel"),
                'destination',
                'purpose',
                DB::raw("CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS depart"), // Handle '0000-00-00' as NULL in select
                'airtravel',
                'tbl_todetails.emp_id'
            )
            ->join('tbl_empdetails AS emp', 'emp.emp_id', '=', 'tbl_todetails.emp_id')
            ->join('tbl_section AS _sec', 'emp.sec_id', '=', '_sec.id')
            ->join('tbl_division AS _div', '_div.id', '=', '_sec.div_id')
            ->join('tbl_office AS _off', DB::raw('CAST(_div.off_id AS INTEGER)'), '=', '_off.id')
            ->join('tbl_tostatus AS _ts', function($join) {
                $join->on('tbl_todetails.id', '=', '_ts.to_id')
                     ->where('_ts.stat_act', '=', 'APPROVED');
            })
            ->where('tbl_todetails.to_status', '=', 'APPROVED')
            ->where(DB::raw("EXTRACT(YEAR FROM CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE))"), '=', $year)  // Handle '0000-00-00' in WHERE clause
            ->where('_off.office_name', 'LIKE', '%Misamis Oriental%')
            ->orderByDesc('tbl_todetails.depart')
            ->get();

            return json_encode($results);
        }

        public function getTOmisorByAir() {
            $todetails_model = new Tbl_todetails;
            $year = date('Y');

            $results['records'] = $todetails_model::select(
                DB::raw("
                    CONCAT(
                        LPAD(CAST(FLOOR(EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP))) / 3600) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 3600) / 60) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 60)) AS TEXT), 2, '0')
                    ) AS avgtime
                "),
                'tbl_todetails.id',
                '_ts.date_action',
                DB::raw("CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS datefiled"), // Handle '0000-00-00' as NULL
                '_div.div_name AS division',
                DB::raw("CONCAT(emp.f_name, ' ', emp.l_name) AS fullname"),
                DB::raw("CONCAT(TO_CHAR(CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE), 'FMMonth FMDD YYYY'), ' - ', TO_CHAR(CAST(arrival AS DATE), 'FMMonth FMDD YYYY')) AS dateoftravel"),
                'destination',
                'purpose',
                DB::raw("CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS depart"), // Handle '0000-00-00' as NULL in select
                'airtravel',
                'tbl_todetails.emp_id'
            )
            ->join('tbl_empdetails AS emp', 'emp.emp_id', '=', 'tbl_todetails.emp_id')
            ->join('tbl_section AS _sec', 'emp.sec_id', '=', '_sec.id')
            ->join('tbl_division AS _div', '_div.id', '=', '_sec.div_id')
            ->join('tbl_office AS _off', DB::raw('CAST(_div.off_id AS INTEGER)'), '=', '_off.id')
            ->join('tbl_tostatus AS _ts', function($join) {
                $join->on('tbl_todetails.id', '=', '_ts.to_id')
                     ->where('_ts.stat_act', '=', 'APPROVED');
            })
            ->where('tbl_todetails.to_status', '=', 'APPROVED')
            ->where(DB::raw("EXTRACT(YEAR FROM CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE))"), '=', $year)  // Handle '0000-00-00' in WHERE clause
            ->where('_off.office_name', 'LIKE', '%Misamis Oriental%')
            ->where('tbl_todetails.airtravel', '=', 1)
            ->orderByDesc('tbl_todetails.depart')
            ->get();

            return json_encode($results);
        }

        public function getTOregion() {
            $todetails_model = new Tbl_todetails;
            $year = date('Y');

            $results['records'] = $todetails_model::select(
                DB::raw("
                    CONCAT(
                        LPAD(CAST(FLOOR(EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP))) / 3600) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 3600) / 60) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 60)) AS TEXT), 2, '0')
                    ) AS avgtime
                "),
                'tbl_todetails.id',
                '_ts.date_action',
                DB::raw("CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS datefiled"), // Handle '0000-00-00' as NULL
                '_div.div_name AS division',
                DB::raw("CONCAT(emp.f_name, ' ', emp.l_name) AS fullname"),
                DB::raw("CONCAT(TO_CHAR(CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE), 'FMMonth FMDD YYYY'), ' - ', TO_CHAR(CAST(arrival AS DATE), 'FMMonth FMDD YYYY')) AS dateoftravel"),
                'destination',
                'purpose',
                DB::raw("CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS depart"), // Handle '0000-00-00' as NULL in select
                'airtravel',
                'tbl_todetails.emp_id'
            )
            ->join('tbl_empdetails AS emp', 'emp.emp_id', '=', 'tbl_todetails.emp_id')
            ->join('tbl_section AS _sec', 'emp.sec_id', '=', '_sec.id')
            ->join('tbl_division AS _div', '_div.id', '=', '_sec.div_id')
            ->join('tbl_office AS _off', DB::raw('CAST(_div.off_id AS INTEGER)'), '=', '_off.id')
            ->join('tbl_tostatus AS _ts', function($join) {
                $join->on('tbl_todetails.id', '=', '_ts.to_id')
                     ->where('_ts.stat_act', '=', 'APPROVED');
            })
            ->where('tbl_todetails.to_status', '=', 'APPROVED')
            ->where(DB::raw("EXTRACT(YEAR FROM CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE))"), '=', $year)  // Handle '0000-00-00' in WHERE clause
            ->where('_off.id', '<=', 3)
            ->orderByDesc('tbl_todetails.depart')
            ->get();

            return json_encode($results);
        }

        public function getTOregionByAir() {
            $todetails_model = new Tbl_todetails;
            $year = date('Y');

            $results['records'] = $todetails_model::select(
                DB::raw("
                    CONCAT(
                        LPAD(CAST(FLOOR(EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP))) / 3600) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 3600) / 60) AS TEXT), 2, '0'),
                        ':',
                        LPAD(CAST(FLOOR((EXTRACT(EPOCH FROM (CAST(_ts.date_action AS TIMESTAMP) -
                        CAST(CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS TIMESTAMP)))::INTEGER % 60)) AS TEXT), 2, '0')
                    ) AS avgtime
                "),
                'tbl_todetails.id',
                '_ts.date_action',
                DB::raw("CASE WHEN tbl_todetails.date_filed = '0000-00-00' THEN NULL ELSE tbl_todetails.date_filed END AS datefiled"), // Handle '0000-00-00' as NULL
                '_div.div_name AS division',
                DB::raw("CONCAT(emp.f_name, ' ', emp.l_name) AS fullname"),
                DB::raw("CONCAT(TO_CHAR(CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE), 'FMMonth FMDD YYYY'), ' - ', TO_CHAR(CAST(arrival AS DATE), 'FMMonth FMDD YYYY')) AS dateoftravel"),
                'destination',
                'purpose',
                DB::raw("CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS depart"), // Handle '0000-00-00' as NULL in select
                'airtravel',
                'tbl_todetails.emp_id'
            )
            ->join('tbl_empdetails AS emp', 'emp.emp_id', '=', 'tbl_todetails.emp_id')
            ->join('tbl_section AS _sec', 'emp.sec_id', '=', '_sec.id')
            ->join('tbl_division AS _div', '_div.id', '=', '_sec.div_id')
            ->join('tbl_office AS _off', DB::raw('CAST(_div.off_id AS INTEGER)'), '=', '_off.id')
            ->join('tbl_tostatus AS _ts', function($join) {
                $join->on('tbl_todetails.id', '=', '_ts.to_id')
                     ->where('_ts.stat_act', '=', 'APPROVED');
            })
            ->where('tbl_todetails.to_status', '=', 'APPROVED')
            ->where(DB::raw("EXTRACT(YEAR FROM CAST(CASE WHEN tbl_todetails.depart = '0000-00-00' THEN NULL ELSE tbl_todetails.depart END AS DATE))"), '=', $year)  // Handle '0000-00-00' in WHERE clause
            ->where('_off.id', '<=', 3)
            ->where('tbl_todetails.airtravel', '=', 1)
            ->orderByDesc('tbl_todetails.depart')
            ->get();

            return json_encode($results);
        }

    // EOLS API


    public function countApprovedTo($emp_id) {
        $todetails_model = new Tbl_todetails;
        $query = $todetails_model
                ->join('tbl_approveto', 'tbl_todetails.id', '=', 'tbl_approveto.to_id')
                ->selectRaw('COUNT(*)')
                ->where('tbl_todetails.emp_id', $emp_id)
                ->where('tbl_approveto.to_seen', 0)
                ->first();

        return json_encode($query);
    }

    public function updateApprovedTravelOrderSeen($emp_id) {
        $tblapproveto_model = new Tbl_approveto;
        $todetails_model = new Tbl_todetails;
        $query = $todetails_model
                ->join('tbl_approveto', 'tbl_todetails.id', '=', 'tbl_approveto.to_id')
                ->selectRaw('tbl_approveto.id, tbl_approveto.to_id')
                ->where('tbl_todetails.emp_id', $emp_id)
                ->where('tbl_approveto.to_seen', 0)
                ->get();

        foreach($query as $row):
            $tblapproveto_model->find($row->id)->update([
                'to_seen' => 1
            ]);
        endforeach;

    }

    public function getEmpDetailsWithOfficeByOffice($office_id) {
        $model = new View_empdetails_with_office;

        // $query = $model->where('off_id', $office_id)->orderBy('full_name', 'ASC')->get();

        // if($office_id == 0) {
        $query = $model->orderBy('full_name', 'ASC')->get();
        // }

        return json_encode($query);
    }

    public function getAllOffice() {
        $model = new Tbl_office;
        $query = $model
                    ->select('tbl_office.id as off_id',
                            'tbl_office.office_name'
                            )
                    ->orderBy('id', 'ASC')->get();

        return json_encode($query);
    }

    public function updateProfileByAdmin(Request $request, $type) {
        $empDetailsModel = new Tbl_empdetails;
        $accountModel = new Tbl_accounts;

        if($type == 'profileOffice') {
            $data = $empDetailsModel->where('emp_id', $request->emp_id)->update([
                'sec_id' => $request->sec_id
            ]);
            $this->insert_logs(Auth::guard('second_db')->user()->username, 'Admin Updated Profile Information', 'N/A', 'N/A', 'N/A');

        }else if($type == 'password') {
            $data = $accountModel->where('username', $request->emp_id)->update([
                'password' => password_hash('Windows7', PASSWORD_BCRYPT, array("cost" => 10))
            ]);
            $this->insert_logs(Auth::guard('second_db')->user()->username, 'Admin Resetted Password', 'N/A', 'N/A', 'N/A');
        }

        if($data) {
            $result = [
                'message' => 'success',
            ];
        }else {
            $result = [
                'message' => 'error'
            ];
        }

        return json_encode($result);
    }
}
