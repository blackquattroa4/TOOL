// use in conjunction with ../embedded_modal/purchase_quote.blade.php

var vuePurchaseQuoteModal = null;

function addNewPurchaseQuoteLine() {
  vuePurchaseQuoteModal.form.line.push(0);
  vuePurchaseQuoteModal.form.product.push(Object.keys(vuePurchaseQuoteDataSource.selection_product[vuePurchaseQuoteModal.form.entity])[0]);
  vuePurchaseQuoteModal.form.display.push(Object.values(vuePurchaseQuoteDataSource.selection_product[vuePurchaseQuoteModal.form.entity])[0].display);
  vuePurchaseQuoteModal.form.unitprice.push(Object.values(vuePurchaseQuoteDataSource.selection_product[vuePurchaseQuoteModal.form.entity])[0].unit_price);
  vuePurchaseQuoteModal.form.description.push(Object.values(vuePurchaseQuoteDataSource.selection_product[vuePurchaseQuoteModal.form.entity])[0].description);
  vuePurchaseQuoteModal.form.quantity.push(1);
}

function fillPurchaseQuoteDefaultValue(index) {
  let pid = vuePurchaseQuoteModal.form.product[index];
  vuePurchaseQuoteModal.form.display[index] = vuePurchaseQuoteDataSource.selection_product[vuePurchaseQuoteModal.form.entity][pid]["display"];
  vuePurchaseQuoteModal.form.description[index] = vuePurchaseQuoteDataSource.selection_product[vuePurchaseQuoteModal.form.entity][pid]["description"];
  vuePurchaseQuoteModal.form.unitprice[index] = vuePurchaseQuoteDataSource.selection_product[vuePurchaseQuoteModal.form.entity][pid]["unitprice"];
}

function purchaseQuoteEntityChange() {
  vuePurchaseQuoteModal.form.payment = vuePurchaseQuoteModal.modal.entity[vuePurchaseQuoteModal.form.entity].payment;
  vuePurchaseQuoteModal.form.currency = vuePurchaseQuoteModal.modal.entity[vuePurchaseQuoteModal.form.entity].currency;
  vuePurchaseQuoteModal.form.contact = vuePurchaseQuoteModal.modal.entity[vuePurchaseQuoteModal.form.entity].contact;
}

function loadPurchaseQuoteWithAjax(quote_id) {
  // use ajax to load quote #id
  return $.ajax({
    type: 'GET',
    url: '/purchase-quote/' + quote_id + '/ajax',
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
      vuePurchaseQuoteModal.errors = result['errors'];
    } else {
      vuePurchaseQuoteModal.errors = { general : "System failure" };
    }
  });
}

function populatePurchaseQuoteModalWithAjaxResult(result) {
  vuePurchaseQuoteModal.modal.readonly = result.readonly;
  vuePurchaseQuoteModal.modal.history = result.history;
  vuePurchaseQuoteModal.modal.csrf = result.csrf;
  vuePurchaseQuoteModal.modal.action = result.action;
  vuePurchaseQuoteModal.modal.post_url = '';
  vuePurchaseQuoteModal.modal.title = result.title;
  vuePurchaseQuoteModal.modal.entity = result.entities;
  vuePurchaseQuoteModal.modal.contact = result.contacts;
  vuePurchaseQuoteModal.modal.payment = result.payments;
  vuePurchaseQuoteModal.modal.staff = result.staffs;
  vuePurchaseQuoteModal.modal.currency = result.currencies;
  vuePurchaseQuoteModal.modal.product = result.products;
  vuePurchaseQuoteModal.form.id = result.id;
  vuePurchaseQuoteModal.form.increment = result.increment;
  vuePurchaseQuoteModal.form.entity = result.entity;
  vuePurchaseQuoteModal.form.inputdate = result.inputdate;
  vuePurchaseQuoteModal.form.expiration = result.expiration;
  vuePurchaseQuoteModal.form.payment = result.payment;
  vuePurchaseQuoteModal.form.incoterm = result.incoterm;
  vuePurchaseQuoteModal.form.contact = result.contact;
  vuePurchaseQuoteModal.form.staff = result.staff;
  vuePurchaseQuoteModal.form.currency = result.currency;
  vuePurchaseQuoteModal.form.reference = result.reference;
  vuePurchaseQuoteModal.form.line = result.line;
  vuePurchaseQuoteModal.form.product = result.product;
  vuePurchaseQuoteModal.form.display = result.display;
  vuePurchaseQuoteModal.form.unitprice = result.unitprice;
  vuePurchaseQuoteModal.form.description = result.description;
  vuePurchaseQuoteModal.form.quantity = result.quantity;
}

