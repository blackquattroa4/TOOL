// use in conjunction with ../embedded_modal/sales_order.blade.php

var vueSalesEntryModal = null;

function loadSalesEntryWithAjax(order_id) {
  // use ajax to load sales-entry #id
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
      vueSalesEntryModal.errors = result['errors'];
    } else {
      vueSalesEntryModal.errors = { general : "System failure" };
    }
  });
}

function populateSalesEntryModalWithAjaxResult(result) {
  vueSalesEntryModal.modal.readonly = result.readonly;
  vueSalesEntryModal.modal.title = result.title;
  vueSalesEntryModal.modal.csrf = result.csrf;
  vueSalesEntryModal.modal.action = result.action;
  vueSalesEntryModal.modal.post_url = result.post_url;
  vueSalesEntryModal.modal.customer = result.entities;
  vueSalesEntryModal.modal.payment = result.payments;
  vueSalesEntryModal.modal.contact = result.contacts;
  vueSalesEntryModal.modal.staff  = result.staffs;
  vueSalesEntryModal.modal.currency = result.currencies;
  vueSalesEntryModal.modal.currency_min = result.currency_min;
  vueSalesEntryModal.modal.currency_regex = result.currency_regex;
  vueSalesEntryModal.modal.currency_fdigit = result.currency_fdigit;
  vueSalesEntryModal.modal.currency_symbol = result.currency_symbol;
  vueSalesEntryModal.modal.currency_icon = result.currency_icon;
  vueSalesEntryModal.modal.product = result.products;
  vueSalesEntryModal.modal.warehouse = result.warehouses;
  vueSalesEntryModal.modal.billing_address = result.billing_addresses;
  vueSalesEntryModal.modal.shipping_address = result.shipping_addresses;
  vueSalesEntryModal.modal.history = result.history;
  vueSalesEntryModal.modal.shipment_info = result.shipment_info;
  vueSalesEntryModal.form.id = result.id;
  vueSalesEntryModal.form.increment = result.increment;
  vueSalesEntryModal.form.type = result.type;
  vueSalesEntryModal.form.customer = result.customer;
  vueSalesEntryModal.form.reserved_receivable_title = result.reserved_receivable_title;
  vueSalesEntryModal.form.inputdate = result.inputdate;
  vueSalesEntryModal.form.payment = result.payment;
  vueSalesEntryModal.form.expiration = result.expiration;
  vueSalesEntryModal.form.incoterm = result.incoterm;
  vueSalesEntryModal.form.status = result.status;
  vueSalesEntryModal.form.contact = result.contact;
  vueSalesEntryModal.form.reference = result.reference;
  vueSalesEntryModal.form.currency = result.currency;
  vueSalesEntryModal.form.staff = result.staff;
  vueSalesEntryModal.form.tax_rate = result.tax_rate;
  vueSalesEntryModal.form.via = result.via;
  vueSalesEntryModal.form.show_bank_account = result.show_bank_account;
  vueSalesEntryModal.form.show_discount = result.show_discount;
  vueSalesEntryModal.form.email_when_invoiced = result.email_when_invoiced;
  vueSalesEntryModal.form.palletized = result.palletized;
  vueSalesEntryModal.form.warehouse = result.warehouse;
  vueSalesEntryModal.form.billing_address = result.billing_address;
  vueSalesEntryModal.form.shipping_address = result.shipping_address;
  vueSalesEntryModal.form.notes = result.notes;
  vueSalesEntryModal.form.line = result.line;
  vueSalesEntryModal.form.product = result.product;
  vueSalesEntryModal.form.display = result.display;
  vueSalesEntryModal.form.unitprice = result.unitprice;
  vueSalesEntryModal.form.description = result.description;
  vueSalesEntryModal.form.quantity = result.quantity;
  vueSalesEntryModal.form.disctype = result.disctype;
  vueSalesEntryModal.form.discount = result.discount;
  vueSalesEntryModal.form.taxable = result.taxable;
  vueSalesEntryModal.form.subtotal = result.subtotal;
  vueSalesEntryModal.form.untaxed_subtotal = result.untaxed_subtotal;
  vueSalesEntryModal.form.taxed_subtotal = result.taxed_subtotal;
  vueSalesEntryModal.form.tax_amount = result.tax_amount;
  vueSalesEntryModal.form.grand_total = result.grand_total;
}

