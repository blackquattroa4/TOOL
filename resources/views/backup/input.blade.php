@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading">{{ $source['title'] }}</div>
				<div class="panel-body">
					<form class="form-horizontal" role="form" method="POST" action="{{ $source['post_url'] }}">
						{{ csrf_field() }}
						<div class="form-group">
							<label for="tablestruct" class="col-md-4 control-label">{{ trans('tool.Include table structure') }}</label>

							<div class="col-md-1">
								<input id="tablestruct" type="checkbox" class="form-control" name="tablestruct">
							</div>

						</div>

						<div class="form-group">
							<label for="withlock" class="col-md-4 control-label">{{ trans('tool.Lock table before insert') }}</label>

							<div class="col-md-1">
								<input id="withlock" type="checkbox" class="form-control" name="withlock">
							</div>

						</div>
					
						<div class="col-md-2 col-md-offset-10">
							<button type="submit" class="btn btn-primary">
								<i class="fa fa-btn fa-floppy-o"></i> {{ $source['action'] }}
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('post-content')
@endsection
