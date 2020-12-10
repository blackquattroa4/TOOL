@extends('layouts.app')

@section('additional-style')
	<style>
		div.participant-select {
			overflow-y: scroll;
			height: 100px;
		}

		div.participant-select input {
			margin: 10px 5px 10px 20px;
			width: 25px;
			height: 25px;
		}

		div.participant-select span {
			padding: 10px;
			font-size: 18px;
		}

		input.active-checkbox,
		input.all-day-checkbox {
			width: 25px !important;
			height: 25px !important;
		}

		div.progress-indicator {
			position: fixed;
			left: 40%;
			top: 40%;
			z-index: 100;
		}
	</style>

@if (!App::environment('local'))
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.min.css">
@else
	<link rel="stylesheet" href="{{ asset('external/ajax/libs/fullcalendar/3.9.0/fullcalendar.min.css') }}"/>
@endif
@endsection

@section('content')
<div class="modal fade" id="eventModal" tabindex="-1" role="dialog" aria-labelledby="eventModalLabel" data-backdrop="static" aria-hidden="false">
	<div class="modal-dialog" role="">
		<div class="modal-content">
			<div class="modal-header">
				<h4>{{ trans('tool.Add event') }}</h4>
			</div>
			<div class="modal-body">
				<div class="progress-indicator hide"><i class="fa fa-spinner fa-pulse fa-5x fa-fw"></i></div>
				<div class="form-horizontal">
					<div class="form-group">
						<div class="col-md-10">
							{{ trans('tool.Title') }}
							<input id="event_id" class="form-control hidden" name="event_id" value="0">
							<input id="event_title" class="form-control" name="event_title" value="">
						</div>
						<div class="col-md-2">
							{{ trans('tool.Active')}}
							<input class="active-checkbox form-control" type="checkbox" />
						</div>
					</div>
					<div class="form-group">
						<div class=" col-md-8">
							{{ trans('tool.Start') }}
							<div class="input-group date" data-provide="datepicker">
								<input id="start_date" type="text" class="form-control" name="start_date" value="">
								<div class="input-group-addon">
									<span class="glyphicon glyphicon-calendar"></span>
								</div>
								<select id="start_time" class="form-control" name="start_time">
									{!! \App\Helpers\HtmlSelectHelper::getWorkHourOptions() !!}
								</select>
								<div class="input-group-addon">
									<i class="fa fa-clock-o"></i>
								</div>
							</div>
						</div>
						<div class="col-md-2 col-md-offset-2">
							{{ trans('tool.All day')}}
							<input class="all-day-checkbox form-control" type="checkbox" />
						</div>
					</div>
					<div class="form-group">
						<div class=" col-md-8">
							{{ trans('tool.End') }}
							<div class="input-group date" data-provide="datepicker">
								<input id="end_date" type="text" class="form-control" name="end_date" value="">
								<div class="input-group-addon">
									<span class="glyphicon glyphicon-calendar"></span>
								</div>
								<select id="end_time" class="form-control" name="end_time">
									{!! \App\Helpers\HtmlSelectHelper::getWorkHourOptions() !!}
								</select>
								<div class="input-group-addon">
									<i class="fa fa-clock-o"></i>
								</div>
							</div>
						</div>
						<div class="col-md-4">
						</div>
					</div>
					<div class="form-group">
						<div class=" col-md-12">
							{{ trans('tool.Participants') }}
							<div class="participant-select">
							@foreach (\App\User::getActiveStaff('name', 'asc') as $user)
								<div class="col-md-5">
									<input class="col-md-3" id="participants[{{ $user->id }}]" type="checkbox" name="participants[{{ $user->id }}]" />
									<span title="" class="col-md-9">{{ $user->name }}</span>
								</div>
							@endforeach
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary event_reply" data-action="accepted">{{ trans('forms.Accept') }}</button>
				<button type="button" class="btn btn-primary event_reply" data-action="declined">{{ trans('forms.Decline') }}</button>
				<button type="button" class="btn btn-primary" id="event_write_back">{{ trans('forms.Submit') }}</button>
				<button type="button" class="btn btn-primary" data-dismiss="modal">{{ trans('forms.Close') }}</button>
			</div>
		</div>
	</div>
</div>

<div class="container">
	{!! $calendar->calendar() !!}
</div>
@endsection

@section('post-content')
@if (!App::environment('local'))
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
@else
	<script src="{{ asset('external/ajax/libs/moment/2.24.0/moment.min.js') }}"></script>
