@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading">{{ trans('messages.Dashboard') }}</div>

				<div class="panel-body">

					<div id="accordion-parent" role="tablist" aria-multiselectable="true">
					@foreach ($groups as $group)
						<div class="panel panel-default">
							<div class="panel-heading" role="tab" id="heading-{{ $group['slug'] }}">
								<h4 class="panel-title">
									<a data-toggle="collapse" data-target="#collapse-{{ $group['slug'] }}" data-parent="#accordion-parent" href="#" aria-expanded="true" aria-controls="collapse-{{ $group['slug'] }}">
										{{ $group['title'] }}
									</a>&emsp;<span id="count-{{ $group['slug'] }}">???</span>
									<a data-toggle="collapse" data-target="#collapse-{{ $group['slug'] }}" data-parent="#accordion-parent" href="#" aria-expanded="true" aria-controls="collapse-{{ $group['slug'] }}">
										<i class="fa fa-chevron-down pull-right"></i>
									</a>
								</h4>
							</div>
							<div class="panel-body collapse" id="collapse-{{ $group['slug'] }}">
							</div>
						</div>
					@endforeach
					</div>

				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('post-content')

<script type="text/javascript">

	$(document).ready(function() {
		$.ajax({
			type: 'GET',
	    url: '/home/ajax',
	    data: {
	      },
	    dataType: 'html',
	    beforeSend: function(data) {
	      $('.ajax-processing').removeClass('hidden');
	    },
		}).always(function (data) {
	    $('.ajax-processing').addClass('hidden');
		}).done(function (data) {
			let result = JSON.parse(data);
			if (result['success']) {
				for (key in result['data']) {
					$('span#count-' + result['data'][key]['slug']).html("(" + result['data'][key]['items'].length + ")");
					for (key2 in result['data'][key]['items']) {
						$('div#collapse-' + result['data'][key]['slug']).append("<p>" + result['data'][key]['items'][key2]['html'] + "</p>");
					}
				}
			}
		}).fail(function (data) {
			// nothing to do when pulling failed.
		});
	});

</script>

@endsection
