@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">{{ trans('tool.File upload') }}</div>
                <div class="panel-body">
                    <form class="form-horizontal" role="form" enctype="multipart/form-data" method="POST" action="{{ $source['post_url'] }}">
                        {{ csrf_field() }}

						<!-- <label class="btn btn-primary" for="upload-selector">
							<input id="upload-selector" type="file" style="display:none;" onchange="$('#upload-file-info').html($(this).val());">{{ trans('Browse file') }}</input>
						</label>
						<span class='label label-info' id="upload-file-info"></span> -->

						<div class="col-md-4{{ $errors->has('upload-selector') ? ' has-error' : '' }}">
							<label class="btn btn-info" for="upload-selector">
                <!-- assuming value='C:\fakepath\filename' -->
								<input id="upload-selector" name="upload-selector" type="file" style="display:none;" onchange="$('#upload-selector-label').html( ($(this).val().substring($(this).val().lastIndexOf( '\\' ) + 1)) );" />
								<span id="upload-selector-label" >{{ trans('tool.Browse file') }}</span>
							</label>

						@if ($errors->has('upload-selector'))
							<span class="help-block">
								<strong>{{ $errors->first('upload-selector') }}</strong>
							</span>
						@endif
						</div>

						<button type="submit" class="btn btn-primary pull-right">
							<i class="fa fa-upload"></i> {{ trans('tool.Upload') }}
						</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('post-content')
@endsection
