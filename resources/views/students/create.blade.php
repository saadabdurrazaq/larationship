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
        <h3 class="card-title">Create New Students</h3>
    </div>
    <div class="card-body">
        <!-- enctype="multipart/form-data" karena kita akan mengunggah (mengupload) file dari form -->
        <form action="{{ route('students.store') }}" method="POST" id="formKirim" files="true" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <label for="name">Students</label> 
                    <input type="text" placeholder="Students" class="form-control {{ $errors->first('students') ? "is-invalid": "" }}" name="students" id="students" value="{{ old('students') }}" />
                    <div class="invalid-feedback">
                        {{ $errors->first('students') }}
                    </div>
                    <small class="text-muted">End with a comma for single and multiple inputs. E.g: data1, data2,</a></small>
                    <br>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />

<!-- Tokenfield -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" integrity="sha512-aOG0c6nPNzGk+5zjwyJaoRUgCdOrfSDhmMID2u4+OIslr0GjpLKo7Xm0Ao3xmpM4T8AmIouRkqwj1nrdVsLKEQ==" crossorigin="anonymous" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tokenfield/0.12.0/css/bootstrap-tokenfield.min.css" integrity="sha512-YWDtZYKUekuPMIzojX205b/D7yCj/ZM82P4hkqc9ZctHtQjvq3ei11EvAmqxQoyrIFBd9Uhfn/X6nJ1Nnp+F7A==" crossorigin="anonymous" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tokenfield/0.12.0/bootstrap-tokenfield.js" integrity="sha512-A2ag+feOqri5SJxJ54oIIFj9/JuhZqiNNwyNLiAvQIyxJQT5JvZzn17vAb4BkP0a210NWz1DlsnLuRKZdouBnw==" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js" integrity="sha512-uto9mlQzrs59VwILcLiRYeLKPPbS/bT71da/OEBYEwcdNUk8jYIy+D176RYoop1Da+f9mvkYrmj5MCLZWEtQuA==" crossorigin="anonymous"></script>

<script>
$('.alert-success').fadeIn().delay(700).fadeOut();
$('#students').tokenfield({
        /*
  autocomplete: {
    source: ['red','blue','green','yellow','violet','brown','purple','black','white'],
    delay: 100,
  },
  */
  showAutocompleteOnFocus: false
});
    /*
$('#tokenfield').on('tokenfield:createtoken', function (event) {
    
    // user can't select twice from autocomplete
    var existingTokens = $(this).tokenfield('getTokens');
    $.each(existingTokens, function(index, token) {
        if (token.value === event.attrs.value) // token.value means data from autocomplete
            event.preventDefault();
    });

    // disable select from autocomplete

    setTimeout(function() {
            $('#tokenfield-tokenfield').blur();
            $('#tokenfield-tokenfield').focus();
    }, 0);
});
*/
</script>
@endsection