function createSalesEntryInModal(type) {
  let jqxhr = loadSalesEntryWithAjax(0);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      let customerId = Object.keys(vueSalesEntryDataSource.selection_entity)[0];
      // populate modal
      result['data']['readonly'] = false;
      // key can not be computed dynamically, create an empty object and stuff attribute
      result['data']['title'] = vueSalesEntryDataSource["text_create_sales_" + type];
      result['data']['action'] = {};
      result['data']['action'][vueSalesEntryDataSource.button_submit] = 'createSalesEntryPost';
      result['data']['post_url'] = '';
      result['data']['entities'] = vueSalesEntryDataSource.selection_entity;
      result['data']['payments'] = vueSalesEntryDataSource.selection_payment;
      result['data']['contacts'] = vueSalesEntryDataSource.selection_contact[customerId];
      result['data']['staffs'] = vueSalesEntryDataSource.selection_staff;
      result['data']['currencies'] = vueSalesEntryDataSource.selection_currency;
      result['data']['currency_min'] = vueSalesEntryDataSource.selection_entity[customerId].min;
      result['data']['currency_regex'] = vueSalesEntryDataSource.selection_entity[customerId].regex;
      result['data']['currency_fdigit'] = vueSalesEntryDataSource.selection_entity[customerId].fdigit;
      result['data']['currency_symbol'] = vueSalesEntryDataSource.selection_entity[customerId].symbol;
      result['data']['currency_icon'] = vueSalesEntryDataSource.selection_entity[customerId].icon;
      result['data']['products'] = vueSalesEntryDataSource.selection_product[customerId];
      result['data']['warehouses'] = vueSalesEntryDataSource.selection_warehouse;;
      result['data']['billing_addresses'] = vueSalesEntryDataSource.selection_billing[customerId];
      result['data']['shipping_addresses'] = vueSalesEntryDataSource.selection_billing[customerId];;
      result['data']['history'] = [ ];
      result['data']['shipment_info'] = [ ];
      result['data']['id'] = 0;
      result['data']['increment'] = '????';
      result['data']['type'] = type;
      result['data']['customer'] = customerId;
      result['data']['reserved_receivable_title'] = '';
      result['data']['inputdate'] = vueSalesEntryDataSource.text_today;
      result['data']['payment'] = vueSalesEntryDataSource.selection_entity[customerId].payment;
      result['data']['expiration'] = vueSalesEntryDataSource.text_today;
      result['data']['incoterm'] = '';
      result['data']['status'] = 'open';
      result['data']['contact'] =  Object.values(vueSalesEntryDataSource.selection_contact[customerId])[0].id;;
      result['data']['reference'] = '';
      result['data']['currency'] = vueSalesEntryDataSource.selection_entity[customerId].currency;
      result['data']['staff'] =  vueSalesEntryDataSource.current_user_id;
      result['data']['tax_rate'] = '0';
      result['data']['via'] = '';
      result['data']['show_bank_account'] = 0;
      result['data']['show_discount'] = 0;
      result['data']['email_when_invoiced'] = 0;
      result['data']['palletized'] = 0;
      result['data']['warehouse'] = Object.values(vueSalesEntryDataSource.selection_warehouse)[0].id;
      result['data']['billing_address'] = Object.values(vueSalesEntryDataSource.selection_billing[customerId])[0].id;
      result['data']['shipping_address'] = Object.values(vueSalesEntryDataSource.selection_shipping[customerId])[0].id;
      result['data']['notes'] = '';
      result['data']['line'] = [ ];
      result['data']['product'] = [ ];
      result['data']['display'] = [ ];
      result['data']['unitprice'] = [ ];
      result['data']['description'] = [ ];
      result['data']['quantity'] = [ ];
      result['data']['disctype'] = [ ];
      result['data']['discount'] = [ ];
      result['data']['taxable'] = [ ];
      result['data']['subtotal'] = [ ];
      let zero = 0;
      result['data']['untaxed_subtotal'] = zero.toLocaleString(vueSalesEntryDataSource.selection_entity[customerId].regex, { style: 'currency', currency: vueSalesEntryDataSource.selection_entity[customerId].symbol });
      result['data']['taxed_subtotal'] = zero.toLocaleString(vueSalesEntryDataSource.selection_entity[customerId].regex, { style: 'currency', currency: vueSalesEntryDataSource.selection_entity[customerId].symbol });
      result['data']['tax_amount'] = zero.toLocaleString(vueSalesEntryDataSource.selection_entity[customerId].regex, { style: 'currency', currency: vueSalesEntryDataSource.selection_entity[customerId].symbol });
      result['data']['grand_total'] = zero.toLocaleString(vueSalesEntryDataSource.selection_entity[customerId].regex, { style: 'currency', currency: vueSalesEntryDataSource.selection_entity[customerId].symbol });

      populateSalesEntryModalWithAjaxResult(result['data']);
      vueSalesEntryModal.errors = [];

      Vue.nextTick(function () {
        // unbind date-picker
        $('#embeddedSalesEntryModal #inputdate').datepicker('destroy');
        $('#embeddedSalesEntryModal #inputdate').removeClass('hasDatepicker');
        $('#embeddedSalesEntryModal #expiration').datepicker('destroy');
        $('#embeddedSalesEntryModal #expiration').removeClass('hasDatepicker');
        // re-bind date-picker
        $('#embeddedSalesEntryModal #inputdate').datepicker().bind('change', function(event) {
          vueSalesEntryModal.form.inputdate = $(this).val();
        });
        $('#embeddedSalesEntryModal #expiration').datepicker().bind('change', function(event) {
          vueSalesEntryModal.form.expiration = $(this).val();
        });
      });

      // show modal
      $('#embeddedSalesEntryModal').modal('show');
    }
  });
}

