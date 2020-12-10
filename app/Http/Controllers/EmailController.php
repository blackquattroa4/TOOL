<?php

namespace App\Http\Controllers;

use App;
use App\Email;
use App\Http\Resources\Email as EmailResource;
use App\User;
use App\Helpers\EmailHelper;
use App\Helpers\ImapHelper;
use App\Http\Requests;
use Auth;
use DB;
use File;
use Fpdf;
use Illuminate\Http\Request;
use Log;
use Storage;
use Swift_Attachment;
use Swift_Mailer;
use Swift_SmtpTransport;

class EmailController extends Controller
{
	public function index()
	{
		if (!Auth::user()->isEmailSetup()) {
			return view()->first(generateTemplateCandidates('email.not_setup'));
		}

		return view()->first(generateTemplateCandidates('email.list'));
	}

	public function getEmailHeaders($box)
	{
		if (!Auth::user()->isEmailSetup()) {
			return response()->json([ 'success' => false, 'data' => [] ]);
		}

		return response()->json([ 'success' => true, 'data' => EmailResource::collection(ImapHelper::getAllHeaders(Auth::user(), $box)) ]);
	}

	public function get(Request $request)
	{
		// all Ajax controller does not register with session-history
		// $this->removeFromHistory();

		if (!Auth::user()->isEmailSetup()) {
			return view()->first(generateTemplateCandidates('email.not_setup'));
		}

		$mailBox = $request->input('box');

		$result = ImapHelper::getNewHeaders(Auth::user(), $mailBox);

		return json_encode($result);
	}

	// controller of "view" AJAX call
	public function view($id, Request $request)
	{
		// all Ajax controller does not register with session-history
		// $this->removeFromHistory();

		if (!Auth::user()->isEmailSetup()) {
			return view()->first(generateTemplateCandidates('email.not_setup'));
		}

		$mailpart = ImapHelper::getMail(Auth::user(), $id);

		// set mail as viewed
		Email::find($id)->update([ 'seen' => 1 ]);

		return json_encode($mailpart);
	}

	public function attachment($hash, Request $request)
	{
		// exclude this controller from registering with session-history
		// $this->removeFromHistory();

		// get the file
		$result = glob(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $hash . '*');
		// there should only be one file.  We'll make assumption grab first one
		$fileLocation = $result[0];
		$mimeType = mime_content_type($fileLocation);
		$headers = [
						'Content-Type: ' . $mimeType,
					];
		return response()->download($fileLocation, str_replace(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $hash , "", $fileLocation), $headers);
	}

