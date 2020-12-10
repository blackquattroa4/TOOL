<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LogoController extends Controller
{
	public function getLogo(Request $request)
	{
    // this controller does not register with session-history
		// removal no longer needed since this controller is in web-ajax group
		// $this->removeFromHistory();

		return response()->download(
			session('session_logo_file_location'),
			'company_logo.png',
      [
        'Content-Type: image/png',
        'Cache-Control: private',
      ]);
  }
}
