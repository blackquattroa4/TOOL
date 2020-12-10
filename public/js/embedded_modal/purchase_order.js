// use in conjunction with ../embedded_modal/purchase_order.blade.php

var vuePurchaseEntryModal = null;

function loadPurchaseEntryWithAjax(order_id) {
  // use ajax to load transactable #id
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
      vuePurchaseEntryModal.errors = result['errors'];
    } else {
      vuePurchaseEntryModal.errors = { general : "System failure" };
    }
  });
}

function populatePurchaseEntryModalWithAjaxResult(result) {
  vuePurchaseEntryModal.modal.readonly = result.readonly;
  vuePurchaseEntryModal.modal.title = result.title;
  vuePurchaseEntryModal.modal.csrf = result.csrf;
  vuePurchaseEntryModal.modal.action = result.action;
  vuePurchaseEntryModal.modal.post_url = result.post_url;
  vuePurchaseEntryModal.modal.supplier = result.entities;
  vuePurchaseEntryModal.modal.payment = result.payments;
  vuePurchaseEntryModal.modal.contact = result.contacts;
  vuePurchaseEntryModal.modal.staff = result.staffs;
  vuePurchaseEntryModal.modal.currency = result.currencies;
  vuePurchaseEntryModal.modal.currency_min = result.currency_min;
  vuePurchaseEntryModal.modal.currency_regex = result.currency_regex;
  vuePurchaseEntryModal.modal.currency_fdigit = result.currency_fdigit;
  vuePurchaseEntryModal.modal.currency_symbol = result.currency_symbol;
  vuePurchaseEntryModal.modal.product = result.products;
  vuePurchaseEntryModal.modal.warehouse = result.warehouses;
  vuePurchaseEntryModal.modal.billing_address = result.billing_addresses;
  vuePurchaseEntryModal.modal.shipping_address = result.shipping_addresses;
  vuePurchaseEntryModal.modal.history = result.history;
  vuePurchaseEntryModal.form.id = result.id;
  vuePurchaseEntryModal.form.type = result.type;
  vuePurchaseEntryModal.form.increment = result.increment;
  vuePurchaseEntryModal.form.supplier = result.supplier;
  vuePurchaseEntryModal.form.inputdate = result.inputdate;
  vuePurchaseEntryModal.form.payment = result.payment;
  vuePurchaseEntryModal.form.reference = result.reference;
  vuePurchaseEntryModal.form.incoterm = result.incoterm;
  vuePurchaseEntryModal.form.via = result.via;
  vuePurchaseEntryModal.form.contact = result.contact;
  vuePurchaseEntryModal.form.staff = result.staff;
  vuePurchaseEntryModal.form.currency = result.currency;
  vuePurchaseEntryModal.form.billing_address = result.billing_address;
  vuePurchaseEntryModal.form.shipping_address = result.shipping_address;
  vuePurchaseEntryModal.form.line = result.line;
  vuePurchaseEntryModal.form.product = result.product;
  vuePurchaseEntryModal.form.display = result.display;
  vuePurchaseEntryModal.form.ivcost = result.ivcost;
  vuePurchaseEntryModal.form.unitprice = result.unitprice;
  vuePurchaseEntryModal.form.description = result.description;
  vuePurchaseEntryModal.form.quantity = result.quantity;
  vuePurchaseEntryModal.form.ddate = result.ddate;
  vuePurchaseEntryModal.form.warehouse = result.warehouse;
  vuePurchaseEntryModal.form.taxable = result.taxable;
  vuePurchaseEntryModal.form.subtotal = result.subtotal;
  vuePurchaseEntryModal.form.untaxed_subtotal = result.untaxed_subtotal;
  vuePurchaseEntryModal.form.taxed_subtotal = result.taxed_subtotal;
  vuePurchaseEntryModal.form.tax_amount = result.tax_amount;
  vuePurchaseEntryModal.form.grand_total = result.grand_total;
}

