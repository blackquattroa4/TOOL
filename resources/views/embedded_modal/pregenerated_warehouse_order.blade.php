@php
  $quantity_formatter = \App\Helpers\QuantityHelper::getHtmlAttribute();
@endphp
<div class="modal fade" id="embeddedWarehouseOrderModal" tabindex="-1" role="dialog" aria-labelledby="embeddedWarehouseOrderModalLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">

  <!-- the form -->
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <font size="4" style="padding-right:30px;" >@{{ modal.title }}</font>
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
              <input id="reference" type="text" class="form-control" name="reference" v-model="order.reference" readonly disabled>
              <span v-if="'reference' in errors" class="help-block">
                <strong>@{{ errors['reference'][0] }}</strong>
              </span>
            </div>
          </div>

          <div class="form-group">

            <label for="process_date" class="col-md-2 control-label">{{ trans('forms.Date') }}</label>
            <div class="col-md-3" v-bind:class="{ 'has-error' : 'process_date' in errors }">
              <div class="input-group date" >
                <input id="process_date" type="text" class="form-control" v-bind:class="{ 'datepicker' : !modal.readonly }" name="process_date" v-model="order.process_date" readonly disabled>
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
              <select id="staff" class="form-control" name="staff" v-model="order.staff" readonly disabled>
                <option v-for="(display, index) in modal.staff" v-bind:value="index">@{{ display }}</option>
              </select>
              <span v-if="'staff' in errors" class="help-block">
                <strong>@{{ errors['staff'][0] }}</strong>
              </span>
            </div>
          </div>

          <div class="form-group">

            <label for="via" class="col-md-2 control-label">{{ trans('forms.Via') }}</label>
            <div class="col-md-3" v-bind:class="{ 'has-error' : 'via' in errors }">
              <input id="via" type="text" class="form-control" name="via" v-model="order.via" readonly disabled>
              <span v-if="'via' in errors" class="help-block">
                <strong>@{{ errors['via'][0] }}</strong>
              </span>
            </div>

            <label for="location" class="col-md-2 control-label">{{ trans('forms.Warehouse') }}</label>
            <div class="col-md-4" v-bind:class="{ 'has-error' : 'location' in errors }">
              <select id="location" class="form-control" name="location" v-model="order.location" v-on:change="binLocationRefresh()" disabled>
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
              <select id="entity" class="form-control" name="entity" v-model="order.entity" v-on:change="order.address = modal.addresses[Object. keys(modal.addresses)[0]].id" disabled>
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
                <button type="button" class="btn dropdown-toggle selectpicker btn-default" data-toggle="dropdown" data-id="address" aria-expanded="false" v-bind:style="{ 'pointer-events' : 'none', 'color' : '#000', 'background-color' : '#eee' }" disabled>
                  <span id="address_display" class="filter-option pull-left">@{{ address_display.name }}<br>@{{ address_display.street }} @{{ address_display.unit }}<br>@{{ address_display.city }} @{{ address_display.district }}<br>@{{ address_display.state }}<br>@{{ address_display.country }} @{{ address_display.zipcode }}<br>
                  </span>
                  <span class="caret">
                  </span>
                </button>
                <div class="dropdown-menu open" style="overflow: hidden; min-height: 0px;">
                  <ul class="dropdown-menu inner selectpicker" role="menu" style="overflow-y: auto; min-height: 0px;">
                    <li v-for="(address, index) in modal.address" v-bind:rel="index">
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

          <div v-for = "(id, index) in order.line" class="form-group detail-line">

            <input id="line[]" type="hidden" v-model="id" class="form-control col-md-2" name="line[]" ></input>

            <div class="col-md-3 col-md-offset-1" v-bind:class="{ 'has-error' : 'product.'+index in errors }">
              {{ trans('forms.Item') }}
              <select id="product[]" v-model="order.product[index]" class="form-control" name="product[]" readonly disabled>
                <option v-for="(display, index) in modal.product" v-bind:value="index">@{{ display.sku }}</option>
              </select>
              <span v-if="'product.'+index in errors" class="help-block">
                <strong>@{{ errors['product.'+index][0] }}</strong>
              </span>
            </div>

            <div class="col-md-5" v-bind:class="{ 'has-error' : 'description.'+index in errors }">
              {{ trans('forms.Description') }}
              <input id="description[]" type="text" v-model="order.description[index]" class="form-control" name="description[]" readonly disabled>
              <span v-if="'description.'+index in errors" class="help-block">
                <strong>@{{ errors['description.'+index][0] }}</strong>
              </span>
            </div>

            <div class="col-md-2 col-md-offset-1" v-bind:class="{ 'has-error' : 'quantity.'+index in errors }">
              {{ trans('forms.Quantity') }}
              <input id="quantity[]" type="number" v-model="order.quantity[index]" style="text-align:right;" min="{{ $quantity_formatter['zero'] }}" step="{{ $quantity_formatter['step'] }}" class="form-control" name="quantity[]" readonly disabled>
              <span v-if="'quantity.'+index in errors" class="help-block">
                <strong>@{{ errors['quantity.'+index][0] }}</strong>
              </span>
            </div>

            <div v-if="!modal.readonly" class="col-md-2 col-md-offset-10" v-bind:class="{ 'has-error' : 'processing.'+index in errors }">
              {{ trans('forms.Processed') }}
              <input id="processing[]" type="number" v-model="order.processing[index]" style="text-align:right;" min="{{ $quantity_formatter['zero'] }}" step="{{ $quantity_formatter['step'] }}" v-bind:max="order.quantity[index]" class="form-control" name="processing[]" />
              <span v-if="'processing.'+index in errors" class="help-block">
                <strong>@{{ errors['processing.'+index][0] }}</strong>
              </span>
            </div>

          </div>

        </form>

      </div>
      <div v-if="!modal.readonly || (Object.keys(modal.action).length > 0)" class="modal-footer">
        <div class="form-group">
          <div class="col-md-12">
            <button v-for="(callback, display) in modal.action" type="button" class="btn btn-primary action pull-right" v-on:click="window[callback]();" v-bind:disabled="!modal.readonly && order.processing.every(x => x == 0)" v-html="display">
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>