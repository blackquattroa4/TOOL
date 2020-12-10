// use in conjunction with ../embedded_modal/role_form.blade.php

var vueRoleFormModal = null;

function loadRoleWithAjax(role_id) {
  // use ajax to load role #id
  return $.ajax({
    type: 'GET',
    url: '/system/role/' + role_id + '/ajax',
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
      vueRoleFormModal.errors = result['errors'];
    } else {
      vueRoleFormModal.errors = { general : "System failure" };
    }
  });
}

function populateRoleModalWithAjaxResult(result) {
  // populate modal
  vueRoleFormModal.modal.readonly = result['readonly'];
  vueRoleFormModal.modal.title = result['title'];
  vueRoleFormModal.modal.csrf = result['csrf'];
  vueRoleFormModal.modal.action = result['action']
  vueRoleFormModal.form.id = result['id'];
  vueRoleFormModal.form.name = result['name'];
  vueRoleFormModal.form.display_name = result['display'];
  vueRoleFormModal.form.description = result['description'];
  vueRoleFormModal.form.permission = result['permission'];

}

function viewRoleInModal(role_id) {
  let jqxhr = loadRoleWithAjax(role_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      result['data']['readonly'] = true;
      result['data']['title'] = vueRoleFormDataSource.text_view_role;
      result['data']['action'] = { };

      populateRoleModalWithAjaxResult(result['data']);
      vueRoleFormModal.errors = [];

      // show modal
      $('#embeddedRoleEntryModal').modal('show');
    }
  });
}

function createRoleInModal() {
  let jqxhr = loadRoleWithAjax(0);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      result['data']['readonly'] = false;
      result['data']['title'] = vueRoleFormDataSource.text_create_role;
      result['data']['action'] = { };
      result['data']['action'][vueRoleFormDataSource.button_create] = "createRolePost";

      populateRoleModalWithAjaxResult(result['data']);
      vueRoleFormModal.errors = [];

      // show modal
      $('#embeddedRoleEntryModal').modal('show');
    }
  });
}

function createRolePost() {
  $.ajax({
    type : 'POST',
    url : '/system/role/create/ajax',
    enctype : 'multipart/form-data',
    data : new FormData($('#embeddedRoleEntryModal form')[0]),
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
      $('#embeddedRoleEntryModal').modal('hide');
      // update role table
      if ('insertCallback' in vueRoleFormDataSource) {
        vueRoleFormDataSource.insertCallback(data['data']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vueRoleFormModal.errors = data['errors'];
    }
  });
}

function updateRoleInModal(role_id) {
  let jqxhr = loadRoleWithAjax(role_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      result['data']['readonly'] = false;
      result['data']['title'] = vueRoleFormDataSource.text_update_role;
      result['data']['action'] = { };
      result['data']['action'][vueRoleFormDataSource.button_update] = "updateRolePost";

      populateRoleModalWithAjaxResult(result['data']);
      vueRoleFormModal.errors = [];

      // show modal
      $('#embeddedRoleEntryModal').modal('show');
    }
  });
}

function updateRolePost() {
  $.ajax({
    type : 'POST',
    url : '/system/role/update/' + vueRoleFormModal.form.id + '/ajax',
    enctype : 'multipart/form-data',
    data : new FormData($('#embeddedRoleEntryModal form')[0]),
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
      $('#embeddedRoleEntryModal').modal('hide');
      // update role table
      if ('updateCallback' in vueRoleFormDataSource) {
        vueRoleFormDataSource.updateCallback(data['data']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vueRoleFormModal.errors = data['errors'];
    }
  });
}

$(document).ready(function() {

  vueRoleFormModal = new Vue({
    el : "#embeddedRoleEntryModal",
    data : {
      modal : {
        readonly : false,
        csrf : '',
        title : '',
        action : [ ],
      },
      form : {
        id : 0,
        name : '',
        display_name : '',
        description : '',
        permission : [ ]
      },
      errors : { }
    },
    mounted : function() {
      // disable ENTER
      $('#embeddedRoleEntryModal form').keypress(function (e) {
        if (e.keyCode == 13) { e.preventDefault(); }
      });
      // ESC only works at document level and on keydown
      $(document).keydown(function (e) {
        if (e.keyCode == 27) {
          if ($('#embeddedRoleEntryModal').hasClass("in")) {
            $('#embeddedRoleEntryModal').modal('hide');
          }
        }
      });
    }
  });

});
