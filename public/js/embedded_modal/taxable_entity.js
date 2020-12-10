// use in conjunction with ../embedded_modal/taxable_entity.blade.php

var vueTaxableEntityModal = null;

function loadTaxableEntityWithAjax(entity_id) {
  // use ajax to load entity
  return $.ajax({
    type: 'GET',
    url: '/taxable-entity/' + entity_id + '/ajax',
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
      vueTaxableEntityModal.errors = result['errors'];
    } else {
      vueTaxableEntityModal.errors = { general : "System failure" };
    }
  });
}

function populateTaxableEntityModalWithAjaxResult(result) {
  vueTaxableEntityModal.modal.readonly = result.readonly,
  vueTaxableEntityModal.modal.title = result.title;
  vueTaxableEntityModal.modal.csrf = result.csrf;
  vueTaxableEntityModal.modal.action = result.action;
  vueTaxableEntityModal.modal.country = result.countries;
  vueTaxableEntityModal.modal.payment = result.payments;
  vueTaxableEntityModal.modal.currency = result.currencies;

  vueTaxableEntityModal.form.id = result.id;
  vueTaxableEntityModal.form.code = result.code;
  vueTaxableEntityModal.form.type = result.type;
  vueTaxableEntityModal.form.name = result.name;
  vueTaxableEntityModal.form.active = result.active;
  vueTaxableEntityModal.form.contact = result.contact;
  vueTaxableEntityModal.form.email = result.email;
  vueTaxableEntityModal.form.phone = result.phone;
  vueTaxableEntityModal.form.bstreet = result.bstreet;
  vueTaxableEntityModal.form.bunit = result.bunit;
  vueTaxableEntityModal.form.bdistrict = result.bdistrict;
  vueTaxableEntityModal.form.bcity = result.bcity;
  vueTaxableEntityModal.form.bstate = result.bstate;
  vueTaxableEntityModal.form.bcountry = result.bcountry;
  vueTaxableEntityModal.form.bzipcode = result.bzipcode;
  vueTaxableEntityModal.form.sstreet = result.sstreet;
  vueTaxableEntityModal.form.sunit = result.sunit;
  vueTaxableEntityModal.form.sdistrict = result.sdistrict;
  vueTaxableEntityModal.form.scity = result.scity;
  vueTaxableEntityModal.form.sstate = result.sstate;
  vueTaxableEntityModal.form.scountry = result.scountry;
  vueTaxableEntityModal.form.szipcode = result.szipcode;
  vueTaxableEntityModal.form.payment = result.payment;
  vueTaxableEntityModal.form.currency = result.currency;

}

function createTaxableEntityInModal(type)
{
  let jqxhr = loadTaxableEntityWithAjax(0);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      result['data']['readonly'] = false,
      result['data']['title'] = vueTaxableEntityDataSource['text_create_' + type];
      result['data']['action'] = {};
      result['data']['action'][vueTaxableEntityDataSource.button_create] = 'createTaxableEntityPost';
      result['data']['countries'] = vueTaxableEntityDataSource.selection_country;
      result['data']['payments'] = vueTaxableEntityDataSource.selection_payment;
      result['data']['currencies'] = vueTaxableEntityDataSource.selection_currency;
      result['data']['type'] = type;

      populateTaxableEntityModalWithAjaxResult(result['data']);
      vueTaxableEntityModal.errors = [];

      // Vue.nextTick(function () {
      // });

      // show modal
      $('#embeddedTaxableEntityModal').modal('show');
    }
  });
}

function createTaxableEntityPost() {
  $.ajax({
    type : 'POST',
    url : '/taxable-entity/create/ajax',
    enctype : 'multipart/form-data',
    data : new FormData($('#embeddedTaxableEntityModal form')[0]),
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
      $('#embeddedTaxableEntityModal').modal('hide');
      // update entity table
      if ('insertCallback' in vueTaxableEntityDataSource) {
        vueTaxableEntityDataSource.insertCallback(data['data']['entity']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vueTaxableEntityModal.errors = data['errors'];
    }
  });
}

function viewTaxableEntityInModal(entity_id)
{
  let jqxhr = loadTaxableEntityWithAjax(entity_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      result['data']['readonly'] = true,
      result['data']['title'] = vueTaxableEntityDataSource['text_view_' + result['data']['type']];
      result['data']['action'] = {};
      result['data']['countries'] = vueTaxableEntityDataSource.selection_country;
      result['data']['payments'] = vueTaxableEntityDataSource.selection_payment;
      result['data']['currencies'] = vueTaxableEntityDataSource.selection_currency;

      populateTaxableEntityModalWithAjaxResult(result['data']);
      vueTaxableEntityModal.errors = [];

      // Vue.nextTick(function () {
      // });

      // show modal
      $('#embeddedTaxableEntityModal').modal('show');
    }
  });
}

function updateTaxableEntityInModal(entity_id)
{
  let jqxhr = loadTaxableEntityWithAjax(entity_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      result['data']['readonly'] = false,
      result['data']['title'] = vueTaxableEntityDataSource['text_update_' + result['data']['type']];
      result['data']['action'] = {};
      result['data']['action'][vueTaxableEntityDataSource.button_update] = 'updateTaxableEntityPost';
      result['data']['countries'] = vueTaxableEntityDataSource.selection_country;
      result['data']['payments'] = vueTaxableEntityDataSource.selection_payment;
      result['data']['currencies'] = vueTaxableEntityDataSource.selection_currency;

      populateTaxableEntityModalWithAjaxResult(result['data']);
      vueTaxableEntityModal.errors = [];

      // Vue.nextTick(function () {
      // });

      // show modal
      $('#embeddedTaxableEntityModal').modal('show');
    }
  });
}

function updateTaxableEntityPost() {
  $.ajax({
    type : 'POST',
    url : '/taxable-entity/update/' + vueTaxableEntityModal.form.id + '/ajax',
    enctype : 'multipart/form-data',
    data : new FormData($('#embeddedTaxableEntityModal form')[0]),
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
      $('#embeddedTaxableEntityModal').modal('hide');
      // update entity table
      if ('updateCallback' in vueTaxableEntityDataSource) {
        vueTaxableEntityDataSource.updateCallback(data['data']['entity']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vueTaxableEntityModal.errors = data['errors'];
    }
  });
}

$(document).ready(function() {

  vueTaxableEntityModal = new Vue({
    el : '#embeddedTaxableEntityModal',
    data : {
      modal : {
        readonly : true,
        title : '',
        csrf : '',
        action : [ ],
        country : [ ],
        payment : [ ],
        currency : [ ]
      },
      form : {
        id : 0,
        code : '',
        type : '',
        name : '',
        active : 0,
        contact : '',
        email : '',
        phone : '',
        bstreet : '',
        bunit : '',
        bdistrict : '',
        bcity : '',
        bstate : '',
        bcountry : '',
        bzipcode : '',
        sstreet : '',
        sunit : '',
        sdistrict : '',
        scity : '',
        sstate : '',
        scountry : '',
        szipcode : '',
        payment : 0,
        currency : 0,
      },
      errors : { }
    },
    mounted : function() {
      $('#embeddedTaxableEntityModal form#warehouse_form').keypress(function (e) {
        if (e.keyCode == 13) { e.preventDefault(); }
      });
      // ESC only works at document level and on keydown
      $(document).keydown(function (e) {
        if (e.keyCode == 27) {
          if ($('#embeddedTaxableEntityModal').hasClass("in")) {
            $('#embeddedTaxableEntityModal').modal('hide');
          }
        }
      });
    }
  });

});
