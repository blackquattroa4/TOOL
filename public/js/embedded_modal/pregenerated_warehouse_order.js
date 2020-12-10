// use in conjunction with ../embedded_modal/pregenerated_warehouse_order.blade.php

var vueWarehouseOrderModal = null;

function loadWarehouseOrderWithAjax(order_id) {
  // use ajax to load warehouse order #id
  return $.ajax({
    type: 'GET',
    url: '/warehouse-order/pregenerated/' + order_id + '/ajax',
    data: {
      },
    dataType: 'html',
    beforeSend: function(data) {
      $('.ajax-processing').removeClass('hidden');
    },
  }).always(function (data) {
    $('.ajax-processing').addClass('hidden');
  }).fail(function (data) {
    if ('errors' in result) {
      vueWarehouseOrderModal.errors = result['errors'];
    } else {
      vueWarehouseOrderModal.errors = { general : "System failure" };
    }
  });
}

function populateWarehouseOrderModalWithAjaxResult(result) {
  vueWarehouseOrderModal.modal.readonly = result.readonly;
  vueWarehouseOrderModal.modal.title = result.title;
  vueWarehouseOrderModal.modal.csrf = result.csrf;
  vueWarehouseOrderModal.modal.action = result.action;
  vueWarehouseOrderModal.modal.post_url = result.post_url;
  vueWarehouseOrderModal.modal.address = result.addresses;
  vueWarehouseOrderModal.modal.staff = result.staffs;
  vueWarehouseOrderModal.modal.location = result.locations;
  vueWarehouseOrderModal.modal.entity = result.entities;
  vueWarehouseOrderModal.modal.product = result.products;
  vueWarehouseOrderModal.modal.history = result.history;
  vueWarehouseOrderModal.order.id = result.id;
  vueWarehouseOrderModal.order.increment = result.increment;
  vueWarehouseOrderModal.order.type = result.type;
  vueWarehouseOrderModal.order.status = result.status;
  vueWarehouseOrderModal.order.reference = result.reference;
  vueWarehouseOrderModal.order.process_date = result.process_date;
  vueWarehouseOrderModal.order.staff = result.staff;
  vueWarehouseOrderModal.order.via = result.via;
  vueWarehouseOrderModal.order.location = result.location;
  vueWarehouseOrderModal.order.entity = result.entity;
  vueWarehouseOrderModal.order.address = result.address;
  vueWarehouseOrderModal.order.notes = result.notes;
  vueWarehouseOrderModal.order.internal_notes = result.internal_notes;
  vueWarehouseOrderModal.order.line = result.line;
  vueWarehouseOrderModal.order.product = result.product;
  vueWarehouseOrderModal.order.description = result.description;
  vueWarehouseOrderModal.order.quantity = result.quantity;
  vueWarehouseOrderModal.order.processing = result.processing;
}

function viewWarehouseOrderInModal(order_id)
{
  let jqxhr = loadWarehouseOrderWithAjax(order_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      result['data']['readonly'] = true;
      // key can not be computed dynamically, create an empty object and stuff attribute
      result['data']['title'] = vueWarehouseOrderDataSource.text_view_warehouse_order + " #" + result['data']['increment'];
      result['data']['action'] = {};
      result['data']['action'][vueWarehouseOrderDataSource.button_pdf] = 'printWarehouseOrderPost';
      result['data']['post_url'] = '';
      result['data']['addresses'] = vueWarehouseOrderDataSource.selection_shipping[result['data']['entity']];
      result['data']['staffs'] = vueWarehouseOrderDataSource.selection_staff;
      result['data']['warehouses'] = vueWarehouseOrderDataSource.selection_warehouse;
      result['data']['entities'] = vueWarehouseOrderDataSource.selection_entity;
      result['data']['products'] = vueWarehouseOrderDataSource.selection_product;
      result['data']['processing'] = new Array(result['data']['line'].length).fill(0);
      populateWarehouseOrderModalWithAjaxResult(result['data']);
      vueWarehouseOrderModal.errors = [];

      Vue.nextTick(function () {
        // unbind date-picker
        $('#embeddedWarehouseOrderModal #process_date').datepicker('destroy');
        $('#embeddedWarehouseOrderModal #process_date').removeClass('hasDatepicker');
      });

      // show modal
      $('#embeddedWarehouseOrderModal').modal('show');
    }
  });
}

function printWarehouseOrderPost() {
  $.ajax({
    url: '/warehouse/vieworder/' + vueWarehouseOrderModal.order.id,
    data: {
      _token : vueWarehouseOrderModal.modal.csrf
    },
    method: 'POST',
    xhrFields: {
        responseType: 'blob'
    },
    success: function (data) {
      console.log(data);
      var a = document.createElement('a');
      var url = window.URL.createObjectURL(data);
      console.log(a);
      a.href = url;
      a.download = 'Warehouse ' + vueWarehouseOrderModal.order.type.charAt(0).toUpperCase() + vueWarehouseOrderModal.order.type.slice(1) + ' #' + vueWarehouseOrderModal.order.increment + '.pdf';
      document.body.append(a);
      a.click();
      a.remove();
      window.URL.revokeObjectURL(url);
    }
  });
}

