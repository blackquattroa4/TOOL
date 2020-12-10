// use in conjunction with ../embedded_modal/sales_quote.blade.php

var vueSalesQuoteModal = null;

function addNewSalesQuoteLine() {
  vueSalesQuoteModal.form.line.push(0);
  vueSalesQuoteModal.form.product.push(Object.keys(vueSalesQuoteDataSource.selection_product[vueSalesQuoteModal.form.entity])[0]);
  vueSalesQuoteModal.form.display.push(Object.values(vueSalesQuoteDataSource.selection_product[vueSalesQuoteModal.form.entity])[0].display);
  vueSalesQuoteModal.form.unitprice.push(Object.values(vueSalesQuoteDataSource.selection_product[vueSalesQuoteModal.form.entity])[0].unit_price);
  vueSalesQuoteModal.form.description.push(Object.values(vueSalesQuoteDataSource.selection_product[vueSalesQuoteModal.form.entity])[0].description);
  vueSalesQuoteModal.form.quantity.push(1);
}

function fillSalesQuoteDefaultValue(index) {
  let pid = vueSalesQuoteModal.form.product[index];
  vueSalesQuoteModal.form.display[index] = vueSalesQuoteDataSource.selection_product[vueSalesQuoteModal.form.entity][pid]["display"];
  vueSalesQuoteModal.form.description[index] = vueSalesQuoteDataSource.selection_product[vueSalesQuoteModal.form.entity][pid]["description"];
  vueSalesQuoteModal.form.unitprice[index] = vueSalesQuoteDataSource.selection_product[vueSalesQuoteModal.form.entity][pid]["unitprice"];
}

function salesQuoteEntityChange() {
  vueSalesQuoteModal.form.payment = vueSalesQuoteModal.modal.entity[vueSalesQuoteModal.form.entity].payment;
  vueSalesQuoteModal.form.currency = vueSalesQuoteModal.modal.entity[vueSalesQuoteModal.form.entity].currency;
  vueSalesQuoteModal.form.contact = vueSalesQuoteModal.modal.entity[vueSalesQuoteModal.form.entity].contact;
}

function loadSalesQuoteWithAjax(quote_id) {
  // use ajax to load quote #id
  return $.ajax({
    type: 'GET',
    url: '/sales-quote/' + quote_id + '/ajax',
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
      vueSalesQuoteModal.errors = result['errors'];
    } else {
      vueSalesQuoteModal.errors = { general : "System failure" };
    }
  });
}

function populateSalesQuoteModalWithAjaxResult(result) {
  vueSalesQuoteModal.modal.readonly = result.readonly;
  vueSalesQuoteModal.modal.history = result.history;
  vueSalesQuoteModal.modal.csrf = result.csrf;
  vueSalesQuoteModal.modal.action = result.action;
  vueSalesQuoteModal.modal.post_url = '';
  vueSalesQuoteModal.modal.title = result.title;
  vueSalesQuoteModal.modal.entity = result.entities;
  vueSalesQuoteModal.modal.contact = result.contacts;
  vueSalesQuoteModal.modal.payment = result.payments;
  vueSalesQuoteModal.modal.staff = result.staffs;
  vueSalesQuoteModal.modal.currency = result.currencies;
  vueSalesQuoteModal.modal.product = result.products;
  vueSalesQuoteModal.form.id = result.id;
  vueSalesQuoteModal.form.increment = result.increment;
  vueSalesQuoteModal.form.entity = result.entity;
  vueSalesQuoteModal.form.inputdate = result.inputdate;
  vueSalesQuoteModal.form.expiration = result.expiration;
  vueSalesQuoteModal.form.payment = result.payment;
  vueSalesQuoteModal.form.incoterm = result.incoterm;
  vueSalesQuoteModal.form.contact = result.contact;
  vueSalesQuoteModal.form.staff = result.staff;
  vueSalesQuoteModal.form.currency = result.currency;
  vueSalesQuoteModal.form.reference = result.reference;
  vueSalesQuoteModal.form.line = result.line;
  vueSalesQuoteModal.form.product = result.product;
  vueSalesQuoteModal.form.display = result.display;
  vueSalesQuoteModal.form.unitprice = result.unitprice;
  vueSalesQuoteModal.form.description = result.description;
  vueSalesQuoteModal.form.quantity = result.quantity;
}

