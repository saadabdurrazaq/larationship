@extends("layouts.app")

@section("title") Users list @endsection  

@section("content")
     
@if(session('status'))
<div class="alert alert-success"> 
  {{session('status')}} 
</div>
@endif 

<div id="flash-message">
  @if( Session::has("success") )
  <div class="alert alert-success alert-block" role="alert" id="success-div">
    <button class="close" data-dismiss="alert"></button>
    {{ Session::get("success") }}
  </div> 
  @endif
  @if( Session::has("error") )
  <div class="alert alert-danger alert-block" role="alert" id="success-div">
    <button class="close" data-dismiss="alert"></button>
    {{ Session::get("error") }}
  </div>
  @endif
</div>

  @section("loader")
    <div class="whole-page-overlay" id="whole_page_loader">
      <img class="center-loader" src="{{ asset('public/images/loader.svg') }}" alt=""  width="50" height="50">
    </div>
  @endsection

  <div class="card card-secondary"> 
      <div class="card-header">
          <h3 class="card-title">Jobs & Type of Jobs</h3>
      </div>
      <div class="card-body">
          <div class="row" style="margin-top:-20px;">
            <div class="col-md-12 menu">
              <nav class="navecation" style="margin-left:-40px;margin-top:20px;">
                <ul id="navi">
                  
                </ul>
              </nav>
            </div>
            <div class="col-md-12 text-right">
              <a href="{{ route('job-types.create') }}" class="btn btn-primary">Add New Type of Jobs</a>
              <a href="{{ route('jobs.index') }}" class="btn btn-primary">List of Jobs</a>
            </div>
          </div>
          <hr>
            <div style="float:left;padding-top:3px;padding-right:4px;">Show</div>
            <div style="width:73px;float:left;margin-bottom:7px;">
              <select id="pagination" class="form-control select2bs4 select2-hidden-accessible" style="width:73px;"" data-select2-id="17" tabindex="-1" aria-hidden="true">
                <option value="5" @if($items == 5) selected @endif data-select2-id="19">5</option>
                <option value="10" @if($items == 10) selected @endif data-select2-id="38">10</option>
                <option value="25" @if($items == 25) selected @endif data-select2-id="39">25</option>
                <option value="50" @if($items == 50) selected @endif data-select2-id="40">50</option>
                <option value="100" @if($items == 100) selected @endif data-select2-id="41">100</option>
                <option value="250" @if($items == 250) selected @endif data-select2-id="42">250</option>
              </select>
            </div>
            <div style="float:left;padding-top:3px;padding-left:4px;padding-right:10px;">entries</div>
            <div style="float:right;"> 
              <form action="{{route('job-types.index')}}">
              <div class="input-group input-group-sm" style="width:215px;">
                <input type="text" value="{{Request::get('keyword')}}" name="keyword" class="form-control float-right" placeholder="Search by name">
                <div class="input-group-append">
                  <button type="submit" value="Filter" class="btn btn-default"><i class="fas fa-search"></i></button>
                </div>
              </div>
              </form>
            </div>
            <div class="panel-body table-responsive" style="overflow:hidden;">
                <table class="table table-bordered table-hover dataTable">
                <thead>
                    <tr>
                        <th><b>Type of Jobs</b></th>
                        <th><b>Jobs</b></th>
                        <th><b>Jobs Action</b></th> 
                    </tr>
                  </thead> 
                <tbody>
        @foreach($data as $jobType)
          <tr>
            <td>{{ $jobType->name }}</td> 
            <td>
              <?php $elements = array(); ?>
              @foreach($jobType->jobs as $category)
                <?php $elements[] = $category->name; ?>
              @endforeach
              <?php echo implode(', ', $elements); ?> 
            </td>
            <td> 
              <a href="{{ route('job-types.assign-job', $jobType->id) }}" style="margin-top:5px;" class="btn btn-success btn-sm">Assign</a> 
              <a href="{{ route('job-types.edit-type', $jobType->id) }}" style="margin-top:5px;" class="btn btn-primary btn-sm">Edit</a> 
              <form class="d-inline" id="#submitDelete" action="{{ route('jobtype.delete-permanent',['id'=>$jobType->id]) }}" method="POST">
                @csrf 
                <input type="hidden" name="_method" value="DELETE"/>
                <input type="submit" style="margin-top:5px;" class="btn btn-danger btn-sm" value="Delete" onclick="return confirmDelete()"/>
              </form>
            </td>
          </tr> 
        @endforeach 
        </tbody>
                </table>
              <div class="row">
                  <div class="col"></div>
                  <div class="col text-right"></div>
              </div>
              <div class="row">
                    <div class="col">
                      <div class="float-left" style="margin-top:15px;">{{$showData}}</div>
                    </div>
                    <div class="col">
                        <div class="float-right" style="margin-top:7px;">{{$data->appends(Request::all())->links()}}</div>
                    </div>
              </div>
              
            </div> <!-- panel-body table-responsive -->
      </div> <!--card body-->
      <div class="card-footer">
            Visit <a href="https://select2.github.io/">Select2 documentation</a> for more examples and information about
            the plugin.
          </div> 
  </div> <!--card card-secondary-->
@endsection
@section('crud-js') <!--terkait dengan kode@yield('crud-js') di app.blade.php-->
<script>
  $('.alert-success').fadeIn().delay(700).fadeOut();

  //Show entries
  document.getElementById('pagination').onchange = function() { 
    $("#whole_page_loader").show();
    window.location = "{{URL::route('job-types.index')}}?items=" + this.value; 
  }; 
  
  //Checkbox
  $('#select-all').on('click', function(e) {
    if($(this).is(':checked',true))  {
        $(".sub_chk").prop('checked', true);  
    } else {  
        $(".sub_chk").prop('checked',false);  
    }  
  });

  function confirmDelete() {
    if(confirm('Delete this type?')) {
      $("#whole_page_loader").show();
      $("#submitTrash").submit();
    } else {
      return false;
    }
  }
</script>
@endsection