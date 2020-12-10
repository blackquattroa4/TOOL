// use in conjunction with ../embedded_modal/tradable_form.blade.php

var vueTradableFormModal = null;

function loadTradableWithAjax(tradable_id) {
  // use ajax to load tradable #id
  return $.ajax({
    type: 'GET',
    url: '/tradable/' + tradable_id + '/ajax',
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
      vueTradableFormModal.errors = result['errors'];
    } else {
      vueTradableFormModal.errors = { general : "System failure" };
    }
  });
}

function populateTradableModalWithAjaxResult(result) {
  vueTradableFormModal.modal.readonly = result.readonly;
  vueTradableFormModal.modal.csrf = result.csrf;
  vueTradableFormModal.modal.title = result.title;
  vueTradableFormModal.modal.action = result.actions;
  vueTradableFormModal.modal.account = result.accounts;
  vueTradableFormModal.modal.supplier = result.suppliers;
  vueTradableFormModal.modal.origin = result.origins;
  vueTradableFormModal.modal.length = result.length_unit;
  vueTradableFormModal.modal.weight = result.weight_unit;

  vueTradableFormModal.form.id = result.id;
  vueTradableFormModal.form.sku = result.sku;
  vueTradableFormModal.form.description = result.description;
  vueTradableFormModal.form.product_id = result.product_id;
  vueTradableFormModal.form.phasing_out = result.phasing_out;
  vueTradableFormModal.form.item_type = result.item_type;
  vueTradableFormModal.form.forecastable = result.forecastable;
  vueTradableFormModal.form.account = result.account;
  vueTradableFormModal.form.current = result.current;
  vueTradableFormModal.form.serial_pattern = result.serial_pattern;
  vueTradableFormModal.form.supplier = result.supplier;
  vueTradableFormModal.form.unit_length = result.unit_length;
  vueTradableFormModal.form.unit_width = result.unit_width;
  vueTradableFormModal.form.unit_height = result.unit_height;
  vueTradableFormModal.form.unit_weight = result.unit_weight;
  vueTradableFormModal.form.unit_per_carton = result.unit_per_carton;
  vueTradableFormModal.form.carton_length = result.carton_length;
  vueTradableFormModal.form.carton_width = result.carton_width;
  vueTradableFormModal.form.carton_height = result.carton_height;
  vueTradableFormModal.form.carton_weight = result.carton_weight;
  vueTradableFormModal.form.carton_per_pallet = result.carton_per_pallet;
  vueTradableFormModal.form.lead_day = result.lead_day;
  vueTradableFormModal.form.content = result.content;
  vueTradableFormModal.form.origin = result.country;
}

function createTradableInModal() {
  let jqxhr = loadTradableWithAjax(0);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      result['data']['readonly'] = false;
      result['data']['title'] = vueTradableFormDataSource.text_create_tradable;
      result['data']['actions'] = { },
      result['data']['actions'][vueTradableFormDataSource.button_create] = "createTradablePostAjax";
      result['data']['suppliers'] = vueTradableFormDataSource.selection_supplier;
      result['data']['accounts'] = vueTradableFormDataSource.selection_account;
      result['data']['origins'] = vueTradableFormDataSource.selection_country;
      result['data']['length_unit'] = vueTradableFormDataSource.text_length_unit;
      result['data']['weight_unit'] = vueTradableFormDataSource.text_weight_unit;

      populateTradableModalWithAjaxResult(result['data']);
      vueTradableFormModal.errors = [];

      Vue.nextTick(function () {
      });

      $('#embeddedTradableFormModal').modal('show');
    }
  });

}

