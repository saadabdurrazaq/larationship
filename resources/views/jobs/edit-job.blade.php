@extends("layouts.app")

@section("title") Create New User @endsection

@section('list-admin-create')
{{ Breadcrumbs::render('list-user-create') }}
@endsection

@section("content")

@if(session('status'))
    <div class="alert alert-success">
        {{ session('status') }}
    </div>
@endif

<div class="card card-secondary">
    <div class="card-header">
        <h3 class="card-title">Edit Job</h3>
    </div>
    <div class="card-body"> 
        <!-- enctype="multipart/form-data" karena kita akan mengunggah (mengupload) file dari form -->
        {!! Form::model($job, ['method' => 'GET','route' => ['jobs.update', $job->id]]) !!}
            @csrf
            <div class="row">
                <div class="col-md-6"> 
                    <label for="roles">Type of Job</label>
                    <input value="{{ $job->name }}" class="form-control" placeholder="Job"
                    type="text" name="job" id="job" />
                    <br>

                    <div class="col-sm-12 text-right" style="margin-top:10px;">
                        <input class="btn btn-primary" type="submit" id="button" value="Save" />
                    </div>
                </div> <!-- kanan -->
            </div> <!-- row-->
        {!! Form::close() !!}

    </div> <!-- card body -->
    <div class="card-footer">
        Visit <a href="https://select2.github.io/">Select2 documentation</a> for more examples and information about the
        plugin.
    </div>
</div>
@endsection
@section('crud-js') <!--terkait dengan kode@yield('crud-js') di app.blade.php-->
<script>
$('.alert-success').fadeIn().delay(700).fadeOut();
</script>
@endsection
@section('footer-scripts')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
 
<script>

</script>
@endsection