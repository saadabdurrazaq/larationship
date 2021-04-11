<?php

namespace App\Http\Controllers;

use App\Applicant;
use App\VerifyUser;
use App\mail\VerifyMail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Auth\Events\Registered;
use App\Notifications\MailForApplicant;
use Spatie\Permission\Models\Role;
use DB;
use App\Notifications\ApprovedApplicant;
use App\Notifications\RejectedApplicant;
use App\Notifications\ApplicantOnHold;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\ClientMail;
use Session;
use View;
use Exception;
use App\Mail\SendEmail;
use App\Mail\RejectedEmail;
use App\Mail\OnHoldEmail;
use Carbon\Carbon;
use Config;
use \App\Provinces;
use \App\Regencies;
use \App\Districts;
use \App\Villages;
use Illuminate\Support\Facades\Input;

class JobController extends Controller
{
    public $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filterKeyword = $request->get('keyword');

        $count = \App\JobTypes::count();
        $items = $request->items ?? 10;
        $page    = $request->has('name') ? $request->get('name') : 1;
        $showingTotal  = $page * $items;
        $admins = \App\JobTypes::paginate($items);
        $showingStarted = $admins->currentPage();

        $showData = "Showing $showingStarted to $showingTotal of $count";

        $data = \App\Jobs::paginate($items);

        if ($filterKeyword) {
            $data = \App\Jobs::where('name', 'LIKE', "%$filterKeyword%")->paginate($items);
        }

        return view('jobs.index', compact('data'))->with(array('showData' => $showData, 'count' => $count))->withItems($items); //admin mengacu ke table admin di phpmyadmin
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createJobs()
    {
        $permission = \App\Jobs::get();
        return view('jobs.create', compact('permission'));
    }

    public function storeJobs(Request $request)
    {
        $this->validate($request, [
            'jobs' => 'required',
        ]);

        $convertString = json_encode($request->get('jobs')); //object to json string conversion
        $dataString = json_decode($convertString); // json string to array
        $jobs = explode(",", $dataString);

        foreach ($jobs as $job) {
            \App\Jobs::insert([
                'name'    => $job
            ]);
        }

        return redirect()->route('jobs.index')->with('success', 'Jobs created successfully');
    }

    public function deletePermanentJob($id)
    {
        $typeOfJob = \App\Jobs::withTrashed()->findOrFail($id);
        $typeOfJob->forceDelete();
    }

    public function deletePermanent($id)
    {
        $this->deletePermanentJob($id);
        return redirect()->route('jobs.index')->with('status', 'Type of job successfully deleted permanently');
    }

    public function editJob($id)
    {
        $job = \App\Jobs::find($id);

        return view('jobs.edit-job', compact(['job']));
    }

    public function update($id)
    {
        $this->validate($this->request, [
            'job' => 'required',
        ]);

        $user = \App\Jobs::find($id);
        $user->name = $this->request->input('job');
        $user->save();

        return redirect()->route('jobs.index')->with('success', 'Job updated successfully');
    }
}
