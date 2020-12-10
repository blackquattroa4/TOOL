@extends('layouts.app')

@section('additional-style')
	<link rel="stylesheet" type="text/css" href="/external/imagebox/css/style.css">
@endsection

@section('content')
<div class="container">

    <!-- document-loading progress modal -->
    <div class="modal fade" id="progressModal" tabindex="-1" role="dialog" aria-labelledby="progressModalLabel" data-backdrop="static" aria-hidden="false">
      <div class="modal-dialog" role="progress">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title" id="progressModalLabel">{{ trans('forms.Document') }}</h4>
          </div>
          <div class="modal-body">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <div class="image-group">
      <div class="image-container">
        <img id="image-canvas" class="img hide" data-url="" />
      </div>
    </div>

    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">{{ trans('hr.Staff information') }}</div>
                <div class="panel-body">
                    <form class="form-horizontal" role="form" method="POST" action="{{ $path }}">
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                            <label for="name" class="col-md-4 control-label">{{ trans('messages.Name') }}</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control" name="name" value="{{ $user['name'] }}" {{ $readonly ? " disabled" : "" }}>

                                @if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="email" class="col-md-4 control-label">{{ trans('messages.E-mail address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="text" class="form-control" name="email" value="{{ $user['email'] }}" {{ $readonly ? " disabled" : "" }}>

                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('phone') ? ' has-error' : '' }}">
                            <label for="email" class="col-md-4 control-label">{{ trans('messages.Contact phone') }}</label>

                            <div class="col-md-6">
                                <input id="phone" type="phone" class="form-control" name="phone" value="{{ $user['phone'] }}" {{ $readonly ? " disabled" : "" }}>

                                @if ($errors->has('phone'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('phone') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('street') ? ' has-error' : '' }}">
                            <label for="street" class="col-md-4 control-label">{{ trans('messages.Street address') }}</label>

                            <div class="col-md-6">
                                <input id="street" type="street" class="form-control" name="street" value="{{ $user['street'] }}" {{ $readonly ? " disabled" : "" }}>

                                @if ($errors->has('street'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('street') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-2">
                                <input id="unit" type="unit" class="form-control" name="unit" value="{{ $user['unit'] }}" {{ $readonly ? " disabled" : "" }}>

                                @if ($errors->has('unit'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('unit') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('city') ? ' has-error' : '' }}">
                            <label for="city" class="col-md-4 control-label">{{ trans('messages.City') }}</label>

                            <div class="col-md-6">
                                <input id="city" type="city" class="form-control" name="city" value="{{ $user['city'] }}" {{ $readonly ? " disabled" : "" }}>

                                @if ($errors->has('city'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('city') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('district') ? ' has-error' : '' }}">
                            <label for="phone" class="col-md-4 control-label">{{ trans('messages.District') }}</label>

                            <div class="col-md-6">
                                <input id="district" type="district" class="form-control" name="district" value="{{ $user['district'] }}" {{ $readonly ? " disabled" : "" }}>

                                @if ($errors->has('district'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('district') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('state') ? ' has-error' : '' }}">
                            <label for="city" class="col-md-4 control-label">{{ trans('messages.State') }}</label>

                            <div class="col-md-6">
                                <input id="state" type="state" class="form-control" name="state" value="{{ $user['state'] }}" {{ $readonly ? " disabled" : "" }}>

                                @if ($errors->has('state'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('state') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('country') ? ' has-error' : '' }}">
                            <label for="country" class="col-md-4 control-label">{{ trans('messages.Country') }}</label>

                            <div class="col-md-6">
                                <select id="country" type="country" class="form-control" name="country" {{ $readonly ? " disabled" : "" }}>
								@foreach ($country as $abbr => $fullname)
									<option value="{{ $abbr }}" {{ ($user['country'] == $abbr) ? "selected" : ""}}>{{ $abbr }}&emsp;{{ $fullname }}</option>
								@endforeach
								</select>

                                @if ($errors->has('country'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('country') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('zipcode') ? ' has-error' : '' }}">
                            <label for="zipcode" class="col-md-4 control-label">{{ trans('messages.Zipcode') }}</label>

                            <div class="col-md-6">
                                <input id="zipcode" type="zipcode" class="form-control" name="zipcode" value="{{ $user['zipcode'] }}" {{ $readonly ? " disabled" : "" }}>

                                @if ($errors->has('zipcode'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('zipcode') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                      @if (!$readonly)
                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-btn fa-floppy-o"></i> {{ trans('forms.Update') }}
                                </button>
                            </div>
                        </div>
                      @endif
                    </form>
                </div>
            </div>

        @if (isset($files))
            <div class="panel panel-default">
                <div class="panel-heading">{{ trans('hr.Staff files') }}</div>
                <div class="panel-body">
                @if (count($files))
                    <table width="100%" class="table table-striped">
                        <tr>
                            <th>{{ trans('forms.Date') }}</th>
                            <th>{{ trans('forms.Description') }}</th>
                            <th>{{ trans('forms.Staff') }}</th>
                            <th></th>
                        </tr>
                        @foreach ($files as $oneFile)
                        <tr>
                            <td>{{ \App\Helpers\DateHelper::dbToGuiDate($oneFile['created_at']) }}</td>
                            <td>{{ $oneFile['title'] }}</td>
                            <td>{{ \App\User::find($oneFile['pivot']['creator_id'])->name }}</td>
                            <td>
                                <button class="btn btn-info btn-xs image-button" title="{{ trans('forms.View') }}" data-url="{{ $oneFile['file_path'] }}">
                                  <i class="fa fa-eye" aria-hidden="true"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </table>
                @endif
                </div>
            </div>
        @endif
        </div>
    </div>
</div>
@endsection

@section('post-content')

  <script src="{{ asset('external/imagebox/js/jquery.imagebox.js') }}"></script>

  <script type="text/javascript">
    $(document).ready(function() {

      $("div.image-group").imageBox();

      $("button.image-button").bind('click', function() {

        var url = '/hr/archive/' + $(this).data('url') + "/download";

        $.ajax({
          type: 'GET',
          url: url,
          dataType: 'html',
          beforeSend: function(data) {
            // show spinning wheel when downloading...
            $('div#progressModal div.modal-body').html('<i class="fa fa-spinner fa-pulse fa-2x fa-fw" aria-hidden="true"></i>');
            $('div#progressModal div.modal-footer button').addClass('hidden');
            $('div#progressModal').modal('show');
          },
        }).done(function(data) {
          $('#progressModal').modal('hide');
          var result = JSON.parse(data);
          if (result['success']) {
            $('div.image-group div img#image-canvas').data('url', result['content']);
            $('div.image-group div img#image-canvas').trigger('click');
          }
        }).fail(function(data) {
          // show failed message.
          $('#progressModal div.modal-body').html('{{ trans('messages.Document download failed') }}');
          $('#progressModal div.modal-footer button').removeClass('hidden');
        }).always(function(data) {
        });
      });
    });
  </script>

@endsection
