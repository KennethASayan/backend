<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\DMS\Document_info;

class DmsController extends Controller
{
    //

    public function getDueDocuments()
    {
        $model = new Document_info;
        $tomorrow = Carbon::tomorrow()->toDateString();

        // due today
        $dueTodayQuery = $model->newQuery()
            ->select('referred_to', 'reroute_offices', 'final_action_offices', 'document_no', 'subject', 'document_classification', 'arta_deadline', 'document_deadline')
            ->where(function ($q) {
                $q->whereDate('document_deadline', now()->toDateString())
                ->orWhereDate('arta_deadline', now()->toDateString());
            })
            ->where('acted_status', 0)
            ->where('docu_type', 'InCom');

        if (session()->has('dept')) {
            $dept = session('dept');
            if (!in_array($dept, ['ored', 'legal'])) {
                $dueTodayQuery->where('document_classification', 'General Circulation');
            }
        } else {
            $dueTodayQuery->where('document_classification', 'General Circulation');
        }

        //due tomorrow
        $dueTomorrowQuery = $model->newQuery()
            ->select(
                'referred_to',
                'reroute_offices',
                'final_action_offices',
                'document_no',
                'subject',
                'document_classification',
                'arta_deadline',
                'document_deadline'
            )
            ->where(function ($q) use ($tomorrow) {
                $q->whereDate('document_deadline', $tomorrow)
                ->orWhereDate('arta_deadline', $tomorrow);
            })
            ->where('acted_status', 0)
            ->where('docu_type', 'InCom');

        if (session()->has('dept')) {
            $dept = session('dept');
            if (!in_array($dept, ['ored', 'legal'])) {
                $dueTomorrowQuery->where('document_classification', 'General Circulation');
            }
        } else {
            $dueTomorrowQuery->where('document_classification', 'General Circulation');
        }

        $all_documents = array_merge(
            $dueTodayQuery->get()->toArray(),
            $dueTomorrowQuery->get()->toArray()
        );

        $result = [
            'due_today' => $dueTodayQuery->get(),
            'due_tomorrow' => $dueTomorrowQuery->get(),
            'all_documents' => $all_documents
        ];

        return json_encode($result);
    }
}
