<?php
namespace App\Helpers;

use Auth;
use App\Email;

class ImapHelper
{
	public static function getAllHeaders($theAccountUser, $mailBox)
	{
		// unmark all 'recent'
		Email::where('user_id', $theAccountUser->id)->update([ 'recent' => 0 ]);

		// pull from existing database.
		return Email::where([
				['user_id', '=', $theAccountUser->id],
				['deleted', '=', 0],
				['folder', '=', $mailBox]
			])->orderBy('created_at', 'desc')->orderBy('sent_at', 'desc')->get();
	}

	public static function getNewHeaders($theAccountUser, $mailBox)
	{
		$inbox = [];

		switch ($mailBox) {
			case "INBOX":
				$mailbox = imap_open('{'.$theAccountUser->imap_endpoint.'}'.$mailBox, $theAccountUser->email, $theAccountUser->email_password);

				// only look for messages after last pull
				$lastDate = Email::where('user_id', $theAccountUser->id)->max('sent_at');

		    $seq = imap_search($mailbox, 'SINCE "' . ($lastDate ? date("Y-m-d", strtotime($lastDate)) : "1970-01-01") . '"');
				if ($seq !== false) {
		   		$seq = imap_fetch_overview($mailbox, implode(",", $seq));
					foreach ($seq as $email) {
						// only save un-saved.
						if (!Email::where([['user_id', '=', $theAccountUser->id], ['uid', '=', $email->message_id]])->exists()) {
							$newEmail = Email::create([
									'user_id' => $theAccountUser->id,
									'sent_at' => date("Y-m-d H:i:s", strtotime($email->date)),
									'folder' => 'inbox',
									'from' => imap_mime_header_decode($email->from)[0]->text,
									'subject' => imap_mime_header_decode($email->subject)[0]->text,
									'uid' => $email->message_id,
									'recent' => 1,
									'seen' => 0,
									'flagged' => 0,
									'answered' => 0,
									'deleted' => 0,
									'draft' => 0,
								]);
							array_push($inbox, [
									'id' => $newEmail->id,
									'sent_at' => date("Y-m-d g:iA", strtotime($email->date)),
									'from' => imap_mime_header_decode($email->from)[0]->text,
									'subject' => imap_mime_header_decode($email->subject)[0]->text,
								]);
						}
					}
				}

				imap_close($mailbox);
				break;
			default:
				break;
		}

		return $inbox;
	}

	public static function getMail($theAccountUser, $id)
	{
		$mailBoxName = strtoupper(Email::find($id)->folder);
		// argument is the directory into which attachments are to be saved:
		$mailbox = imap_open('{'.$theAccountUser->imap_endpoint.'}'.$mailBoxName, $theAccountUser->email, $theAccountUser->email_password);
		$mailpart = array('subject' => '', 'from' => '', 'to' => array(), 'cc' => array(), 'bcc' => array(), 'plainmsg' => '', 'htmlmsg' => '', 'charset' => '', 'attachments' => array());

		// get $uid from emails.id
		$Key = Email::find($id)->uid;
		$sequence = imap_search($mailbox, 'UNDELETED');
   	$sequence = imap_fetch_overview($mailbox, implode(",", $sequence));
		$uidLookup = array_column($sequence, 'msgno', 'message_id');
		$uid = $uidLookup[$Key];

		$header=imap_fetchheader($mailbox, $uid, FT_UID);
		preg_match_all('/([^: ]+): (.+?(?:\r\n\s(?:.+?))*)\r\n/m', $header, $match);

		// open the mail header
		/*
		$header = imap_header($mailbox, imap_msgno($mailbox, $uid));
		$mailpart['subject'] = isset($header->subject) ? imap_mime_header_decode($header->Subject)[0]->text : "<no subject>";
		$fromInfo = $header->from[0];
		$mailpart['from'] = (isset($fromInfo->personal) ? imap_mime_header_decode($fromInfo->personal)[0]->text . ' <' . $fromInfo->mailbox . '@' . $fromInfo->host . '>' : $fromInfo->mailbox . '@' . $fromInfo->host);
		foreach (array('to', 'cc', 'bcc') as $oneType) {
			if (isset($header->{$oneType})) {
				foreach ($header->{$oneType} as $oneRecipient) {
					$mailpart[$oneType][] = (isset($oneRecipient->personal) ? imap_mime_header_decode($oneRecipient->personal)[0]->text . ' <' . $oneRecipient->mailbox . '@' . $oneRecipient->host . '>' : $oneRecipient->mailbox . '@' . $oneRecipient->host);
				}
			}
		}
		*/

		$subjectKey = array_search("Subject", $match[1]);
		$fromKey = array_search("From", $match[1]);
		$toKey = array_search("To", $match[1]);
		$ccKey = array_search("Cc", $match[1]);
		$bccKey = array_search("Bcc", $match[1]);

		$mailpart['subject'] = imap_mime_header_decode($match[2][$subjectKey])[0]->text;
		$mailpart['from'] = $match[2][$fromKey];
		if ($toKey !== false) {
			$mailpart['to'] = explode(",\r\n", $match[2][$toKey]);
		}
		if ($ccKey !== false) {
			$mailpart['cc'] = explode(",\r\n", $match[2][$ccKey]);
		}
		if ($bccKey !== false) {
			$mailpart['bcc'] = explode(",\r\n", $match[2][$bccKey]);
		}

		// open the mail body
		$struct = imap_fetchstructure($mailbox, $uid, FT_UID);
		if (isset($struct->parts) && $struct->parts) {
			// multipart: cycle through each part
			foreach ($struct->parts as $partno0 => $p) {
				self::getpart($mailbox, $uid, $p, $partno0+1, $mailpart);
			}
		} else {
			// simple or no parts at all.
			self::getpart($mailbox, $uid, $struct, 0, $mailpart);  // pass 0 as part-number
		}
		imap_close($mailbox);
		//logger()->info(' mail -> ', $mailpart);
		return $mailpart;
	}

