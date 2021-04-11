@extends('layouts.app')

@section('title') Edit User @endsection

@section('profile')
{{ Breadcrumbs::render('list-profile') }}
@endsection

@section('title2')
<h1>Profile</h1> 
@endsection

@section('content')

@if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif
@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if (session('status'))
    <p id="msg_div" class="alert alert-success">{{ session('status') }} <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a></p>
@endif
@foreach (['danger', 'warning', 'success', 'info'] as $msg)
    @if(Session::has('alert-' . $msg))
        <p class="alert alert-{{ $msg }}">{{ Session::get('alert-' . $msg) }} <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a></p>
    @endif
@endforeach
<!-- Main content -->
<section class="content">
<div class="container-fluid">
    <div class="row">
    <div class="col-md-3">

        <!-- Profile Image -->
        <div class="card card-primary card-outline">
        <div class="card-body box-profile">
            <div class="text-center">
                @if($users->avatar == null) 
                    @if($users->gender == 'Male')
                    <img src="{{URL::asset('public/bower_components/admin-lte/dist/img/fb-male.jpg')}}" class="profile-user-img img-fluid img-circle" alt="User Image">
                    @else
                    <img src="{{URL::asset('public/bower_components/admin-lte/dist/img/female-fb.jpg')}}" class="profile-user-img img-fluid img-circle" alt="User Image">
                    @endif
                @else
                <img src="{{ asset('storage/app/public/'.$users->avatar) }}" class="profile-user-img img-fluid img-circle" />
                @endif
            </div>

            <h3 class="profile-username text-center">
                @if(\Auth::user()) {{Auth::user()->name}} @endif
            </h3>

            <p class="text-muted text-center">@if(\Auth::user()) {{Auth::user()->getRoleNames()}} @endif</p>

            <ul class="list-group list-group-unbordered mb-3">
            <li class="list-group-item">
                <b>Last Login</b> <a class="float-right">12/10/2018 10:35:15</a>
            </li>
            <li class="list-group-item">
                <b>Joining Period</b> <a class="float-right">3y1mos</a>
            </li>
            <li class="list-group-item">
                <b>Friends</b> <a class="float-right">13,287</a>
            </li>
            </ul>

            <a href="#" class="btn btn-primary btn-block"><b>Show All</b></a>
        </div>
        <!-- /.card-body -->
        </div>
        <!-- /.card -->

        <!-- About Me Box -->
        <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">About Me</h3>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <strong><i class="fas fa-book mr-1"></i> Education</strong>

            <p class="text-muted">
            B.S. in Computer Science from the University of Tennessee at Knoxville
            </p>

            <hr>

            <strong><i class="fas fa-map-marker-alt mr-1"></i> Location</strong>

            <p class="text-muted">Malibu, California</p>

            <hr>

            <strong><i class="fas fa-pencil-alt mr-1"></i> Skills</strong>

            <p class="text-muted">
            <span class="tag tag-danger">UI Design</span>
            <span class="tag tag-success">Coding</span>
            <span class="tag tag-info">Javascript</span>
            <span class="tag tag-warning">PHP</span>
            <span class="tag tag-primary">Node.js</span>
            </p>

            <hr>

            <strong><i class="far fa-file-alt mr-1"></i> Notes</strong>

            <p class="text-muted">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam fermentum enim neque.</p>
        </div>
        <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.col -->
    <div class="col-md-9">
        <div class="card">
        <div class="card-header p-2">
        <ul class="nav nav-pills" id="myTab">
            <li class="nav-item"><a class="nav-link active" id="fetchActivity" href="#activity" data-toggle="tab">Activity</a></li>
            <li class="nav-item"><a class="nav-link" id="fetchDetail" href="#detail" data-id="{{ $users->username }}" data-toggle="tab">Detail</a></li>
            <li class="nav-item"><a class="nav-link" id="fetchChangepassword" href="#changepassword" data-toggle="tab">Change Password</a></li>    
        </ul>
        </div><!-- /.card-header -->
        <div class="card-body">
            <div class="tab-content">

            <div class="active tab-pane" id="activity">
                   <!-- Post -->
                   <div class="post">
                    <div class="user-block">
                        <img class="img-circle img-bordered-sm" src="../../dist/img/user1-128x128.jpg" alt="user image">
                        <span class="username">
                        <a href="#">Jonathan Burke Jr.</a>
                        <a href="#" class="float-right btn-tool"><i class="fas fa-times"></i></a>
                        </span>
                        <span class="description">Shared publicly - 7:30 PM today</span>
                    </div>
                    <!-- /.user-block -->
                    <p>
                        Lorem ipsum represents a long-held tradition for designers,
                        typographers and the like. Some people hate it and argue for
                        its demise, but others ignore the hate as they create awesome
                        tools to help create filler text for everyone from bacon lovers
                        to Charlie Sheen fans.
                    </p>
    
                    <p>
                        <a href="#" class="link-black text-sm mr-2"><i class="fas fa-share mr-1"></i> Share</a>
                        <a href="#" class="link-black text-sm"><i class="far fa-thumbs-up mr-1"></i> Like</a>
                        <span class="float-right">
                        <a href="#" class="link-black text-sm">
                            <i class="far fa-comments mr-1"></i> Comments (5)
                        </a>
                        </span>
                    </p>
    
                    <input class="form-control form-control-sm" type="text" placeholder="Type a comment">
                    </div>
                    <!-- /.post -->
    
                    <!-- Post -->
                    <div class="post clearfix">
                    <div class="user-block">
                        <img class="img-circle img-bordered-sm" src="../../dist/img/user7-128x128.jpg" alt="User Image">
                        <span class="username">
                        <a href="#">Sarah Ross</a>
                        <a href="#" class="float-right btn-tool"><i class="fas fa-times"></i></a>
                        </span>
                        <span class="description">Sent you a message - 3 days ago</span>
                    </div>
                    <!-- /.user-block -->
                    <p>
                        Lorem ipsum represents a long-held tradition for designers,
                        typographers and the like. Some people hate it and argue for
                        its demise, but others ignore the hate as they create awesome
                        tools to help create filler text for everyone from bacon lovers
                        to Charlie Sheen fans.
                    </p>
    
                    <form class="form-horizontal">
                        <div class="input-group input-group-sm mb-0">
                        <input class="form-control form-control-sm" placeholder="Response">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-danger">Send</button>
                        </div>
                        </div>
                    </form>
                    </div>
                    <!-- /.post -->
    
                    <!-- Post -->
                    <div class="post">
                    <div class="user-block">
                        <img class="img-circle img-bordered-sm" src="../../dist/img/user6-128x128.jpg" alt="User Image">
                        <span class="username">
                        <a href="#">Adam Jones</a>
                        <a href="#" class="float-right btn-tool"><i class="fas fa-times"></i></a>
                        </span>
                        <span class="description">Posted 5 photos - 5 days ago</span>
                    </div>
                    <!-- /.user-block -->
                    <div class="row mb-3">
                        <div class="col-sm-6">
                        <img class="img-fluid" src="../../dist/img/photo1.png" alt="Photo">
                        </div>
                        <!-- /.col -->
                        <div class="col-sm-6">
                        <div class="row">
                            <div class="col-sm-6">
                            <img class="img-fluid mb-3" src="../../dist/img/photo2.png" alt="Photo">
                            <img class="img-fluid" src="../../dist/img/photo3.jpg" alt="Photo">
                            </div>
                            <!-- /.col -->
                            <div class="col-sm-6">
                            <img class="img-fluid mb-3" src="../../dist/img/photo4.jpg" alt="Photo">
                            <img class="img-fluid" src="../../dist/img/photo1.png" alt="Photo">
                            </div>
                            <!-- /.col -->
                        </div>
                        <!-- /.row -->
                        </div>
                        <!-- /.col -->
                    </div>
                    <!-- /.row -->
    
                    <p>
                        <a href="#" class="link-black text-sm mr-2"><i class="fas fa-share mr-1"></i> Share</a>
                        <a href="#" class="link-black text-sm"><i class="far fa-thumbs-up mr-1"></i> Like</a>
                        <span class="float-right">
                        <a href="#" class="link-black text-sm">
                            <i class="far fa-comments mr-1"></i> Comments (5)
                        </a>
                        </span>
                    </p>
    
                    <input class="form-control form-control-sm" type="text" placeholder="Type a comment">
                    </div>
                    <!-- /.post --> 
            </div>
            <!-- /.tab-pane -->

            <div class="tab-pane" id="detail">
                @include("profile.detail")
            </div>

            <div class="tab-pane" id="changepassword">
                @include("profile.changepassword")
            </div>
            <!-- /.tab-pane -->

            </div>
            <!-- /.tab-content -->
        </div><!-- /.card-body -->
        </div>
        <!-- /.nav-tabs-custom -->
    </div>
    <!-- /.col -->
    </div>
    <!-- /.row -->
