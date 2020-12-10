
<div class="modal fade" id="embeddedTradableAnalysisModal" tabindex="-1" role="dialog" aria-labelledby="embeddedTradableAnalysisModalLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">

  <div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<table width="100%">
					<tr>
						<td>
							<font size="4" style="padding-right:30px;">@{{ modal.title }}&emsp;(@{{ form.sku }})</font>
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
				<div style="margin-top:20px;">
					<div class="alert alert-warning" role="alert">
						<strong>Analysis tool is to be designed/implemented.</strong>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>