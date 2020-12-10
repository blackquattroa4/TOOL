// use in conjunction with ../embedded_modal/user_form.blade.php

var vueUserFormModal = null;

function loadUserWithAjax(user_id) {
  // use ajax to load user #id
  return $.ajax({
    type: 'GET',
    url: '/system/user/' + user_id + '/ajax',
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
      vueUserFormModal.errors = result['errors'];
    } else {
      vueUserFormModal.errors = { general : "System failure" };
    }
  });
}

function populateUserModalWithAjaxResult(result) {
  // populate modal
  vueUserFormModal.modal.readonly = result['readonly'];
  vueUserFormModal.modal.csrf = result['csrf'];
  vueUserFormModal.modal.title = result['title'];
  vueUserFormModal.modal.action = result['action'];
  vueUserFormModal.form.id = result['id'];
  vueUserFormModal.form.name = result['name'];
  vueUserFormModal.form.email = result['email'];
  vueUserFormModal.form.password = result['password'];
  vueUserFormModal.form.password_confirm = result['password_confirm'];
  vueUserFormModal.form.roles = result['roles'];
}

function viewUserInModal(user_id) {
  let jqxhr = loadUserWithAjax(user_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      result['data']['readonly'] = true;
      result['data']['title'] = vueUserFormDataSource.text_view_user;
      result['data']['action'] = { };

      populateUserModalWithAjaxResult(result['data']);
      vueUserFormModal.errors = [];

      // show modal
      $('#embeddedUserEntryModal').modal('show');
    }
  });
}

function createUserInModal() {
  let jqxhr = loadUserWithAjax(0);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      result['data']['readonly'] = false;
      result['data']['title'] = vueUserFormDataSource.text_create_user;
      result['data']['action'] = { };
      result['data']['action'][vueUserFormDataSource.button_create] = "createUserPostAjax";

      populateUserModalWithAjaxResult(result['data']);
      vueUserFormModal.errors = [];

      // show modal
      $('#embeddedUserEntryModal').modal('show');
    }
  });
}

function createUserPostAjax() {
  $.ajax({
    type : 'POST',
    url : '/system/user/create/ajax',
    enctype : 'multipart/form-data',
    data : new FormData($('#embeddedUserEntryModal form')[0]),
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
      $('#embeddedUserEntryModal').modal('hide');
      // update user table
      if ('insertCallback' in vueUserFormDataSource) {
        vueUserFormDataSource.insertCallback(data['data']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vueUserFormModal.errors = data['errors'];
    }
  });
}

function updateUserInModal(user_id) {
  let jqxhr = loadUserWithAjax(user_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      result['data']['readonly'] = false;
      result['data']['title'] = vueUserFormDataSource.text_update_user;
      result['data']['action'] = { };
      result['data']['action'][vueUserFormDataSource.button_update] = "updateUserPostAjax";

      populateUserModalWithAjaxResult(result['data']);
      vueUserFormModal.errors = [];

      // show modal
      $('#embeddedUserEntryModal').modal('show');
    }
  });
}

function updateUserPostAjax() {
  $.ajax({
    type : 'POST',
    url : '/system/user/update/' + vueUserFormModal.form.id + '/ajax',
    enctype : 'multipart/form-data',
    data : new FormData($('#embeddedUserEntryModal form')[0]),
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
      $('#embeddedUserEntryModal').modal('hide');
      // update user table
      if ('updateCallback' in vueUserFormDataSource) {
        vueUserFormDataSource.updateCallback(data['data']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vueUserFormModal.errors = data['errors'];
    }
  });
}

$(document).ready(function() {

  vueUserFormModal = new Vue({
    el : "#embeddedUserEntryModal",
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
        email : '',
        password : '',
        password_confirm : '',
        permission : [ ]
      },
      errors : { }
    },
    mounted : function() {
      // disable ENTER
      $('#embeddedUserEntryModal form').keypress(function (e) {
        if (e.keyCode == 13) { e.preventDefault(); }
      });
      // ESC only works at document level and on keydown
      $(document).keydown(function (e) {
        if (e.keyCode == 27) {
          if ($('#embeddedUserEntryModal').hasClass("in")) {
            $('#embeddedUserEntryModal').modal('hide');
          }
        }
      });
    }
  });

});
