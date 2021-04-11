<?php

namespace App\Http\Controllers;

use App\Applicant;
use App\Http\Controllers\Schema;
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

class PersonController extends Controller
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

        $count = \App\Person::count();
        $items = $request->items ?? 10;
        $page    = $request->has('name') ? $request->get('name') : 1;
        $showingTotal  = $page * $items;
        $admins = \App\Person::paginate($items);
        $showingStarted = $admins->currentPage();

        $showData = "Showing $showingStarted to $showingTotal of $count";

        $data = \App\Person::paginate($items);

        if ($filterKeyword) {
            $data = \App\Person::where("name", "LIKE", "%$filterKeyword%")
                ->paginate($items);
        }

        return view('persons.index', compact('data'))->with(array('showData' => $showData, 'count' => $count))->withItems($items); //admin mengacu ke table admin di phpmyadmin

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createPersons()
    {
        $permission = \App\Person::get();
        return view('persons.create', compact('permission'));
    }

    public function storePersons(Request $request)
    {
        $this->validate($request, [
            'person' => 'required',
        ]);

        $convertString = json_encode($request->get('person')); //object to json string conversion
        $dataString = json_decode($convertString); // json string to array
        $persons = explode(",", $dataString);

        foreach ($persons as $person) {
            \App\Person::insert([
                'name'    => $person
            ]);
        }

        return redirect()->route('persons.index')->with('success', 'Persons created successfully');
    }

    public function assignIdentity($id)
    {
        $person = \App\Person::withTrashed()->find($id);

        // fill the input column with these data
        $identities = \App\Identity::with('person')->whereHas('person', function ($q) use ($id) {
            $q->where('id', $id);
        })->get();

        $this->ajaxSearch($id);

        return view('persons.assign-identity', compact(['person', 'identities']));
    }

    public function ajaxSearch($id)
    {
        $keyword = $this->request->get('q');

        $categories = \App\Identity::with('person')
            ->orWhereDoesntHave('person')
            ->OrWhereHas('person', function ($q) use ($id) {
                $q->where('id', $id);
            })->where("no_ktp", "LIKE", "%$keyword%")
            ->get();

        return $categories;
    }

    public function storeIdentities(Request $request)
    {
        $this->validate($request, [
            'identities' => ['required'],
        ]);

        $person = $request->get('personid'); // id of job type

        $convertString = json_encode($request->get('identities')); //object to json string conversion
        $dataString = json_decode($convertString); // json string to array
        $identity = implode(",", $dataString);

        $id = \App\Identity::withTrashed()->findOrFail($identity);
        $people = \App\Person::withTrashed()->findOrFail($person);

        // delete the identity relationship associated with the person
        $relatedStudents = \App\Identity::with('person')
            ->WhereHas('person', function ($q) use ($person) { // where has job type with id of job type $person
                $q->where('id', $person);
            });
        $relatedStudents->update(['person_id' => null]);

        // Assign new identity
        //associate() is used in a belongsTo() relationship, not hasOne(). Use save() instead of associate. You'll want to save the user first though.
        $id->person()->associate($people)->save();

        return redirect()->route('persons.index')->with('success', 'Data updated successfully');
    }

    public function deletePermanentType($id)
    {
        $person = \App\Person::withTrashed()->findOrFail($id);

        $person->identity()->update(['person_id' => null]);
        $person->forceDelete();
    }

    public function deletePermanent($id)
    {
        $this->deletePermanentType($id);
        return redirect()->route('persons.index')->with('status', 'Person successfully deleted permanently');
    }

    public function editPerson($id)
    {
        $person = \App\Person::find($id);

        return view('persons.edit-person', compact(['person']));
    }

    public function update($id)
    {
        $this->validate($this->request, [
            'type' => 'required',
        ]);

        $user = \App\Person::find($id);
        $user->name = $this->request->input('type');
        $user->save();

        return redirect()->route('persons.index')->with('success', 'Person updated successfully');
    }
}
