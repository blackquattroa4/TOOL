

<div class="modal fade" id="embeddedTaccountReconciliationModal" tabindex="-1" role="dialog" aria-labelledby="embeddedTaccountReconciliationLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">

  <div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<table width='100%'>
					<tr>
            <td>
              <font size="4" style="padding-right:30px;">{{ trans('finance.Account transaction') }}</font>
              <span v-if="'general' in errors" v-bind:class="{ 'text-danger' : 'general' in errors }" >
								<strong>@{{ errors['general'][0] }}</strong>
							</span>
            </td>
						<td align='right'>@{{ modal.account }}<br>(@{{ modal.currency }})</td>
						<td>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</td>
					</tr>
				</table>
			</div>

			<div style="height: 70vh; overflow-y: scroll;" class="modal-body">
				<form class="form-horizontal" role="form" method="POST" action="">

					<input type="hidden" name="_token" v-model="modal.csrf">

					<table id="trxtable" class="table table-striped table-hovered" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th>{{ trans('finance.Date') }}</th>
								<th>{{ trans('finance.Source') }}</th>
								<th style="text-align:right;">{{ trans('finance.Debit') }}</th>
								<th style="text-align:right;">{{ trans('finance.Credit') }}</th>
								<th style="text-align:right;">{{ trans('forms.Reconcile') }}</th>
							</tr>
						</thead>
            <tfoot>
              <tr>
                <th>{{ trans('finance.Date') }}</th>
                <th>{{ trans('finance.Source') }}</th>
                <th style="text-align:right;">{{ trans('finance.Debit') }}</th>
                <th style="text-align:right;">{{ trans('finance.Credit') }}</th>
                <th style="text-align:right;">{{ trans('forms.Reconcile') }}</th>
              </tr>
            </tfoot>
						<tbody>
								<tr v-for="(trxid, index) in form.line">
									<td>@{{ form.date[index] }}</td>
									<td>@{{ form.source[index] }}</td>
									<td style="text-align:right;" v-bind:title="form.debit_title[index]">@{{ form.debit[index] }}</td>
									<td style="text-align:right;" v-bind:title="form.credit_title[index]">@{{ form.credit[index] }}</td>
									<td style="text-align:right;">
                    <input type="hidden" id="line[]" name="line[]" v-model="form.line[index]" />
                    <input type="hidden" id="reconciliation[]" name="reconciliation[]" v-model="form.reconciled[index]" />
                    <input type="checkbox" style="width:22px;height:22px;border:0px;margin:0px;" id="_reconciliation[]" name="_reconciliation[]" v-model="form.reconciled[index]" v-on:change="adjustTaccountReconciliationBalance()" />
                  </td>
								</tr>
						</tbody>
					</table>
				</form>
        <a href='#' class="show-balance-hint" style="position:fixed;bottom:3px;left:3px;text-decoration:none;color:#000000;background-color:rgba(255, 255, 255, 0.0);font-size:15px;padding:1em;z-index:10;">
          <img src="{{ asset('images/sum-512.png') }}" height='40' width='40'></img>
        </a>
        <div class="show-balance" style="position:fixed;bottom:3px;left:3px;text-decoration:none;color:#000000;background-color:rgba(255, 255, 255, 1.0);font-size:15px;padding:1em;display:none;z-index:10;">
          <p style="font-size:38px;font-weight:bold;line-height:38px;" >{{ trans('forms.Balance') }} @{{ modal.display_value }}</p>
        </div>
			</div>

      <div class="modal-footer">
				<div class="form-group">
					<div class="col-md-12">
						<button v-for="(callback, display) in modal.action" type="button" class="btn btn-primary pull-right" style="margin-left:3px;" v-on:click="window[callback]()" v-bind:disabled="!form.reconciled.some(o => o == true)" v-html="display">
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>