<!-- modal to enter dividend/interst income to bank account -->
<div class="modal fade" id="embeddedDividendIncomeModal" tabindex="-1" role="dialog" aria-labelledby="embeddedDividendIncomeModalLabel" data-backdrop="static" aria-hidden="false">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <font size="4" style="padding-right:30px;">{{ trans('finance.Record interest/dividend') }}</font>
        <span v-if="'general' in errors" v-bind:class="{ 'text-danger' : 'general' in errors }" >
          <strong>@{{ errors['general'][0] }}</strong>
        </span>
      </div>
      <div class="modal-body">

        <form class="form-horizontal" role="form" method="POST" action="#" >

          <input type="hidden" name="_token" v-model="modal.csrf">

          <div class="form-group">
            <label class="col-xs-3 col-sm-3 col-md-3 control-label">{{ trans('forms.Date') }}</label>
            <div class="col-xs-5 col-sm-5 col-md-5" v-bind:class="{ 'has-error' : 'date' in errors }">
              <div id="dividend_date_group" class="input-group date" data-provide="datepicker">
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
            <label class="col-xs-3 col-sm-3 col-md-3 control-label">{{ trans('forms.Revenue account') }}</label>
            <div class="col-xs-7 col-sm-7 col-md-7" v-bind:class="{ 'has-error' : 'revenue' in errors  }">
              <select id="revenue" name="revenue" class="form-control" v-model="form.revenue">
                <option value="">{{ trans('forms.Select an account') }}</option>
                <option v-for="(account, index) in modal.revenue" v-bind:value="index">@{{ account.account }} @{{ account.description }}</option>
              </select>
              <span class="help-block" v-if="'revenue' in errors">
                <strong>@{{ errors['revenue'][0] }}</strong>
              </span>
            </div>
          </div>

          <div class="form-group">
            <label class="col-xs-3 col-sm-3 col-md-3 control-label">{{ trans('forms.Bank account') }}</label>
            <div class="col-xs-5 col-sm-5 col-md-5" v-bind:class="{ 'has-error' : 'bank' in errors }">
              <select id="bank" name="bank" class="form-control" v-model="form.bank">
                <option value="">{{ trans('forms.Select an account') }}</option>
                <option v-for="(account, index) in modal.bank" v-bind:value="index">@{{ account.description }}</option>
              </select>
              <span class="help-block" v-if="'bank' in errors">
                <strong>@{{ errors['bank'][0] }}</strong>
              </span>
            </div>
          </div>

          <div class="form-group">
            <label class="col-xs-3 col-sm-3 col-md-3 control-label">{{ trans('finance.Amount') }}</label>
            <div class="col-xs-5 col-sm-5 col-md-5" v-bind:class="{ 'has-error' : 'amount' in errors }">
              <input type="number" id="amount" name="amount" class="form-control" min="0" v-bind:step="(form.bank == '') ? 1 : modal.currency[modal.bank[form.bank].currency_id].min" v-model="form.amount" style="text-align:right;" >
              <span class="help-block" v-if="'amount' in errors">
                <strong>@{{ errors['amount'][0] }}</strong>
              </span>
            </div>
          </div>

          <div class="form-group">
            <label class="col-xs-3 col-sm-3 col-md-3 control-label">{{ trans('forms.Notes') }}</label>
            <div class="col-xs-7 col-sm-7 col-md-7" v-bind:class="{ 'has-error' : 'notes' in errors }">
              <textarea id="notes" name="notes" class="form-control" v-model="form.notes" ></textarea>
              <span class="help-block" v-if="'notes' in errors">
                <strong>@{{ errors['notes'][0] }}</strong>
              </span>
            </div>
          </div>
        </form>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" onclick="submitDividendIncomePost();" >{{ trans('forms.Submit') }}</button>
      </div>
    </div>
  </div>
</div>