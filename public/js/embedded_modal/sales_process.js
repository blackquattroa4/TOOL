// use in conjunction with ../embedded_modal/sales_process.blade.php

var vueSalesProcessModal = null;

function loadSalesProcessWithAjax(order_id) {
  // use ajax to load order #id
  return $.ajax({
    type: 'GET',
    url: '/sales-entry/' + order_id + '/ajax',
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
      vueSalesProcessModal.errors = result['errors'];
    } else {
      vueSalesProcessModal.errors = { general : "System failure" };
    }
  });
}

function populateSalesProcessModalWithAjaxResult(result) {
  vueSalesProcessModal.modal.title = result.title;
  vueSalesProcessModal.modal.csrf = result.csrf;
  vueSalesProcessModal.modal.action = result.action;
  vueSalesProcessModal.modal.post_url = result.post_url;
  vueSalesProcessModal.modal.customer = result.entities;
  vueSalesProcessModal.modal.payment = result.payments;
  vueSalesProcessModal.modal.contact = result.contacts;
  vueSalesProcessModal.modal.staff = result.staffs;
  vueSalesProcessModal.modal.currency = result.currencies;
  vueSalesProcessModal.modal.currency_min = result.currency_min;
  vueSalesProcessModal.modal.currency_regex = result.currency_regex;
  vueSalesProcessModal.modal.currency_fdigit = result.currency_fdigit;
  vueSalesProcessModal.modal.currency_symbol = result.currency_symbol;
  vueSalesProcessModal.modal.product = result.products;
  vueSalesProcessModal.modal.warehouse = result.warehouses;
  vueSalesProcessModal.modal.history = result.history;
  vueSalesProcessModal.form.id = result.id;
  vueSalesProcessModal.form.increment = result.increment;
  vueSalesProcessModal.form.customer = result.customer;
  vueSalesProcessModal.form.inputdate = result.inputdate;
  vueSalesProcessModal.form.payment = result.payment;
  vueSalesProcessModal.form.reference = result.reference;
  vueSalesProcessModal.form.incoterm = result.incoterm;
  vueSalesProcessModal.form.via = result.via;
  vueSalesProcessModal.form.contact = result.contact;
  vueSalesProcessModal.form.staff = result.staff;
  vueSalesProcessModal.form.currency = result.currency;
  vueSalesProcessModal.form.line = result.line;
  vueSalesProcessModal.form.product = result.product;
  vueSalesProcessModal.form.display = result.display;
  vueSalesProcessModal.form.unitprice = result.unitprice;
  vueSalesProcessModal.form.description = result.description;
  vueSalesProcessModal.form.quantity = result.quantity;
  vueSalesProcessModal.form.disctype = result.disctype;
  vueSalesProcessModal.form.discount = result.discount;
  vueSalesProcessModal.form.taxable = result.taxable;
  vueSalesProcessModal.form.subtotal = result.subtotal;
  vueSalesProcessModal.form.untaxed_subtotal = result.untaxed_subtotal;
  vueSalesProcessModal.form.taxed_subtotal = result.taxed_subtotal;
  vueSalesProcessModal.form.tax_amount = result.tax_amount;
  vueSalesProcessModal.form.grand_total = result.grand_total;
}

function processSalesProcessInModal(order_id) {
  let jqxhr = loadSalesProcessWithAjax(order_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      // key can not be computed dynamically, create an empty object and stuff attribute
      result['data']['title'] = vueSalesProcessDataSource["text_process_sales_" + result['data']['type']] + " #" + ((result['data']['type'] == 'return') ? 'R' : '') + result['data']['increment'];
      result['data']['action'] = {};
      result['data']['action'][vueSalesProcessDataSource.button_process] = 'processSalesProcessPost';
      result['data']['post_url'] = '';
      result['data']['entities'] = vueSalesProcessDataSource.selection_entity;
      result['data']['payments'] = vueSalesProcessDataSource.selection_payment;
      result['data']['contacts'] = vueSalesProcessDataSource.selection_contact[result['data']['customer']];
      result['data']['staffs'] = vueSalesProcessDataSource.selection_staff;
      result['data']['currencies'] = vueSalesProcessDataSource.selection_currency;
      result['data']['products'] = vueSalesProcessDataSource.selection_product;
      result['data']['warehouses'] = vueSalesProcessDataSource.selection_warehouse;
      populateSalesProcessModalWithAjaxResult(result['data']);
      vueSalesProcessModal.form.expiration = vueSalesProcessDataSource.text_today;
      vueSalesProcessModal.form.processing = new Array(vueSalesProcessModal.form.line.length).fill(0);
      vueSalesProcessModal.errors = [];

      Vue.nextTick(function () {
        // unbind date-picker
        $('#embeddedSalesProcessModal #expiration').datepicker('destroy');
        $('#embeddedSalesProcessModal #expiration').removeClass('hasDatepicker');
        // re-bind date-picker
        $('#embeddedSalesProcessModal #expiration').datepicker().bind('change', function(event) {
          vueSalesProcessModal.form.expiration = $(this).val();
        });

      });

      // show modal
      $('#embeddedSalesProcessModal').modal('show');
    }
  });
}

function processSalesProcessPost() {
  // finish this function
  $.ajax({
    type : 'POST',
    url : '/sales-entry/process/' + vueSalesProcessModal.form.id + '/ajax',
    enctype : 'multipart/form-data',
    data : new FormData($('#embeddedSalesProcessModal form')[0]),
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
      $('#embeddedSalesProcessModal').modal('hide');
      // update payable table
      if ('insertCallback' in vueSalesProcessDataSource) {
        vueSalesProcessDataSource.insertCallback(data['data']['transactable']);
      }
      if ('updateCallback' in vueSalesProcessDataSource) {
        vueSalesProcessDataSource.updateCallback(data['data']['entry']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vueSalesProcessModal.errors = data['errors'];
    }
  });
}

$(document).ready(function() {

  vueSalesProcessModal = new Vue({
    el : '#embeddedSalesProcessModal',
    data : {
      modal : {
        title : '',
        csrf : '',
        action : [ ],
        post_url : '',
        customer : [ ],
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
        customer : 0,
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
        unitprice : [ ],
        description : [ ],
        processing : [ ],
        quantity : [ ],
        disctype : [ ],
        discount : [ ],
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
      // disable ENTER
      $('#embeddedSalesProcessModal form').keypress(function (e) {
        if (e.keyCode == 13) { e.preventDefault(); }
      });
      $(document).keydown(function (e) {
        if (e.keyCode == 27) {
          if ($('#embeddedSalesProcessModalHistoryModal').hasClass("in")) {
            $('#embeddedSalesProcessModalHistoryModal').modal('hide');
          } else if ($('#embeddedSalesProcessModalAggregationModal').hasClass("in")) {
              $('#embeddedSalesProcessModalAggregationModal').modal('hide');
          } else if ($('#embeddedSalesProcessModal').hasClass("in")) {
            $('#embeddedSalesProcessModal').modal('hide');
          }
        }
      });
    }
  });

});
