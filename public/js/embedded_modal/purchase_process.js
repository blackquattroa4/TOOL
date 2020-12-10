// use in conjunction with ../embedded_modal/purchase_process.blade.php

var vuePurchaseProcessModal = null;

function loadPurchaseProcessWithAjax(order_id) {
  // use ajax to load order #id
  return $.ajax({
    type: 'GET',
    url: '/purchase-entry/' + order_id + '/ajax',
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
      vuePurchaseProcessModal.errors = result['errors'];
    } else {
      vuePurchaseProcessModal.errors = { general : "System failure" };
    }
  });
}

function populatePurchaseProcessModalWithAjaxResult(result) {
  vuePurchaseProcessModal.modal.title = result.title;
  vuePurchaseProcessModal.modal.csrf = result.csrf;
  vuePurchaseProcessModal.modal.action = result.action;
  vuePurchaseProcessModal.modal.post_url = result.post_url;
  vuePurchaseProcessModal.modal.supplier = result.entities;
  vuePurchaseProcessModal.modal.payment = result.payments;
  vuePurchaseProcessModal.modal.contact = result.contacts;
  vuePurchaseProcessModal.modal.staff = result.staffs;
  vuePurchaseProcessModal.modal.currency = result.currencies;
  vuePurchaseProcessModal.modal.currency_min = result.currency_min;
  vuePurchaseProcessModal.modal.currency_regex = result.currency_regex;
  vuePurchaseProcessModal.modal.currency_fdigit = result.currency_fdigit;
  vuePurchaseProcessModal.modal.currency_symbol = result.currency_symbol;
  vuePurchaseProcessModal.modal.product = result.products;
  vuePurchaseProcessModal.modal.warehouse = result.warehouses;
  vuePurchaseProcessModal.modal.history = result.history;
  vuePurchaseProcessModal.form.id = result.id;
  vuePurchaseProcessModal.form.increment = result.increment;
  vuePurchaseProcessModal.form.supplier = result.supplier;
  vuePurchaseProcessModal.form.inputdate = result.inputdate;
  vuePurchaseProcessModal.form.payment = result.payment;
  vuePurchaseProcessModal.form.reference = result.reference;
  vuePurchaseProcessModal.form.incoterm = result.incoterm;
  vuePurchaseProcessModal.form.via = result.via;
  vuePurchaseProcessModal.form.contact = result.contact;
  vuePurchaseProcessModal.form.staff = result.staff;
  vuePurchaseProcessModal.form.currency = result.currency;
  vuePurchaseProcessModal.form.line = result.line;
  vuePurchaseProcessModal.form.product = result.product;
  vuePurchaseProcessModal.form.display = result.display;
  vuePurchaseProcessModal.form.ivcost = result.ivcost;
  vuePurchaseProcessModal.form.unitprice = result.unitprice;
  vuePurchaseProcessModal.form.description = result.description;
  vuePurchaseProcessModal.form.quantity = result.quantity;
  vuePurchaseProcessModal.form.ddate = result.ddate;
  vuePurchaseProcessModal.form.warehouse = result.warehouse;
  vuePurchaseProcessModal.form.taxable = result.taxable;
  vuePurchaseProcessModal.form.subtotal = result.subtotal;
  vuePurchaseProcessModal.form.untaxed_subtotal = result.untaxed_subtotal;
  vuePurchaseProcessModal.form.taxed_subtotal = result.taxed_subtotal;
  vuePurchaseProcessModal.form.tax_amount = result.tax_amount;
  vuePurchaseProcessModal.form.grand_total = result.grand_total;
}