@endif

@if (!App::environment('local'))
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.1/fullcalendar.min.js"></script>
@else
	<script src="{{ asset('external/ajax/libs/fullcalendar/3.9.0/fullcalendar.min.js') }}"></script>
@endif

@if (!App::environment('local'))
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.1/locale-all.js"></script>
@else
	<script src="{{ asset('external/ajax/libs/fullcalendar/3.9.0/locale-all.js') }}"></script>
@endif

	{!! $calendar->script() !!}

	<script type="text/javascript">
		$(document).ready(function() {
			$('input.all-day-checkbox').bind('change', function() {
				$('select[id$="_time"]').toggle();
			});

			$('button.event_reply').bind('click', function() {
				$.ajax({
					type: 'POST',
					url: '/calendar/reply/' + $('div.modal-body input#event_id').val(),
					data: {
							_token : '{{ csrf_token() }}',
							action : $(this).data('action'),
						},
					dataType: 'html',
				}).done(function(data) {
					$('#eventModal').modal('hide');
					// clear out old calendar and regenerate
					$('div[id^="calendar-"]').fullCalendar('removeEvents');
					var result = JSON.parse(data);
					for (var i in result) {
						$('div[id^="calendar-"]').fullCalendar('renderEvent', result[i], true);
					}
				}).fail(function(data) {
					alert('update failed');
				});
			});

			$('button#event_write_back').bind('click', function() {
				var participantIds = [];
				$('div.modal-body input[id^="participants["]:checked').each(function() {
					participantIds.push( $(this).attr('id').replace(/(participants\[)([0-9]+)(\])/g, '$2') );
				});
				$.ajax({
					type: 'POST',
					url: '/calendar/save',
					data: {
							_token : '{{ csrf_token() }}',
							id : $('div.modal-body input#event_id').val(),
							title : $('div.modal-body input#event_title').val(),
							active : $('div.modal-body input.active-checkbox').prop('checked'),
							start_date : $('div.modal-body input#start_date').val(),
							start_time : $('div.modal-body select#start_time').val(),
							all_day : $('div.modal-body input.all-day-checkbox').prop('checked'),
							end_date : $('div.modal-body input#end_date').val(),
							end_time : $('div.modal-body select#end_time').val(),
							participants : participantIds,
						},
					dataType: 'html',
				}).done(function(data) {
					$('#eventModal').modal('hide');
					// clear out old calendar and regenerate
					$('div[id^="calendar-"]').fullCalendar('removeEvents');
					var result = JSON.parse(data);
					for (var i in result) {
						$('div[id^="calendar-"]').fullCalendar('renderEvent', result[i], true);
					}
				}).fail(function(data) {
					alert('update failed');
				});
			});

			var params = new URLSearchParams(window.location.search);
			if (params.has('event')) {
				var startDate = moment($('div[id^="calendar-"]').fullCalendar('clientEvents', params.get('event'))[0].start).format("YYYY-MM-DD");
				$('div[id^="calendar-"]').fullCalendar('gotoDate', startDate);
				$('div.fc-content#event-' + params.get('event')).parent().trigger('click');
			}
		});

		function getClosestStartTime() {
			var now = new Date().getTime();
			now = (now + 1800000) - (now % 1800000);
			var later = new Date(now);
			return ("" + later.getHours()).padStart(2, "0") + ":" + ("" + later.getMinutes()).padStart(2, "0") + ":00";
		}

		function getEndTime(minute) {
			var now = new Date().getTime();
			now = (now + 1800000 + minute * 60000) - (now % 1800000);
			var later = new Date(now);
			return ("" + later.getHours()).padStart(2, "0") + ":" + ("" + later.getMinutes()).padStart(2, "0") + ":00";
		}

		function prepareAndShowModalForDayClick(date, jsEvent, view) {
			var theDate = new Date(date.toISOString() + ' 23:59:59');
			var today = new Date();
			if (theDate.getTime() < today.getTime()) {
				return;
			}

			theDate = date.toArray();
			var isToday = (theDate[0] == today.getFullYear()) &&
										(theDate[1] == today.getMonth()) &&
										(theDate[2] == today.getDate());

			$('div.modal-body input').prop("disabled", false);
			$('div.modal-body select').prop("disabled", false);

			$('div.modal-header h4').html('{{ trans('tool.Add event') }}');
			$('input#event_id').val("0");
			$('input#event_title').val("");
			$('input#start_date').val("{{ $datePattern }}".replace("yy", theDate[0]).replace("M", (theDate[1]+1)).replace("d", theDate[2])); $('select#start_time').val(isToday ? getClosestStartTime() : "08:00:00");
			$('input#end_date').val("{{ $datePattern }}".replace("yy", theDate[0]).replace("M", (theDate[1]+1)).replace("d", theDate[2]));
			$('select#end_time').val(isToday ? getEndTime(30) : "08:30:00");
			$('input.all-day-checkbox').prop('checked', false);
			$('div.modal-body select[id$="_time"]').show();
			$('input.active-checkbox').prop('checked', true);
			// clear out everyone and check only the current user
			$('input[id^="participants["]').prop('checked', false);
			$('input[id="participants[{{ auth()->user()->id }}]"]').prop('checked', true);
			$('input[id="participants[{{ auth()->user()->id }}]"]').prop('disabled', true);
			$('button.event_reply').prop('disabled', true);
			$('button.event_reply').hide();
			$('button#event_write_back').prop('disabled', false);
			$('button#event_write_back').show();
			$('#eventModal').modal('show');
		}

		function prepareAndShowModalForEventClick(event, jsEvent, view) {
			$.ajax({
				type: 'GET',
				url: '/calendar/get/' + event.id,
				data: {
					},
				dataType: 'html',
				beforeSend: function(data) {
					$('div.progress-indicator').removeClass('hide');
					$('div.modal-header h4').html('');
					$('div.modal-body input').val("")
					$('div.modal-body input').prop("disabled", true);
					$('div.modal-body select').val("");
					$('div.modal-body select').prop("disabled", true);
					$('div.modal-body select[id$="_time"]').show();
					$('div.modal-body input:checkbox').prop('checked', false);
					$('div.modal-body input:checkbox').prop('disabled', true);
					$('button.event_reply').prop('disabled', true);
					$('button.event_reply').hide();
					$('button#event_write_back').prop('disabled', true);
					$('button#event_write_back').hide();
				},
			}).done(function(data) {
				$('div.progress-indicator').addClass('hide');
				var result = JSON.parse(data);
				$('div.modal-header h4').html('{{ trans('tool.Edit event') }}');
				$('div.modal-body input').prop("disabled", !result['can_edit']);
				$('div.modal-body select').prop("disabled", !result['can_edit']);

				$('div.modal-body input#event_id').val(result['id']);
				$('div.modal-body input#event_title').val(result['title']);
				$('div.modal-body input#start_date').val(result['start_date']);
				$('div.modal-body select#start_time').val(result['start_time']);
				$('div.modal-body input#end_date').val(result['end_date']);
				$('div.modal-body select#end_time').val(result['end_time']);
				if (result['all_day']) {
					$('div.modal-body select[id$="_time"]').hide();
				}
				$('div.modal-body input.active-checkbox').prop('checked', result['active']);
				$('div.modal-body input.all-day-checkbox').prop('checked', result['all_day']);
				// participants
				$('input[id^="participants["]').prop('disabled', !result['can_edit']);
				$('input[id="participants[' + result['host_id'] + ']"]').prop('disabled', true);
				$('input[id^="participants["]').prop('checked', false);
				$('input[id^="participants["]').parent().find(' > span').removeClass('label');
				$('input[id^="participants["]').parent().find(' > span').attr('title', '');
				$('input[id^="participants["]').parent().find(' > span').css('background-color', false);
				for (var i in result['participants']) {
					$('input[id="participants[' + result['participants'][i] + ']"]').prop('checked', true);
					 $('input[id="participants[' + result['participants'][i] + ']"]').parent().find(' > span').addClass('label');
					 $('input[id="participants[' + result['participants'][i] + ']"]').parent().find(' > span').attr('title', result['decisions'][result['participants'][i]]);
					 $('input[id="participants[' + result['participants'][i] + ']"]').parent().find(' > span').css('background-color', result['decision_colors'][result['participants'][i]]);
				}

				$('button.event_reply').prop('disabled', result['can_edit']);
				$('button#event_write_back').prop('disabled', !result['can_edit']);
				if (result['can_edit']) {
					$('button.event_reply').hide();
					$('button#event_write_back').show();
				} else {
					$('button#event_write_back').hide();
					$('button.event_reply').show();
				}
			}).fail(function(data) {
				$('div.progress-indicator').addClass('hide');
				$('div.modal-header h4').html("{{ trans('tool.Event can not be loaded') }}");
			});

			$('#eventModal').modal('show');
		}
	</script>
@endsection
