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

class ApplicantController extends Controller
{
    
    /**
    * Create a new controller instance.
    *
    * @return void
    */
    public function __construct()
    {
        //
    }

    public function register()
    {
        return view('applicants.register');
    }

    public function store(Request $request) 
    {
         $validation = \Validator::make($request->all(),[
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users', 'unique:applicants'],
            'phone' => ['required', 'digits_between:10,12', 'unique:users', 'unique:applicants'],
            'username' => ['required','min:5', 'max:20', 'unique:users', 'unique:applicants', 'regex:/^\S*$/u'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ])->validate();
        
        $new_user = new \App\Applicant(); //Panggil model User
        $new_user->name = $request->get('name');
        $new_user->email = $request->get('email');
        $new_user->phone = $request->get('phone');
        $new_user->username = $request->get('username');
        $new_user->gender = $request->get('gender');
        $new_user->password = \Hash::make($request->get('password'));
        $new_user->assignRole('Applicant');
        $new_user->save();

        $new_user->notify(new MailForApplicant($new_user)); 

        return redirect()->route('applicant')->with('status', 'Thank you for registration! You will be notified if you are approved');
    }

    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index(Request $request) {
        $filterKeyword = $request->get('keyword');
        $status = $request->get('status');

        $activeStatus = \App\Applicant::where('status', "Approved")->count();
        $inactiveStatus = \App\Applicant::where('status', "Rejected")->count();
        $countTrash = \App\Applicant::onlyTrashed()->count();
        $countPending = \App\Applicant::where('status', "Pending")->count();

        $count = \App\Applicant::count();
        $items = $request->items ?? 5;
        $page    = $request->has('name') ? $request->get('name') : 1;
        $showingTotal  = $page * $items;
        $admins= \App\Applicant::paginate($items);
        $showingStarted = $admins->currentPage(); 

        $showData = "Showing $showingStarted to $showingTotal of $count";

        if($status) {
            $data = \App\Applicant::where('status', $status)->paginate($items);
        } else {
            $data = \App\Applicant::paginate($items);
        }

        if($filterKeyword) {
            if($status) {
                $data = \App\Applicant::where('name', 'LIKE', "%$filterKeyword%")->orWhere('email', 'LIKE', "%$filterKeyword%")->where('status', $status)->paginate($items);
            } else {
                $data = \App\Applicant::where('name', 'LIKE', "%$filterKeyword%")->orWhere('email', 'LIKE', "%$filterKeyword%")->paginate($items);
            }
        } 
    
        return view('applicants.index', compact('data'))->with(array('countPending' => $countPending, 'showData' => $showData, 'count' => $count, 'activeStatus' => $activeStatus, 'inactiveStatus' => $inactiveStatus, 'countTrash' => $countTrash))->withItems($items); //admin mengacu ke table admin di phpmyadmin
    }

    public function destroy($id) {
        Applicant::find($id)->delete();
        return redirect()->route('applicants.index')->with('success','Applicant deleted successfully');
    }

    public function destroyMultiple(Request $request) {
        $get_ids = $request->ids;
        $ids = explode(',', $get_ids);
        $users = \App\Applicant::whereIn('id', $ids);
        $users->delete(); 

        return response()->json(['success' => "Applicants successfully moved to trash."]);
    }

    public function trash(Request $request) {
        $filterKeyword = $request->get('keyword');
        $status = $request->get('status');

        $count = \App\Applicant::count();
        $activeStatus = \App\Applicant::where('status', "Approved")->count();
        $inactiveStatus = \App\Applicant::where('status', "Rejected")->count();
        $countTrash = \App\Applicant::onlyTrashed()->count();
        $countPending = \App\Applicant::where('status', "Pending")->count();

        $count = \App\Applicant::count();
        $items = $request->items ?? 5;
        $page    = $request->has('name') ? $request->get('name') : 1;
        $showingTotal  = $page * $items;
        $admins= \App\Applicant::paginate($items);
        $showingStarted = $admins->currentPage(); 

        $showData = "Showing $showingStarted to $showingTotal of $countTrash";

        if($status) {
            $data = \App\Applicant::where('status', $status)->paginate($items);
        } else {
            $data = \App\Applicant::onlyTrashed()->paginate($items);
        }

        if($filterKeyword) {
            if($status) {
                $data = \App\Applicant::onlyTrashed()->where('name', 'LIKE', "%$filterKeyword%")->orWhere('email', 'LIKE', "%$filterKeyword%")->where('status', $status)->paginate($items);
            } else {
                $data = \App\Applicant::onlyTrashed()->where('name', 'LIKE', "%$filterKeyword%")->orWhere('email', 'LIKE', "%$filterKeyword%")->paginate($items);
            }
        } 

        return view('applicants.trash', compact('data'))->with(array('countPending' => $countPending, 'showData' => $showData, 'count' => $count, 'activeStatus' => $activeStatus, 'inactiveStatus' => $inactiveStatus, 'countTrash' => $countTrash))->withItems($items); //admin mengacu ke table admin di phpmyadmin
    }

    public function restore($id) {
        $category = \App\Applicant::withTrashed()->findOrFail($id);

        if($category->trashed()) {
            $category->restore(); 
        } else {
            return redirect()->route('applicants.trash')->with('status', 'Applicant is not in trash');
        }  
      
        return redirect()->route('applicants.trash')->with('status', 'Applicant successfully restored');
    }

    public function restoreMultiple(Request $request) {
        $get_ids = $request->ids;
        $ids = explode(',', $get_ids);
        $users = \App\Applicant::whereIn('id', $ids);
        $users->restore();
        
        return response()->json(['success' => "Applicants successfully restored"]);
    }

    public function deletePermanent($id) {
        $category = \App\Applicant::withTrashed()->findOrFail($id);

        $selectedUsers = \App\User::where('username', function($query) use ($id) { //retrieve a collection of users from users table whereIn usernames. (continue below)
            $query->select('username')->from('applicants')->where('id', $id); //$query(whereIn usernames in table users) like selected usernames in applicants table. (To get selected usernames in applicants table, use whereIn('id', $ids) parameter.)
        });

        if(!$category->trashed()){
            return redirect()->route('applicants.trash')->with('status', 'Can not delete permanent applicant');
        } 
        else {
            if($selectedUsers) {
                $selectedUsers->forceDelete(); 
                $category->forceDelete(); 
            } else {
                $category->forceDelete();
            }
            return redirect()->route('applicants.trash')->with('status', 'Applicant permanently deleted');
        }
    }

    public function deleteMultiple(Request $request) {
        $get_ids = $request->ids;
        $ids = explode(',', $get_ids);
        $users = \App\Applicant::whereIn('id', $ids);

        $selectedUsers = \App\User::whereIn('username', function($query) use ($ids) { //retrieve a collection of users from users table whereIn usernames. (continue below)
            $query->select('username')->from('applicants')->whereIn('id', $ids); //$query(whereIn usernames in table users) like selected usernames in applicants table. (To get selected usernames in applicants table, use whereIn('id', $ids) parameter.)
        });

        if($selectedUsers) {
            $selectedUsers->forceDelete(); 
            $users->forceDelete(); 
        } else {
            $users->forceDelete(); 
        }

        return response()->json(['success' => "Applicants successfully permanently deleted"]);
    }

    public function pending(Request $request) {
        $filterKeyword = $request->get('keyword');
        $status = $request->get('status');

        $count = \App\Applicant::count();
        $activeStatus = \App\Applicant::where('status', "Approved")->count();
        $inactiveStatus = \App\Applicant::where('status', "Rejected")->count();
        $countTrash = \App\Applicant::onlyTrashed()->count();
        $countPending = \App\Applicant::where('status', "Pending")->count();

        $count = \App\Applicant::count();
        $items = $request->items ?? 5;
        $page    = $request->has('name') ? $request->get('name') : 1;
        $showingTotal  = $page * $items;
        $admins= \App\Applicant::paginate($items);
        $showingStarted = $admins->currentPage(); 

        $showData = "Showing $showingStarted to $showingTotal of $countPending";

        if($status) {
            $data = \App\Applicant::where('status', $status)->paginate($items);
        } else {
            $data = \App\Applicant::where('status', "Pending")->paginate($items);
        }

        if($filterKeyword) {
            if($status) {
                $data = \App\Applicant::where('name', 'LIKE', "%$filterKeyword%")->orWhere('email', 'LIKE', "%$filterKeyword%")->where('status', $status)->paginate($items);
            } else {
                $data = \App\Applicant::where('name', 'LIKE', "%$filterKeyword%")->orWhere('email', 'LIKE', "%$filterKeyword%")->paginate($items);
            }
        } 

        return view('applicants.pending', compact('data'))->with(array('countPending' => $countPending, 'showData' => $showData, 'count' => $count, 'activeStatus' => $activeStatus, 'inactiveStatus' => $inactiveStatus, 'countTrash' => $countTrash))->withItems($items); //admin mengacu ke table admin di phpmyadmin
    }

    public function approve($id) {
        $applicant = \App\Applicant::where('id', $id);
        $applicant->update(['status' => 'Approved']);

        //replicate the data to users table
        $find_one = \App\Applicant::where('id', $id)->firstOrFail();
        $find_one->makeHidden(['status', 'id', 'email_sent']);
        $new_user = $find_one->replicate();
        $new_user = $find_one->toArray();

        $getSelectedUser = \App\User::where('username', function($query) use ($id) { //retrieve a collection of users from users table where username in table users. (continue below)
            $query->select('username')->from('applicants')->where('id', $id); //$query(where usernames in table users) like selected usernames in applicants table. (To get selected usernames in applicants table, use where('id', $id) parameter.)
        });

        if($getSelectedUser) {
            $getSelectedUser->forceDelete(); 
            $user = \App\User::firstOrCreate($new_user);
            $user->assignRole('Applicant');
            $user->password = $find_one->password;
            $user->save();
        } else {
            $user = \App\User::firstOrCreate($new_user);
            $user->assignRole('Applicant');
            $user->password = $find_one->password;
            $user->save();
        }

        $user->notify(new ApprovedApplicant($user)); 
      
        return redirect()->route('applicants.pending')->with('status', 'User successfully approved');
    }

    public function approveMultiple(Request $request) {
        $get_ids = $request->ids;
        $ids = explode(',', $get_ids); 

        $users = \App\Applicant::whereIn('id', $ids);
        $users->update(['status' => 'Approved']); 

        $find_selected = \App\Applicant::whereIn('id', $ids)->get();
        $find_selected->makeHidden(['status', 'id', 'email_sent']);
        $find_selected->makeVisible(['password']);
        $new_users = $find_selected->toArray();

        $selectedUsers = \App\User::whereIn('username', function($query) use($ids) { //retrieve a collection of users from users table whereIn usernames. (continue below)
            $query->select('username')->from('applicants')->whereIn('id', $ids); //$query(whereIn usernames in table users) like selected usernames in applicants table. (To get selected usernames in applicants table, use whereIn('id', $ids) parameter.)
        });

        if($selectedUsers) {
           $selectedUsers->forceDelete(); //prevent duplicate data in users table
           $bulkUsers = \App\User::insert($new_users);
        } else {
            $bulkUsers = \App\User::insert($new_users);
        }

        $getSelectedUsers = \App\User::whereIn('username', function($query) use($ids) { //retrieve a collection of users from users table whereIn usernames. (continue below)
            $query->select('username')->from('applicants')->whereIn('id', $ids); //$query(whereIn usernames in table users) like selected usernames in applicants table. (To get selected usernames in applicants table, use whereIn('id', $ids) parameter.)
        })->get();
        
        foreach($getSelectedUsers as $user) {
            $user->assignRole('Applicant');
            $user->save();
        }

        $users->each(function($user) {
            Mail::to($user)->send(new SendEmail());
        });

        if(count(Mail::failures()) > 0) {
            //The Mail::failures() will return an array of failed emails.
            foreach(Mail::failures() as $sent_status) { 
                $newData = \App\SentStatus::create(['email' => $sent_status]);
                $newData->save();
            }
        } 

        return response()->json(['success' => "Selected applicant(s) successfully approved."]);
    }

    public function showApproved(Request $request) {
        $filterKeyword = $request->get('keyword');
        $status = $request->get('status');

        $count = \App\Applicant::count();
        $activeStatus = \App\Applicant::where('status', "Approved")->count();
        $inactiveStatus = \App\Applicant::where('status', "Rejected")->count();
        $countTrash = \App\Applicant::onlyTrashed()->count();
        $countPending = \App\Applicant::where('status', "Pending")->count();

        $count = \App\Applicant::count();
        $items = $request->items ?? 5;
        $page    = $request->has('name') ? $request->get('name') : 1;
        $showingTotal  = $page * $items;
        $admins= \App\Applicant::paginate($items);
        $showingStarted = $admins->currentPage(); 

        $showData = "Showing $showingStarted to $showingTotal of $countPending";

        if($status) {
            $data = \App\Applicant::where('status', $status)->paginate($items);
        } else {
            $data = \App\Applicant::where('status', "Approved")->paginate($items);
        }

        if($filterKeyword) {
            if($status) {
                $data = \App\Applicant::where('name', 'LIKE', "%$filterKeyword%")->orWhere('email', 'LIKE', "%$filterKeyword%")->where('status', $status)->paginate($items);
            } else {
                $data = \App\Applicant::where('name', 'LIKE', "%$filterKeyword%")->orWhere('email', 'LIKE', "%$filterKeyword%")->paginate($items);
            }
        } 

        return view('applicants.show-approved', compact('data'))->with(array('countPending' => $countPending, 'showData' => $showData, 'count' => $count, 'activeStatus' => $activeStatus, 'inactiveStatus' => $inactiveStatus, 'countTrash' => $countTrash))->withItems($items); //admin mengacu ke table admin di phpmyadmin
    }

    public function showRejected(Request $request) {
        $filterKeyword = $request->get('keyword');
        $status = $request->get('status');

        $count = \App\Applicant::count();
        $activeStatus = \App\Applicant::where('status', "Approved")->count();
        $inactiveStatus = \App\Applicant::where('status', "Rejected")->count();
        $countTrash = \App\Applicant::onlyTrashed()->count();
        $countPending = \App\Applicant::where('status', "Pending")->count(); 

        $count = \App\Applicant::count();
        $items = $request->items ?? 5;
        $page    = $request->has('name') ? $request->get('name') : 1;
        $showingTotal  = $page * $items;
        $admins= \App\Applicant::paginate($items);
        $showingStarted = $admins->currentPage(); 

        $showData = "Showing $showingStarted to $showingTotal of $countPending";

        if($status) {
            $data = \App\Applicant::where('status', $status)->paginate($items);
        } else {
            $data = \App\Applicant::where('status', "Rejected")->paginate($items);
        }

        if($filterKeyword) {
            if($status) {
                $data = \App\Applicant::where('name', 'LIKE', "%$filterKeyword%")->orWhere('email', 'LIKE', "%$filterKeyword%")->where('status', $status)->paginate($items);
            } else {
                $data = \App\Applicant::where('name', 'LIKE', "%$filterKeyword%")->orWhere('email', 'LIKE', "%$filterKeyword%")->paginate($items);
            }
        } 

        return view('applicants.show-rejected', compact('data'))->with(array('countPending' => $countPending, 'showData' => $showData, 'count' => $count, 'activeStatus' => $activeStatus, 'inactiveStatus' => $inactiveStatus, 'countTrash' => $countTrash))->withItems($items); //admin mengacu ke table admin di phpmyadmin
    }

    public function reject($id) {
        $applicant = \App\Applicant::where('id', $id);
        $applicant->update(['status' => 'Rejected']);

        $getSelectedUser = \App\User::where('username', function($query) use ($id) { //retrieve a collection of users from users table where username in table users. (continue below)
            $query->select('username')->from('applicants')->where('id', $id); //$query(where usernames in table users) like selected usernames in applicants table. (To get selected usernames in applicants table, use where('id', $id) parameter.)
        });

        if($getSelectedUser) {
            $getSelectedUser->forceDelete(); 
        }

        $user = \App\Applicant::where('id', $id)->firstOrFail();
        $user->notify(new RejectedApplicant($user)); 
      
        return redirect()->route('applicants.pending')->with('status', 'User successfully rejected');

    }

    public function rejectMultiple(Request $request) {
        $get_ids = $request->ids;
        $ids = explode(',', $get_ids); 

        $users = \App\Applicant::whereIn('id', $ids);
        $users->update(['status' => 'Rejected']); 

        $selectedUsers = \App\User::whereIn('username', function($query) use($ids) { //retrieve a collection of users from users table whereIn usernames. (continue below)
            $query->select('username')->from('applicants')->whereIn('id', $ids); //$query(whereIn usernames in table users) like selected usernames in applicants table. (To get selected usernames in applicants table, use whereIn('id', $ids) parameter.)
        });

        if($selectedUsers) {
           $selectedUsers->forceDelete(); //prevent duplicate data in users table
        }

        $users->each(function($user) {
            Mail::to($user)->send(new RejectedEmail());
        });

        if(count(Mail::failures()) > 0) {
            //The Mail::failures() will return an array of failed emails.
            foreach(Mail::failures() as $sent_status) { 
                $newData = \App\SentStatus::create(['email' => $sent_status]);
                $newData->save();
            }
        } 

        return response()->json(['success' => "Selected applicant(s) successfully rejected."]);
    }

    public function hold($id) {
        $applicant = \App\Applicant::where('id', $id);
        $applicant->update(['status' => 'Pending']);

        $getSelectedUser = \App\User::where('username', function($query) use ($id) { //retrieve a collection of users from users table where username in table users. (continue below)
            $query->select('username')->from('applicants')->where('id', $id); //$query(where usernames in table users) like selected usernames in applicants table. (To get selected usernames in applicants table, use where('id', $id) parameter.)
        });

        if($getSelectedUser) {
            $getSelectedUser->forceDelete(); 
        }

        $user = \App\Applicant::where('id', $id)->firstOrFail();
        $user->notify(new ApplicantOnHold($user)); 
      
        return redirect()->route('applicants.pending')->with('status', 'User successfully on hold');
    }

    public function holdMultiple(Request $request) {
        $get_ids = $request->ids;
        $ids = explode(',', $get_ids); 

        $users = \App\Applicant::whereIn('id', $ids);
        $users->update(['status' => 'Pending']); 

        $selectedUsers = \App\User::whereIn('username', function($query) use($ids) { //retrieve a collection of users from users table whereIn usernames. (continue below)
            $query->select('username')->from('applicants')->whereIn('id', $ids); //$query(whereIn usernames in table users) like selected usernames in applicants table. (To get selected usernames in applicants table, use whereIn('id', $ids) parameter.)
        });

        if($selectedUsers) {
           $selectedUsers->forceDelete(); //prevent duplicate data in users table
        }

        $users->each(function($user) {
            Mail::to($user)->send(new OnHoldEmail());
        });

        if(count(Mail::failures()) > 0) {
            //The Mail::failures() will return an array of failed emails.
            foreach(Mail::failures() as $sent_status) { 
                $newData = \App\SentStatus::create(['email' => $sent_status]);
                $newData->save();
            }
        } 

        return response()->json(['success' => "Selected applicant(s) successfully on Hold."]);
    }

    public function show($id)
    {
        $user = \App\Applicant::find($id);
        return view('applicants.show', compact('user'));
    }

}