function viewPurchaseQuoteInModal(quote_id) {
  let jqxhr = loadPurchaseQuoteWithAjax(quote_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      result['data']['readonly'] = true;
      result['data']['action'] = { };
      result['data']['action'][vuePurchaseQuoteDataSource.button_pdf] = "printPostAjax";
      result['data']['title'] = vuePurchaseQuoteDataSource.text_view_purchase_quote;
      result['data']['entities'] = vuePurchaseQuoteDataSource.selection_entity;
      result['data']['contacts'] = vuePurchaseQuoteDataSource.selection_contact;
      result['data']['payments'] = vuePurchaseQuoteDataSource.selection_payment;
      result['data']['staffs'] = vuePurchaseQuoteDataSource.selection_staff;
      result['data']['currencies'] = vuePurchaseQuoteDataSource.selection_currency;
      result['data']['products'] = vuePurchaseQuoteDataSource.selection_product;

      populatePurchaseQuoteModalWithAjaxResult(result['data']);
      vuePurchaseQuoteModal.errors = [];

      Vue.nextTick(function () {
        // unbind date-picker
        $('#embeddedPurchaseQuoteModal #inputdate').datepicker('destroy');
        $('#embeddedPurchaseQuoteModal #inputdate').removeClass('hasDatepicker');
        $('#embeddedPurchaseQuoteModal #expiration').datepicker('destroy');
        $('#embeddedPurchaseQuoteModal #expiration').removeClass('hasDatepicker');
      });

      // show modal
      $('#embeddedPurchaseQuoteModal').modal('show');
    }
  });
}

function printPostAjax() {
  $.ajax({
    url: '/purchase-quote/print/' + vuePurchaseQuoteModal.form.id + '/ajax',
    data: {
      _token : vuePurchaseQuoteModal.modal.csrf
    },
    method: 'POST',
    xhrFields: {
        responseType: 'blob'
    },
    success: function (data) {
      var a = document.createElement('a');
      var url = window.URL.createObjectURL(data);
      a.href = url;
      a.download = 'Purchase Quote #' + vuePurchaseQuoteModal.form.increment + '.pdf';
      document.body.append(a);
      a.click();
      a.remove();
      window.URL.revokeObjectURL(url);
    }
  });
}

function createPurchaseQuoteInModal() {
  let jqxhr = loadPurchaseQuoteWithAjax(0);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      let supplierId = Object.keys(vuePurchaseQuoteDataSource.selection_entity)[0];
      result['data']['readonly'] = false;
      result['data']['action'] = { };
      result['data']['action'][vuePurchaseQuoteDataSource.button_submit] = "createPostAjax";
      result['data']['title'] = vuePurchaseQuoteDataSource.text_create_purchase_quote;
      result['data']['entities'] = vuePurchaseQuoteDataSource.selection_entity;
      result['data']['contacts'] = vuePurchaseQuoteDataSource.selection_contact;
      result['data']['payments'] = vuePurchaseQuoteDataSource.selection_payment;
      result['data']['staffs'] = vuePurchaseQuoteDataSource.selection_staff;
      result['data']['currencies'] = vuePurchaseQuoteDataSource.selection_currency;
      result['data']['products'] = vuePurchaseQuoteDataSource.selection_product;
      result['data']['entity'] = supplierId;
      result['data']['payment'] = vuePurchaseQuoteDataSource.selection_entity[supplierId].payment;
      result['data']['contact'] = vuePurchaseQuoteDataSource.selection_entity[supplierId].contact;
      result['data']['currency'] = vuePurchaseQuoteDataSource.selection_entity[supplierId].currency;

      populatePurchaseQuoteModalWithAjaxResult(result['data']);
      vuePurchaseQuoteModal.errors = [];

      Vue.nextTick(function () {
        // unbind date-picker
        $('#embeddedPurchaseQuoteModal #inputdate').datepicker('destroy');
        $('#embeddedPurchaseQuoteModal #inputdate').removeClass('hasDatepicker');
        $('#embeddedPurchaseQuoteModal #expiration').datepicker('destroy');
        $('#embeddedPurchaseQuoteModal #expiration').removeClass('hasDatepicker');
        // rebind date-picker
        $('#embeddedPurchaseQuoteModal #inputdate').datepicker().bind('change', function(event) {
          vuePurchaseQuoteModal.form.inputdate = $(this).val();
        });
        $('#embeddedPurchaseQuoteModal #expiration').datepicker().bind('change', function(event) {
          vuePurchaseQuoteModal.form.expiration = $(this).val();
        });
      });

      // show modal
      $('#embeddedPurchaseQuoteModal').modal('show');
    }
  });
}

