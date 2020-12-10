// use in conjunction with ../embedded_modal/tradable_notice.blade.php

var vueTradableNoticeModal = null;

function loadTradableNoticeWithAjax(notice_id) {
  // use ajax to load tradable #id
  return $.ajax({
    type: 'GET',
    url: '/tradable/notice/' + notice_id + '/ajax',
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
      vueTradableNoticeModal.errors = result['errors'];
    } else {
      vueTradableNoticeModal.errors = { general : "System failure" };
    }
  });
}

function populateTradableNoticeModalWithAjaxResult(result) {
  vueTradableNoticeModal.modal.readonly = result.readonly;
  vueTradableNoticeModal.modal.csrf = result.csrf;
  vueTradableNoticeModal.modal.title = result.title;
  vueTradableNoticeModal.modal.tradable = result.tradable;
  vueTradableNoticeModal.modal.action = result.action;
  vueTradableNoticeModal.form.id = result.id;
  vueTradableNoticeModal.form.document_id = result.document_id;
  vueTradableNoticeModal.form.file_name = result.file_name;
  vueTradableNoticeModal.form.product = result.product;
  vueTradableNoticeModal.form.summary = result.summary;
}

function createTradableNoticeInModal() {
  let jqxhr = loadTradableNoticeWithAjax(0);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      result['data']['readonly'] = false;
      result['data']['title'] = vueTradableNoticeDataSource.text_create_notice;
      result['data']['action'] = { },
      result['data']['action'][vueTradableNoticeDataSource.button_create] = "createTradableNoticePostAjax";
      result['data']['tradable'] = vueTradableNoticeDataSource.selection_tradable;

      populateTradableNoticeModalWithAjaxResult(result['data']);
      // remedial fix, reset file-browse button
      $('#embeddedTradableNoticeModal #thefile').val('');
      $('#embeddedTradableNoticeModal #upload-selector-label').html(vueTradableNoticeDataSource.text_browse_file);
      vueTradableNoticeModal.errors = [];

      Vue.nextTick(function () {
      });

      $('#embeddedTradableNoticeModal').modal('show');
    }
  });

}

function createTradableNoticePostAjax() {
  $.ajax({
    type : 'POST',
    url : '/tradable/notice/create/ajax',
    enctype : 'multipart/form-data',
    data : new FormData($('#embeddedTradableNoticeModal form')[0]),
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
      $('#embeddedTradableNoticeModal').modal('hide');
      // update expense table
      if ('insertCallback' in vueTradableNoticeDataSource) {
        vueTradableNoticeDataSource.insertCallback(data['data']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vueTradableNoticeModal.errors = data['errors'];
    }
  });
}

function updateTradableNoticeInModal(notice_id) {
  let jqxhr = loadTradableNoticeWithAjax(notice_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      result['data']['readonly'] = false;
      result['data']['title'] = vueTradableNoticeDataSource.text_update_notice;
      result['data']['action'] = { },
      result['data']['action'][vueTradableNoticeDataSource.button_update] = "updateTradableNoticePostAjax";
      result['data']['tradable'] = vueTradableNoticeDataSource.selection_tradable;

      populateTradableNoticeModalWithAjaxResult(result['data']);
      // remedial fix, reset file-browse button
      $('#embeddedTradableNoticeModal #thefile').val('');
      $('#embeddedTradableNoticeModal #upload-selector-label').html(vueTradableNoticeDataSource.text_browse_file);
      vueTradableNoticeModal.errors = [];

      Vue.nextTick(function () {
      });

      $('#embeddedTradableNoticeModal').modal('show');
    }
  });

}

function updateTradableNoticePostAjax() {
  $.ajax({
    type : 'POST',
    url : '/tradable/notice/update/' + vueTradableNoticeModal.form.id + '/ajax',
    enctype : 'multipart/form-data',
    data : new FormData($('#embeddedTradableNoticeModal form')[0]),
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
      $('#embeddedTradableNoticeModal').modal('hide');
      // update expense table
      if ('updateCallback' in vueTradableNoticeDataSource) {
        vueTradableNoticeDataSource.updateCallback(data['data']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vueTradableNoticeModal.errors = data['errors'];
    }
  });
}

$(document).ready(function() {

  vueTradableNoticeModal = new Vue({
    el : "#embeddedTradableNoticeModal",
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
        summary : ''
      },
      errors : { },
    },
    mounted : function() {
      // disable ENTER
      $('#embeddedTradableNoticeModal form').keypress(function (e) {
        if (e.keyCode == 13) { e.preventDefault(); }
      });
      $(document).keydown(function (e) {
        if (e.keyCode == 27) {
          if ($('#embeddedTradableNoticeModal').hasClass("in")) {
            $('#embeddedTradableNoticeModal').modal('hide');
          }
        }
      });

    }
  });

});
