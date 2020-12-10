<!-- modal to enter loan interest (revenue or expense) -->
<div class="modal fade" id="embeddedLoanInterestModal" tabindex="-1" role="dialog" aria-labelledby="embeddedLoanInterestModalLabel" data-backdrop="static" aria-hidden="false">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <font size="4" style="padding-right:30px;">{{ trans('finance.Record interest') }}</font>
        <span v-if="'general' in errors" v-bind:class="{ 'text-danger' : 'general' in errors }" >
          <strong>@{{ errors['general'][0] }}</strong>
        </span>
      </div>
      <div class="modal-body">

        <form class="form-horizontal" role="form" method="POST" action="" >

          <input type="hidden" name="_token" v-model="modal.csrf">

          <input type="hidden" id="loan_id" v-model="form.id" />

          <div class="form-group">
            <label class="col-xs-3 col-sm-3 col-md-3 control-label">{{ trans('forms.Date') }}</label>
            <div class="col-xs-5 col-sm-5 col-md-5" v-bind:class="{ 'has-error' : 'date' in errors }">
              <div id="interest_date_group" class="input-group date" data-provide="datepicker">
                <input id="date" type="text" class="form-control" name="date" v-bind:value="form.date" >
                <div class="input-group-addon">
                  <span class="glyphicon glyphicon-calendar"></span>
                </div>
              </div>
              <span v-if="'date' in errors" class="help-block" >
                <strong>@{{ errors['date'][0] }}</strong>
              </span>
            </div>
          </div>

          <div class="form-group">
            <label v-if="modal.is_revenue" class="col-xs-3 col-sm-3 col-md-3 control-label revenue">{{ trans('forms.Revenue account') }}</label>
            <label v-if="!modal.is_revenue" class="col-xs-3 col-sm-3 col-md-3 control-label expense">{{ trans('forms.Expense account') }}</label>
            <div class="col-xs-7 col-sm-7 col-md-7" v-bind:class="{ 'has-error' : 'account' in errors }">
              <select id="account" name="account" class="form-control" v-model="form.account">
                <option value="">{{ trans('forms.Select an account') }}</option>
                <option v-if="modal.is_revenue" v-for="(account, index) in modal.revenue" v-bind:value="index" >@{{ account.account }} @{{ account.description }}</option>
                <option v-if="!modal.is_revenue" v-for="(account, index) in modal.expense" v-bind:value="index" >@{{ account.account }} @{{ account.description }}</option>
              </select>
              <span v-if="'account' in errors" class="help-block" >
                <strong>@{{ errors['account'][0] }}</strong>
              </span>
            </div>
          </div>

          <div class="form-group">
            <label class="col-xs-3 col-sm-3 col-md-3 control-label">{{ trans('finance.Amount') }}</label>
            <div class="col-xs-5 col-sm-5 col-md-5" v-bind:class="{ 'has-error' : 'amount' in errors }">
              <input type="number" id="amount" name="amount" class="form-control" min="0" v-bind:step="modal.currency_min[form.id]" v-model="form.amount" style="text-align:right;" >
              <span v-if="'amount' in errors" class="help-block" >
                <strong>@{{ errors['amount'][0] }}</strong>
              </span>
            </div>
          </div>

        </form>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" onclick="submitLoanInterestPost();" >{{ trans('forms.Submit') }}</button>
      </div>
    </div>
  </div>
</div>