	private static function getpart($mbox, $mid, $p, $partno, &$mailpart)
	{
		// $partno = '1', '2', '2.1', '2.1.3', etc for multipart, 0 if simple
		//global $htmlmsg,$plainmsg,$charset,$attachments;

		// DECODE DATA
		$data = ($partno)?
					imap_fetchbody($mbox,$mid,$partno) :  // multipart
					imap_body($mbox,$mid);  // simple
		// Any part may be encoded, even plain text messages, so check everything.
		if ($p->encoding == 4) {
			$data = quoted_printable_decode($data);
		} elseif ($p->encoding == 3) {
			$data = base64_decode($data);
		}

		// PARAMETERS
		// get all parameters, like charset, filenames of attachments, etc.
		$params = array();
		if (property_exists($p, "parameters")) {
			foreach ($p->parameters as $x) {
				$params[strtolower($x->attribute)] = $x->value;
			}
		}
		if (property_exists($p, "dparameters")) {
			foreach ($p->dparameters as $x) {
				$params[strtolower($x->attribute)] = $x->value;
			}
		}

		// ATTACHMENT
		// Any part with a filename is an attachment,
		// so an attached text file (type 0) is not mistaken as the message.
		if (isset($params['filename']) || isset($params['name'])) {
			// filename may be given as 'Filename' or 'Name' or both
			$filename = isset($params['filename']) ? $params['filename'] : $params['name'];
			// filename may be encoded, so see imap_mime_header_decode()
			//$mailpart['attachments'][$filename] = $data;  // this is a problem if two files have same name
			$tempFileHash = sha1(rand());
			file_put_contents(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $tempFileHash . $filename, $data);
			$mailpart['attachments'][$filename] = $tempFileHash;
		}

		// TEXT
		switch ($p->type) {
			case 0:
				// Messages may be split in different parts because of inline attachments,
				// so append parts together with blank row.
				if ($data) {
					if (strtolower($p->subtype)=='plain') {
						$mailpart['plainmsg'] .= preg_replace('/[[:^print:]]/', '', trim($data)) ."\n\n";
					} else {
						$mailpart['htmlmsg'] .= $data ."<br><br>";
					}
					$mailpart['charset'] = $params['charset'];  // assume all parts are same charset
				}
				break;
			case 1:
				if ($data) {
					if (strtolower($p->subtype)=='plain') {
						$mailpart['plainmsg'] .= preg_replace('/[[:^print:]]/', '', trim($data)) ."\n\n";
					} else {
						$mailpart['htmlmsg'] .= $data ."<br><br>";
					}
					$mailpart['charset'] = 'utf-8';  // parts charset is encoded in message.
				}
				break;
			case 2:
				// EMBEDDED MESSAGE
				// Many bounce notifications embed the original message as type 2,
				// but AOL uses type 1 (multipart), which is not handled here.
				// There are no PHP functions to parse embedded messages,
				// so this just appends the raw source to the main message.
				if ($data) {
					$mailpart['plainmsg'] .= $data."\n\n";
				}
				break;
		}

		// SUBPART RECURSION
		if (property_exists($p, "parts")) {
			foreach ($p->parts as $partno0=>$p2) {
				self::getpart($mbox,$mid,$p2,$partno.'.'.($partno0+1),$mailpart);  // 1.2, 1.2.1, etc.
			}
		}
	}

}
?>
