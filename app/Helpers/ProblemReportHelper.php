<?php

// a global error/exception reporting mechanism
function recordAndReportProblem($exception)
{
	$errorId = md5(date('YmdHis'));

	// write into mongoDB logging
	$event = new App\NoSqlLogEvent();
	$event->client_ip = request()->ip();
	$event->client_id = auth()->user() ? auth()->user()->id : \App\User::getSystemUser()->id;
	$event->client_agent = request()->header('User-Agent');;
	$event->request_url = request()->fullurl();
	$event->server_ip = request()->server('SERVER_ADDR');
	$event->time = date("Y-m-d H:i:s");
	$event->severity = 'exception';
	$event->usid = $errorId;
	$event->summary = $exception->getMessage();
	$event->stack = json_encode($exception->getTrace());
	$event->save();

	// check for valid slack URL and then POST on Slack channel
	if (preg_match("/^(.)+$/i", env("SLACK_API_DOMAIN")) && preg_match("/^(.)+$/i", env("SLACK_API_URL"))) {
		// POST to Slack
		$client = new \GuzzleHttp\Client([ "base_uri" => env("SLACK_API_DOMAIN") ]);
    $response = $client->post("/" . env("SLACK_API_URL"), [
      "header" => [
        "Content-Type" => "application/json",
      ],
      "body" => json_encode([
        "text" => "```\n#" . $errorId . "\n" . $exception->getMessage() . "```\n"
      ])
    ]);
	}

	return $errorId;
}

?>