</div><!-- /.container-fluid -->

</section>
<!-- /.content -->
@endsection
@section('crud-js') <!--terkait dengan kode@yield('crud-js') di app.blade.php-->
<script>
$('#myTab a').click(function(e) {
  e.preventDefault();
  $(this).tab('show');
});

// store the currently selected tab in the hash value
$("ul.nav-tabs > li > a").on("shown.bs.tab", function(e) {
  var id = $(e.target).attr("href").substr(1);
  window.location.hash = id;
});

// on load of the page: switch to the currently selected tab
var hash = window.location.hash;
$('#myTab a[href="' + hash + '"]').tab('show'); 

$(document).ready(function() {

    $('#fetchDetail').click(function() {
        var user_id = ($(this).attr("data-id"));
        
        $.ajax({
            url: '{{ url('profile/+user_id') }}', 
            type: 'get',
            dataType: 'json',
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            beforeSend: function() {
                //$(".part-loader").show();
            },
            success: function(response) {
                //$(".part-loader").hide();
                console.log(response);
                var len = 0;
                $('#userTable tbody').empty(); // Empty <tbody>
                if(response['data'] != null) {
                    //
                }
            },
            error: function(jqxhr, status, exception) {
             alert('Exception:', exception);
            }
        }); 

    });

});
</script>
@endsection