function createPurchaseEntryInModal(type) {
  let jqxhr = loadPurchaseEntryWithAjax(0);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      let supplierId = Object.keys(vuePurchaseEntryDataSource.selection_entity)[0];
      // populate modal
      result['data']['readonly'] = false;
      // key can not be computed dynamically, create an empty object and stuff attribute
      result['data']['title'] = vuePurchaseEntryDataSource["text_create_purchase_" + type];
      result['data']['action'] = {};
      result['data']['action'][vuePurchaseEntryDataSource.button_submit] = 'createPurchaseEntryPost';
      result['data']['post_url'] = '';
      result['data']['entities'] = vuePurchaseEntryDataSource.selection_entity;
      result['data']['payments'] = vuePurchaseEntryDataSource.selection_payment;
      result['data']['contacts'] = vuePurchaseEntryDataSource.selection_contact[supplierId];
      result['data']['staffs'] = vuePurchaseEntryDataSource.selection_staff;
      result['data']['currencies'] = vuePurchaseEntryDataSource.selection_currency;
      result['data']['currency_min'] = vuePurchaseEntryDataSource.selection_entity[supplierId].min;
      result['data']['currency_regex'] = vuePurchaseEntryDataSource.selection_entity[supplierId].regex;
      result['data']['currency_fdigit'] = vuePurchaseEntryDataSource.selection_entity[supplierId].fdigit;
      result['data']['currency_symbol'] = vuePurchaseEntryDataSource.selection_entity[supplierId].symbol;
      result['data']['products'] = vuePurchaseEntryDataSource.selection_product[supplierId];
      result['data']['warehouses'] = vuePurchaseEntryDataSource.selection_warehouse;
      result['data']['billing_addresses'] = vuePurchaseEntryDataSource.selection_billing[supplierId];
      result['data']['shipping_addresses'] = vuePurchaseEntryDataSource.selection_shipping[supplierId];
      result['data']['history'] = [ ];
      result['data']['id'] = 0;
      result['data']['type'] = type;
      result['data']['increment'] = '????';
      result['data']['supplier'] = supplierId;
      result['data']['inputdate'] = vuePurchaseEntryDataSource.text_today;
      result['data']['payment'] = vuePurchaseEntryDataSource.selection_entity[supplierId].payment;
      result['data']['reference'] = '';
      result['data']['incoterm'] = '';
      result['data']['via'] = '';
      result['data']['contact'] = Object.values(vuePurchaseEntryDataSource.selection_contact[supplierId])[0].id;
      result['data']['staff'] =  vuePurchaseEntryDataSource.current_user_id;
      result['data']['currency'] = vuePurchaseEntryDataSource.selection_entity[supplierId].currency;
      result['data']['billing_address'] = Object.values(vuePurchaseEntryDataSource.selection_billing[supplierId])[0].id;
      result['data']['shipping_address'] = Object.values(vuePurchaseEntryDataSource.selection_shipping[supplierId])[0].id;
      result['data']['line'] = [ ];
      result['data']['product'] = [ ];
      result['data']['display'] = [ ];
      result['data']['ivcost'] = [ ];
      result['data']['unitprice'] = [ ];
      result['data']['description'] = [ ];
      result['data']['quantity'] = [ ];
      result['data']['ddate'] = [ ];
      result['data']['warehouse'] = [ ];
      result['data']['taxable'] = [ ];
      result['data']['subtotal'] = [ ];
      let zero = 0;
      result['data']['untaxed_subtotal'] = zero.toLocaleString(vuePurchaseEntryDataSource.selection_entity[supplierId].regex, { style: 'currency', currency: vuePurchaseEntryDataSource.selection_entity[supplierId].symbol });
      result['data']['taxed_subtotal'] = zero.toLocaleString(vuePurchaseEntryDataSource.selection_entity[supplierId].regex, { style: 'currency', currency: vuePurchaseEntryDataSource.selection_entity[supplierId].symbol });
      result['data']['tax_amount'] = zero.toLocaleString(vuePurchaseEntryDataSource.selection_entity[supplierId].regex, { style: 'currency', currency: vuePurchaseEntryDataSource.selection_entity[supplierId].symbol });
      result['data']['grand_total'] = zero.toLocaleString(vuePurchaseEntryDataSource.selection_entity[supplierId].regex, { style: 'currency', currency: vuePurchaseEntryDataSource.selection_entity[supplierId].symbol });

      populatePurchaseEntryModalWithAjaxResult(result['data']);
      vuePurchaseEntryModal.errors = [];

      Vue.nextTick(function () {
        // unbind date-picker
        $('#embeddedPurchaseEntryModal #inputdate').datepicker('destroy');
        $('#embeddedPurchaseEntryModal #inputdate').removeClass('hasDatepicker');
        $('#embeddedPurchaseEntryModal input[name^="ddate["]').datepicker('destroy');
        $('#embeddedPurchaseEntryModal input[name^="ddate["]').removeClass('hasDatepicker');
        // re-bind date-picker
        $('#embeddedPurchaseEntryModal #inputdate').datepicker().bind('change', function(event) {
          vuePurchaseEntryModal.form.inputdate = $(this).val();
        });
        $('#embeddedPurchaseEntryModal input[name^="ddate["]').datepicker().bind('change', function(event) {
          vuePurchaseEntryModal.form.ddate[$(this).data('line')] = $(this).val();
        });
      });

      // show modal
      $('#embeddedPurchaseEntryModal').modal('show');
    }
  });
}

