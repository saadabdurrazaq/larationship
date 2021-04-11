<?php

namespace App\Http\Controllers;

use App\Applicant;
use App\VerifyUser;
use App\mail\VerifyMail;
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

class TeacherController extends Controller
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
        $items = $request->items ?? 5;
        $page    = $request->has('name') ? $request->get('name') : 1;
        $showingTotal  = $page * $items;
        $admins = \App\Teacher::paginate($items);
        $showingStarted = $admins->currentPage();

        $showData = "Showing $showingStarted to $showingTotal of $count";


        $data = \App\Teacher::with('student')
            ->whereHas('student')
            ->orWhereDoesntHave('student')
            ->paginate($items);

        if ($filterKeyword) {
            $data = \App\Teacher::where("name", "LIKE", "%$filterKeyword%")
                ->paginate($items);
        }

        return view('teachers.index', compact('data'))->with(array('showData' => $showData, 'count' => $count))->withItems($items); //admin mengacu ke table admin di phpmyadmin

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createTeachers()
    {
        $permission = \App\Teacher::get();
        return view('teachers.create', compact('permission'));
    }

    public function storeTeachers(Request $request)
    {
        $this->validate($request, [
            'teacher' => 'required',
        ]);

        $convertString = json_encode($request->get('teacher')); //object to json string conversion
        $dataString = json_decode($convertString); // json string to array
        $teachers = explode(",", $dataString);

        foreach ($teachers as $teacher) {
            \App\Teacher::insert([
                'name'    => $teacher
            ]);
        }

        return redirect()->route('teachers.index')->with('success', 'Teacher data created successfully');
    }

    public function assignStudent($id)
    {
        $teacher = \App\Teacher::find($id);

        // fill the input column with these data
        $students = \App\Student::with('teacher')->whereHas('teacher', function ($q) use ($id) {
            $q->where('id', $id);
        })->get();

        $this->ajaxSearch($id);

        return view('teachers.assign-student', compact(['teacher', 'students']));
    }

    public function ajaxSearch($id)
    {
        $keyword = $this->request->get('q');

        $categories = \App\Student::with('teacher')
            ->orWhereDoesntHave('teacher')
            ->OrWhereHas('teacher', function ($q) use ($id) {
                $q->where('id', $id);
            })
            ->where("name", "LIKE", "%$keyword%")
            ->get();

        return $categories;
    }

    public function storeStudents(Request $request)
    {

        $this->validate($request, [
            'students' => ['required'],
        ]);

        $teacher = $request->get('teacherid'); // id of job type
        $students = $request->get('students'); // id of jobs

        // delete the jobs relationship associated with the Students
        $relatedStudents = \App\Student::with('teacher')
            ->WhereHas('teacher', function ($q) use ($teacher) { // where has job type with id of job type $teacher
                $q->where('id', $teacher);
            });
        $relatedStudents->update(['teacher_id' => null]);

        // Assign new jobs
        $selectedStudents = \App\Student::whereIn('id', $students);
        $selectedStudents->update(['teacher_id' => $teacher]);

        return redirect()->route('teachers.index')->with('success', 'Students created successfully');
    }

    public function deletePermanentType($id)
    {
        $teacher = \App\Teacher::withTrashed()->findOrFail($id);

        $teacher->student()->update(['teacher_id' => null]);
        $teacher->forceDelete();
    }

    public function deletePermanent($id)
    {
        $this->deletePermanentType($id);
        return redirect()->route('teachers.index')->with('status', 'Teacher successfully deleted permanently');
    }

    public function editType($id)
    {
        $teacher = \App\Teacher::find($id);

        return view('teachers.edit-type', compact(['teacher']));
    }

    public function update($id)
    {
        $this->validate($this->request, [
            'teacher' => 'required',
        ]);

        $user = \App\Teacher::find($id);
        $user->name = $this->request->input('teacher');
        $user->save();

        return redirect()->route('teachers.index')->with('success', 'Teacher updated successfully');
    }
}
