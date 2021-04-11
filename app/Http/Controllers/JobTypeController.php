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

class JobTypeController extends Controller
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
        $items = $request->items ?? 5;
        $page    = $request->has('name') ? $request->get('name') : 1;
        $showingTotal  = $page * $items;
        $admins = \App\JobTypes::paginate($items);
        $showingStarted = $admins->currentPage();

        $showData = "Showing $showingStarted to $showingTotal of $count";

        $data = \App\JobTypes::with('jobs')
            ->whereHas('jobs')
            ->orWhereDoesntHave('jobs')
            ->paginate($items);

        if ($filterKeyword) {
            $data = \App\JobTypes::where("name", "LIKE", "%$filterKeyword%")
                ->paginate($items);
        }

        return view('job-types.index', compact('data'))->with(array('showData' => $showData, 'count' => $count))->withItems($items); //admin mengacu ke table admin di phpmyadmin

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createTypeOfJobs()
    {
        $permission = \App\JobTypes::get();
        return view('job-types.create', compact('permission'));
    }

    public function storeTypeOfJobs(Request $request)
    {
        $this->validate($request, [
            'type' => 'required',
        ]);

        $convertString = json_encode($request->get('type')); //object to json string conversion
        $dataString = json_decode($convertString); // json string to array
        $jobTypes = explode(",", $dataString);

        foreach ($jobTypes as $jobType) {
            \App\JobTypes::insert([
                'name'    => $jobType
            ]);
        }

        return redirect()->route('job-types.index')->with('success', 'Type of jobs created successfully');
    }

    public function assignJob($id)
    {
        $jobType = \App\JobTypes::withTrashed()->find($id);
        $jobs = $jobType->jobs;

        return view('job-types.assign-job', compact(['jobType', 'jobs']));
    }

    public function ajaxSearch($id)
    {
        $keyword = $this->request->get('q');

        $categories = \App\Jobs::with('job_types')
            ->where("name", "LIKE", "%$keyword%")
            ->get();

        return $categories;
    }

    public function storeJobs(Request $request)
    {
        $this->validate($request, [
            'jobs' => ['required'],
        ]);

        $job_type = $request->get('typejob'); // id of job type
        $jobs = $request->get('jobs'); // id of jobs

        // Remove relationship
        $jobType = \App\JobTypes::withTrashed()->findOrFail($job_type);
        $jobType->jobs()->detach();

        // Assign new jobs 
        $jobType->jobs()->attach($jobs);

        return redirect()->route('job-types.index')->with('success', 'Type of jobs created successfully');
    }

    public function deletePermanentType($id)
    {
        $typeOfJob = \App\JobTypes::withTrashed()->findOrFail($id);

        $typeOfJob->jobs()->detach();
        $typeOfJob->forceDelete();
    }

    public function deletePermanent($id)
    {
        $this->deletePermanentType($id);
        return redirect()->route('job-types.index')->with('status', 'Type of job successfully deleted permanently');
    }

    public function editType($id)
    {
        $jobType = \App\JobTypes::find($id);

        return view('job-types.edit-type', compact(['jobType']));
    }

    public function update($id)
    {
        $this->validate($this->request, [
            'type' => 'required',
        ]);

        $user = \App\JobTypes::find($id);
        $user->name = $this->request->input('type');
        $user->save();

        return redirect()->route('job-types.index')->with('success', 'Type of job updated successfully');
    }
}
