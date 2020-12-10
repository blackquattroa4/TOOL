<?php

namespace App\Http\Controllers;

use App\Interaction;
use App\InteractionLog;
use App\Http\Controllers\InteractionBaseController;
use App\Http\Resources\Interaction as InteractionResource;
use DB;
use Illuminate\Http\Request;

class InteractionController extends InteractionBaseController
{
  // This prefix is concat to view path
  protected $ViewNamespacePrefix = "";

	public function changePropertyAjax(Request $request, $id)
  {
    // all Ajax controller does not register with session-history
		// $this->removeFromHistory();

    $interaction = Interaction::find($id);

    try {
      DB::transaction(function() use ($request, &$interaction) {

        $interaction->type = $request->type;
        if ($interaction->isDirty('type')) {
          InteractionLog::create([
            'interaction_id' => $interaction->id,
						'staff_id' => auth()->user()->id,
						'log' => "'type' changed to " . $interaction->type,
						'downloadable_id' => null,
          ]);
        }
        $interaction->status = $request->status;
        if ($interaction->isDirty('status')) {
          InteractionLog::create([
            'interaction_id' => $interaction->id,
						'staff_id' => auth()->user()->id,
						'log' => "'status' changed to " . $interaction->status,
						'downloadable_id' => null,
          ]);
        }
        $interaction->save();

        // update participant
        $oldUserSet = $interaction->users()->withPivot('role')->get()->pluck('pivot')->toArray();
        $indexSet = array_column($oldUserSet, "staff_id");
        $newUserSet = array_combine($indexSet, $oldUserSet);
        $newType = $request->type;
        $newResponsibility = $request->responsibility;
        array_walk($newUserSet, function(&$value) use($newType, $newResponsibility) {
          $value['role'] = ($value['staff_id'] == $newResponsibility) ? Interaction::responsibleRole($newType) : "participant";
          unset($value['interaction_id']);
          unset($value['staff_id']);
        });
        $interaction->users()->sync($newUserSet);

      });
    } catch (\Exception $e) {
      $registration = recordAndReportProblem($e);
      return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
    }

    return response()->json([ 'success' => true, 'data' => new InteractionResource($interaction) ]);
  }

}

?>
