<div class="modal fade" id="embeddedPayDisbursementModal" tabindex="-1" role="dialog" aria-labelledby="embeddedPayDisbursementModalLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">

	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<table width="100%">
					<tr>
						<td>
							<font size="4" style="padding-right:30px;">@{{ modal.title }}</font>
							<span v-if="'general' in errors" v-bind:class="{ 'text-danger' : 'general' in errors }">
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
				<form class="form-horizontal" role="form" method="POST" action="" >

					<input type="hidden" name="_token" v-model="modal.csrf">

					<div class="form-group">
						<label class="col-xs-2 col-sm-2 col-md-2 col-lg-2 control-label">{{ trans('forms.To') }}</label>
						<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
							<input id="code" type="code" name="code" class="form-control" v-model="form.code" disabled/>
						</div>

						<label class="col-xs-2 col-sm-2 col-md-2 col-xs-offset-1 col-sm-offset-1 col-md-offset-1 control-label">{{ trans('forms.Date') }}</label>

						<div class="col-xs-3 col-sm-3 col-md-3" v-bind:class="{ 'has-error' : 'inputdate' in errors }">
							<div class="input-group date" v-bind:data-provide="modal.readonly ? '' : 'datepicker'">
								<input id="inputdate" type="text" class="form-control" name="inputdate" v-model="form.inputdate" v-bind:readonly="modal.readonly" >
								<div class="input-group-addon">
									<span class="glyphicon glyphicon-calendar"></span>
								</div>
							</div>
							<span class="help-block" v-if="'inputdate' in errors">
								<strong>@{{ errors['inputdate'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group">
						<label class="col-xs-2 col-sm-2 col-md-2 control-label">{{ trans('forms.Bank account') }}</label>

						<div class="col-xs-5 col-sm-5 col-md-5" v-bind:class="{ 'has-error' : 'bank_account' in errors }">
							<select id="bank_account" name="bank_account" class="form-control" v-model="form.bank_account" v-on:change="if (form.bank_account != '') delete errors['bank_account'];">
								<option value="">{{ trans('forms.Select an account') }}</option>
								<option v-for="(display, index) in modal.bank_account" v-bind:value="index">@{{ display }}</option>
							</select>
							<span class="help-block" v-if="'bank_account' in errors">
								<strong>@{{ errors['bank_account'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group">
						<label class="col-xs-2 col-sm-2 col-md-2 control-label">{{ trans('forms.Reference') }}</label>

						<div class="col-xs-6 col-sm-6 col-md-6" v-bind:class="{ 'has-error' : 'reference' in errors }">
							<input id="reference" name="reference" class="form-control" v-model="form.reference" />
							<span class="help-block" v-if="'reference' in errors">
								<strong>@{{ errors['reference'][0] }}</strong>
							</span>
						</div>
					</div>

					<hr />

					<table class="table table-striped">
						<tr>
							<th class="col-xs-1 col-sm-1 col-md-1">{{ trans('forms.Invoice') }}</th>
							<th class="col-xs-2 col-sm-2 col-md-2">{{ trans('forms.Date') }}</th>
							<th class="col-xs-2 col-sm-2 col-md-2">{{ trans('forms.Due') }}</th>
							<th class="col-xs-3 col-sm-3 col-md-3">{{ trans('forms.Summary') }}</th>
							<th class="col-xs-2 col-sm-2 col-md-2" style="text-align:right;">{{ trans('forms.Balance') }}</th>
							<th class="col-xs-2 col-sm-2 col-md-2" style="text-align:right;">{{ trans('forms.Apply') }}</th>
						</tr>
						<tr v-for="(id, index) in form.transactable_id">
							<td>@{{ form.transactable_title[index] }}</td>
							<td>@{{ form.transactable_date[index] }}</td>
							<td>@{{ form.transactable_duedate[index] }}</td>
							<td>@{{ form.transactable_summary[index] }}</td>
							<td class="text-right" v-bind:title="'{{ trans('finance.Total') }} ' + form.transactable_total[index]">
								<span v-if="!form.transactable_credit[index]" class="badge badge-info">{{ trans('forms.Debit') }}</span>&emsp;@{{ form.transactable_balance[index] }}</td>
							<td>
								<div style="margin:1px;" class="form-group" v-bind:class="{ 'has-error' : 'transactable.'+index in errors }">
									<input id="line[]" name="line[]" type="hidden" v-model="form.transactable_id[index]">
                  <input id="transactable[]" name="transactable[]" class="form-control text-right span1" v-bind:class="{ 'credit' : form.transactable_credit[index] }" v-model="form.transactable_applied[index]" min="0" v-bind:step="modal.currency_min" v-bind:max="form.transactable_balance_raw[index]" type="number" onchange="updateUnappliedPayableCredit()">
									<span class="help-block" v-if="'transactable.'+index in errors">
										<strong>@{{ errors['transactable.'+index][0] }}</strong>
									</span>
								</div>
							</td>
						</tr>
					</table>

				</form>
				<a href='#' class="show-balance-hint" style="position:fixed;bottom:3px;left:3px;text-decoration:none;color:#000000;background-color:rgba(255, 255, 255, 0.0);font-size:15px;padding:1em;z-index:10;">
          <img src="{{ asset('images/sum-512.png') }}" height='40' width='40'></img>
        </a>
        <div class="show-balance" style="position:fixed;bottom:3px;left:3px;text-decoration:none;color:#000000;background-color:rgba(255, 255, 255, 1.0);font-size:15px;padding:1em;display:none;z-index:10;">
          <p style="font-size:38px;font-weight:bold;line-height:38px;" >{{ trans('finance.Total') }} @{{ modal.total_display }}</p>
        </div>
			</div>

			<div v-if="!modal.readonly" class="modal-footer">
        <div class="col-md-12">
          <button v-for="(callback, display) in modal.action" type="button" class="btn btn-primary pull-right" style="margin-left:3px;" v-on:click="window[callback]()" v-bind:disabled="form.transactable_applied.every(o => o == 0) || (Object.keys(errors).length > 0)" v-html="display">
          </button>
        </div>
	    </div>

		</div>
	</div>
</div>