function createPostAjax() {
  $.ajax({
    type : 'POST',
    url : '/purchase-quote/create/ajax',
    enctype : 'multipart/form-data',
    data : new FormData($('#embeddedPurchaseQuoteModal form')[0]),
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
      $('#embeddedPurchaseQuoteModal').modal('hide');
      // update order/return table
      if ('insertCallback' in vuePurchaseQuoteDataSource) {
        vuePurchaseQuoteDataSource.insertCallback(data['data']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vuePurchaseQuoteModal.errors = data['errors'];
    }
  });
}

function updatePurchaseQuoteInModal(quote_id) {
  let jqxhr = loadPurchaseQuoteWithAjax(quote_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      result['data']['readonly'] = false;
      result['data']['action'] = { };
      result['data']['action'][vuePurchaseQuoteDataSource.button_update] = "updatePostAjax";
      result['data']['title'] = vuePurchaseQuoteDataSource.text_update_purchase_quote;
      result['data']['entities'] = vuePurchaseQuoteDataSource.selection_entity;
      result['data']['contacts'] = vuePurchaseQuoteDataSource.selection_contact;
      result['data']['payments'] = vuePurchaseQuoteDataSource.selection_payment;
      result['data']['staffs'] = vuePurchaseQuoteDataSource.selection_staff;
      result['data']['currencies'] = vuePurchaseQuoteDataSource.selection_currency;
      result['data']['products'] = vuePurchaseQuoteDataSource.selection_product;

      populatePurchaseQuoteModalWithAjaxResult(result['data']);
      vuePurchaseQuoteModal.errors = [];

      Vue.nextTick(function () {
        // unbind date-picker
        $('#embeddedPurchaseQuoteModal #inputdate').datepicker('destroy');
        $('#embeddedPurchaseQuoteModal #inputdate').removeClass('hasDatepicker');
        $('#embeddedPurchaseQuoteModal #expiration').datepicker('destroy');
        $('#embeddedPurchaseQuoteModal #expiration').removeClass('hasDatepicker');
        // rebind date-picker
        $('#embeddedPurchaseQuoteModal #inputdate').datepicker().bind('change', function(event) {
          vuePurchaseQuoteModal.form.inputdate = $(this).val();
        });
        $('#embeddedPurchaseQuoteModal #expiration').datepicker().bind('change', function(event) {
          vuePurchaseQuoteModal.form.expiration = $(this).val();
        });
      });

      // show modal
      $('#embeddedPurchaseQuoteModal').modal('show');
    }
  });
}

