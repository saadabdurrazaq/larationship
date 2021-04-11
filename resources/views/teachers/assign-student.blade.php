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
        <h3 class="card-title">Assign Students</h3>
    </div>
    <div class="card-body">
        <!-- enctype="multipart/form-data" karena kita akan mengunggah (mengupload) file dari form -->
        <form action="{{ route('teachers.store-student') }}" method="POST" id="formKirim" files="true" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-6"> 
                    <label for="roles">Teacher</label>
                    <input value="{{ $teacher->id }}" class="form-control" placeholder="Teacher"
                    type="hidden" name="teacherid" id="teacherid" />
                    <input value="{{ $teacher->name }}" readonly class="form-control" placeholder="Teacher"
                    type="text" name="teacher" id="teacher" />
                    <br>

                    <label for="name">Student</label>
                    <select selected="selected" multiple class="form-control {{ $errors->first('Students') ? "is-invalid" : "" }}" name="students[]" id="students" class="students"></select>
                    <div class="invalid-feedback">
                        {{ $errors->first('students') }} 
                    </div>

                    <div class="col-sm-12 text-right" style="margin-top:10px;">
                        <input class="btn btn-primary" type="submit" id="button" value="Save" />
                    </div>
                </div> <!-- kanan -->
            </div> <!-- row-->
        </form><!--</form>-->
    </div> <!-- card body -->
    <div class="card-footer">
        Visit <a href="https://select2.github.io/">Select2 documentation</a> for more examples and information about the
        plugin.
    </div>
</div>
@endsection

@section('crud-js') <!--terkait dengan kode@yield('crud-js') di app.blade.php-->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>

<script>
$('.alert-success').fadeIn().delay(700).fadeOut();
$('#students').select2({ 
    placeholder: "Select available students",
ajax: {
    url: '{{ route('searchstudents', $teacher->id) }}', 
    processResults: function(data) {
    return {
        results: data.map( function(item) {
            return { 
                id: item.id, 
                text: item.name
            } 
        })
      }
    }
}
}); 

// Show related data in select box
var showStudents = {!! $students !!}

showStudents.forEach(function(student){
    var option = new Option(student.name, student.id, true, true);
    $('#students').append(option).trigger('change');
});
</script>
@endsection