function processPurchaseProcessInModal(order_id) {
  let jqxhr = loadPurchaseProcessWithAjax(order_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      // key can not be computed dynamically, create an empty object and stuff attribute
      result['data']['title'] = vuePurchaseProcessDataSource["text_process_purchase_" + result['data']['type']] + " #" + ((result['data']['type'] == 'return') ? 'R' : '') + result['data']['increment'];
      result['data']['action'] = {};
      result['data']['action'][vuePurchaseProcessDataSource.button_process] = 'processPurchaseProcessPost';
      result['data']['post_url'] = '';
      result['data']['entities'] = vuePurchaseProcessDataSource.selection_entity;
      result['data']['payments'] = vuePurchaseProcessDataSource.selection_payment;
      result['data']['contacts'] = vuePurchaseProcessDataSource.selection_contact[result['data']['supplier']];
      result['data']['staffs'] = vuePurchaseProcessDataSource.selection_staff;
      result['data']['currencies'] = vuePurchaseProcessDataSource.selection_currency;
      result['data']['products'] = vuePurchaseProcessDataSource.selection_product;
      result['data']['warehouses'] = vuePurchaseProcessDataSource.selection_warehouse;
      populatePurchaseProcessModalWithAjaxResult(result['data']);
      vuePurchaseProcessModal.form.expiration = vuePurchaseProcessDataSource.text_today;
      vuePurchaseProcessModal.form.processing = new Array(vuePurchaseProcessModal.form.line.length).fill(0);
      vuePurchaseProcessModal.errors = [];

      Vue.nextTick(function () {
        // unbind date-picker
        $('#embeddedPurchaseProcessModal #expiration').datepicker('destroy');
        $('#embeddedPurchaseProcessModal #expiration').removeClass('hasDatepicker');
        // re-bind date-picker
        $('#embeddedPurchaseProcessModal #expiration').datepicker().bind('change', function(event) {
          vuePurchaseProcessModal.form.expiration = $(this).val();
        });

      });

      // show modal
      $('#embeddedPurchaseProcessModal').modal('show');
    }
  });
}

function processPurchaseProcessPost() {
  // finish this function
  $.ajax({
    type : 'POST',
    url : '/purchase-entry/process/' + vuePurchaseProcessModal.form.id + '/ajax',
    enctype : 'multipart/form-data',
    data : new FormData($('#embeddedPurchaseProcessModal form')[0]),
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
      $('#embeddedPurchaseProcessModal').modal('hide');
      // update payable table
      if ('insertCallback' in vuePurchaseProcessDataSource) {
        vuePurchaseProcessDataSource.insertCallback(data['data']['transactable']);
      }
      if ('updateCallback' in vuePurchaseProcessDataSource) {
        vuePurchaseProcessDataSource.updateCallback(data['data']['entry']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vuePurchaseProcessModal.errors = data['errors'];
    }
  });
}

$(document).ready(function() {

  vuePurchaseProcessModal = new Vue({
    el : '#embeddedPurchaseProcessModal',
    data : {
      modal : {
        title : '',
        csrf : '',
        action : [ ],
        post_url : '',
        supplier : [ ],
        payment : [ ],
        contact : [ ],
        staff : [ ],
        currency : [ ],
        currency_min : 0.01,
        currency_regex : '',
        currency_fdigit : '',
        currency_symbol : '',
        product : [ ],
        warehouse : [ ],
        history : [ ]
      },
      form : {
        id : 0,
        increment : '',
        supplier : 0,
        inputdate : '',
        payment : 0,
        expiration : '',
        incoterm : '',
        contact : 0,
        reference : '',
        via : '',
        staff : 0,
        currency : 0,
        status : '',
        line : [ ],
        product : [ ],
        display : [ ],
        ivcost : [ ],
        unitprice : [ ],
        description : [ ],
        processing : [ ],
        quantity : [ ],
        ddate : [ ],
        destination : [ ],
        taxable : [ ],
        subtotal : [ ],
        untaxed_subtotal : '',
        taxed_subtotal : '',
        tax_amount : '',
        grand_total : ''
      },
      errors : { }
    },
    mounted : function() {
      $(document).keydown(function (e) {
        // disable ENTER
        $('#embeddedPurchaseProcessModal form').keypress(function (e) {
          if (e.keyCode == 13) { e.preventDefault(); }
        });
        if (e.keyCode == 27) {
          if ($('#embeddedPurchaseProcessModalHistoryModal').hasClass("in")) {
            $('#embeddedPurchaseProcessModalHistoryModal').modal('hide');
          } else if ($('#embeddedPurchaseProcessModalAggregationModal').hasClass("in")) {
              $('#embeddedPurchaseProcessModalAggregationModal').modal('hide');
          } else if ($('#embeddedPurchaseProcessModal').hasClass("in")) {
            $('#embeddedPurchaseProcessModal').modal('hide');
          }
        }
      });
    }
  });

});