function createSalesEntryPost() {
  // finish this function
  $.ajax({
    type : 'POST',
    url : '/sales-entry/create/ajax',
    enctype : 'multipart/form-data',
    data : new FormData($('#embeddedSalesEntryModal form')[0]),
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
      $('#embeddedSalesEntryModal').modal('hide');
      // update order/return table
      if ('insertCallback' in vueSalesEntryDataSource) {
        vueSalesEntryDataSource.insertCallback(data['data']['entry']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vueSalesEntryModal.errors = data['errors'];
    }
  });
}

function viewSalesEntryInModal(order_id) {
  let jqxhr = loadSalesEntryWithAjax(order_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      result['data']['readonly'] = true;
      // key can not be computed dynamically, create an empty object and stuff attribute
      result['data']['title'] = vueSalesEntryDataSource["text_view_sales_" + result['data']['type']] + " #" + result['data']['increment'];
      result['data']['action'] = {};
      result['data']['action'][vueSalesEntryDataSource.button_pdf] = 'viewSalesEntryPost';
      result['data']['post_url'] = '';
      result['data']['entities'] = vueSalesEntryDataSource.selection_entity;
      result['data']['payments'] = vueSalesEntryDataSource.selection_payment;
      result['data']['contacts'] = vueSalesEntryDataSource.selection_contact[result['data']['customer']];
      result['data']['staffs'] = vueSalesEntryDataSource.selection_staff;
      result['data']['currencies'] = vueSalesEntryDataSource.selection_currency;
      result['data']['products'] = vueSalesEntryDataSource.selection_product[result['data']['customer']];
      result['data']['warehouses'] = vueSalesEntryDataSource.selection_warehouse;
      result['data']['billing_addresses'] = vueSalesEntryDataSource.selection_billing[result['data']['customer']];
      result['data']['shipping_addresses'] = vueSalesEntryDataSource.selection_shipping[result['data']['customer']];

      populateSalesEntryModalWithAjaxResult(result['data']);
      vueSalesEntryModal.errors = [];

      Vue.nextTick(function () {
        // unbind date-picker
        $('#embeddedSalesEntryModal #inputdate').datepicker('destroy');
        $('#embeddedSalesEntryModal #inputdate').removeClass('hasDatepicker');
        $('#embeddedSalesEntryModal #expiration').datepicker('destroy');
        $('#embeddedSalesEntryModal #expiration').removeClass('hasDatepicker');
      });

      // show modal
      $('#embeddedSalesEntryModal').modal('show');
    }
  });
}