function viewSalesQuoteInModal(quote_id) {
  let jqxhr = loadSalesQuoteWithAjax(quote_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      result['data']['readonly'] = true;
      result['data']['action'] = { };
      result['data']['action'][vueSalesQuoteDataSource.button_pdf] = "printPostAjax";
      result['data']['title'] = vueSalesQuoteDataSource.text_view_sales_quote;
      result['data']['entities'] = vueSalesQuoteDataSource.selection_entity;
      result['data']['contacts'] = vueSalesQuoteDataSource.selection_contact;
      result['data']['payments'] = vueSalesQuoteDataSource.selection_payment;
      result['data']['staffs'] = vueSalesQuoteDataSource.selection_staff;
      result['data']['currencies'] = vueSalesQuoteDataSource.selection_currency;
      result['data']['products'] = vueSalesQuoteDataSource.selection_product;

      populateSalesQuoteModalWithAjaxResult(result['data']);
      vueSalesQuoteModal.errors = [];

      Vue.nextTick(function () {
        // unbind date-picker
        $('#embeddedSalesQuoteModal #inputdate').datepicker('destroy');
        $('#embeddedSalesQuoteModal #inputdate').removeClass('hasDatepicker');
        $('#embeddedSalesQuoteModal #expiration').datepicker('destroy');
        $('#embeddedSalesQuoteModal #expiration').removeClass('hasDatepicker');
      });

      // show modal
      $('#embeddedSalesQuoteModal').modal('show');
    }
  });
}

function printPostAjax() {
  $.ajax({
    url: '/sales-quote/print/' + vueSalesQuoteModal.form.id + '/ajax',
    data: {
      _token : vueSalesQuoteModal.modal.csrf
    },
    method: 'POST',
    xhrFields: {
        responseType: 'blob'
    },
    success: function (data) {
      var a = document.createElement('a');
      var url = window.URL.createObjectURL(data);
      a.href = url;
      a.download = 'Sales Quote #' + vueSalesQuoteModal.form.increment + '.pdf';
      document.body.append(a);
      a.click();
      a.remove();
      window.URL.revokeObjectURL(url);
    }
  });
}

function createSalesQuoteInModal() {
  let jqxhr = loadSalesQuoteWithAjax(0);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      let customerId = Object.keys(vueSalesQuoteDataSource.selection_entity)[0];
      result['data']['readonly'] = false;
      result['data']['action'] = { };
      result['data']['action'][vueSalesQuoteDataSource.button_submit] = "createPostAjax";
      result['data']['title'] = vueSalesQuoteDataSource.text_create_sales_quote;
      result['data']['entities'] = vueSalesQuoteDataSource.selection_entity;
      result['data']['contacts'] = vueSalesQuoteDataSource.selection_contact;
      result['data']['payments'] = vueSalesQuoteDataSource.selection_payment;
      result['data']['staffs'] = vueSalesQuoteDataSource.selection_staff;
      result['data']['currencies'] = vueSalesQuoteDataSource.selection_currency;
      result['data']['products'] = vueSalesQuoteDataSource.selection_product;
      result['data']['entity'] = customerId;
      result['data']['payment'] = vueSalesQuoteDataSource.selection_entity[customerId].payment;
      result['data']['contact'] = vueSalesQuoteDataSource.selection_entity[customerId].contact;
      result['data']['currency'] = vueSalesQuoteDataSource.selection_entity[customerId].currency;

      populateSalesQuoteModalWithAjaxResult(result['data']);
      vueSalesQuoteModal.errors = [];

      Vue.nextTick(function () {
        // unbind date-picker
        $('#embeddedSalesQuoteModal #inputdate').datepicker('destroy');
        $('#embeddedSalesQuoteModal #inputdate').removeClass('hasDatepicker');
        $('#embeddedSalesQuoteModal #expiration').datepicker('destroy');
        $('#embeddedSalesQuoteModal #expiration').removeClass('hasDatepicker');
        // rebind date-picker
        $('#embeddedSalesQuoteModal #inputdate').datepicker().bind('change', function(event) {
          vueSalesQuoteModal.form.inputdate = $(this).val();
        });
        $('#embeddedSalesQuoteModal #expiration').datepicker().bind('change', function(event) {
          vueSalesQuoteModal.form.expiration = $(this).val();
        });
      });

      // show modal
      $('#embeddedSalesQuoteModal').modal('show');
    }
  });
}

