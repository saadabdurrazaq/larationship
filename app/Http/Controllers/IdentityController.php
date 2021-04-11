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

class IdentityController extends Controller
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

        $count = \App\Identity::count();
        $items = $request->items ?? 10;
        $page    = $request->has('name') ? $request->get('name') : 1;
        $showingTotal  = $page * $items;
        $admins = \App\Identity::paginate($items);
        $showingStarted = $admins->currentPage();

        $showData = "Showing $showingStarted to $showingTotal of $count";

        $data = \App\Identity::paginate($items);

        if ($filterKeyword) {
            $data = \App\Identity::where('name', 'LIKE', "%$filterKeyword%")->paginate($items);
        }

        return view('identities.index', compact('data'))->with(array('showData' => $showData, 'count' => $count))->withItems($items); //admin mengacu ke table admin di phpmyadmin
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createIdentities()
    {
        $permission = \App\Identity::get();
        return view('identities.create', compact('permission'));
    }

    public function storeIdentities(Request $request)
    {
        $this->validate($request, [
            'identities' => 'required',
        ]);

        $convertString = json_encode($request->get('identities')); //object to json string conversion
        $dataString = json_decode($convertString); // json string to array
        $identities = explode(",", $dataString);

        foreach ($identities as $identity) {
            \App\Identity::insert([
                'no_ktp'    => $identity
            ]);
        }

        return redirect()->route('identities.index')->with('success', 'Identities created successfully');
    }

    public function editIdentity($id)
    {
        $identity = \App\Identity::find($id);

        return view('identities.edit-identity', compact(['identity']));
    }

    public function update($id)
    {
        $this->validate($this->request, [
            'identity' => 'required',
        ]);

        $user = \App\Identity::find($id);
        $user->no_ktp = $this->request->input('identity');
        $user->save();

        return redirect()->route('identities.index')->with('success', 'Identity updated successfully');
    }

    public function deletePermanentJob($id)
    {
        $typeOfJob = \App\Identity::withTrashed()->findOrFail($id);
        $typeOfJob->forceDelete();
    }

    public function deletePermanent($id)
    {
        $this->deletePermanentJob($id);
        return redirect()->route('identities.index')->with('status', 'Identity successfully deleted permanently');
    }
}
