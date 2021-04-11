<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes(); //The method routes(); is implemented in core of the framework in the file /vendor/laravel/framework/src/Illuminate/Routing/Router.php public function auth()

Route::get('/home', 'HomeController@index')->name('home');

//facebook login
Route::get('/redirect', 'SocialController@redirect');
Route::get('auth/{provider}/callback', 'SocialController@callback');

//Google login
Route::get('/redirect/google', 'SocialAuthGoogleController@redirect')->name('google');
Route::get('/callback', 'SocialAuthGoogleController@callback');

//verify user after registration
Auth::routes(['verify' => true]); //untuk menjalankan kode ini tambahkan implements MustVerifyEmail di User.php

//applicant register page
Route::get('/applicant', 'ApplicantController@register')->name('applicant');

Route::group(['middleware' => ['auth']], function () {
    //Manage Role
    Route::resource('roles', 'RoleController');

    //Manage Users
    Route::get('/users/adminPDF', 'UserController@downloadPDF')->name('users.pdf');
    Route::get('/users/downloadExcel', 'UserController@downloadExcel')->name('users.excel');
    Route::get('/users/downloadWord', 'UserController@downloadWord')->name('users.word');
    Route::get('/users/activeAdminPDF', 'UserController@downloadActiveAdminPDF')->name('users.pdfactiveadmin');
    Route::get('/users/downloadActiveExcel', 'UserController@downloadActiveExcel')->name('users.activeexcel');
    Route::get('/users/activeAdminWord', 'UserController@downloadActiveAdminWord')->name('users.wordactiveadmin');
    Route::get('/users/inactiveAdminPDF', 'UserController@downloadInactiveAdminPDF')->name('users.pdfinactiveadmin');
    Route::get('/users/downloadInactiveExcel', 'UserController@downloadInactiveExcel')->name('users.inactiveexcel');
    Route::get('/users/inactiveAdminWord', 'UserController@downloadInactiveAdminWord')->name('users.wordinactiveadmin');
    Route::get('users/trash', 'UserController@trash')->name('users.trash');
    Route::get('/user/{id}/restore', 'UserController@restore')->name('users.restore');
    Route::get('usersadminRestoreAll', 'UserController@restoreMultiple');
    Route::delete('/user/{id}/delete-permanent', 'UserController@deletePermanent')->name('users.delete-permanent');
    Route::get('usersadminDeleteAll', 'UserController@deleteMultiple');
    Route::name('users.active')->get('/users/active', 'UserController@active');
    Route::name('users.inactive')->get('/users/inactive', 'UserController@inactive');
    Route::get('/user/{id}/activate', 'UserController@activate')->name('users.activate');
    Route::get('/user/{id}/deactivate', 'UserController@deactivate')->name('user.deactivate');
    Route::get('usersadminDeactivateAll', 'UserController@deactivateMultiple');
    Route::get('usersadminActivateAll', 'UserController@activateMultiple');
    Route::get('searchrole', 'UserController@ajaxSearch');
    Route::get('/user/{id}/edit',  ['as' => 'user.edit', 'uses' => 'UserController@edit']); //for breadcrumbs
    Route::resource('users', 'UserController');
    Route::get('usersadminTrashAll', 'UserController@destroyMultiple'); //for multiple trash

    //Profile
    Route::get('/profile/{username}', 'ProfileUserController@show')->name('show.applicant');
    Route::get('saveForm', 'ProfileUserController@update');
    Route::get('/usersadmin/{id}/avatar', 'ProfileUserController@deleteAvatar')->name('delete.avatar');
    Route::get('password/change', 'ProfileUserController@changePassword');
    Route::post('password/change', 'ProfileUserController@postChangePassword');
    Route::resource("profile", 'ProfileUserController'); //tidak boleh diletakkan di urutan pertama agar route ke view bekerja. Kode ini akan mengenerate otomatis route edit, index, store, create, edit, destroy, update, show

    //applicant index page
    Route::get('applicantsTrashAll', 'ApplicantController@destroyMultiple'); //for multiple trash
    Route::get('applicants/trash', 'ApplicantController@trash')->name('applicants.trash');
    Route::get('/applicants/{id}/restore', 'ApplicantController@restore')->name('applicants.restore');
    Route::get('applicantsRestoreAll', 'ApplicantController@restoreMultiple');
    Route::delete('/applicants/{id}/delete-permanent', 'ApplicantController@deletePermanent')->name('applicants.delete-permanent');
    Route::get('applicantsDeleteAll', 'ApplicantController@deleteMultiple');
    Route::name('applicants.pending')->get('/applicants/pending', 'ApplicantController@pending');
    Route::name('applicants.showapproved')->get('/applicants/show-approved', 'ApplicantController@showApproved');
    Route::name('applicants.showrejected')->get('/applicants/show-rejected', 'ApplicantController@showRejected');
    Route::get('/applicant/{id}/approve', 'ApplicantController@approve')->name('applicants.approve');
    Route::get('applicantsApproveAll', 'ApplicantController@approveMultiple');
    Route::get('applicantsRejectAll', 'ApplicantController@rejectMultiple');
    Route::get('/applicant/{id}/reject', 'ApplicantController@reject')->name('applicants.reject');
    Route::get('/applicant/{id}/hold', 'ApplicantController@hold')->name('applicants.hold');
    Route::get('applicantsHoldAll', 'ApplicantController@holdMultiple');
    Route::resource('applicants', 'ApplicantController');

    // ========================================================================================================

    // One to one relationship

    // Master data for type of persons 
    Route::get('persons/index', 'PersonController@index')->name('persons.index');
    Route::get('persons/create', 'PersonController@createPersons')->name('persons.create');
    Route::post('/persons/store', 'PersonController@storePersons')->name('persons.store');
    Route::delete('/persons/{id}/delete-permanent', 'PersonController@deletePermanent')->name('person.delete-permanent');
    Route::get('searchidentities/{id}', 'PersonController@ajaxSearch')->name('searchidentities');
    Route::get('persons/{id}/assign-identity', 'PersonController@assignIdentity')->name('persons.assign-identity');
    Route::post('/persons/store-identity', 'PersonController@storeIdentities')->name('persons.store-identity');
    Route::get('/persons/{id}/edit', 'PersonController@editPerson')->name('persons.edit-person');
    Route::get('/persons/{id}/update', 'PersonController@update')->name('persons.update');

    // Master data for identities
    Route::get('identities/index', 'IdentityController@index')->name('identities.index');
    Route::get('identities/create', 'IdentityController@createIdentities')->name('identities.create');
    Route::post('/identities/store', 'IdentityController@storeIdentities')->name('identities.store');
    Route::delete('/identity/{id}/delete-permanent', 'IdentityController@deletePermanent')->name('identity.delete-permanent');
    Route::get('/identities/{id}/edit', 'IdentityController@editIdentity')->name('identities.edit-identity');
    Route::get('/identities/{id}/update', 'IdentityController@update')->name('identities.update');

    // ========================================================================================================

    // One to many relationship

    // Master data for type of teachers
    Route::get('teachers/index', 'TeacherController@index')->name('teachers.index');
    Route::get('teachers/create', 'TeacherController@createTeachers')->name('teachers.create');
    Route::post('/teachers/store', 'TeacherController@storeTeachers')->name('teachers.store');
    Route::delete('/teachers/{id}/delete-permanent', 'TeacherController@deletePermanent')->name('teacher.delete-permanent');
    Route::get('searchstudents/{id}', 'TeacherController@ajaxSearch')->name('searchstudents');
    Route::get('teachers/{id}/assign-student', 'TeacherController@assignStudent')->name('teachers.assign-student');
    Route::post('/teachers/store-student', 'TeacherController@storeStudents')->name('teachers.store-student');
    Route::get('/teachers/{id}/edit', 'TeacherController@editType')->name('teachers.edit-type');
    Route::get('/teachers/{id}/update', 'TeacherController@update')->name('teachers.update');

    // Master data for students
    Route::get('students/index', 'StudentController@index')->name('students.index');
    Route::get('students/create', 'StudentController@createStudents')->name('students.create');
    Route::post('/students/store', 'StudentController@storeStudents')->name('students.store');
    Route::delete('/student/{id}/delete-permanent', 'StudentController@deletePermanent')->name('student.delete-permanent');
    Route::get('/students/{id}/edit', 'StudentController@editStudent')->name('students.edit-student');
    Route::get('/students/{id}/update', 'StudentController@update')->name('students.update');

    // ========================================================================================================

    // Many to many relationship

    // Master data for type of jobs 
    Route::get('job-types/index', 'JobTypeController@index')->name('job-types.index');
    Route::get('job-types/create', 'JobTypeController@createTypeOfJobs')->name('job-types.create');
    Route::post('/job-types/store', 'JobTypeController@storeTypeOfJobs')->name('job-types.store');
    Route::delete('/job-types/{id}/delete-permanent', 'JobTypeController@deletePermanent')->name('jobtype.delete-permanent');
    Route::get('searchjobs/{id}', 'JobTypeController@ajaxSearch')->name('searchjobs');
    Route::get('job-types/{id}/assign-job', 'JobTypeController@assignJob')->name('job-types.assign-job');
    Route::post('/job-types/store-job', 'JobTypeController@storeJobs')->name('job-types.store-job');
    Route::get('/job-types/{id}/edit', 'JobTypeController@editType')->name('job-types.edit-type');
    Route::get('/job-types/{id}/update', 'JobTypeController@update')->name('job-types.update');

    // Master data for jobs
    Route::get('jobs/index', 'JobController@index')->name('jobs.index');
    Route::get('jobs/create', 'JobController@createJobs')->name('jobs.create');
    Route::post('/jobs/store', 'JobController@storeJobs')->name('jobs.store');
    Route::delete('/job/{id}/delete-permanent', 'JobController@deletePermanent')->name('job.delete-permanent');
    Route::get('/jobs/{id}/edit', 'JobController@editJob')->name('jobs.edit-job');
    Route::get('/jobs/{id}/update', 'JobController@update')->name('jobs.update');
});
