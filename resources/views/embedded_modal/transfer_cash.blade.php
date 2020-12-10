<!-- modeal to transfer cash between bank account -->
<div class="modal fade" id="embeddedTransferCashModal" tabindex="-1" role="dialog" aria-labelledby="embeddedTransferCashModalLabel" data-backdrop="static" aria-hidden="false">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <font size="4" style="padding-right:30px;">{{ trans('finance.Transfer cash') }}</font>
        <span v-if="'general' in errors" class="help-block" >
          <strong>@{{ errors['general'][0] }}</strong>
        </span>
      </div>
      <div class="modal-body">

        <form class="form-horizontal" role="form" method="POST" action="" >

          <input type="hidden" name="_token" v-model="modal.csrf">

          <div class="form-group">
            <label class="col-xs-2 col-sm-2 col-md-2 control-label">{{ trans('forms.From') }}</label>
            <div class="col-xs-5 col-sm-5 col-md-5" v-bind:class="{ 'has-error' : 'from' in errors }">
              <select id="from" name="from" class="form-control" v-model="form.source" >
                <option value="">{{ trans('forms.Select an account') }}</option>
                <option v-for="(account, id) in modal.account" v-bind:value="id">@{{ account.description }}</option>
              </select>
              <span class="help-block" v-if="'from' in errors">
                <strong>@{{ errors['from'][0] }}</strong>
              </span>
            </div>
          </div>

          <div class="form-group">
            <label class="col-xs-2 col-sm-2 col-md-2 control-label">{{ trans('forms.To') }}</label>
            <div class="col-xs-5 col-sm-5 col-md-5" v-bind:class="{ 'has-error' : 'to' in errors }">
              <select id="to" name="to" class="form-control" v-model="form.target">
                <option value="">{{ trans('forms.Select an account') }}</option>
                <option v-for="(account, id) in modal.account" v-bind:value="id">@{{ account.description }}</option>
              </select>
              <span class="help-block" v-if="'to' in errors">
                <strong>@{{ errors['to'][0] }}</strong>
              </span>
            </div>
          </div>

          <div class="form-group">
            <label class="col-xs-2 col-sm-2 col-md-2 control-label">{{ trans('forms.Date') }}</label>
            <div class="col-xs-5 col-sm-5 col-md-5" v-bind:class="{ 'has-error' : 'date' in errors }">
              <div id="transfer_date_group" class="input-group date" data-provide="datepicker">
                <input id="date" type="text" class="form-control" name="date" v-model="form.date" >
                <div class="input-group-addon">
                  <span class="glyphicon glyphicon-calendar"></span>
                </div>
              </div>
              <span class="help-block" v-if="'date' in errors">
                <strong>@{{ errors['date'][0] }}</strong>
              </span>
            </div>
          </div>

          <div class="form-group">
            <label class="col-xs-2 col-sm-2 col-md-2 control-label">{{ trans('finance.Amount') }}</label>
            <div class="col-xs-5 col-sm-5 col-md-5" v-bind:class="{ 'has-error' : 'amount' in errors }">
              <input type="number" id="amount" name="amount" class="form-control" min="0" v-bind:step="(form.source == '') ? 1 : modal.currency[modal.account[form.source].currency_id].min" v-model="form.amount" style="text-align:right;" >
              <span class="help-block" v-if="'amount' in errors">
                <strong>@{{ errors['amount'][0] }}</strong>
              </span>
            </div>
          </div>

        </form>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" onclick="transferCashPost();" >
          <i class="fa fa-btn fa-file-text"></i>{{ trans('forms.Submit') }}
        </button>
      </div>
    </div>
  </div>
</div>