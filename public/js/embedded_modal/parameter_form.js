// use in conjunction with ../embedded_modal/parameter_form.blade.php

var vueParameterModal = null;

function loadParameterWithAjax(parameter_id) {
  // use ajax to load parameter #id
  return $.ajax({
    type: 'GET',
    url: '/system/parameter/' + parameter_id + '/ajax',
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
      vueParameterModal.errors = result['errors'];
    } else {
      vueParameterModal.errors = { general : "System failure" };
    }
  });
}

function populateParameterModalWithAjaxResult(result) {
  vueParameterModal.modal.readonly = result.readonly;
  vueParameterModal.modal.csrf = result.csrf;
  vueParameterModal.modal.action = result.action;
  vueParameterModal.modal.title = result.title;
  vueParameterModal.form.id = result.id;
  vueParameterModal.form.key = result.key;
  vueParameterModal.form.value = result.value;
}

function createParameterInModal() {
  let jqxhr = loadParameterWithAjax(0);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      result['data']['readonly'] = false;
      result['data']['action'] = { };
      result['data']['action'][vueParameterDataSource.button_create] = "createPostAjax";
      result['data']['title'] = vueParameterDataSource.text_create_parameter;

      populateParameterModalWithAjaxResult(result['data']);
      vueParameterModal.errors = [];

      // Vue.nextTick(function () {
      // });

      // show modal
      $('#embeddedParameterModal').modal('show');
    }
  });
}

function createPostAjax() {
  $.ajax({
    type : 'POST',
    url : '/system/parameter/ajax',
    enctype : 'multipart/form-data',
    data : new FormData($('#embeddedParameterModal form')[0]),
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
      $('#embeddedParameterModal').modal('hide');
      // update order/return table
      if ('insertCallback' in vueParameterDataSource) {
        vueParameterDataSource.insertCallback(data['data']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vueParameterModal.errors = data['errors'];
    }
  });
}

function updateParameterInModal(parameter_id) {
  let jqxhr = loadParameterWithAjax(parameter_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      result['data']['readonly'] = false;
      result['data']['action'] = { };
      result['data']['action'][vueParameterDataSource.button_update] = "updatePostAjax";
      result['data']['title'] = vueParameterDataSource.text_update_parameter;

      populateParameterModalWithAjaxResult(result['data']);
      vueParameterModal.errors = [];

      // Vue.nextTick(function () {
      // });

      // show modal
      $('#embeddedParameterModal').modal('show');
    }
  });
}

function updatePostAjax() {
  $.ajax({
    type : 'POST',
    url : '/system/parameter/' + vueParameterModal.form.id + '/ajax',
    enctype : 'multipart/form-data',
    data : new FormData($('#embeddedParameterModal form')[0]),
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
      $('#embeddedParameterModal').modal('hide');
      // update order/return table
      if ('updateCallback' in vueParameterDataSource) {
        vueParameterDataSource.updateCallback(data['data']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vueParameterModal.errors = data['errors'];
    }
  });
}

$(document).ready(function() {

  vueParameterModal = new Vue({
    el : '#embeddedParameterModal',
    data : {
      modal : {
        readonly : false,
        csrf : '',
        title : '',
        action : [ ]
      },
      form : {
        id : 0,
        key : '',
        value : ''
      },
      errors : { }
    },
    mounted : function() {
      // disable ENTER
      $('#embeddedParameterModal form').keypress(function (e) {
        if (e.keyCode == 13) { e.preventDefault(); }
      });
      $(document).keydown(function (e) {
        if (e.keyCode == 27) {
          if ($('#embeddedParameterModal #historyModal').hasClass("in")) {
            $('#embeddedParameterModal #historyModal').modal('hide');
          } else if ($('#embeddedParameterModal').hasClass("in")) {
            $('#embeddedParameterModal').modal('hide');
          }
        }
      });
    },
  });

});