function processWarehouseOrderInModal(order_id)
{
  let jqxhr = loadWarehouseOrderWithAjax(order_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      result['data']['readonly'] = false;
      // key can not be computed dynamically, create an empty object and stuff attribute
      result['data']['title'] = vueWarehouseOrderDataSource.text_process_warehouse_order + " #" + result['data']['increment'];
      result['data']['action'] = {};
      result['data']['action'][vueWarehouseOrderDataSource.button_update] = 'processWarehouseOrderPost';
      result['data']['post_url'] = '';
      result['data']['addresses'] = vueWarehouseOrderDataSource.selection_shipping[result['data']['entity']];
      result['data']['staffs'] = vueWarehouseOrderDataSource.selection_staff;
      result['data']['warehouses'] = vueWarehouseOrderDataSource.selection_warehouse;
      result['data']['entities'] = vueWarehouseOrderDataSource.selection_entity;
      result['data']['products'] = vueWarehouseOrderDataSource.selection_product;
      result['data']['processing'] = new Array(result['data']['line'].length).fill(0);
      populateWarehouseOrderModalWithAjaxResult(result['data']);
      vueWarehouseOrderModal.errors = [];

      Vue.nextTick(function () {
        // unbind date-picker
        $('#embeddedWarehouseOrderModal #process_date').datepicker('destroy');
        $('#embeddedWarehouseOrderModal #process_date').removeClass('hasDatepicker');
      });

      // show modal
      $('#embeddedWarehouseOrderModal').modal('show');
    }
  });
}

function processWarehouseOrderPost() {
  // finish this function
  $.ajax({
    type : 'POST',
    url : '/warehouse-order/pregenerated/process/' + vueWarehouseOrderModal.order.id + '/ajax',
    enctype : 'multipart/form-data',
    data : new FormData($('#embeddedWarehouseOrderModal form')[0]),
    processData : false,
    contentType : false,
    cache : false,
    //dataType: 'html',
    beforeSend: function(data) {
      $('.ajax-processing').removeClass('hidden');
    },
  }).done(function(data) {
    // data is already an object, no need to parse it.
    if (data['success']) {
      $('#embeddedWarehouseOrderModal').modal('hide');
      // update order table
      if ('updateCallback' in vueWarehouseOrderDataSource) {
        vueWarehouseOrderDataSource.updateCallback(data['data']['entry']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vueWarehouseOrderModal.errors = data['errors'];
    }
  });
}

$(document).ready(function() {

  vueWarehouseOrderModal = new Vue({
    el : '#embeddedWarehouseOrderModal',
    data : {
      modal : {
        readonly : true,
        title : '',
        csrf : '',
        action : '',
        post_url : '',
        address : [ ],
        staff : [ ],
        location : [ ],
        entity : [ ],
        product : [ ],
        history : [ ],
      },
      order : {
        id : 0,
        increment : '',
        type : '',
        status : '',
        reference : '',
        process_date : '',
        staff : 0,
        via : '',
        location : 0,
        entity : 0,
        address : 0,
        notes : '',
        internal_notes : '',
        line : [ ],
        product : [ ],
        description : [ ],
        quantity : [ ],
        processing : [ ],
      },
      errors : { }
    },
    computed : {
      address_display : function() {
        let address = this.modal.address.find(o => { return o.id == this.order.address; });
        return (address === undefined) ? { id : 0, street : "", unit : "", city : "", district : "", state : "", country : "", zipcode : "" } : address;
      }
    },
    mounted : function() {
      // $('#embeddedWarehouseOrderModal #process_date').datepicker().bind('change', function(event) {
      //   vueWarehouseOrderModal.order.process_date = $(this).val();
      // });
      $('#embeddedWarehouseOrderModal form#warehouse_form').keypress(function (e) {
        if (e.keyCode == 13) { e.preventDefault(); }
      });
      // ESC only works at document level and on keydown
      $(document).keydown(function (e) {
        if (e.keyCode == 27) {
          if ($('#embeddedWarehouseOrderModal #addProductModal').hasClass("in")) {
            $('#embeddedWarehouseOrderModal #addProductModal').modal('hide');
          }  else if ($('#embeddedWarehouseOrderModal #splitProductModal').hasClass("in")) {
            $('#embeddedWarehouseOrderModal #splitProductModal').modal('hide');
          } else if ($('#embeddedWarehouseOrderModal').hasClass("in")) {
            $('#embeddedWarehouseOrderModal').modal('hide');
          }
        }
      });
    }
  });

});