function viewSalesEntryPost() {
  $.ajax({
    url: '/sales-entry/print/' + vueSalesEntryModal.form.id + '/ajax',
    data: {
      _token : vueSalesEntryModal.modal.csrf
    },
    method: 'POST',
    xhrFields: {
        responseType: 'blob'
    },
    success: function (data) {
      var a = document.createElement('a');
      var url = window.URL.createObjectURL(data);
      a.href = url;
      a.download = 'Sales ' + vueSalesEntryModal.form.type.charAt(0).toUpperCase() + vueSalesEntryModal.form.type.slice(1) + ' #' + vueSalesEntryModal.form.increment + '.pdf';
      document.body.append(a);
      a.click();
      a.remove();
      window.URL.revokeObjectURL(url);
    }
  });
}

function updateSalesEntryInModal(order_id) {
  let jqxhr = loadSalesEntryWithAjax(order_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      result['data']['readonly'] = false;
      // key can not be computed dynamically, create an empty object and stuff attribute
      result['data']['title'] = vueSalesEntryDataSource["text_update_sales_" + result['data']['type']] + " #" + result['data']['increment'];
      result['data']['action'] = {};
      result['data']['action'][vueSalesEntryDataSource.button_update] = 'updateSalesEntryPost';
      result['data']['post_url'] = '';
      result['data']['entities'] = vueSalesEntryDataSource.selection_entity;
      result['data']['payments'] = vueSalesEntryDataSource.selection_payment;
      result['data']['contacts'] = vueSalesEntryDataSource.selection_contact[result['data']['customer']];
      result['data']['staffs'] = vueSalesEntryDataSource.selection_staff;
      result['data']['currencies'] = vueSalesEntryDataSource.selection_currency;
      result['data']['products'] = vueSalesEntryDataSource.selection_product[result['data']['customer']];
      result['data']['warehouses'] = vueSalesEntryDataSource.selection_warehouse;
      result['data']['billing_addresses'] = vueSalesEntryDataSource.selection_billing[result['data']['customer']];
      result['data']['shipping_addresses'] = vueSalesEntryDataSource.selection_shipping[result['data']['customer']];

      populateSalesEntryModalWithAjaxResult(result['data']);
      vueSalesEntryModal.errors = [];

      Vue.nextTick(function () {
        // unbind date-picker
        $('#embeddedSalesEntryModal #inputdate').datepicker('destroy');
        $('#embeddedSalesEntryModal #inputdate').removeClass('hasDatepicker');
        $('#embeddedSalesEntryModal #expiration').datepicker('destroy');
        $('#embeddedSalesEntryModal #expiration').removeClass('hasDatepicker');
        // re-bind date-picker
        $('#embeddedSalesEntryModal #inputdate').datepicker().bind('change', function(event) {
          vueSalesEntryModal.form.inputdate = $(this).val();
        });
        $('#embeddedSalesEntryModal #expiration').datepicker().bind('change', function(event) {
          vueSalesEntryModal.form.expiration = $(this).val();
        });
      });

      // show modal
      $('#embeddedSalesEntryModal').modal('show');
    }
  });
}

