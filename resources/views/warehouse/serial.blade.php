@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div id="productwindow" class="panel panel-default">
				<div class="panel-heading">
					<table>
						<tr>
							<td>
								<div class="input-group">
									<input id="searchbox" type="text" class="form-control" name="searchbox" placeholder="{{ trans('forms.Serial to be searched') }}" value="" >
									<span class="input-group-btn">
										<button id="search-btn" class="btn btn-secondary" type="button" >
											<i class="fa fa-search"></i>
										</button>
									</span>
								</div>
							</td>
						</tr>
					</table>
				</div>

				<div id="search_result" class="panel-body"></div>
			</div>

		</div>
	</div>
</div>
@endsection

@section('post-content')
	<script type="text/javascript">
		$('#search-btn').bind('click', function() {
			var serial = $('#searchbox').val();
			$.ajax({
				type: 'GET',
				url: '/warehouse/serial/ajax',
				data: {
						serial : $('#searchbox').val(),
					},
				dataType: 'html',
				beforeSend: function(data) {
					$('#search_result').html('<div class=\"text-center\"><i class=\"fa fa-spinner fa-pulse fa-2x fa-fw\"></i></div>');
				},
			}).done(function(data) {
				var entries = JSON.parse(data);
				var content = "";
				for(entry in entries) {
					content += "<p>" + entries[entry] + "</p>";
				}
				$('#search_result').html(content);
			}).fail(function(data) {
				$('#search_result').html('<div class=\"text-center\">{{ trans('forms.No result found') }}</div>');
			});

		});
	</script>
@endsection