function createTradablePostAjax() {
  $.ajax({
    type : 'POST',
    url : '/tradable/create/ajax',
    enctype : 'multipart/form-data',
    data : new FormData($('#embeddedTradableFormModal form')[0]),
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
      $('#embeddedTradableFormModal').modal('hide');
      // update expense table
      if ('insertCallback' in vueTradableFormDataSource) {
        vueTradableFormDataSource.insertCallback(data['data']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vueTradableFormModal.errors = data['errors'];
    }
  });
}

function viewTradableInModal(tradable_id) {
  let jqxhr = loadTradableWithAjax(tradable_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      result['data']['readonly'] = true;
      result['data']['title'] = vueTradableFormDataSource.text_view_tradable;
      result['data']['actions'] = { },
      result['data']['suppliers'] = vueTradableFormDataSource.selection_supplier;
      result['data']['accounts'] = vueTradableFormDataSource.selection_account;
      result['data']['origins'] = vueTradableFormDataSource.selection_country;
      result['data']['length_unit'] = vueTradableFormDataSource.text_length_unit;
      result['data']['weight_unit'] = vueTradableFormDataSource.text_weight_unit;

      populateTradableModalWithAjaxResult(result['data']);
      vueTradableFormModal.errors = [];

      Vue.nextTick(function () {
      });

      $('#embeddedTradableFormModal').modal('show');
    }
  });

}

function updateTradableInModal(tradable_id) {
  let jqxhr = loadTradableWithAjax(tradable_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      result['data']['readonly'] = false;
      result['data']['title'] = vueTradableFormDataSource.text_update_tradable;
      result['data']['actions'] = { },
      result['data']['actions'][vueTradableFormDataSource.button_update] = "updateTradablePostAjax";
      result['data']['suppliers'] = vueTradableFormDataSource.selection_supplier;
      result['data']['accounts'] = vueTradableFormDataSource.selection_account;
      result['data']['origins'] = vueTradableFormDataSource.selection_country;
      result['data']['length_unit'] = vueTradableFormDataSource.text_length_unit;
      result['data']['weight_unit'] = vueTradableFormDataSource.text_weight_unit;

      populateTradableModalWithAjaxResult(result['data']);
      vueTradableFormModal.errors = [];

      Vue.nextTick(function () {
      });

      $('#embeddedTradableFormModal').modal('show');
    }
  });

}

function updateTradablePostAjax() {
  $.ajax({
    type : 'POST',
    url : '/tradable/update/' + vueTradableFormModal.form.id + '/ajax',
    enctype : 'multipart/form-data',
    data : new FormData($('#embeddedTradableFormModal form')[0]),
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
      $('#embeddedTradableFormModal').modal('hide');
      // update expense table
      if ('updateCallback' in vueTradableFormDataSource) {
        vueTradableFormDataSource.updateCallback(data['data']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vueTradableFormModal.errors = data['errors'];
    }
  });
}

$(document).ready(function() {

  vueTradableFormModal = new Vue({
    el : "#embeddedTradableFormModal",
    data : {
      modal : {
        readonly : false,
        csrf : '',
        title : '',
        action : [ ],
        account : [ ],
        supplier : [ ],
        origin : [ ],
        length : '',
        weight : ''
      },
      form : {
        id : 0,
        sku : '',
        description : '',
        product_id : '',
        phasing_out : false,
        item_type : '',
        forecastable : false,
        account : 0,
        current : false,
        serial_pattern : '',
        supplier : 0,
        unit_length : 0,
        unit_width : 0,
        unit_height : 0,
        unit_weight : 0,
        unit_per_carton : 0,
        carton_length : 0,
        carton_width : 0,
        carton_height : 0,
        carton_weight : 0,
        carton_per_pallet : 0,
        lead_day : 0,
        content : '',
        origin : 0
      },
      errors : { },
    },
    mounted : function() {
      // disable ENTER
      $('#embeddedTradableFormModal form').keypress(function (e) {
        if (e.keyCode == 13) { e.preventDefault(); }
      });
      $(document).keydown(function (e) {
        if (e.keyCode == 27) {
          if ($('#embeddedTradableFormModal').hasClass("in")) {
            $('#embeddedTradableFormModal').modal('hide');
          }
        }
      });

    }
  });

});