function updateSalesEntryPost() {
  // finish this function
  $.ajax({
    type : 'POST',
    url : '/sales-entry/update/' + vueSalesEntryModal.form.id + '/ajax',
    enctype : 'multipart/form-data',
    data : new FormData($('#embeddedSalesEntryModal form')[0]),
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
      $('#embeddedSalesEntryModal').modal('hide');
      // update expense table
      if ('updateCallback' in vueSalesEntryDataSource) {
        vueSalesEntryDataSource.updateCallback(data['data']['entry']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vueSalesEntryModal.errors = data['errors'];
    }
  });
}

function approveSalesEntryInModal(order_id) {
  let jqxhr = loadSalesEntryWithAjax(order_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      result['data']['readonly'] = true;
      // key can not be computed dynamically, create an empty object and stuff attribute
      result['data']['title'] = vueSalesEntryDataSource["text_approve_sales_" + result['data']['type']] + " #" + result['data']['increment'];
      result['data']['action'] = {};
      result['data']['action'][vueSalesEntryDataSource.button_approve] = 'approveSalesEntryPost';
      result['data']['action'][vueSalesEntryDataSource.button_reject] = 'rejectSalesEntryPost';
      result['data']['post_url'] = '';
      result['data']['entities'] = vueSalesEntryDataSource.selection_entity;
      result['data']['payments'] = vueSalesEntryDataSource.selection_payment;
      result['data']['contacts'] = vueSalesEntryDataSource.selection_contact[result['data']['customer']];
      result['data']['staffs'] = vueSalesEntryDataSource.selection_staff;
      result['data']['currencies'] = vueSalesEntryDataSource.selection_currency;
      result['data']['products'] = vueSalesEntryDataSource.selection_product[result['data']['customer']];
      result['data']['warehouses'] = vueSalesEntryDataSource.selection_warehouse;
      result['data']['billing_addresses'] = vueSalesEntryDataSource.selection_billing[result['data']['customer']];
      result['data']['shipping_addresses'] = vueSalesEntryDataSource.selection_shipping[result['data']['customer']];

      populateSalesEntryModalWithAjaxResult(result['data']);
      vueSalesEntryModal.errors = [];

      Vue.nextTick(function () {
        // unbind date-picker
        $('#embeddedSalesEntryModal #inputdate').datepicker('destroy');
        $('#embeddedSalesEntryModal #inputdate').removeClass('hasDatepicker');
        $('#embeddedSalesEntryModal #expiration').datepicker('destroy');
        $('#embeddedSalesEntryModal #expiration').removeClass('hasDatepicker');
      });

      // show modal
      $('#embeddedSalesEntryModal').modal('show');
    }
  });
}

