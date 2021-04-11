@extends("layouts.app")

@section("title") Create New User @endsection

@section("content")

@if(session('status'))
    <div class="alert alert-success">
        {{ session('status') }}
    </div>
@endif

<div class="card card-secondary">
    <div class="card-header">
        <h3 class="card-title">Edit Student</h3>
    </div>
    <div class="card-body"> 
        <!-- enctype="multipart/form-data" karena kita akan mengunggah (mengupload) file dari form -->
        {!! Form::model($student, ['method' => 'GET','route' => ['students.update', $student->id]]) !!}
            @csrf
            <div class="row">
                <div class="col-md-6"> 
                    <label for="roles">Teacher</label>
                    <input value="{{ $student->name }}" class="form-control" placeholder="Student"
                    type="text" name="student" id="student" />
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
<script>
$('.alert-success').fadeIn().delay(700).fadeOut();
</script>
@endsection