function createPurchaseEntryPost() {
  // finish this function
  $.ajax({
    type : 'POST',
    url : '/purchase-entry/create/ajax',
    enctype : 'multipart/form-data',
    data : new FormData($('#embeddedPurchaseEntryModal form')[0]),
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
      $('#embeddedPurchaseEntryModal').modal('hide');
      // update expense table
      if ('insertCallback' in vuePurchaseEntryDataSource) {
        vuePurchaseEntryDataSource.insertCallback(data['data']['entry']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vuePurchaseEntryModal.errors = data['errors'];
    }
  });
}

function viewPurchaseEntryInModal(order_id) {
  let jqxhr = loadPurchaseEntryWithAjax(order_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      result['data']['readonly'] = true;
      // key can not be computed dynamically, create an empty object and stuff attribute
      result['data']['title'] = vuePurchaseEntryDataSource["text_view_purchase_" + result['data']['type']] + " #" + result['data']['increment'];
      result['data']['action'] = {};
      result['data']['action'][vuePurchaseEntryDataSource.button_pdf] = 'viewPurchaseEntryPost';
      result['data']['post_url'] = '';
      result['data']['entities'] = vuePurchaseEntryDataSource.selection_entity;
      result['data']['payments'] = vuePurchaseEntryDataSource.selection_payment;
      result['data']['contacts'] = vuePurchaseEntryDataSource.selection_contact[result['data']['supplier']];
      result['data']['staffs'] = vuePurchaseEntryDataSource.selection_staff;
      result['data']['currencies'] = vuePurchaseEntryDataSource.selection_currency;
      result['data']['products'] = vuePurchaseEntryDataSource.selection_product[result['data']['supplier']];
      result['data']['warehouses'] = vuePurchaseEntryDataSource.selection_warehouse;
      result['data']['billing_addresses'] = vuePurchaseEntryDataSource.selection_billing[result['data']['supplier']];
      result['data']['shipping_addresses'] = vuePurchaseEntryDataSource.selection_shipping[result['data']['supplier']];
      populatePurchaseEntryModalWithAjaxResult(result['data']);
      vuePurchaseEntryModal.errors = [];

      Vue.nextTick(function () {
        // unbind date-picker
        $('#embeddedPurchaseEntryModal #inputdate').datepicker('destroy');
        $('#embeddedPurchaseEntryModal #inputdate').removeClass('hasDatepicker');
        $('#embeddedPurchaseEntryModal input[name^="ddate["]').datepicker('destroy');
        $('#embeddedPurchaseEntryModal input[name^="ddate["]').removeClass('hasDatepicker');
      });

      // show modal
      $('#embeddedPurchaseEntryModal').modal('show');
    }
  });
}

