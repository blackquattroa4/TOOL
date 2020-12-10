// use in conjunction with ../embedded_modal/taccount_form.blade.php

var vueTaccountFormModal = null;

function loadTaccountFormWithAjax(account_id) {
  // use ajax to load order #id
  return $.ajax({
    type: 'GET',
    url: '/taccount/' + account_id + '/ajax',
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

function populateTaccountFormModalWithAjaxResult(result) {
  vueTaccountFormModal.modal.readonly = result.readonly;
  vueTaccountFormModal.modal.action = result.action;
  vueTaccountFormModal.modal.title = result.title;
  vueTaccountFormModal.modal.csrf = result.csrf;
  vueTaccountFormModal.modal.type = result.types;
  vueTaccountFormModal.modal.currency = result.currencies;
  vueTaccountFormModal.form.id = result.id;
  vueTaccountFormModal.form.account = result.account;
  vueTaccountFormModal.form.type = result.type;
  vueTaccountFormModal.form.currency = result.currency;
  vueTaccountFormModal.form.active = result.active;
  vueTaccountFormModal.form.description = result.description;
}

function viewTaccountFormInModal(account_id) {
  let jqxhr = loadTaccountFormWithAjax(account_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      result['data']['readonly'] = true;
      result['data']['action'] = {};
      result['data']['title'] = vueTaccountFormDataSource.text_view_account;
      result['data']['types'] = vueTaccountFormDataSource.selection_type;
      result['data']['currencies'] = vueTaccountFormDataSource.selection_currency;
      populateTaccountFormModalWithAjaxResult(result['data']);
      vueTaccountFormModal.errors = [ ];

      // Vue.nextTick(function () {
      // });

      // show modal
      $('#embeddedTaccountFormModal').modal('show');
    }
  });
}

function createTaccountFormInModal() {
  let jqxhr = loadTaccountFormWithAjax(0);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      result['data']['readonly'] = false;
      result['data']['action'] = {};
      result['data']['action'][vueTaccountFormDataSource.button_submit] = "createTaccountFormPost";
      result['data']['title'] = vueTaccountFormDataSource.text_create_account;
      result['data']['types'] = vueTaccountFormDataSource.selection_type;
      result['data']['currencies'] = vueTaccountFormDataSource.selection_currency;
      populateTaccountFormModalWithAjaxResult(result['data']);
      vueTaccountFormModal.errors = [ ];

      // Vue.nextTick(function () {
      // });

      // show modal
      $('#embeddedTaccountFormModal').modal('show');
    }
  });
}

function createTaccountFormPost() {
  $.ajax({
    type : 'POST',
    url : '/taccount/create/ajax',
    enctype : 'multipart/form-data',
    data : new FormData($('#embeddedTaccountFormModal form')[0]),
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
      $('#embeddedTaccountFormModal').modal('hide');
      // update expense table
      if ('insertCallback' in vueTaccountFormDataSource) {
        vueTaccountFormDataSource.insertCallback(data['data']['taccount']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vueTaccountFormDataSource.errors = data['errors'];
    }
  });
}

function updateTaccountFormInModal(account_id) {
  let jqxhr = loadTaccountFormWithAjax(account_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      result['data']['readonly'] = false;
      result['data']['action'] = {};
      result['data']['action'][vueTaccountFormDataSource.button_update] = "updateTaccountFormPost";
      result['data']['title'] = vueTaccountFormDataSource.text_update_account;
      result['data']['types'] = vueTaccountFormDataSource.selection_type;
      result['data']['currencies'] = vueTaccountFormDataSource.selection_currency;
      populateTaccountFormModalWithAjaxResult(result['data']);
      vueTaccountFormModal.errors = [ ];

      // Vue.nextTick(function () {
      // });

      // show modal
      $('#embeddedTaccountFormModal').modal('show');
    }
  });
}

function updateTaccountFormPost() {
  $.ajax({
    type : 'POST',
    url : '/taccount/' + vueTaccountFormModal.form.id + '/ajax',
    enctype : 'multipart/form-data',
    data : new FormData($('#embeddedTaccountFormModal form')[0]),
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
      $('#embeddedTaccountFormModal').modal('hide');
      // update expense table
      if ('updateCallback' in vueTaccountFormDataSource) {
        vueTaccountFormDataSource.updateCallback(data['data']['taccount']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vueTaccountFormDataSource.errors = data['errors'];
    }
  });
}

$(document).ready(function() {

  vueTaccountFormModal = new Vue({
    el : '#embeddedTaccountFormModal',
    data : {
      modal : {
        readonly : false,
        action : [ ],
        title : '',
        csrf : '',
        type : [ ],
        currency : [ ]
      },
      form : {
        id : 0,
        account : '',
        type : 0,
        currency : 0,
        active : 0,
        description : ''
      },
      errors : { }
    },
    mounted : function() {
      $('#embeddedTaccountFormModal form').keypress(function (e) {
        if (e.keyCode == 13) { e.preventDefault(); }
      });
      $(document).keydown(function (e) {
        if (e.keyCode == 27) {
          if ($('#embeddedTaccountFormModal').hasClass("in")) {
            $('#embeddedTaccountFormModal').modal('hide');
          }
        }
      });
    }
  });

});
