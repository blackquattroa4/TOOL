// use in conjunction with ../embedded_modal/tradable_faq.blade.php

var vueTradableFaqModal = null;

function loadTradableFaqWithAjax(faq_id) {
  // use ajax to load tradable #id
  return $.ajax({
    type: 'GET',
    url: '/tradable/faq/' + faq_id + '/ajax',
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
      vueTradableFaqModal.errors = result['errors'];
    } else {
      vueTradableFaqModal.errors = { general : "System failure" };
    }
  });
}

function populateTradableFaqModalWithAjaxResult(result) {
  vueTradableFaqModal.modal.readonly = result.readonly;
  vueTradableFaqModal.modal.csrf = result.csrf;
  vueTradableFaqModal.modal.title = result.title;
  vueTradableFaqModal.modal.tradable = result.tradable;
  vueTradableFaqModal.modal.action = result.action;
  vueTradableFaqModal.form.id = result.id;
  vueTradableFaqModal.form.document_id = result.document_id;
  vueTradableFaqModal.form.file_name = result.file_name;
  vueTradableFaqModal.form.product = result.product;
  vueTradableFaqModal.form.question = result.question;
  vueTradableFaqModal.form.answer = result.answer;
}

function createTradableFaqInModal() {
  let jqxhr = loadTradableFaqWithAjax(0);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      result['data']['readonly'] = false;
      result['data']['title'] = vueTradableFaqDataSource.text_create_faq;
      result['data']['action'] = { },
      result['data']['action'][vueTradableFaqDataSource.button_create] = "createTradableFaqPostAjax";
      result['data']['tradable'] = vueTradableFaqDataSource.selection_tradable;

      populateTradableFaqModalWithAjaxResult(result['data']);
      // remedial fix, reset file-browse button
      $('#embeddedTradableFaqModal #thefaqfile').val('');
      $('#embeddedTradableFaqModal #upload-selector-label').html(vueTradableFaqDataSource.text_browse_file);
      vueTradableFaqModal.errors = [];

      Vue.nextTick(function () {
      });

      $('#embeddedTradableFaqModal').modal('show');
    }
  });

}

function createTradableFaqPostAjax() {
  $.ajax({
    type : 'POST',
    url : '/tradable/faq/create/ajax',
    enctype : 'multipart/form-data',
    data : new FormData($('#embeddedTradableFaqModal form')[0]),
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
      $('#embeddedTradableFaqModal').modal('hide');
      // update expense table
      if ('insertCallback' in vueTradableFaqDataSource) {
        vueTradableFaqDataSource.insertCallback(data['data']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vueTradableFaqModal.errors = data['errors'];
    }
  });
}

function updateTradableFaqInModal(faq_id) {
  let jqxhr = loadTradableFaqWithAjax(faq_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      result['data']['readonly'] = false;
      result['data']['title'] = vueTradableFaqDataSource.text_update_faq;
      result['data']['action'] = { },
      result['data']['action'][vueTradableFaqDataSource.button_update] = "updateTradableFaqPostAjax";
      result['data']['tradable'] = vueTradableFaqDataSource.selection_tradable;

      populateTradableFaqModalWithAjaxResult(result['data']);
      // remedial fix, reset file-browse button
      $('#embeddedTradableFaqModal #thefaqfile').val('');
      $('#embeddedTradableFaqModal #upload-selector-label').html(vueTradableFaqDataSource.text_browse_file);
      vueTradableFaqModal.errors = [];

      Vue.nextTick(function () {
      });

      $('#embeddedTradableFaqModal').modal('show');
    }
  });

}

function updateTradableFaqPostAjax() {
  $.ajax({
    type : 'POST',
    url : '/tradable/faq/update/' + vueTradableFaqModal.form.id + '/ajax',
    enctype : 'multipart/form-data',
    data : new FormData($('#embeddedTradableFaqModal form')[0]),
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
      $('#embeddedTradableFaqModal').modal('hide');
      // update expense table
      if ('updateCallback' in vueTradableFaqDataSource) {
        vueTradableFaqDataSource.updateCallback(data['data']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vueTradableFaqModal.errors = data['errors'];
    }
  });
}

$(document).ready(function() {

  vueTradableFaqModal = new Vue({
    el : "#embeddedTradableFaqModal",
    data : {
      modal : {
        readonly : false,
        csrf : '',
        title : '',
        tradable : [ ],
        action : [ ]
      },
      form : {
        id : 0,
        document_id : 0,
        file_name : '',
        product : [ ],
        question : '',
        answer : '',
      },
      errors : { },
    },
    mounted : function() {
      // disable ENTER
      $('#embeddedTradableFaqModal form').keypress(function (e) {
        if (e.keyCode == 13) { e.preventDefault(); }
      });
      $(document).keydown(function (e) {
        if (e.keyCode == 27) {
          if ($('#embeddedTradableFaqModal').hasClass("in")) {
            $('#embeddedTradableFaqModal').modal('hide');
          }
        }
      });

    }
  });

});