function viewPurchaseEntryPost() {
  $.ajax({
    url: '/purchase-entry/print/' + vuePurchaseEntryModal.form.id + '/ajax',
    data: {
      _token : vuePurchaseEntryModal.modal.csrf
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
      a.download = 'Purchase ' + vuePurchaseEntryModal.form.type.charAt(0).toUpperCase() + vuePurchaseEntryModal.form.type.slice(1) + ' #' + vuePurchaseEntryModal.form.increment + '.pdf';
      document.body.append(a);
      a.click();
      a.remove();
      window.URL.revokeObjectURL(url);
    }
  });
}

function updatePurchaseEntryInModal(order_id) {
  let jqxhr = loadPurchaseEntryWithAjax(order_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      result['data']['readonly'] = false;
      // key can not be computed dynamically, create an empty object and stuff attribute
      result['data']['title'] = vuePurchaseEntryDataSource["text_update_purchase_" + result['data']['type']] + " #" + result['data']['increment'];
      result['data']['action'] = {};
      result['data']['action'][vuePurchaseEntryDataSource.button_update] = 'updatePurchaseEntryPost';
      result['data']['post_url'] = '';
      result['data']['entities'] = vuePurchaseEntryDataSource.selection_entity;
      result['data']['payments'] = vuePurchaseEntryDataSource.selection_payment;
      result['data']['contacts'] = vuePurchaseEntryDataSource.selection_contact[result['data']['supplier']];
      result['data']['staffs'] = vuePurchaseEntryDataSource.selection_staff;
      result['data']['currencies'] = vuePurchaseEntryDataSource.selection_currency;
      result['data']['products'] = vuePurchaseEntryDataSource.selection_product[result['data']['supplier']];
      result['data']['warehouses'] = vuePurchaseEntryDataSource.selection_warehouse;
      result['data']['billing_addresses'] = vuePurchaseEntryDataSource.selection_billing[result['data']['supplier']];
      result['data']['shipping_addresses'] = vuePurchaseEntryDataSource.selection_shipping[result['data']['supplier']];
      populatePurchaseEntryModalWithAjaxResult(result['data']);
      vuePurchaseEntryModal.errors = [];

      Vue.nextTick(function () {
        // unbind date-picker
        $('#embeddedPurchaseEntryModal #inputdate').datepicker('destroy');
        $('#embeddedPurchaseEntryModal #inputdate').removeClass('hasDatepicker');
        $('#embeddedPurchaseEntryModal input[name^="ddate["]').datepicker('desctroy');
        $('#embeddedPurchaseEntryModal input[name^="ddate["]').removeClass('hasDatepicker');
        // re-bind date-picker
        $('#embeddedPurchaseEntryModal #inputdate').datepicker().bind('change', function(event) {
          vuePurchaseEntryModal.form.inputdate = $(this).val();
        });
        $('#embeddedPurchaseEntryModal input[name^="ddate["]').datepicker().bind('change', function(event) {
          vuePurchaseEntryModal.form.ddate[$(this).data('line')] = $(this).val();
        });
      });

      // show modal
      $('#embeddedPurchaseEntryModal').modal('show');
    }
  });
}

