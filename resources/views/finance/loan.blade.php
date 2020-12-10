@extends('layouts.app')

@section('additional-style')
<style>
	label.form-check-label {
		vertical-align: top;
		margin-top: 9px;
	}

	table.permission-table {
		width: 100%;
	}
</style>
@endsection

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			@if (isset($history) && count($history))
				<!-- history modal -->
				<div class="modal fade" id="historyModal" tabindex="-1" role="dialog" aria-labelledby="historyModalLabel" data-backdrop="static" aria-hidden="false">
					<div class="modal-dialog" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
								<h4 class="modal-title" id="deleteModalLabel">{{ trans('forms.History') }}</h4>
							</div>
							<div class="modal-body">
							@foreach ($history as $oneLine)
								<p>{{ sprintf(trans('messages.%1$s %2$s at %3$s'), $oneLine->staff['name'], trans('action.'.$oneLine['process_status']), \App\Helpers\DateHelper::dbToGuiDate($oneLine['updated_at']->format("Y-m-d")) . " " . $oneLine['updated_at']->format("g:iA")) }}</p>
							@endforeach
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans("forms.Close") }}</button>
							</div>
						</div>
					</div>
				</div>
			@endif

			<!-- loan bad debt modal -->
			@if (!$readonly && is_array($action) && (array_key_exists('baddebt', $action)))
				<div class="modal fade" id="baddebtModal" tabindex="-1" role="dialog" aria-labelledby="baddebtModalLabel" data-backdrop="static" aria-hidden="false">
					<div class="modal-dialog" role="baddebt">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
								<h4 class="modal-title" id="interestModalLabel">{{ trans('finance.Bad debt') }}</h4>
							</div>
							<div class="modal-body">

								<form class="form-horizontal" role="form" method="POST" action="{{ $postUrl }}" >
									{{ csrf_field() }}

									<input type="hidden" id="loan_id" value="{{ old('loan_id') }}" />

									<div class="form-group">
										<label class="col-xs-3 col-sm-3 col-md-3 control-label">{{ trans('forms.Date') }}</label>
										<div class="col-xs-5 col-sm-5 col-md-5{{ $errors->has('baddebt_date') ? ' has-error' : ''  }}">
											<div id="baddebt_date_group" class="input-group date" data-provide="datepicker">
												<input id="baddebt_date" type="text" class="form-control" name="baddebt_date" value="" >
												<div class="input-group-addon">
													<span class="glyphicon glyphicon-calendar"></span>
												</div>
											</div>
										@if ($errors->has('baddebt_date'))
											<p class="help-block"><strong>{{ $errors->first('baddebt_date') }}</strong></p>
										@endif
										</div>
									</div>

									<div class="form-group">
									@if ((old('role') == 'borrower'))
										<label class="col-xs-3 col-sm-3 col-md-3 control-label">{{ trans('forms.Revenue account') }}</label>
									@else
										<label class="col-xs-3 col-sm-3 col-md-3 control-label">{{ trans('forms.Expense account') }}</label>
									@endif
										<div class="col-xs-7 col-sm-7 col-md-7{{ $errors->has('baddebt_account') ? ' has-error' : ''  }}">
											<select id="baddebt_account" name="baddebt_account" class="form-control" >
												<option value="">{{ trans('forms.Select an account') }}</option>
											@foreach (\App\ChartAccount::where([ 'active' => 1 ])->whereIn('type', [ (old('role') == 'borrower') ? 'revenue' : 'expense' ])->orderBy('account')->get() as $chartAccount)
												<option value="{{ $chartAccount->id }}">{{ $chartAccount->account }} {{ $chartAccount->description }}</option>
											@endforeach
											</select>
										@if ($errors->has('baddebt_account'))
											<p class="help-block"><strong>{{ $errors->first('baddebt_account') }}</strong></p>
										@endif
										</div>
									</div>

								</form>

							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-primary" onclick="$('div#baddebtModal div.modal-body form').submit()">{{ trans('forms.Submit') }}</button>
								<button type="button" class="btn btn-primary" data-dismiss="modal">{{ trans('forms.Cancel') }}</button>
							</div>
						</div>
					</div>
				</div>
			@endif

			<div class="panel panel-default">
				<div class="panel-heading">
					<table width="100%">
						<tr>
							<td>{{ trans('finance.Loan') }}</td>
							<td>
							@if (isset($history) && count($history))
								<a href="#" data-toggle="modal" data-target="#historyModal"><span class="fa fa-2x fa-history pull-right"></span></a>
							@endif
							</td>
						</tr>
					</table>
				</div>
				<div class="panel-body">
					<form class="form-horizontal" role="form" enctype="multipart/form-data" method="POST" action="{{ $postUrl }}">
						{{ csrf_field() }}

						<div class="form-group">
							<label for="title" class="col-md-3 control-label">{{ trans('finance.Loan title') }}</label>

							<div class="col-md-7{{ $errors->has('title') ? ' has-error' : '' }}">
							@if ($readonly)
								<input id="title" type="text" class="form-control" name="title" value="{{ old('title') }}" readonly>
							@else
								<input id="title" type="text" class="form-control" name="title" value="{{ old('title') }}" >
							@endif

								@if ($errors->has('title'))
									<span class="help-block">
										<strong>{{ $errors->first('title') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group">
							<label for="role" class="col-md-3 control-label">{{ trans('finance.Loan role') }}</label>

							<div class="col-md-7{{ $errors->has('role') ? ' has-error' : '' }}">

								<label for="role" class="col-md-3 control-label">{{ trans('finance.Lender') }}</label>
								<div class="col-md-2">
									<input id="role" type="radio" class="form-control" name="role" value="lender"{{ (old('role') == "lender") ? " checked" : ""}}{{ (isset($is_create) && $is_create) ? "" : " disabled" }}>
								</div>
								<label for="role" class="col-md-3 control-label">{{ trans('finance.Borrower') }}</label>
								<div class="col-md-2">
									<input id="role" type="radio" class="form-control" name="role" value="borrower"{{ (old('role') == "borrower") ? " checked" : ""}}{{ (isset($is_create) && $is_create) ? "" : " disabled" }}>
								</div>

								@if ($errors->has('role'))
									<span class="help-block">
										<strong>{{ $errors->first('role') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group">
							<label for="entity" class="col-md-3 control-label">{{ trans('forms.Entity') }}</label>

							<div class="col-md-7{{ $errors->has('entity') ? ' has-error' : '' }}">
								<select id="entity" class="form-control" name="entity"{{ (isset($is_create) && $is_create) ? "" : " disabled" }}>
								@foreach ($entities as $entity)
									<option value="{{ $entity->id }}"{{ ($entity->id == old('entity')) ? " selected" : "" }}>{{ $entity->name . " (" . $entity->code . ")" }}</option>
								@endforeach
								</select>

								@if ($errors->has('entity'))
									<span class="help-block">
										<strong>{{ $errors->first('entity') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group">
							<label for="principal" class="col-md-3 control-label">{{ trans('finance.Principal') }}</label>

							<div class="col-md-3{{ $errors->has('principal') ? ' has-error' : '' }}">
								<input id="principal" type="number" class="form-control text-right" name="principal" min="{{ $principal_min }}" step="{{ $principal_min }}" value="{{ old('principal') }}"{{ (isset($is_create) && $is_create) ? "" : " readonly" }}>

								@if ($errors->has('principal'))
									<span class="help-block">
										<strong>{{ $errors->first('principal') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group">
							<label for="apr" class="col-md-3 control-label">{{ trans('finance.APR') }}</label>

							<div class="col-md-3{{ $errors->has('apr') ? ' has-error' : '' }}">
								<div class="input-group">
								@if ($readonly)
									<input id="apr" type="number" class="form-control text-right" name="apr" min="0.00" step="0.01" value="{{ old('apr') }}" readonly>
								@else
									<input id="apr" type="number" class="form-control text-right" name="apr" min="0.00" step="0.01" value="{{ old('apr') }}">
								@endif
									<span class="input-group-addon">%</span>
								</div>

								@if ($errors->has('apr'))
									<span class="help-block">
										<strong>{{ $errors->first('apr') }}</strong>
									</span>
								@endif
							</div>
						</div>

					@if (isset($is_create) && $is_create)
						<div class="form-group">
							<label for="cash_account" class="col-md-3 control-label">{{ trans('forms.Bank account') }}</label>

							<div class="col-md-7{{ $errors->has('cash_account') ? ' has-error' : '' }}">
								<select id="cash_account" class="form-control" name="cash_account"{{ $readonly ? ' disabled' : '' }}>
								@foreach (unserialize(\App\Parameter::where('key', 'bank_cash_t_account_ids')->first()->value) as $id)
									<option value="{{ $id }}">{{ \App\ChartAccount::find($id)->description }}</option>
								@endforeach
								</select>

								@if ($errors->has('cash_account'))
									<span class="help-block">
										<strong>{{ $errors->first('cash_account') }}</strong>
									</span>
								@endif
							</div>
						</div>
					@endif

						<div class="form-group">
							<label for="notes" class="col-md-3 control-label">{{ trans('forms.Notes') }}</label>

							<div class="col-md-7{{ $errors->has('notes') ? ' has-error' : '' }}">
								<textarea id="notes" class="form-control" name="notes"{{ $readonly ? " readonly" : "" }}>{{ old('notes') }}</textarea>

								@if ($errors->has('notes'))
									<span class="help-block">
										<strong>{{ $errors->first('notes') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group">
						@if (isset($action))
							@if (is_array($action))
								<div class="col-md-2 col-md-offset-{{ 9-2*count($action) }}">
								</div>
								@foreach ($action as $keyAction => $oneAction)
								<div class="col-md-2">
									<button type="submit" id="submit" name="submit" class="btn btn-primary" value="{{ $keyAction }}">
										<i class="fa fa-btn fa-floppy-o"></i> {{ $oneAction }}
									</button>
								</div>
								@endforeach
							@else
								<div class="col-md-2 col-md-offset-9">
									<button type="submit" class="btn btn-primary">
										<i class="fa fa-btn fa-floppy-o"></i> {{ $action }}
									</button>
								</div>
							@endif
						@endif
					</form>
				</div>
			</div>
		</div>

		@if (isset($transactions) && (count($transactions) > 0))
			<div class="panel panel-default">
				<div class="panel-heading">
					{{ trans('finance.Transactions') }}
				</div>

				<div class="panel-body">
					<table class="table table-stripped">
						<tr>
							<th>{{ trans('forms.Date') }}</th>
							<th>{{ trans('forms.Description') }}</th>
							<th>{{ trans('finance.Debit') }}</th>
							<th>{{ trans('finance.Credit') }}</th>
							<th>{{ trans('finance.Balance') }}</th>
						</tr>
					@foreach ($transactions as $transaction)
						<tr>
							<td>{{ $transaction->date }}</td>
							<td>{{ $transaction->description }}</td>
							<td class="text-right">{{ $transaction->debit }}</td>
							<td class="text-right">{{ $transaction->credit }}</td>
							<td class="text-right">{{ $transaction->balance }}</td>
						</tr>
					@endforeach
					</table>
				</div>
			</div>
		@endif
		</div>
	</div>
</div>
@endsection

@section('post-content')
	<script type="text/javascript">

		$(document).ready(function() {
		@if (!$readonly && is_array($action) && (array_key_exists('baddebt', $action)))
			$('button[type="submit"][value="baddebt"]').prop("type", "button");
			$('button[type="button"][value="baddebt"]').prop("id", "baddebt");
			$('button[type="button"][value="baddebt"]').prop("name", "baddebt");
			$('button[type="button"][value="baddebt"]').bind("click", function() {
				$('div#baddebtModal input#baddebt_date').val("{{ \App\Helpers\DateHelper::dbToGuiDate(date("Y-m-d")) }}");
				$('div#baddebtModal select#baddebt_account').val("");
				$('div#baddebtModal div.has-error').removeClass("has-error");
				$('div#baddebtModal div.help-block').detach();
				$('div#baddebtModal').modal('show');
			});

			@if ($errors->any())
				$('div#baddebtModal').modal('show');
			@endif
		@endif
		});

	</script>
@endsection