function createPostAjax() {
  $.ajax({
    type : 'POST',
    url : '/sales-quote/create/ajax',
    enctype : 'multipart/form-data',
    data : new FormData($('#embeddedSalesQuoteModal form')[0]),
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
      $('#embeddedSalesQuoteModal').modal('hide');
      // update order/return table
      if ('insertCallback' in vueSalesQuoteDataSource) {
        vueSalesQuoteDataSource.insertCallback(data['data']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vueSalesQuoteModal.errors = data['errors'];
    }
  });
}

function updateSalesQuoteInModal(quote_id) {
  let jqxhr = loadSalesQuoteWithAjax(quote_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      result['data']['readonly'] = false;
      result['data']['action'] = { };
      result['data']['action'][vueSalesQuoteDataSource.button_update] = "updatePostAjax";
      result['data']['title'] = vueSalesQuoteDataSource.text_update_sales_quote;
      result['data']['entities'] = vueSalesQuoteDataSource.selection_entity;
      result['data']['contacts'] = vueSalesQuoteDataSource.selection_contact;
      result['data']['payments'] = vueSalesQuoteDataSource.selection_payment;
      result['data']['staffs'] = vueSalesQuoteDataSource.selection_staff;
      result['data']['currencies'] = vueSalesQuoteDataSource.selection_currency;
      result['data']['products'] = vueSalesQuoteDataSource.selection_product;

      populateSalesQuoteModalWithAjaxResult(result['data']);
      vueSalesQuoteModal.errors = [];

      Vue.nextTick(function () {
        // unbind date-picker
        $('#embeddedSalesQuoteModal #inputdate').datepicker('destroy');
        $('#embeddedSalesQuoteModal #inputdate').removeClass('hasDatepicker');
        $('#embeddedSalesQuoteModal #expiration').datepicker('destroy');
        $('#embeddedSalesQuoteModal #expiration').removeClass('hasDatepicker');
        // rebind date-picker
        $('#embeddedSalesQuoteModal #inputdate').datepicker().bind('change', function(event) {
          vueSalesQuoteModal.form.inputdate = $(this).val();
        });
        $('#embeddedSalesQuoteModal #expiration').datepicker().bind('change', function(event) {
          vueSalesQuoteModal.form.expiration = $(this).val();
        });
      });

      // show modal
      $('#embeddedSalesQuoteModal').modal('show');
    }
  });
}