function updatePurchaseEntryPost() {
  // finish this function
  $.ajax({
    type : 'POST',
    url : '/purchase-entry/update/' + vuePurchaseEntryModal.form.id + '/ajax',
    enctype : 'multipart/form-data',
    data : new FormData($('#embeddedPurchaseEntryModal form')[0]),
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
      $('#embeddedPurchaseEntryModal').modal('hide');
      // update expense table
      if ('updateCallback' in vuePurchaseEntryDataSource) {
        vuePurchaseEntryDataSource.updateCallback(data['data']['entry']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vuePurchaseEntryModal.errors = data['errors'];
    }
  });
}

function approvePurchaseEntryInModal(order_id) {
  let jqxhr = loadPurchaseEntryWithAjax(order_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      result['data']['readonly'] = true;
      // key can not be computed dynamically, create an empty object and stuff attribute
      result['data']['title'] = vuePurchaseEntryDataSource["text_approve_purchase_" + result['data']['type']] + " #" + result['data']['increment'];
      result['data']['action'] = {};
      result['data']['action'][vuePurchaseEntryDataSource.button_approve] = 'approvePurchaseEntryPost';
      result['data']['action'][vuePurchaseEntryDataSource.button_reject] = 'rejectPurchaseEntryPost';
      result['data']['post_url'] = '';
      result['data']['entities'] = vuePurchaseEntryDataSource.selection_entity;
      result['data']['payments'] = vuePurchaseEntryDataSource.selection_payment;
      result['data']['contacts'] = vuePurchaseEntryDataSource.selection_contact[result['data']['supplier']];
      result['data']['staffs'] = vuePurchaseEntryDataSource.selection_staff;
      result['data']['currencies'] = vuePurchaseEntryDataSource.selection_currency;
      result['data']['products'] = vuePurchaseEntryDataSource.selection_product[result['data']['supplier']];
      result['data']['warehouses'] = vuePurchaseEntryDataSource.selection_warehouse;
      result['data']['billing_addresses'] = vuePurchaseEntryDataSource.selection_billing[result['data']['supplier']];
      result['data']['shipping_addresses'] = vuePurchaseEntryDataSource.selection_shipping[result['data']['supplier']];
      populatePurchaseEntryModalWithAjaxResult(result['data']);
      vuePurchaseEntryModal.errors = [];

      Vue.nextTick(function () {
        // unbind date-picker
        $('#embeddedPurchaseEntryModal #inputdate').datepicker('destroy');
        $('#embeddedPurchaseEntryModal #inputdate').removeClass('hasDatepicker');
        $('#embeddedPurchaseEntryModal input[name^="ddate["]').datepicker('desctroy');
        $('#embeddedPurchaseEntryModal input[name^="ddate["]').removeClass('hasDatepicker');
      });

      // show modal
      $('#embeddedPurchaseEntryModal').modal('show');
    }
  });
}