function approveSalesEntryPost() {
  $.ajax({
    url: '/sales-entry/approve/' + vueSalesEntryModal.form.id + '/ajax',
    data: {
      _token : vueSalesEntryModal.modal.csrf,
      decision : 'approve'
    },
    method: 'POST',
    beforeSend: function(data) {
      $('.ajax-processing').removeClass('hidden');
    },
  }).done(function(data) {
    // data is already an object, no need to parse it.
    if (data['success']) {
      $('#embeddedSalesEntryModal').modal('hide');
      // update expense table
      if ('updateCallback' in vueSalesEntryDataSource) {
        vueSalesEntryDataSource.updateCallback(data['data']['entry']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vueSalesEntryModal.errors = data['errors'];
    }
  });
}

function rejectSalesEntryPost() {
  $.ajax({
    url: '/sales-entry/approve/' + vueSalesEntryModal.form.id + '/ajax',
    data: {
      _token : vueSalesEntryModal.modal.csrf,
      decision : 'disapprove'
    },
    method: 'POST',
    beforeSend: function(data) {
      $('.ajax-processing').removeClass('hidden');
    },
  }).done(function(data) {
    // data is already an object, no need to parse it.
    if (data['success']) {
      $('#embeddedSalesEntryModal').modal('hide');
      // update order/return table
      if ('updateCallback' in vueSalesEntryDataSource) {
        vueSalesEntryDataSource.updateCallback(data['data']['entry']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vueSalesEntryModal.errors = data['errors'];
    }
  });
}

function salesReservePostAjax(id) {
  $.ajax({
    // obtain csrf token first
    type: 'GET',
    url: '/dashboard/sales/reserve/ajax',
    data: { },
    dataType: 'html',
    beforeSend: function(data) {
      $('.ajax-processing').removeClass('hidden');
    },
  }).fail(function (data) {
    $('.ajax-processing').addClass('hidden');
  }).done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // successful obtaining csrf, now we post
      $.ajax({
        type: 'POST',
        url: '/crm/reserve/order/' + id,
        data: {
            _token: result.data.csrf,
          },
        dataType: 'html',
      }).always(function(data) {
        $('.ajax-processing').addClass('hidden');
      }).done(function(data) {
        let result = JSON.parse(data);
        if (result['success']) {
          // update order/return table
          if ('updateCallback' in vueSalesEntryDataSource) {
            vueSalesEntryDataSource.updateCallback(result['data']);
          }
        }
      });
    }
  });
}

// not used for the moment
function voidSalesEntryInModal(order_id) {
  let jqxhr = loadSalesEntryWithAjax(order_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      result['data']['readonly'] = true;
      // key can not be computed dynamically, create an empty object and stuff attribute
      result['data']['title'] = vueSalesEntryDataSource["text_void_sales_" + result['data']['type']] + " #" + result['data']['increment'];
      result['data']['action'] = {};
      result['data']['action'][vueSalesEntryDataSource.button_void] = 'voidSalesEntryPost';
      result['data']['post_url'] = '';
      result['data']['entities'] = vueSalesEntryDataSource.selection_entity;
      result['data']['payments'] = vueSalesEntryDataSource.selection_payment;
      result['data']['contacts'] = vueSalesEntryDataSource.selection_contact[result['data']['customer']];
      result['data']['staffs'] = vueSalesEntryDataSource.selection_staff;
      result['data']['currencies'] = vueSalesEntryDataSource.selection_currency;
      result['data']['products'] = vueSalesEntryDataSource.selection_product[result['data']['customer']];
      result['data']['warehouses'] = vueSalesEntryDataSource.selection_warehouse;
      result['data']['billing_addresses'] = vueSalesEntryDataSource.selection_billing[result['data']['customer']];
      result['data']['shipping_addresses'] = vueSalesEntryDataSource.selection_shipping[result['data']['customer']];

      populateSalesEntryModalWithAjaxResult(result['data']);
      vueSalesEntryModal.errors = [];

      Vue.nextTick(function () {
        // unbind date-picker
        $('#embeddedSalesEntryModal #inputdate').datepicker('destroy');
        $('#embeddedSalesEntryModal #inputdate').removeClass('hasDatepicker');
        $('#embeddedSalesEntryModal #expiration').datepicker('destroy');
        $('#embeddedSalesEntryModal #expiration').removeClass('hasDatepicker');
      });

      // show modal
      $('#embeddedSalesEntryModal').modal('show');
    }
  });
}

function changeSalesEntryModalEntity() {
  // payment term
  vueSalesEntryModal.form.payment = vueSalesEntryDataSource.selection_entity[vueSalesEntryModal.form.customer].payment;
  // order currency
  vueSalesEntryModal.form.currency = vueSalesEntryDataSource.selection_entity[vueSalesEntryModal.form.customer].currency;
  changeSalesEntryModalCurrency();
  // change contact
  vueSalesEntryModal.modal.contact = vueSalesEntryDataSource.selection_contact[vueSalesEntryModal.form.customer];
  vueSalesEntryModal.form.contact = vueSalesEntryModal.modal.contact[0].id;
  // change billing Address
  vueSalesEntryModal.modal.billing_address = vueSalesEntryDataSource.selection_billing[vueSalesEntryModal.form.customer];
  vueSalesEntryModal.form.billing_address = vueSalesEntryModal.modal.billing_address[0].id;
  // change shipping address
  vueSalesEntryModal.modal.shipping_address = vueSalesEntryDataSource.selection_shipping[vueSalesEntryModal.form.customer];
  vueSalesEntryModal.form.shipping_address = vueSalesEntryModal.modal.shipping_address[0].id;
  // change product selection
  vueSalesEntryModal.modal.product = vueSalesEntryDataSource.selection_product[vueSalesEntryModal.form.customer];
}