function updatePostAjax() {
  $.ajax({
    type : 'POST',
    url : '/sales-quote/update/' + vueSalesQuoteModal.form.id + '/ajax',
    enctype : 'multipart/form-data',
    data : new FormData($('#embeddedSalesQuoteModal form')[0]),
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
      $('#embeddedSalesQuoteModal').modal('hide');
      // update order/return table
      if ('updateCallback' in vueSalesQuoteDataSource) {
        vueSalesQuoteDataSource.updateCallback(data['data']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vueSalesQuoteModal.errors = data['errors'];
    }
  });
}

function approveSalesQuoteInModal(quote_id) {
  let jqxhr = loadSalesQuoteWithAjax(quote_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      result['data']['readonly'] = true;
      result['data']['action'] = { };
      result['data']['action'][vueSalesQuoteDataSource.button_approve] = "approvePostAjax";
      result['data']['action'][vueSalesQuoteDataSource.button_reject] = "rejectPostAjax";
      result['data']['title'] = vueSalesQuoteDataSource.text_approve_sales_quote;
      result['data']['entities'] = vueSalesQuoteDataSource.selection_entity;
      result['data']['contacts'] = vueSalesQuoteDataSource.selection_contact;
      result['data']['payments'] = vueSalesQuoteDataSource.selection_payment;
      result['data']['staffs'] = vueSalesQuoteDataSource.selection_staff;
      result['data']['currencies'] = vueSalesQuoteDataSource.selection_currency;
      result['data']['products'] = vueSalesQuoteDataSource.selection_product;

      populateSalesQuoteModalWithAjaxResult(result['data']);
      vueSalesQuoteModal.errors = [];

      Vue.nextTick(function () {
        // unbind date-picker
        $('#embeddedSalesQuoteModal #inputdate').datepicker('destroy');
        $('#embeddedSalesQuoteModal #inputdate').removeClass('hasDatepicker');
        $('#embeddedSalesQuoteModal #expiration').datepicker('destroy');
        $('#embeddedSalesQuoteModal #expiration').removeClass('hasDatepicker');
      });

      // show modal
      $('#embeddedSalesQuoteModal').modal('show');
    }
  });
}

function approvePostAjax() {
  $.ajax({
    type : 'POST',
    url : '/sales-quote/approve/' + vueSalesQuoteModal.form.id + '/ajax',
    data : {
      _token : vueSalesQuoteModal.modal.csrf,
      submit : 'approve'
    },
    beforeSend: function(data) {
      $('.ajax-processing').removeClass('hidden');
    },
  }).done(function(data) {
    // data is already an object, no need to parse it.
    if (data['success']) {
      $('#embeddedSalesQuoteModal').modal('hide');
      // update order/return table
      if ('updateCallback' in vueSalesQuoteDataSource) {
        vueSalesQuoteDataSource.updateCallback(data['data']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vueSalesQuoteModal.errors = data['errors'];
    }
  });
}

function rejectPostAjax() {
  $.ajax({
    type : 'POST',
    url : '/sales-quote/approve/' + vueSalesQuoteModal.form.id + '/ajax',
    data : {
      _token : vueSalesQuoteModal.modal.csrf,
      submit : 'disapprove'
    },
    beforeSend: function(data) {
      $('.ajax-processing').removeClass('hidden');
    },
  }).done(function(data) {
    // data is already an object, no need to parse it.
    if (data['success']) {
      $('#embeddedSalesQuoteModal').modal('hide');
      // update order/return table
      if ('updateCallback' in vueSalesQuoteDataSource) {
        vueSalesQuoteDataSource.updateCallback(data['data']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vueSalesQuoteModal.errors = data['errors'];
    }
  });
}

$(document).ready(function() {

  vueSalesQuoteModal = new Vue({
    el : '#embeddedSalesQuoteModal',
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
      $('#embeddedSalesQuoteModal form').keypress(function (e) {
        if (e.keyCode == 13) { e.preventDefault(); }
      });
      $(document).keydown(function (e) {
        if (e.keyCode == 27) {
          if ($('#embeddedSalesQuoteModal #historyModal').hasClass("in")) {
            $('#embeddedSalesQuoteModal #historyModal').modal('hide');
          } else if ($('#embeddedSalesQuoteModal').hasClass("in")) {
            $('#embeddedSalesQuoteModal').modal('hide');
          }
        }
      });
    },
  });

});
