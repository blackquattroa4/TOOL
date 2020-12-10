<?php

namespace App\Http\Controllers;

use App;
use App\CalendarEvent;
use App\Http\Requests;
use App\Helpers\DateHelper;
use App\User;
use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MaddHatter\LaravelFullcalendar\Facades\Calendar;
use Mail;
use Validator;

class CalendarController extends Controller
{
	public function index(Request $request)
	{
		$events = [];

		foreach (auth()->user()->getEvents()->get() as $event) {
			/*
			$events[] = Calendar::event(
				'Event One', //event title
				false, //full day event?
				'2018-07-11T0800', //start time (you can also use Carbon instead of DateTime)
				'2018-07-12T0800', //end time (you can also use Carbon instead of DateTime)
				0, //optionally, you can specify an event ID
				[
					'url' => '#1',
				]
			);
			*/

			$events[] = Calendar::event(
				$event->subject,  // subject
				(($event->from_time == '00:00:00') && ($event->to_time == '00:00:00')),  // full day event?
				$event->from_date.'T'.preg_replace("/([0-9]{2}):([0-9]{2}):([0-9]{2})/i", "\$1:\$2", $event->from_time),  // from
				$event->to_date.'T'.preg_replace("/([0-9]{2}):([0-9]{2}):([0-9]{2})/i", "\$1:\$2", $event->to_time),  // to
				$event->id,  // event id
				[
					'color' => $event->getReplyColor(),
				]
			);
		}

		//$eloquentEvent = EventModel::first(); //EventModel implements MaddHatter\LaravelFullcalendar\Event
		$calendar = Calendar::addEvents($events) //add an array with addEvents
			/*
			->addEvent($eloquentEvent, [ //set custom color fo this event
					'color' => '#800',
				])
			*/
			->setOptions([ //set fullcalendar options
					//'firstDay' => 0,
					'locale' => trans('tool.locale'),
					'buttonText' => [
						'today' => trans('tool.today'),
						'month' => trans('tool.month'),
						'week' => trans('tool.week'),
						'day' => trans('tool.day'),
					],
				])
			->setCallbacks([ //set fullcalendar callback options (will not be JSON encoded)
					//'viewRender' => 'function(view) { }',
					'dayClick' => 'function(date, jsEvent, view) { prepareAndShowModalForDayClick(date, jsEvent, view); }',
					'eventClick' => 'function(event, jsEvent, view) { prepareAndShowModalForEventClick(event, jsEvent, view); }',
					'eventRender' => 'function(event, element) { element.find(".fc-content").attr("id", "event-" + event.id); }',
				]);

		$datePattern = DateHelper::guiDatePattern();

		return view()->first(generateTemplateCandidates('calendar.index'), compact('datePattern', 'calendar'));
	}

	public function get($id, Request $request)
	{
		// all Ajax controller does not register with session-history
		// $this->removeFromHistory();

		$event = CalendarEvent::find($id);
		if (!in_array(auth()->user()->id, $event->getParticipants()->pluck('users.id')->toArray()  )) {
			throw new Exceptions('Event can not be loaded');
		}

		return [
				'id' => $event->id,
				'host_id' => $event->staff_id,
				'can_edit' => $event->isHost(auth()->user()->id),
				'title' => $event->subject,
				'start_date' => DateHelper::dbToGuiDate($event->from_date),
				'start_time' => ($event->from_time == '00:00:00') ? "08:00:00" : $event->from_time,
				'end_date' => DateHelper::dbToGuiDate($event->to_date),
				'end_time' => ($event->to_time == '00:00:00') ? "08:30:00" : $event->to_time,
				'active' => ($event->active == 1),
				'all_day' => (($event->from_time == '00:00:00') && ($event->to_time == '00:00:00')),
				'participants' => $event->getParticipants()->pluck('users.id')->toArray(),
				'default_decision_color' => $event->getReplyColor(),
				'decisions' => array_map(function($userId) use ($event) { return trans('forms.' . ucfirst($event->getReply($userId))); }, $event->getParticipants()->pluck('users.id', 'users.id')->toArray()),
				'decision_colors' => array_map(function($userId) use ($event) { return $event->getReplyColor($userId); }, $event->getParticipants()->pluck('users.id', 'users.id')->toArray()),
			];
	}