function changeSalesEntryModalCurrency() {
  let cx = vueSalesEntryDataSource.selection_currency[vueSalesEntryModal.form.currency];
  vueSalesEntryModal.modal.currency_min = cx.min;
  vueSalesEntryModal.modal.currency_regex = cx.regex;
  vueSalesEntryModal.modal.currency_fdigit = cx.fdigit;
  vueSalesEntryModal.modal.currency_symbol = cx.symbol;
  vueSalesEntryModal.modal.currency_icon = cx.icon;
}

function updateSalesEntryLineItem(line_id) {
  let product_id = vueSalesEntryModal.form.product[line_id];
  vueSalesEntryModal.form.display[line_id] = vueSalesEntryModal.modal.product[product_id].display;
  vueSalesEntryModal.form.unitprice[line_id] = vueSalesEntryModal.modal.product[product_id].unit_price;
  vueSalesEntryModal.form.description[line_id] = vueSalesEntryModal.modal.product[product_id].description;
  vueSalesEntryModal.form.quantity[line_id] = 0;
  vueSalesEntryModal.form.disctype[line_id] = '%';
  vueSalesEntryModal.form.discount[line_id] = '0';
  vueSalesEntryModal.form.taxable[line_id] = false;

  updateSalesEntrySubtotal(line_id);
}

function updateSalesEntryTotal() {
  let untaxed = 0, taxed = 0, tax = 0;

  // discount already taken into consideration when calculating subtotal
  for(index in vueSalesEntryModal.form.subtotal) {
    if (vueSalesEntryModal.form.taxable[index]) {
      taxed += parseFloat(vueSalesEntryModal.form.subtotal[index]);
      tax += parseFloat(vueSalesEntryModal.form.subtotal[index]) * parseFloat(vueSalesEntryModal.form.tax_rate) / 100;
    } else {
      untaxed += parseFloat(vueSalesEntryModal.form.subtotal[index]);
    }
  }

  vueSalesEntryModal.form.untaxed_subtotal = untaxed.toLocaleString(vueSalesEntryModal.modal.currency_regex, { style: 'currency', currency: vueSalesEntryModal.modal.currency_symbol });
  vueSalesEntryModal.form.taxed_subtotal = taxed.toLocaleString(vueSalesEntryModal.modal.currency_regex, { style: 'currency', currency: vueSalesEntryModal.modal.currency_symbol });
  vueSalesEntryModal.form.tax_amount = tax.toLocaleString(vueSalesEntryModal.modal.currency_regex, { style: 'currency', currency: vueSalesEntryModal.modal.currency_symbol });
  vueSalesEntryModal.form.grand_total = (taxed + untaxed + tax).toLocaleString(vueSalesEntryModal.modal.currency_regex, { style: 'currency', currency: vueSalesEntryModal.modal.currency_symbol });

  // hideThenShowHint('.show-balance', '.show-balance-hint');
}

function updateSalesEntrySubtotal(index) {
  switch (vueSalesEntryModal.form.disctype[index]) {
  case '%':
    vueSalesEntryModal.form.subtotal[index] = (parseFloat(vueSalesEntryModal.form.unitprice[index]) * parseFloat(vueSalesEntryModal.form.quantity[index]) * (100 - parseFloat(vueSalesEntryModal.form.discount[index])) / 100).toFixed(vueSalesEntryModal.modal.currency_fdigit);
    break;
  case vueSalesEntryModal.modal.currency_icon:
    vueSalesEntryModal.form.subtotal[index] = ((parseFloat(vueSalesEntryModal.form.unitprice[index]) - parseFloat(vueSalesEntryModal.form.discount[index])) * parseFloat(vueSalesEntryModal.form.quantity[index])).toFixed(vueSalesEntryModal.modal.currency_fdigit);
    break;
  }

  updateSalesEntryTotal();
}

