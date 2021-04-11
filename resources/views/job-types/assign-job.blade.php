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
        <h3 class="card-title">Assign Jobs</h3>
    </div>
    <div class="card-body">
        <!-- enctype="multipart/form-data" karena kita akan mengunggah (mengupload) file dari form -->
        <form action="{{ route('job-types.store-job') }}" method="POST" id="formKirim" files="true" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-6">  
                    <label for="roles">Type of Job</label>
                    <input value="{{ $jobType->id }}" class="form-control" placeholder="Type of Job"
                    type="hidden" name="typejob" id="typejob" />
                    <input value="{{ $jobType->name }}" readonly class="form-control" placeholder="Type of Job"
                    type="text" name="type" id="type" />
                    <br>

                    <label for="name">Job</label>
                    <select selected="selected" multiple class="form-control {{ $errors->first('jobs') ? "is-invalid" : "" }}" name="jobs[]" id="jobs" class="jobs"></select>
                    <div class="invalid-feedback">
                        {{ $errors->first('jobs') }}
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
<script>
$('.alert-success').fadeIn().delay(700).fadeOut(); 
</script>
@endsection

@section('footer-scripts')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
 
<script>
$('#jobs').select2({ 
    placeholder: "Select available jobs",
ajax: {
    url: '{{ route('searchjobs', $jobType->id) }}',
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

var showJobs = {!! $jobs !!}

showJobs.forEach(function(job){
    var option = new Option(job.name, job.id, true, true);
    $('#jobs').append(option).trigger('change');
});
</script>
@endsection