	public function save(Request $request)
	{
		// all Ajax controller does not register with session-history
		// $this->removeFromHistory();

		try {
			DB::transaction(function() use ($request) {
				$isCreate = ($request->input('id') == 0);
				if ($isCreate) {
					// create a new event
					$event = CalendarEvent::create([
							'staff_id' => auth()->user()->id,
							'subject' => $request->input('title'),
							'description' => $request->input('title'),
							'from_date' => DateHelper::guiToDbDate($request->input('start_date')),
							//'to_date' => date("Y-m-d", strtotime(DateHelper::guiToDbDate($request->input('end_date')) . " + 1 day")),
							'to_date' => DateHelper::guiToDbDate($request->input('end_date')),
							'repeat_type' => 'never',
							'from_time' => ($request->input('all_day') == "true") ? "00:00:00" : $request->input('start_time'),
							'to_time' => ($request->input('all_day') == "true") ? "00:00:00" : $request->input('end_time'),
							'active' => 1,
						]);
				} else {
					// update an existing event
					$event = CalendarEvent::find($request->input('id'));
					$event->update([
							'subject' => $request->input('title'),
							'description' => $request->input('title'),
							'from_date' => DateHelper::guiToDbDate($request->input('start_date')),
							//'to_date' => date("Y-m-d", strtotime(DateHelper::guiToDbDate($request->input('end_date')) . " + 1 day")),
							'to_date' => DateHelper::guiToDbDate($request->input('end_date')),
							'repeat_type' => 'never',
							'from_time' => ($request->input('all_day') == "true") ? "00:00:00" : $request->input('start_time'),
							'to_time' => ($request->input('all_day') == "true") ? "00:00:00" : $request->input('end_time'),
							'active' => ($request->input('active') == "true"),
						]);
				}
				// add participants
				$oldParticipants = array_column(DB::select("select staff_id from calendar_event_participants where calendar_event_id = " . $event->id), 'staff_id');
				$newParticipants = $request->input('participants');
				// newly added participant
				foreach (array_diff($newParticipants, $oldParticipants) as $participantId) {
					DB::insert("insert into calendar_event_participants (calendar_event_id, staff_id, action, created_at, updated_at) values (" . $event->id . ", " . $participantId . ", '" . (($participantId == auth()->user()->id) ? "accepted" : "invited") . "', utc_timestamp(), utc_timestamp())");
				}
				// old participants that are removed
				foreach (array_diff($oldParticipants, $newParticipants) as $participantId) {
					DB::delete("delete from calendar_event_participants where calendar_event_id = " . $event->id . " and staff_id = " . $participantId);
				}
				// email.
				try {
					Mail::send($isCreate ? 'email_templates.new_event_notification' : 'email_templates.updated_event_notification',
						[
							'event' => $event,
							'host' => User::find($event->staff_id),
						],
						function ($m) use ($event, $isCreate) {
							$m->subject($isCreate ? "New event (" . $event->subject . ") invitation" : "Event (" . $event->subject . ") updates");
							$m->from(config("mail.from.address"), config("mail.from.name"));
							foreach ($event->getParticipants()->get() as $participant) {
								// send invitation to everyone but the creator.
								if ($participant->id != $event->staff_id) {
									$m->to($participant->email, $participant->name);
								}
							}
						});
				} catch (\Exception $e) {
					$registration = recordAndReportProblem($e);
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
		}

		// send back all event
		$events = [];
		foreach (auth()->user()->getEvents()->get() as $oldEvent) {
			$events[] = [
					'id' => $oldEvent->id,
					'title' => $oldEvent->subject,
					'allDay' => (($oldEvent->from_time == '00:00:00') && ($oldEvent->to_time == '00:00:00')),  // full day event?
					'start' => $oldEvent->from_date.'T'.preg_replace("/([0-9]{2}):([0-9]{2}):([0-9]{2})/i", "\$1:\$2", $oldEvent->from_time),
					'end' => $oldEvent->to_date.'T'.preg_replace("/([0-9]{2}):([0-9]{2}):([0-9]{2})/i", "\$1:\$2", $oldEvent->to_time),
					'color' => $oldEvent->getReplyColor(),
				];
		}

		return $events;
	}

	public function reply($id, Request $request)
	{
		// all Ajax controller does not register with session-history
		// $this->removeFromHistory();

		try {
			$event = CalendarEvent::find($id);
			DB::transaction(function() use ($id, $request) {
				DB::update("update calendar_event_participants set action = '" . $request->input('action') . "', updated_at = utc_timestamp() where calendar_event_id = " . $id . " and staff_id = " . auth()->user()->id );
			});
			// email.
			try {
				Mail::send('email_templates.event_reply',
					[
						'event' => $event,
						'action' => $request->input('action'),
						'respondent' => auth()->user(),
					],
					function ($m) use ($event) {
						$m->subject("Event(" . $event->subject . ") reply from " . auth()->user()->name);
						$m->from(config("mail.from.address"), config("mail.from.name"));
						$host = User::find($event->staff_id);
						$m->to($host->email, $host->name);
					});
			} catch (\Exception $e) {
				$registration = recordAndReportProblem($e);
			}
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
		}

		// send back all event
		$events = [];
		foreach (auth()->user()->getEvents()->get() as $oldEvent) {
			$events[] = [
					'id' => $oldEvent->id,
					'title' => $oldEvent->subject,
					'allDay' => (($oldEvent->from_time == '00:00:00') && ($oldEvent->to_time == '00:00:00')),  // full day event?
					'start' => $oldEvent->from_date.'T'.preg_replace("/([0-9]{2}):([0-9]{2}):([0-9]{2})/i", "\$1:\$2", $oldEvent->from_time),
					'end' => $oldEvent->to_date.'T'.preg_replace("/([0-9]{2}):([0-9]{2}):([0-9]{2})/i", "\$1:\$2", $oldEvent->to_time),
					'color' => $oldEvent->getReplyColor(),
				];
		}

		return $events;
	}
}
