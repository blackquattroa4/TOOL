@extends('layouts.app')

@section('additional-style')
<style>
	.aging-inventory div {
		padding: 20px;
	}

	.aging-inventory ul {
		list-style-type: none;
	}

	.list-group-item {
		border: 0px; !important
	}
</style>
@endsection

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">

			<div class="panel panel-default">
				<div class="panel-heading">
					<table width="100%">
						<tr>
							<td>{{ trans('warehouse.Aging inventory') }}</td>
							<td>
							</td>
						</tr>
					</table>
				</div>
				<div class="panel-body">

					<form class="form-horizontal" role="form" method="POST" action="">
						<div class="form-group">
							<label for="enddate" class="col-md-2 control-label">{{ trans('forms.Date') }}</label>

							<div class="col-md-3">
								<div class="input-group date" data-provide="datepicker">
									<input id="enddate" type="text" class="form-control" name="enddate" value="{{ old('enddate') }}" >
									<div class="input-group-addon">
										<span class="glyphicon glyphicon-calendar"></span>
									</div>
								</div>
							</div>

							<button id="refresh" type="button" class="btn btn-info">
								<span class="fa fa-refresh"></span>&nbsp;{{ trans('forms.Update') }}
							</button>

						</div>
					</form>

					<ul class="nav nav-tabs">
						<!-- populated by AJAX -->
					</ul>
					<div class="tab-content aging-inventory">
						<!-- populated by AJAX -->
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('post-content')
	<script type="text/javascript">

		$('#enddate').bind('change', function() {
			$('.nav.nav-tabs').html("");
			$('.tab-content.aging-inventory').html("");
		});

		// Ajax call to pull reports
		$('#refresh').bind('click', function() {
			$.ajax({
				type: 'GET',
				url: '/warehouse/aging/ajax',
				data: {
						date : $('input#enddate').val(),
					},
				dataType: 'html',
				beforeSend: function(data) {
					$('.nav.nav-tabs').html("");
					$('.tab-content.aging-inventory').html('<div class=\"text-center\"><i class=\"fa fa-spinner fa-pulse fa-2x fa-fw\"></i></div>');
				},
			}).done(function(data) {
				var statements = JSON.parse(data);
				var header = "";
				var content = "";
				for(statement in statements) {
					header += "<li><a data-toggle=\"tab\" href=\"#" + statement + "\">" + statements[statement]['title'] + "</a></li>";
					content += "<div id=\"" + statement + "\" class=\"tab-pane fade in\"><div style=\"margin-top:20px;\"><table class=\"table table-hover\"><tr><td style=\"width:70%;\">{{ trans('forms.SKU') }}</td><td style=\"width:15%;\" class=\"text-right\">{{ trans('forms.Quantity') }}</td><td style=\"width:15%;\" class=\"text-right\">{{ trans('messages.Days') }}</td></tr>";
					for (item in statements[statement]["items"]) {
						content += "<tr><td><a onclick=\"$('." + statements[statement]["items"][item]["slug"] + "').toggle();\">" + statements[statement]["items"][item]["title"] + "</a></td><td class=\"text-right\">" + statements[statement]["items"][item]["quantity"] + "</td><td class=\"text-right\">" + statements[statement]["items"][item]["days"] + "</td></tr>";
						for (batch in statements[statement]["items"][item]['batches']) {
							content += "<tr class=\"" + statements[statement]["items"][item]["slug"] + "\" style=\"display:none;\"><td style=\"padding-left:10%;\">" + statements[statement]["items"][item]['batches'][batch]["description"] + "</td><td class=\"text-right\">" + statements[statement]["items"][item]['batches'][batch]["quantity"] + "</td><td class=\"text-right\">" + statements[statement]["items"][item]['batches'][batch]["days"] + "</td></tr>";
						}
					}
					content += "</table></div></div>";
				}
				$('.nav.nav-tabs').html(header);
				$('.tab-content.aging-inventory').html(content);
				$('.nav.nav-tabs li:first-child a').click();
			}).fail(function(data) {
				$('.tab-content.aging-inventory').html('<div class=\"text-center\">{{ trans('messages.Report cannot be generated') }}</div>');
			});
		});
	</script>
@endsection
