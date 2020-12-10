<?php
namespace App\Http\Controllers;

use App;
use App\Helpers\HistoryHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Role as RoleResource;
use App\Role;
use App\Permission;
use Auth;
use DB;
use Illuminate\Http\Request;
use Validator;

class RoleController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request)
	{
		$roles = Role::orderBy('id','DESC')->paginate(10);
		$controlSwitch = [ 'role-modal' => Auth::user()->can(['role-create', 'role-edit', 'role-view']) ];
		return view()->first(generateTemplateCandidates('role.index'),compact('roles', 'controlSwitch'))
			->with('i', ($request->input('page', 1) - 1) * 10);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		$permission = Permission::get();
		return view()->first(generateTemplateCandidates('role.create'),compact('permission'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		$this->validate($request, [
			'name' => 'required|unique:roles,name',
			'display_name' => 'required',
			'description' => 'required',
		]);

		try {
			DB::transaction(function() use ($request) {
				$role = new Role();
				$role->name = $request->input('name');
				$role->display_name = $request->input('display_name');
				$role->description = $request->input('description');
				$role->save();

				foreach ($request->input('permission') as $key => $value) {
					$role->attachPermission($value);
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', trans('Role created successfully'));
	}

	public function createPostAjax(Request $request)
	{
		$validator = Validator::make($request->all(), [
				'name' => 'required|unique:roles,name',
				'display_name' => 'required',
				'description' => 'required',
			]);

		if ($validator->fails()) {
			return response()->json([ 'success' => false, 'errors' => $validator->errors() ]);
		}

		$role = null;
		try {
			DB::transaction(function() use ($request, &$role) {
				$role = new Role();
				$role->name = $request->input('name');
				$role->display_name = $request->input('display_name');
				$role->description = $request->input('description');
				$role->save();

				if ($request->input('permission')) {
					foreach (array_keys($request->input('permission')) as $value) {
						$role->attachPermission($value);
					}
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		return response()->json([ 'success' => true, 'data' => new RoleResource($role) ]);

	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		$role = Role::find($id);
		$rolePermissions = Permission::join("permission_role","permission_role.permission_id","=","permissions.id")
			->where("permission_role.role_id",$id)
			->get();

		return view()->first(generateTemplateCandidates('role.show'),compact('role','rolePermissions'));
	}

	public function loadRoleAjax($id)
	{
		if ($id) {
			$role = Role::find($id);
			$permissions = array_column(DB::select("SELECT
					permissions.id,
					(SELECT COUNT(1)
						FROM permission_role
						WHERE permission_id = permissions.id
							AND role_id = " . $id . ") AS `permitted`
				FROM permissions"), 'permitted', 'id');

			return response()->json([
				'success' => true,
				'data' => [
					'csrf' => csrf_token(),
					'id' => $role->id,
					'name' => $role->name,
					'display' => $role->display_name,
					'description' => $role->description,
					'permission' => $permissions,
				]
			]);
		}

		return response()->json([
			'success' => true,
			'data' => [
				'csrf' => csrf_token(),
				'id' => 0,
				'name' => '',
				'display' => '',
				'description' => '',
				'permission' => array_column(DB::select("SELECT
							permissions.id,
							0 AS `permitted`
						FROM permissions"), 'permitted', 'id'),
			]
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		$role = Role::find($id);
		$permission = Permission::get();
		$rolePermissions = DB::table("permission_role")->where("permission_role.role_id",$id)
			->pluck('permission_role.permission_id','permission_role.permission_id')->toArray();

		return view()->first(generateTemplateCandidates('role.edit'),compact('role','permission','rolePermissions'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		$this->validate($request, [
			'display_name' => 'required',
			'description' => 'required',
		]);

		try {
			DB::transaction(function () use ($id, $request) {
				$role = Role::find($id);
				$role->display_name = $request->input('display_name');
				$role->description = $request->input('description');
				$role->save();

				DB::table("permission_role")->where("permission_role.role_id", $id)
					->delete();

				foreach ($request->input('permission') as $key => $value) {
					$role->attachPermission($value);
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', trans('Role updated successfully'));
	}

	public function updatePostAjax(Request $request, $id)
	{
		$validator = Validator::make($request->all(), [
				'display_name' => 'required',
				'description' => 'required',
			]);

		if ($validator->fails()) {
			return response()->json([ 'success' => false, 'errors' => $validator->errors() ]);
		}

		$role = Role::find($id);
		try {
			DB::transaction(function () use ($role, $request) {
				$role->display_name = $request->input('display_name');
				$role->description = $request->input('description');
				$role->save();

				DB::table("permission_role")->where("permission_role.role_id", $role->id)
					->delete();

				if ($request->input('permission')) {
					foreach (array_keys($request->input('permission')) as $value) {
						$role->attachPermission($value);
					}
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		return response()->json([ 'success' => true, 'data' => new RoleResource($role) ]);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		try {
			DB::transaction(function () use ($id) {
				DB::table("roles")->where('id',$id)->delete();
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', trans('Role deleted successfully'));
	}
}
?>