function updatePostAjax() {
  $.ajax({
    type : 'POST',
    url : '/purchase-quote/update/' + vuePurchaseQuoteModal.form.id + '/ajax',
    enctype : 'multipart/form-data',
    data : new FormData($('#embeddedPurchaseQuoteModal form')[0]),
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
      $('#embeddedPurchaseQuoteModal').modal('hide');
      // update order/return table
      if ('updateCallback' in vuePurchaseQuoteDataSource) {
        vuePurchaseQuoteDataSource.updateCallback(data['data']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vuePurchaseQuoteModal.errors = data['errors'];
    }
  });
}

function approvePurchaseQuoteInModal(quote_id) {
  let jqxhr = loadPurchaseQuoteWithAjax(quote_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      result['data']['readonly'] = true;
      result['data']['action'] = { };
      result['data']['action'][vuePurchaseQuoteDataSource.button_approve] = "approvePostAjax";
      result['data']['action'][vuePurchaseQuoteDataSource.button_reject] = "rejectPostAjax";
      result['data']['title'] = vuePurchaseQuoteDataSource.text_approve_purchase_quote;
      result['data']['entities'] = vuePurchaseQuoteDataSource.selection_entity;
      result['data']['contacts'] = vuePurchaseQuoteDataSource.selection_contact;
      result['data']['payments'] = vuePurchaseQuoteDataSource.selection_payment;
      result['data']['staffs'] = vuePurchaseQuoteDataSource.selection_staff;
      result['data']['currencies'] = vuePurchaseQuoteDataSource.selection_currency;
      result['data']['products'] = vuePurchaseQuoteDataSource.selection_product;

      populatePurchaseQuoteModalWithAjaxResult(result['data']);
      vuePurchaseQuoteModal.errors = [];

      Vue.nextTick(function () {
        // unbind date-picker
        $('#embeddedPurchaseQuoteModal #inputdate').datepicker('destroy');
        $('#embeddedPurchaseQuoteModal #inputdate').removeClass('hasDatepicker');
        $('#embeddedPurchaseQuoteModal #expiration').datepicker('destroy');
        $('#embeddedPurchaseQuoteModal #expiration').removeClass('hasDatepicker');
      });

      // show modal
      $('#embeddedPurchaseQuoteModal').modal('show');
    }
  });
}

function approvePostAjax() {
  $.ajax({
    type : 'POST',
    url : '/purchase-quote/approve/' + vuePurchaseQuoteModal.form.id + '/ajax',
    data : {
      _token : vuePurchaseQuoteModal.modal.csrf,
      submit : 'approve'
    },
    beforeSend: function(data) {
      $('.ajax-processing').removeClass('hidden');
    },
  }).done(function(data) {
    // data is already an object, no need to parse it.
    if (data['success']) {
      $('#embeddedPurchaseQuoteModal').modal('hide');
      // update order/return table
      if ('updateCallback' in vuePurchaseQuoteDataSource) {
        vuePurchaseQuoteDataSource.updateCallback(data['data']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vuePurchaseQuoteModal.errors = data['errors'];
    }
  });
}

function rejectPostAjax() {
  $.ajax({
    type : 'POST',
    url : '/purchase-quote/approve/' + vuePurchaseQuoteModal.form.id + '/ajax',
    data : {
      _token : vuePurchaseQuoteModal.modal.csrf,
      submit : 'disapprove'
    },
    beforeSend: function(data) {
      $('.ajax-processing').removeClass('hidden');
    },
  }).done(function(data) {
    // data is already an object, no need to parse it.
    if (data['success']) {
      $('#embeddedPurchaseQuoteModal').modal('hide');
      // update order/return table
      if ('updateCallback' in vuePurchaseQuoteDataSource) {
        vuePurchaseQuoteDataSource.updateCallback(data['data']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vuePurchaseQuoteModal.errors = data['errors'];
    }
  });
}

$(document).ready(function() {

  vuePurchaseQuoteModal = new Vue({
    el : '#embeddedPurchaseQuoteModal',
    data : {
      modal : {
        readonly : false,
        history : [ ],
        csrf : '',
        action : [ ],
        post_url : '',
        title : '',
        entity : [ ],
        contact : [ ],
        payment : [ ],
        staff : [ ],
        currency : [ ],
        product : [ ],
      },
      form : {
        id : 0,
        increment : '',
        entity : 0,
        inputdate : '',
        expiration : '',
        payment : 0,
        incoterm : '',
        contact : 0,
        staff : 0,
        currency : 0,
        reference : '',
        line : [ ],
        product : [ ],
        display : [ ],
        unitprice : [ ],
        description : [ ],
        quantity : [ ]
      },
      errors : { }
    },
    mounted : function() {
      // disable ENTER
      $('#embeddedPurchaseQuoteModal form').keypress(function (e) {
        if (e.keyCode == 13) { e.preventDefault(); }
      });
      $(document).keydown(function (e) {
        if (e.keyCode == 27) {
          if ($('#embeddedPurchaseQuoteModal #historyModal').hasClass("in")) {
            $('#embeddedPurchaseQuoteModal #historyModal').modal('hide');
          } else if ($('#embeddedPurchaseQuoteModal').hasClass("in")) {
            $('#embeddedPurchaseQuoteModal').modal('hide');
          }
        }
      });
    },
  });

});