function addNewSalesEntryLine() {
  vueSalesEntryModal.form.line.push(0);
  vueSalesEntryModal.form.product.push(Object.keys(vueSalesEntryModal.modal.product)[0]);
  vueSalesEntryModal.form.display.push(Object.values(vueSalesEntryModal.modal.product)[0].display);
  vueSalesEntryModal.form.unitprice.push(Object.values(vueSalesEntryModal.modal.product)[0].unit_price);
  vueSalesEntryModal.form.description.push(Object.values(vueSalesEntryModal.modal.product)[0].description);
  vueSalesEntryModal.form.quantity.push(0);
  vueSalesEntryModal.form.discount.push(0);
  vueSalesEntryModal.form.disctype.push('%');
  vueSalesEntryModal.form.taxable.push(0);
  vueSalesEntryModal.form.subtotal.push(0);
  // wait until next tick (component rendered) to install date selector
  let id = Object.keys(vueSalesEntryModal.form.line).pop();
  Vue.nextTick(function () {
    $('#embeddedSalesEntryModal div.modal-lg div.modal-body').scrollTop($('#embeddedSalesEntryModal div.modal-lg div.modal-body')[0].scrollHeight);
  });
}

/*
function updateDiscountTypeGui(idx, val) {
  document.getElementById("distype["+idx+"]").innerHTML = val + "&emsp;<span class=\"caret\"></span>";
  document.getElementById("disctype["+idx+"]").value = val;
}
*/

$(document).ready(function() {

  vueSalesEntryModal = new Vue({
    el : '#embeddedSalesEntryModal',
    data : {
      modal : {
        readonly : true,
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
        currency_icon : '',
        product : [ ],
        warehouse : [ ],
        billing_address : [ ],
        shipping_address : [ ],
        history : [ ],
        shipment_info : [ ]
      },
      form : {
        id : 0,
        increment : '',
        type : '',
        customer : '',
        reserved_receivable_title : '',
        inputdate : '',
        payment : 0,
        expiration : '',
        incoterm : '',
        status : '',
        contact : 0,
        reference : '',
        currency : 0,
        staff : 0,
        tax_rate : 0,
        via : '',
        show_bank_account : 0,
        show_discount : 0,
        email_when_invoiced : 0,
        palletized : 0,
        warehouse : 0,
        billing_address : 0,
        shipping_address : 0,
        notes : '',
        line : [ ],
        product : [ ],
        display : [ ],
        unitprice : [ ],
        description : [ ],
        quantity : [ ],
        disctype : [ ],
        discount : [ ],
        taxable : [ ],
        subtotal : [ ],
        untaxed_subtotal : '',
        taxed_subtotal : '',
        tax_amount : '',
        grand_total : '',
      },
      errors : { }
    },
    computed : {
      billing_address : function() {
        let address = this.modal.billing_address.find(o => { return o.id == this.form.billing_address; });
        return (address === undefined) ? { id : 0, street : "", unit : "", city : "", district : "", state : "", country : "", zipcode : "" } : address;
      },
      shipping_address : function() {
        let address = this.modal.shipping_address.find(o => { return o.id == this.form.shipping_address; });
        return (address === undefined) ? { id : 0, street : "", unit : "", city : "", district : "", state : "", country : "", zipcode : "" } : address;
      }
    },
    mounted : function() {
      // disable ENTER
      $('#embeddedSalesEntryModal form#sales_order').keypress(function (e) {
        if (e.keyCode == 13) { e.preventDefault(); }
      });
      $(document).keydown(function (e) {
        if (e.keyCode == 27) {
          if ($('#embeddedSalesEntryModal #historyModal').hasClass("in")) {
            $('#embeddedSalesEntryModal #historyModal').modal('hide');
          } else if ($('#embeddedSalesEntryModal #aggregationModal').hasClass("in")) {
              $('#embeddedSalesEntryModal #aggregationModal').modal('hide');
          } else if ($('#embeddedSalesEntryModal #shipmentModal').hasClass("in")) {
              $('#embeddedSalesEntryModal #shipmentModal').modal('hide');
          } else if ($('#embeddedSalesEntryModal').hasClass("in")) {
            $('#embeddedSalesEntryModal').modal('hide');
          }
        }
      });
    }
  });

});