	// controller of "send" AJAX call
	public function send(Request $request)
	{
		// all Ajax controller does not register with session-history
		// $this->removeFromHistory();

		if (!Auth::user()->isEmailSetup()) {
			return view()->first(generateTemplateCandidates('email.not_setup'));
		}

		// send out email
		try {
			DB::transaction(function() use ($request) {
				$toRecipient = EmailHelper::parseAddressee($request->input('to'));
				$ccRecipient = EmailHelper::parseAddressee($request->input('cc'));
				$bccRecipient = EmailHelper::parseAddressee($request->input('bcc'));

				preg_match("/^([a-z.\-A-Z]+)\:([1-9][0-9]+)\/(smtp)\/(ssl|tls|notls)$/i", auth()->user()->smtp_endpoint, $smtpSetting);
				//$transport = Swift_SmtpTransport::newInstance($smtpSetting[1], $smtpSetting[2], $smtpSetting[4]);    // SmtpTransport 5.1
				$transport = new Swift_SmtpTransport($smtpSetting[1], $smtpSetting[2], $smtpSetting[4]);    // SmtpTransport 6.0+
				$transport->setUsername(auth()->user()->email);
				$transport->setPassword(auth()->user()->email_password);
				$transport->setStreamOptions(['ssl' => ['allow_self_signed' => true, 'verify_peer' => false, 'verify_peer_name' => false]]);

				//$mailer = Swift_Mailer::newInstance($transport);    // SmtpTransport 5.1
				$mailer = new Swift_Mailer($transport);    // SmtpTransport 6.0+
				$message = $mailer->createMessage();
				$message->setFrom(auth()->user()->email);
				if (count($toRecipient)) { $message->setTo($toRecipient); }
				if (count($ccRecipient)) { $message->setCc($ccRecipient); }
				if (count($bccRecipient)) { $message->setBcc($bccRecipient); }
				$message->setSubject($request->input('subject'));
				$message->setBody($request->input('content'), 'text/html');
				if ($request->has('attachment')) {
					foreach ($request->input('attachment') as $pathName) {
						$message->attach(Swift_Attachment::fromPath($pathName));
					}
				}

				if ($mailer->send($message)) {
					// record entry in sent-box
					$newEmail = Email::create([
							'user_id' => auth()->user()->id,
							'sent_at' => date("Y-m-d H:i:s"),
							'folder' => 'sent',
							'from' => auth()->user()->email,
							'subject' => $request->input('subject'),
							'uid' => strtotime(date("Y-m-d H:i:s")),
							'recent' => 0,
							'seen' => 0,
							'flagged' => 0,
							'answered' => 0,
							'deleted' => 0,
							'draft' => 0,
						]);
						return json_encode([
								'id' => $newEmail->id,
								'sent_at' => date("Y-m-d g:iA", strtotime($newEmail->sent_at)),
								'subject' => $newEmail->subject,
								'from' => $newEmail->from,
							]);
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return json_encode([]);
		}

		return json_encode([]);
	}

	// controller of "reply", "forward" AJAX call
	public function prepareCorrespondence(Request $request)
	{
		// all Ajax controller does not register with session-history
		// $this->removeFromHistory();

		if (!Auth::user()->isEmailSetup()) {
			return view()->first(generateTemplateCandidates('email.not_setup'));
		}

		$mode = $request->input('mode');
		if ($mode == 'compose') {
			return json_encode([
					'subject' => '',
					'to' => '',
					'cc' => '',
					'bcc' => '',
					'content' => '',
				]);
		}

		$id = $request->input('id');

		$mailpart = ImapHelper::getMail(Auth::user(), $id);

		if (!$mailpart['htmlmsg'] || strlen($mailpart['htmlmsg']) === 0) {
			$body = str_replace(["\r\n", "\n", "\r"], ["<br>", "<br>", "<br>"], $mailpart['plainmsg']);
		} else {
			$body = $mailpart['htmlmsg'];
		}
		$body = preg_replace("/<\/body>/i", "</blockquote>$0", preg_replace("/(<body[\s]*([\s]+[^=]+\=\"[^\"]*\")*)>/i", "$0<br><blockquote>", $body));

		// prepare content according to mode
		switch ($mode) {
			case 'reply':
				return json_encode([
						'subject' => 'RE: ' . $mailpart['subject'],
						'to' => explode(", ", $mailpart['from']),
						'cc' => '',
						'bcc' => '',
						'content' => $body,
					]);
					break;
			case 'forward':
				$attachments = [];
				if ($mailpart['attachments']) {
					if (!Storage::exists('tmp' . DIRECTORY_SEPARATOR . session()->getId())) {
						Storage::makeDirectory('tmp' . DIRECTORY_SEPARATOR . session()->getId());
					}
					// all attachments should be downloaded by ImapHelper::getMail()
					foreach ($mailpart['attachments'] as $fileName => $fileHash) {
						// get the file
						$result = glob(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileHash . '*');
						// there should only be one file.  We'll make assumption grab first one
						$tmpFileLocation = $result[0];
						// move all attachments
						$newFileLocation = Storage::getDriver()->getAdapter()->getPathPrefix() . 'tmp' . DIRECTORY_SEPARATOR . session()->getId() . DIRECTORY_SEPARATOR . $fileName;
						File::move($tmpFileLocation, $newFileLocation);
						// stuff the response
						$attachments[] = [
							"name" => $newFileLocation,
							"size" => filesize($newFileLocation),
						];
					}
				}
				return json_encode(array_merge([
						'subject' => 'FWD: ' . $mailpart['subject'],
						'to' => '',
						'cc' => '',
						'bcc' => '',
						'content' => $body,
					], count($attachments) ? [ 'attachments' => $attachments ] : []));
					break;
		}

		return json_encode([
				'subject' => '',
				'to' => '',
				'cc' => '',
				'bcc' => '',
				'content' => '',
			]);
	}

	// controller of "delete" AJAX call
	public function delete($id, Request $request)
	{
		// all Ajax controller does not register with session-history
		// $this->removeFromHistory();

		if (!Auth::user()->isEmailSetup()) {
			return view()->first(generateTemplateCandidates('email.not_setup'));
		}

		try {
			DB::transaction(function() use ($id) {
				$mailpart = ImapHelper::getMail(Auth::user(), $id);

				// set mail as deleted
				Email::find($id)->update([ 'deleted' => 1 ]);

				return json_encode($mailpart);
			});
		} catch (\Exception $e) {
		}

		return json_encode([]);
	}

	public function attach(Request $request)
	{
		// all Ajax controller does not register with session-history
		// $this->removeFromHistory();

		$response = [];

		foreach ($request->file() as $files) {
			foreach ($files as $file) {
				$file->move(Storage::getDriver()->getAdapter()->getPathPrefix() . 'tmp' . DIRECTORY_SEPARATOR . session()->getId(), $file->getClientOriginalName());
				$response[] = Storage::getDriver()->getAdapter()->getPathPrefix() . 'tmp' . DIRECTORY_SEPARATOR . session()->getId() . DIRECTORY_SEPARATOR . $file->getClientOriginalName();
			}
		}

		return $response;
	}
}