function approvePurchaseEntryPost() {
  $.ajax({
    url: '/purchase-entry/approve/' + vuePurchaseEntryModal.form.id + '/ajax',
    data: {
      _token : vuePurchaseEntryModal.modal.csrf,
      decision : 'approve'
    },
    method: 'POST',
    beforeSend: function(data) {
      $('.ajax-processing').removeClass('hidden');
    },
  }).done(function(data) {
    // data is already an object, no need to parse it.
    if (data['success']) {
      $('#embeddedPurchaseEntryModal').modal('hide');
      // update expense table
      if ('updateCallback' in vuePurchaseEntryDataSource) {
        vuePurchaseEntryDataSource.updateCallback(data['data']['entry']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vuePurchaseEntryModal.errors = data['errors'];
    }
  });
}

function rejectPurchaseEntryPost() {
  $.ajax({
    url: '/purchase-entry/approve/' + vuePurchaseEntryModal.form.id + '/ajax',
    data: {
      _token : vuePurchaseEntryModal.modal.csrf,
      decision : 'disapprove'
    },
    method: 'POST',
    beforeSend: function(data) {
      $('.ajax-processing').removeClass('hidden');
    },
  }).done(function(data) {
    // data is already an object, no need to parse it.
    if (data['success']) {
      $('#embeddedPurchaseEntryModal').modal('hide');
      // update expense table
      if ('updateCallback' in vuePurchaseEntryDataSource) {
        vuePurchaseEntryDataSource.updateCallback(data['data']['entry']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vuePurchaseEntryModal.errors = data['errors'];
    }
  });
}

function purchaseReleasePostAjax(type, id) {
  $.ajax({
    // obtain csrf token first
    type: 'GET',
    url: '/dashboard/purchase/release/ajax',
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
        url: '/vrm/release' + type + '/' + id,
        data: {
            _token: result.data.csrf,
          },
        dataType: 'html',
      }).always(function(data) {
        $('.ajax-processing').addClass('hidden');
      }).done(function (data) {
        var result = JSON.parse(data);
        if (result['success']) {
          // update order/return table
          if ('updateCallback' in vuePurchaseEntryDataSource) {
            vuePurchaseEntryDataSource.updateCallback(result['data']);
          }
        }
      });
    }
  });
}

// not used for the moment
function voidPurchaseEntryInModal(order_id) {
  let jqxhr = loadPurchaseEntryWithAjax(order_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      result['data']['readonly'] = true;
      // key can not be computed dynamically, create an empty object and stuff attribute
      result['data']['title'] = vuePurchaseEntryDataSource["text_void_purchase_" + result['data']['type']] + " #" + result['data']['increment'];
      result['data']['action'] = {};
      result['data']['action'][vuePurchaseEntryDataSource.button_void] = 'voidPurchaseEntryPost';
      result['data']['post_url'] = '';
      result['data']['entities'] = vuePurchaseEntryDataSource.selection_entity;
      result['data']['payments'] = vuePurchaseEntryDataSource.selection_payment;
      result['data']['contacts'] = vuePurchaseEntryDataSource.selection_contact[result['data']['supplier']];
      result['data']['staffs'] = vuePurchaseEntryDataSource.selection_staff;
      result['data']['currencies'] = vuePurchaseEntryDataSource.selection_currency;
      result['data']['products'] = vuePurchaseEntryDataSource.selection_product[result['data']['supplier']];
      result['data']['warehouses'] = vuePurchaseEntryDataSource.selection_warehouse;
      result['data']['billing_addresses'] = vuePurchaseEntryDataSource.selection_billing[result['data']['supplier']];
      result['data']['shipping_addresses'] = vuePurchaseEntryDataSource.selection_shipping[result['data']['supplier']];
      populatePurchaseEntryModalWithAjaxResult(result['data']);
      vuePurchaseEntryModal.errors = [];

      Vue.nextTick(function () {
        // unbind date-picker
        $('#embeddedPurchaseEntryModal #inputdate').datepicker('destroy');
        $('#embeddedPurchaseEntryModal #inputdate').removeClass('hasDatepicker');
        $('#embeddedPurchaseEntryModal input[name^="ddate["]').datepicker('desctroy');
        $('#embeddedPurchaseEntryModal input[name^="ddate["]').removeClass('hasDatepicker');
      });

      // show modal
      $('#embeddedPurchaseEntryModal').modal('show');
    }
  });
}

