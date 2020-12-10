

<div class="modal fade" id="embeddedTaccountTransactionModal" tabindex="-1" role="dialog" aria-labelledby="embeddedTaccountTransactionLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">

	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<table width='100%'>
					<tr>
						<td><font size="4" style="padding-right:30px;">{{ trans('finance.Account transaction') }}</font></td>
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
				<div class="panel-body">
					<table id="trxtable" class="table table-striped table-hovered" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th>{{ trans('finance.Date') }}</th>
								<th>{{ trans('finance.Source') }}</th>
								<th style="text-align:right;">{{ trans('finance.Debit') }}</th>
								<th style="text-align:right;">{{ trans('finance.Credit') }}</th>
								<th style="text-align:right;">{{ trans('finance.Balance') }}</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>{{ trans('finance.Date') }}</th>
								<th>{{ trans('finance.Source') }}</th>
								<th style="text-align:right;">{{ trans('finance.Debit') }}</th>
								<th style="text-align:right;">{{ trans('finance.Credit') }}</th>
								<th style="text-align:right;">{{ trans('finance.Balance') }}</th>
							</tr>
						</tfoot>
						<tbody>
							<tr v-for="content in modal.transaction">
								<td>@{{ content.date }}</td>
								<td v-bind:title="content.notes">@{{ content.source }}</td>
								<td style="text-align:right;" v-bind:title="content.debit_title">@{{ content.debit }}</td>
								<td style="text-align:right;" v-bind:title="content.credit_title">@{{ content.credit }}</td>
								<td style="text-align:right;">@{{ content.balance }}</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>