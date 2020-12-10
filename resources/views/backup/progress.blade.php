@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading">{{ $source['title'] }}</div>
				<div class="panel-body">
					<div id="message">
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('post-content')
	<script type="text/javascript">
		var backupSize = 0;
		$(document).ready(function() {
			var progress = setInterval(function() {
				$.ajax({
					type: 'GET',
					url: '/system/backup/progress',
					data: {
							backupfile : "{{ $source['backupfile'] }}",
						},
					dataType: 'html',
				}).done(function(data) {
					$('#message').html("<h4>" + "{{ trans('tool.%s bytes written') }}".replace('%s', data) + '</h4>');
					if ((backupSize > 0) && (backupSize == parseInt(data))) {
						//alert('backup complete');
						$('#message').html("<h4>" + "{{ trans('tool.Backup completed') }}" + "</h4><a id=\"download\" class=\"btn btn-info pull-right\" href=\"/system/backup/download/{{ substr($source['backupfile'], 7, 14) }}\" aria-hidden=\"false\"><span class=\"fa fa-download\">&nbsp;{{ trans('forms.Download') }}</span></a>");
						clearInterval(progress);
						$('a#download').bind("click", function() {
							$('#message').html("<h4>" + "{{ trans('tool.Backup downloaded') }}" + "</h4>");
						});
					}
					backupSize = parseInt(data);
				}).fail(function(data) {
					$('#message').html("<h4>" + "{{ trans('tool.Backup failed') }}" + "</h4>");
				});
			}, 1000);
		});
	</script>
@endsection