// not used for the moment
function voidPurchaseEntryPost() {
  $.ajax({
    url: '/purchase-entry/void/' + vuePurchaseEntryModal.form.id + '/ajax',
    data: {
      _token : vuePurchaseEntryModal.modal.csrf,
    },
    method: 'POST',
    beforeSend: function(data) {
      $('.ajax-processing').removeClass('hidden');
    },
  }).done(function(data) {
    // data is already an object, no need to parse it.
    if (data['success']) {
      $('#embeddedPurchaseEntryModal').modal('hide');
      // update expense table
      if ('updateCallback' in vuePurchaseEntryDataSource) {
        vuePurchaseEntryDataSource.updateCallback(data['data']['entry']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vuePurchaseEntryModal.errors = data['errors'];
    }
  });
}

function changePurchaseEntryModalEntity() {
  // payment term
  vuePurchaseEntryModal.form.payment = vuePurchaseEntryDataSource.selection_entity[vuePurchaseEntryModal.form.supplier].payment;
  // order currency
  vuePurchaseEntryModal.form.currency = vuePurchaseEntryDataSource.selection_entity[vuePurchaseEntryModal.form.supplier].currency;
  // change contact
  vuePurchaseEntryModal.modal.contact = vuePurchaseEntryDataSource.selection_contact[vuePurchaseEntryModal.form.supplier];
  vuePurchaseEntryModal.form.contact = vuePurchaseEntryModal.modal.contact[0].id;
  // change billing Address
  vuePurchaseEntryModal.modal.billing_address = vuePurchaseEntryDataSource.selection_billing[vuePurchaseEntryModal.form.supplier];
  vuePurchaseEntryModal.form.billing_address = vuePurchaseEntryModal.modal.billing_address[0].id;
  // change shipping address
  vuePurchaseEntryModal.modal.shipping_address = vuePurchaseEntryDataSource.selection_shipping[vuePurchaseEntryModal.form.supplier];
  vuePurchaseEntryModal.form.shipping_address = vuePurchaseEntryModal.modal.shipping_address[0].id;
  // change product selection
  vuePurchaseEntryModal.modal.product = vuePurchaseEntryDataSource.selection_product[vuePurchaseEntryModal.form.supplier];
}

function updatePurchaseEntryLineItem(line_id) {
  let product_id = vuePurchaseEntryModal.form.product[line_id];
  vuePurchaseEntryModal.form.display[line_id] = vuePurchaseEntryModal.modal.product[product_id].display;
  vuePurchaseEntryModal.form.ivcost[line_id] = vuePurchaseEntryModal.modal.product[product_id].unit_price;
  vuePurchaseEntryModal.form.unitprice[line_id] = vuePurchaseEntryModal.modal.product[product_id].unit_price;
  vuePurchaseEntryModal.form.description[line_id] = vuePurchaseEntryModal.modal.product[product_id].description;
  vuePurchaseEntryModal.form.quantity[line_id] = 0;
  //vuePurchaseEntryModal.form.ddate[line_id] = ;
  //vuePurchaseEntryModal.form.warehouse[line_id] = ;
  vuePurchaseEntryModal.form.taxable[line_id] = false;

  updatePurchaseEntrySubtotal(line_id);
}

function updatePurchaseEntryTotal() {
  let untaxed = 0, taxed = 0, tax = 0;

  for(index in vuePurchaseEntryModal.form.subtotal) {
    if (vuePurchaseEntryModal.form.taxable[index]) {
      taxed += parseFloat(vuePurchaseEntryModal.form.subtotal[index]);
      tax += 0;
    } else {
      untaxed += parseFloat(vuePurchaseEntryModal.form.subtotal[index]);
    }
  }

  vuePurchaseEntryModal.form.untaxed_subtotal = untaxed.toLocaleString(vuePurchaseEntryModal.modal.currency_regex, { style: 'currency', currency: vuePurchaseEntryModal.modal.currency_symbol });
  vuePurchaseEntryModal.form.taxed_subtotal = taxed.toLocaleString(vuePurchaseEntryModal.modal.currency_regex, { style: 'currency', currency: vuePurchaseEntryModal.modal.currency_symbol });
  vuePurchaseEntryModal.form.tax_amount = tax.toLocaleString(vuePurchaseEntryModal.modal.currency_regex, { style: 'currency', currency: vuePurchaseEntryModal.modal.currency_symbol });
  vuePurchaseEntryModal.form.grand_total = (taxed + untaxed + tax).toLocaleString(vuePurchaseEntryModal.modal.currency_regex, { style: 'currency', currency: vuePurchaseEntryModal.modal.currency_symbol });

  // hideThenShowHint('.show-balance', '.show-balance-hint');
}

function updatePurchaseEntrySubtotal(index) {
  vuePurchaseEntryModal.form.subtotal[index] = (parseFloat(vuePurchaseEntryModal.form.unitprice[index]) * parseFloat(vuePurchaseEntryModal.form.quantity[index])).toFixed(vuePurchaseEntryModal.modal.currency_fdigit);

  updatePurchaseEntryTotal();
}

function addNewPurchaseEntryLine() {
  vuePurchaseEntryModal.form.line.push(0);
  vuePurchaseEntryModal.form.product.push(Object.keys(vuePurchaseEntryModal.modal.product)[0]);
  vuePurchaseEntryModal.form.display.push(Object.values(vuePurchaseEntryModal.modal.product)[0].display);
  vuePurchaseEntryModal.form.ivcost.push(Object.values(vuePurchaseEntryModal.modal.product)[0].unit_price);
  vuePurchaseEntryModal.form.unitprice.push(Object.values(vuePurchaseEntryModal.modal.product)[0].unit_price);
  vuePurchaseEntryModal.form.description.push(Object.values(vuePurchaseEntryModal.modal.product)[0].description);
  vuePurchaseEntryModal.form.quantity.push(0);
  vuePurchaseEntryModal.form.ddate.push(vuePurchaseEntryDataSource.text_today);
  vuePurchaseEntryModal.form.warehouse.push(Object.values(vuePurchaseEntryModal.modal.warehouse)[0].id);
  vuePurchaseEntryModal.form.taxable.push(0);
  vuePurchaseEntryModal.form.subtotal.push(0);
  // wait until next tick (component rendered) to install date selector
  let id = Object.keys(vuePurchaseEntryModal.form.line).pop();
  Vue.nextTick(function () {
    $(vuePurchaseEntryModal.$refs["ddate"+id]).datepicker().bind('change', function() {
      vuePurchaseEntryModal.form.ddate[$(this).data('line')] = $(this).val();
    });
    $('#embeddedPurchaseEntryModal div.modal-lg div.modal-body').scrollTop($('#embeddedPurchaseEntryModal div.modal-lg div.modal-body')[0].scrollHeight);
  });
}

$(document).ready(function() {

  vuePurchaseEntryModal = new Vue({
    el : '#embeddedPurchaseEntryModal',
    data : {
      modal : {
        readonly : true,
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
        billing_address : [ ],
        shipping_address : [ ],
        history : [ ]
      },
      form : {
        id : 0,
        type : '',
        increment : '',
        supplier : 0,
        inputdate : '',
        payment : 0,
        reference : '',
        incoterm : '',
        via : '',
        contact : 0,
        staff : 0,
        currency : 0,
        billing_address : 0,
        shipping_address : 0,
        line : [ ],
        product : [ ],
        display : [ ],
        ivcost : [ ],
        unitprice : [ ],
        description : [ ],
        quantity : [ ],
        ddate : [ ],
        warehouse : [ ],
        taxable : [ ],
        subtotal : [ ],
        untaxed_subtotal : '',
        taxed_subtotal : '',
        tax_amount : '',
        grand_total : ''
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
      $('#embeddedPurchaseEntryModal form#purchase_order').keypress(function (e) {
        if (e.keyCode == 13) { e.preventDefault(); }
      });
      // ESC only works at document level and on keydown
      $(document).keydown(function (e) {
        if (e.keyCode == 27) {
          if ($('#embeddedPurchaseEntryModal #historyModal').hasClass("in")) {
            $('#embeddedPurchaseEntryModal #historyModal').modal('hide');
          } else if ($('#embeddedPurchaseEntryModal #aggregationModal').hasClass("in")) {
              $('#embeddedPurchaseEntryModal #aggregationModal').modal('hide');
          } else if ($('#embeddedPurchaseEntryModal').hasClass("in")) {
            $('#embeddedPurchaseEntryModal').modal('hide');
          }
        }
      });
    }
  });

});
