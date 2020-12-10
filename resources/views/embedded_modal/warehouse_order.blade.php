@php
  $quantity_formatter = \App\Helpers\QuantityHelper::getHtmlAttribute();
@endphp
<div class="modal fade" id="embeddedWarehouseOrderModal" tabindex="-1" role="dialog" aria-labelledby="embeddedWarehouseOrderModalLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">

  <!-- add product modal -->
  <div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-labelledby="addProductModalLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">
    <div class="modal-dialog modal-sm" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" aria-label="Close" onclick="postCapture()" >
            <span aria-hidden="false">&times;</span>
          </button>
          <h4 class="modal-title" id="addProductModalLabel">{{ trans("warehouse.Please enter SKU or UPC") }}</h4>
        </div>
        <div class="modal-body">
          <input id="product_input" name="product_input" type="text" class="form-control" onkeypress="if(event.keyCode==13){document.getElementById('addProductModalSubmit').click();}" value="" ></input>
        </div>
        <div class="modal-footer">
          <button type="button" id="addProductModalSubmit" class="btn btn-primary" onclick="processCapture();" >{{ trans('forms.Enter') }}</button>
          <button type="button" class="btn btn-secondary" aria-label="Close" onclick="postCapture()" >{{ trans('forms.Cancel') }}</button>
        </div>
      </div>
    </div>
  </div>

  <!-- split product modal -->
  <div class="modal fade" id="splitProductModal" tabindex="-1" role="dialog" aria-labelledby="splitProductModalLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" aria-label="Close" onclick="postSplit()" >
            <span aria-hidden="false">&times;</span>
          </button>
          <h4 class="modal-title" id="splitProductModalLabel">{{ trans("warehouse.Please enter quantity to split") }}</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-4 col-md-offset-1">
              {{ trans('warehouse.Original quantity') }}
              <input id="split_source" name="split_source" type="number" style="text-align:right;" class="form-control" data-index="" data-prev="" value="" ></input>
            </div>

            <div class="col-md-4 col-md-offset-1">
              {{ trans('warehouse.Split quantity') }}
              <input id="split_target" name="split_target" type="number" style="text-align:right;" class="form-control" data-prev="" value="" ></input>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" onclick="processSplit();" >{{ trans('forms.Enter') }}</button>
          <button type="button" class="btn btn-secondary" aria-label="Close" onclick="postSplit()" >{{ trans('forms.Cancel') }}</button>
        </div>
      </div>
    </div>
  </div>

  <!-- the form -->
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <font size="4" style="padding-right:30px;">@{{ modal.title }}</font>
        <span v-if="'general' in errors" v-bind:class="{ 'text-danger' : 'general' in errors }" >
          <strong>@{{ errors['general'][0] }}</strong>
        </span>
      </div>
      <div style="height: 70vh; overflow-y: scroll;" class="modal-body">

        <form id="warehouse_form" class="form-horizontal" role="form" method="POST" >

          <input type="hidden" name="_token" v-model="modal.csrf">

          <input type="hidden" id="id" name="id" v-model="order.id" />

          <input type="hidden" id="type" name="type" v-model="order.type" />

          <div class="form-group">

            <label for="increment" class="col-md-2 control-label">{{ trans('forms.Document') }}</label>
            <div class="col-md-3" v-bind:class="{ 'has-error' : 'increment' in errors }">
              <input id="increment" type="text" class="form-control" name="increment" v-model="order.increment" readonly disabled>
              <span v-if="'increment' in errors" class="help-block">
                <strong>@{{ errors['increment'][0] }}</strong>
              </span>
            </div>

            <label for="reference" class="col-md-2 control-label">{{ trans('forms.Reference') }}</label>
            <div class="col-md-4" v-bind:class="{ 'has-error' : 'reference' in errors }">
              <input id="reference" type="text" class="form-control" v-bind:disabled="modal.readonly" v-bind:readonly="modal.readonly" name="reference" v-model="order.reference" >
              <span v-if="'reference' in errors" class="help-block">
                <strong>@{{ errors['reference'][0] }}</strong>
              </span>
            </div>
          </div>

          <div class="form-group">

            <label for="process_date" class="col-md-2 control-label">{{ trans('forms.Date') }}</label>
            <div class="col-md-3" v-bind:class="{ 'has-error' : 'process_date' in errors }">
              <div class="input-group date" >
                <input id="process_date" type="text" class="form-control" v-bind:class="{ 'datepicker' : !modal.readonly }" v-bind:disabled="modal.readonly" v-bind:readonly="modal.readonly" name="process_date" v-model="order.process_date" >
                <div class="input-group-addon">
                  <span class="glyphicon glyphicon-calendar"></span>
                </div>
              </div>
              <span v-if="'process_date' in errors" class="help-block">
                <strong>@{{ errors['process_date'][0] }}</strong>
              </span>
            </div>

            <label for="staff" class="col-md-2 control-label">{{ trans('forms.Staff') }}</label>
            <div class="col-md-4" v-bind:class="{ 'has-error' : 'staff' in errors }">
              <input id="staff" type="text" class="form-control" name="staff" v-model="order.staff" readonly disabled>
              <span v-if="'staff' in errors" class="help-block">
                <strong>@{{ errors['staff'][0] }}</strong>
              </span>
            </div>
          </div>

          <div class="form-group">

            <label for="via" class="col-md-2 control-label">{{ trans('forms.Via') }}</label>
            <div class="col-md-3" v-bind:class="{ 'has-error' : 'via' in errors }">
              <input id="via" type="text" class="form-control" v-bind:disabled="modal.readonly" v-bind:readonly="modal.readonly" name="via" v-model="order.via" >
              <span v-if="'via' in errors" class="help-block">
                <strong>@{{ errors['via'][0] }}</strong>
              </span>
            </div>

            <label for="location" class="col-md-2 control-label">{{ trans('forms.Warehouse') }}</label>
            <div class="col-md-4" v-bind:class="{ 'has-error' : 'location' in errors }">
              <select id="location" class="form-control" v-bind:disabled="modal.readonly" name="location" v-model="order.location" v-on:change="binLocationRefresh()" >
              @foreach (\App\Location::getActiveWarehouses('name', 'asc') as $oneLocation)
                <option value="{{ $oneLocation->id }}" >{{ $oneLocation->name }}</option>
              @endforeach
              </select>
              <span v-if="'location' in errors" class="help-block">
                <strong>@{{ errors['location'][0] }}</strong>
              </span>
            </div>
          </div>

          <div class="form-group">

            <label for="entity" class="col-md-2 control-label">{{ trans('forms.Entity') }}</label>
            <div class="col-md-3" v-bind:class="{ 'has-error' : 'entity' in errors }">
              <select id="entity" class="form-control" name="entity" v-model="order.entity" v-bind:disabled="modal.readonly" v-on:change="order.address = modal.addresses[order.entity][Object. keys(modal.addresses[order.entity])[0]].id">
              @foreach (\App\TaxableEntity::getExternalEntities('code', 'asc') as $entity)
                <option value="{{ $entity->id }}" >{{ $entity->code }}&emsp;({{ $entity->name }})</option>
              @endforeach
              </select>
              <span v-if="'entity' in errors" class="help-block">
                <strong>@{{ errors['entity'][0] }}</strong>
              </span>
            </div>

            <label for="address" class="col-md-2 control-label">{{ trans('forms.External address') }}</label>
            <div class="col-md-4" v-bind:class="{ 'has-error' : 'address' in errors }">
              <!-- multiline select-option -->
              <div class="btn-group bootstrap-select form-control" style="height:100%;" v-bind:disabled="modal.readonly">
                <input id="address" name="address" type="hidden" v-model="order.address">
                <!-- pointer-events:none prevent change background color when 'hover' -->
                <button type="button" class="btn dropdown-toggle selectpicker btn-default" data-toggle="dropdown" data-id="address" aria-expanded="false" v-bind:style="{ 'pointer-events' : modal.readonly ? 'none' : '', 'color' : modal.readonly ? '#000' : '#555', 'background-color' : modal.readonly ? '#eee' : '#fff' }" v-bind:disabled="modal.readonly">
                  <span id="address_display" class="filter-option pull-left">@{{ address_display.name }}<br>@{{ address_display.street }} @{{ address_display.unit }}<br>@{{ address_display.city }} @{{ address_display.district }}<br>@{{ address_display.state }}<br>@{{ address_display.country }} @{{ address_display.zipcode }}<br>
                  </span>
                  <span class="caret">
                  </span>
                </button>
                <div class="dropdown-menu open" style="overflow: hidden; min-height: 0px;">
                  <ul class="dropdown-menu inner selectpicker" role="menu" style="overflow-y: auto; min-height: 0px;">
                    <li v-for="(address, index) in modal.addresses[order.entity]" v-bind:rel="index">
                      <a tabindex="0" class="" style="" v-on:click="order.address=address.id">@{{ address.name }}<br>@{{ address.street }} @{{ address.unit }}<br>@{{ address.city }}<br>@{{ address.state }}<br>@{{ address.country }} @{{ address.zipcode }}<br>
                        <i class="glyphicon glyphicon-ok icon-ok check-mark"></i>
                      </a>
                    </li>
                  </ul>
                </div>
              </div>

              <span v-if="'address' in errors" class="help-block">
                <strong>@{{ errors['address'][0] }}</strong>
              </span>
            </div>
          </div>

          <hr />

          <div v-for = "(product, index) in order.product_id" class="form-group detail-line">
            <input id="product_id[]" type="hidden" v-model="product" class="form-control col-md-2" name="product_id[]" ></input>
            <div class="col-md-3 col-md-offset-1" v-bind:class="{ 'has-error' : 'sku.'+index in errors }">
              {{ trans('forms.Item') }}
              <input id="sku[]" type="text" v-model="order.sku[index]" class="form-control" name="sku[]" readonly disabled>
              <span v-if="'sku.'+index in errors" class="help-block">
                <strong>@{{ errors['sku.'+index][0] }}</strong>
              </span>
            </div>

            <div class="col-md-5" v-bind:class="{ 'has-error' : 'description.'+index in errors }">
              {{ trans('forms.Description') }}
              <input id="description[]" type="hidden" v-model="order.description[index]" class="form-control" name="description[]" readonly>
              <input id="_description[]" type="text" v-model="order.description[index]" class="form-control" name="description[]" readonly disabled>
              <span v-if="'description.'+index in errors" class="help-block">
                <strong>@{{ errors['description.'+index][0] }}</strong>
              </span>
            </div>

            <div v-if="!modal.readonly" class="col-md-1 col-md-offset-1">
              <br>
              <a class="btn btn-danger" title="{{ trans('forms.Delete') }}" v-on:click="deleteRow(index);">&times;</a>
            </div>

            <div class="clearfix"></div>

            <div class="col-md-1 col-md-offset-6">
              <br>
              <a class="btn btn-info pull-right" v-bind:class="{ 'hidden' : modal.readonly || (order.quantity[index] < 2) }" title="{{ trans('forms.Split') }}" v-on:click="prepareSplit(index);"><span class="fa fa-expand"></span></a>
            </div>

            <div class="col-md-2" v-bind:class="{ 'has-error' : 'quantity.'+index in errors }">
              {{ trans('forms.Processed') }}
              <input id="quantity[]" type="number" v-model="order.quantity[index]" style="text-align:right;" min="{{ $quantity_formatter['zero'] }}" step="{{ $quantity_formatter['step'] }}" class="form-control" v-bind:disabled="modal.readonly" v-bind:readonly="modal.readonly" name="quantity[]" >
              <span v-if="'quantity.'+index in errors" class="help-block">
                <strong>@{{ errors['quantity.'+index][0] }}</strong>
              </span>
            </div>

            <div class="col-md-2" v-bind:class="{ 'has-error' : 'bin.'+index in errors }">
              {{ trans('forms.Bin') }}
              <select id="bin[]" style="text-align:right;" class="form-control" name="bin[]" v-model="order.bin[index]" v-bind:disabled="modal.readonly" >
                <option v-for="(bin, bindex) in order.bins[index]" v-bind:value="bindex" >@{{ bin }}</option>
              </select>
              <span v-if="'bin.'+index in errors" class="help-block">
                <strong>@{{ errors['bin.'+index][0] }}</strong>
              </span>
            </div>
          </div>

        </form>
      </div>
      <div v-if="!modal.readonly || (Object.keys(modal.action).length > 0)" class="modal-footer">
        <div class="form-group">
          <div class="col-md-2">
            <button v-if="!modal.readonly" type="button" class="btn btn-primary pull-right" onclick="$('#addProductModal').modal('show');" >+&nbsp;{{ trans("forms.Add") }}</button>
          </div>
          <div class="col-md-2 col-md-offset-7">
            <button v-for="(callback, display) in modal.action" type="button" class="btn btn-primary action" v-bind:style="{ 'pointer-events' : ((modal.detail_required) && (order.product_id.length < 1)) ? 'none' : '' }" v-bind:class="{ 'disabled' : ((modal.detail_required) && (order.product_id.length < 1)) }" v-on:click="window[callback]();" v-html="display">
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>