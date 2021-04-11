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

class StudentController extends Controller
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

        $count = \App\Teacher::count();
        $items = $request->items ?? 10;
        $page    = $request->has('name') ? $request->get('name') : 1;
        $showingTotal  = $page * $items;
        $admins = \App\Teacher::paginate($items);
        $showingStarted = $admins->currentPage();

        $showData = "Showing $showingStarted to $showingTotal of $count";

        $data = \App\Student::paginate($items);

        if ($filterKeyword) {
            $data = \App\Student::where('name', 'LIKE', "%$filterKeyword%")->paginate($items);
        }

        return view('students.index', compact('data'))->with(array('showData' => $showData, 'count' => $count))->withItems($items); //admin mengacu ke table admin di phpmyadmin

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createStudents()
    {
        $permission = \App\Student::get();
        return view('students.create', compact('permission'));
    }

    public function storeStudents(Request $request)
    {
        $this->validate($request, [
            'students' => 'required',
        ]);

        $convertString = json_encode($request->get('students')); //object to json string conversion
        $dataString = json_decode($convertString); // json string to array
        $students = explode(",", $dataString);

        foreach ($students as $student) {
            \App\Student::insert([
                'name'    => $student
            ]);
        }

        return redirect()->route('students.index')->with('success', 'Students created successfully');
    }

    public function deletePermanentStudent($id)
    {
        $typeOfStudent = \App\Student::withTrashed()->findOrFail($id);
        $typeOfStudent->forceDelete();
    }

    public function deletePermanent($id)
    {
        $this->deletePermanentStudent($id);
        return redirect()->route('students.index')->with('status', 'Type of student successfully deleted permanently');
    }

    public function editStudent($id)
    {
        $student = \App\Student::find($id);

        return view('students.edit-student', compact(['student']));
    }

    public function update($id)
    {
        $this->validate($this->request, [
            'student' => 'required',
        ]);

        $user = \App\Student::find($id);
        $user->name = $this->request->input('student');
        $user->save();

        return redirect()->route('students.index')->with('success', 'student updated successfully');
    }
}
