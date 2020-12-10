
<div class="modal fade" id="embeddedLoanModal" tabindex="-1" role="dialog" aria-labelledby="embeddedLoanModalLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">

	<!-- history modal -->
	<div class="modal fade" id="historyModal" tabindex="-1" role="dialog" aria-labelledby="historyModalLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" aria-label="Close" onclick="$(this).closest('#historyModal').modal('hide')">
						<span aria-hidden="true">&times;</span>
					</button>
					<h4 class="modal-title" id="deleteModalLabel">{{ trans('forms.History') }}</h4>
				</div>
				<div class="modal-body">
					<p v-for="record in modal.history">@{{ record }}</p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" onclick="$(this).closest('#historyModal').modal('hide')">{{ trans("forms.Close") }}</button>
				</div>
			</div>
		</div>
	</div>

	<!-- loan bad debt modal -->
	<div class="modal fade" id="baddebtModal" tabindex="-1" role="dialog" aria-labelledby="baddebtModalLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">
		<div class="modal-dialog" role="baddebt">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" aria-label="Close" onclick="$(this).closest('#baddebtModal').modal('hide')">
						<span aria-hidden="true">&times;</span>
					</button>
					<h4 class="modal-title" id="interestModalLabel">{{ trans('finance.Bad debt') }}</h4>
				</div>
				<div class="modal-body">
					<form class="form-horizontal" id="baddebt_form" role="form" method="POST" action="" >

						<input type="hidden" name="_token" v-model="modal.csrf">

						<div class="form-group">
							<label class="col-xs-3 col-sm-3 col-md-3 control-label">{{ trans('forms.Date') }}</label>

							<div class="col-xs-5 col-sm-5 col-md-5" v-bind:class="{ 'has-error' : 'baddebt_date' in errors  }">
								<div id="baddebt_date_group" class="input-group date" v-bind:data-provide="modal.readonly ? '' : 'datepicker'">
									<input id="baddebt_date" type="text" class="form-control" name="baddebt_date" v-model="form.baddebt_date" >
									<div class="input-group-addon">
										<span class="glyphicon glyphicon-calendar"></span>
									</div>
								</div>
								<span class="help-block" v-if="'baddebt_date' in errors">
									<strong>@{{ errors['baddebt_date'][0] }}</strong>
								</span>
							</div>
						</div>

						<div class="form-group">
							<label v-if="form.role == 'borrower'" class="col-xs-3 col-sm-3 col-md-3 control-label">{{ trans('forms.Revenue account') }}</label>
							<label v-if="form.role == 'lender'" class="col-xs-3 col-sm-3 col-md-3 control-label">{{ trans('forms.Expense account') }}</label>
							<div class="col-xs-7 col-sm-7 col-md-7" v-bind:class="{ 'has-error' : 'baddebt_account' in errors }">
								<select id="baddebt_account" name="baddebt_account" class="form-control" v-model="form.baddebt_account">
									<option value="">{{ trans('forms.Select an account') }}</option>
									<option v-if="form.role == 'borrower'" v-for="(display, index) in modal.revenue_account" v-bind:value="index">@{{ display }}</option>
									<option v-if="form.role == 'lender'" v-for="(display, index) in modal.expense_account" v-bind:value="index">@{{ display }}</option>
								</select>
								<span class="help-block" v-if="'baddebt_account' in errors">
									<strong>@{{ errors['baddebt_account'][0] }}</strong>
								</span>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-primary" onclick="forgivePostAjax()">{{ trans('forms.Submit') }}</button>
					<button type="button" class="btn btn-primary"  onclick="$(this).closest('#baddebtModal').modal('hide')">{{ trans('forms.Cancel') }}</button>
				</div>
			</div>
		</div>
	</div>

  <div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<table width="100%">
					<tr>
						<td>
							<font size="4" style="padding-right:30px;">@{{ modal.title }}</font>
							<a v-if="modal.history && (modal.history.length > 0)" style="padding-right:20px;" href="#" data-toggle="modal" data-target="#embeddedLoanModal #historyModal"><span class="fa fa-2x fa-history"></span></a>
							<span v-if="'general' in errors" v-bind:class="{ 'text-danger' : 'general' in errors }" >
								<strong>@{{ errors['general'][0] }}</strong>
							</span>
						</td>
						<td>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</td>
					</tr>
				</table>
			</div>
			<div style="height: 70vh; overflow-y: scroll;" class="modal-body">

				<form class="form-horizontal" id="loan_form" role="form" enctype="multipart/form-data" method="POST" >

					{{ csrf_field() }}

					<div class="form-group">
						<label for="title" class="col-md-3 control-label">{{ trans('finance.Loan title') }}</label>

						<div class="col-md-7" v-bind:class="{ 'has-error' : 'title' in errors }">
							<input id="title" type="text" class="form-control" name="title" v-model="form.title" v-bind:readonly="modal.readonly">
							<span class="help-block" v-if="'title' in errors">
								<strong>@{{ errors['title'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group">
						<label for="role" class="col-md-3 control-label">{{ trans('finance.Loan role') }}</label>

						<div class="col-md-7" v-bind:class="{ 'has-error' : 'role' in errors }">

							<label for="role" class="col-md-3 control-label">{{ trans('finance.Lender') }}</label>
							<div class="col-md-2">
								<input id="role" type="radio" class="form-control" name="role" value="lender" v-model="form.role" v-bind:disabled="modal.readonly || (form.id != 0)" >
							</div>
							<label for="role" class="col-md-3 control-label">{{ trans('finance.Borrower') }}</label>
							<div class="col-md-2">
								<input id="role" type="radio" class="form-control" name="role" value="borrower" v-model="form.role" v-bind:disabled="modal.readonly || (form.id != 0)">
							</div>
							<span class="help-block" v-if="'role' in errors">
								<strong>@{{ errors['role'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group">
						<label for="entity" class="col-md-3 control-label">{{ trans('forms.Entity') }}</label>

						<div class="col-md-7" v-bind:class="{ 'has-error' : 'entity' in errors }">
							<select id="entity" class="form-control" name="entity" v-model="form.entity" v-bind:disabled="modal.readonly || (form.id != 0)" v-on:change="form.currency = modal.entity[form.entity]['currency']">
								<option v-for="(display, index) in modal.entity" v-bind:value="index">@{{ display['display'] }}</option>
							</select>
							<span class="help-block" v-if="'entity' in errors">
								<strong>@{{ errors['entity'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group">
						<label for="principal" class="col-md-3 control-label">{{ trans('finance.Principal') }}</label>

						<div class="col-md-3" v-bind:class="{ 'has-error' : 'principal' in errors }">
							<input id="principal" type="number" class="form-control text-right" name="principal" min="0" v-bind:step="(form.currency == 0) ? 0.01 : modal.currency[form.currency].currency_min" v-model="form.principal" v-bind:readonly="modal.readonly || (form.id != 0)" >
							<span class="help-block" v-if="'principal' in errors">
								<strong>@{{ errors['principal'][0] }}</strong>
							</span>
						</div>

						<div class="col-md-2" v-bind:class="{ 'has-error' : 'currency' in errors }">
							<select id="currency" class="form-control" name="currency" v-model="form.currency" disabled>
								<option v-for="(display, index) in modal.currency" v-bind:value="index">@{{ display.symbol }}</option>
							</select>
							<span class="help-block" v-if="'currency' in errors">
								<strong>@{{ errors['currency'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group">
						<label for="apr" class="col-md-3 control-label">{{ trans('finance.APR') }}</label>

						<div class="col-md-3" v-bind:class="{ 'has-error' : 'apr' in errors }">
							<div class="input-group">
								<input id="apr" type="number" class="form-control text-right" name="apr" min="0.00" step="0.01" v-model="form.apr" v-bind:readonly="modal.readonly">
								<span class="input-group-addon">%</span>
							</div>
							<span class="help-block" v-if="'apr' in errors">
								<strong>@{{ errors['apr'][0] }}</strong>
							</span>
						</div>
					</div>

					<div v-if="(form.id == 0)" class="form-group">
						<label for="cash_account" class="col-md-3 control-label">{{ trans('forms.Bank account') }}</label>

						<div class="col-md-7" v-bind:class="{ 'has-error' : 'cash_account' in errors }">
							<select id="cash_account" class="form-control" name="cash_account" v-model="form.cash_account" v-bind:disabled="modal.readonly || (form.id != 0)" >
								<option v-for="(display, index) in modal.cash_account" v-bind:value="index">@{{ display }}</option>
							</select>
							<span class="help-block" v-if="'cash_account' in errors">
								<strong>@{{ errors['cash_account'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group">
						<label for="notes" class="col-md-3 control-label">{{ trans('forms.Notes') }}</label>

						<div class="col-md-7" v-bind:class="{ 'has-error' : 'notes' in errors }">
							<textarea id="notes" class="form-control" name="notes" v-model="form.notes" v-bind:readonly="modal.readonly"></textarea>
							<span class="help-block" v-if="'notes' in errors">
								<strong>@{{ errors['notes'][0] }}</strong>
							</span>
						</div>
					</div>

				</form>

				<div class="col-md-10 col-md-offset-1" v-if="modal.readonly && (modal.transaction.length > 0)" style="margin-top:40px;" class="panel panel-default">
					<div class="panel-heading">
						<label>{{ trans('finance.Transactions') }}</label>
					</div>

					<div class="panel-body">
						<table class="table table-stripped">
							<tr>
								<th>{{ trans('forms.Date') }}</th>
								<th>{{ trans('forms.Description') }}</th>
								<th class="text-right">{{ trans('finance.Debit') }}</th>
								<th class="text-right">{{ trans('finance.Credit') }}</th>
								<th class="text-right">{{ trans('finance.Balance') }}</th>
							</tr>
							<tr v-for="(trx, index) in modal.transaction">
								<td>@{{ trx['date'] }}</td>
								<td>@{{ trx['description'] }}</td>
								<td class="text-right">@{{ trx['debit'] }}</td>
								<td class="text-right">@{{ trx['credit'] }}</td>
								<td class="text-right">@{{ trx['balance'] }}</td>
							</tr>
						</table>
					</div>
				</div>

			</div>

			<div v-if="Object.keys(modal.action).length > 0" class="modal-footer">
				<div class="form-group">
					<div class="col-md-12">
						<button v-for="(callback, display) in modal.action" type="button" class="btn btn-primary pull-right" style="margin-left:3px;" v-on:click="window[callback]()" v-html="display">
						</button>
					</div>
				</div>
			</div>

		</div>
	</div>
</div>