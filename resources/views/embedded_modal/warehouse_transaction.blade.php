<div class="modal fade" id="embeddedWarehouseTransactionModal" tabindex="-1" role="dialog" aria-labelledby="embeddedWarehouseTransactionModalLabel" data-backdrop="static" aria-hidden="false">

  <!-- the form -->
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <font size="4" style="padding-right:30px;">{{ trans('warehouse.Transaction') }}</font>
      </div>
      <div style="height: 80vh; overflow-y: scroll;" class="modal-body">

        <form class="form-horizontal" role="form" enctype="multipart/form-data" method="POST" action="#">

          <div class="form-group">
            <div class="col-md-3" v-bind:class="{ 'has-error' : 'location1' in errors }">
              {{ trans('forms.Location') }}
              <select id="location1" class="form-control" name="location1" v-model="transaction.location">
              @foreach (\App\Location::getActiveWarehouses('name', 'asc') as $oneLocation)
                <option value="{{ $oneLocation->id }}" >{{ $oneLocation->name }}</option>
              @endforeach
              </select>
              <span v-if="'location1' in errors" class="help-block">
                <strong>@{{ errors['location'][0] }}</strong>
              </span>
            </div>

            <div class="col-md-3" v-bind:class="{ 'has-error' : 'product1' in errors }">
              {{ trans('forms.SKU') }}
              <select id="product1" class="form-control" name="product1" v-model="transaction.product">
              @foreach (\App\UniqueTradable::getActiveProducts('sku', 'asc') as $oneSku)
                <option value="{{ $oneSku->id }}" >{{ $oneSku->sku }}</option>
              @endforeach
              </select>
              <span v-if="'product1' in errors" class="help-block">
                <strong>@{{ errors['product1'][0] }}</strong>
              </span>
            </div>

            <div class="col-md-3">
              <label for="stats_update" class="col-md-12">&nbsp;</label>
              <a id="stats_update" class="btn btn-primary"><i class="icon-refresh icon-white"></i>&nbsp;{{ trans('forms.Update') }}</a>
            </div>
          </div>
        </form>

        <table id="trxtable" class="table table-striped" cellspacing="0" width="100%">
          <thead>
            <tr>
              <th>{{ trans('forms.Date') }}</th>
              <th>{{ trans('forms.Source') }}</th>
              <th style="text-align:right;">{{ trans('forms.Quantity') }}</th>
              <th style="text-align:right;">{{ trans('warehouse.Balance') }}</th>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <th>{{ trans('forms.Date') }}</th>
              <th>{{ trans('forms.Source') }}</th>
              <th style="text-align:right;">{{ trans('forms.Quantity') }}</th>
              <th style="text-align:right;">{{ trans('warehouse.Balance') }}</th>
            </tr>
          </tfoot>
          <tbody>
          </tbody>
        </table>

      </div>
    </div>
  </div>
</div>