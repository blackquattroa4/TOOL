// use in conjunction with ../embedded_modal/staff_form.blade.php

var vueStaffModal = null;

function loadStaffWithAjax(staff_id) {
  // use ajax to load staff #id
  return $.ajax({
    type: 'GET',
    url: '/staff/' + staff_id + '/ajax',
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
      vueStaffModal.errors = result['errors'];
    } else {
      vueStaffModal.errors = { general : "System failure" };
    }
  });
}

function populateStaffModalWithAjaxResult(result) {
  vueStaffModal.modal.readonly = result.readonly;
  vueStaffModal.modal.title = result.title;
  vueStaffModal.modal.csrf = result.csrf;
  vueStaffModal.modal.country = result.countries;
  vueStaffModal.modal.action = result.action;
  vueStaffModal.form.id = result.id;
  vueStaffModal.form.name = result.name;
  vueStaffModal.form.email = result.email;
  vueStaffModal.form.phone = result.phone;
  vueStaffModal.form.street = result.street;
  vueStaffModal.form.unit = result.unit;
  vueStaffModal.form.city = result.city;
  vueStaffModal.form.district = result.district;
  vueStaffModal.form.state = result.state;
  vueStaffModal.form.country = result.country;
  vueStaffModal.form.zipcode = result.zipcode;
  vueStaffModal.form.file = result.file;
  vueStaffModal.form.file_date = result.file_date_display;
  vueStaffModal.form.file_title = result.file_title;
  vueStaffModal.form.file_creator = result.file_creator;
  vueStaffModal.form.file_path = result.file_path;
}

function executeDownloadableHandler(url) {
  return $.ajax({
    type: 'GET',
    url: url,
    dataType: 'html',
    beforeSend: function(data) {
      $('.ajax-processing').removeClass('hidden');
    },
  }).done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      $('#embeddedStaffModal div.image-group div img#image-canvas').data('url', result['content']);
      $('#embeddedStaffModal div.image-group div img#image-canvas').trigger('click');
    }
  }).fail(function() {
    // show failed message.
    $('#embeddedStaffModal #downloadableAttachmentModal div.modal-body div#img-preview').html(vueStaffDataSource.text_attachment_download_failed);
    $('#embeddedStaffModal #downloadableAttachmentModal div.modal-footer button').removeClass('hidden');
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
  });
}

function viewStaffInModal(staff_id) {
  let jqxhr = loadStaffWithAjax(staff_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      result['data']['readonly'] = true;
      result['data']['title'] = vueStaffDataSource.text_view_staff;
      result['data']['countries'] = vueStaffDataSource.selection_country;
      result['data']['action'] = {};
      populateStaffModalWithAjaxResult(result['data']);
      vueStaffModal.errors = [ ];

      Vue.nextTick(function () {
        // bind file viewer
        $("#embeddedStaffModal button.image-button").unbind('click');
        $("#embeddedStaffModal button.image-button").bind('click', function() {
          // data('url') gets cached old result, attr('data-url') always gets new one
          let url = $(this).attr('data-url') + "?base64=1";
          executeDownloadableHandler(url);
        });
      });

      // show modal
      $('#embeddedStaffModal').modal('show');
    }
  });
}

function updateStaffInModal(staff_id) {
  let jqxhr = loadStaffWithAjax(staff_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      result['data']['readonly'] = false;
      result['data']['title'] = vueStaffDataSource.text_update_staff;
      result['data']['countries'] = vueStaffDataSource.selection_country;
      result['data']['action'] = {};
      result['data']['action'][vueStaffDataSource.button_update] = "updatePostAjax";
      populateStaffModalWithAjaxResult(result['data']);
      vueStaffModal.errors = [ ];

      // Vue.nextTick(function () {
      // });

      // show modal
      $('#embeddedStaffModal').modal('show');
    }
  });
}

function updatePostAjax() {
  $.ajax({
    type : 'POST',
    url : '/staff/' + vueStaffModal.form.id + '/ajax',
    enctype : 'multipart/form-data',
    data : new FormData($('#embeddedStaffModal form')[0]),
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
      $('#embeddedStaffModal').modal('hide');
      // update staff table
      if ('updateCallback' in vueStaffDataSource) {
        vueStaffDataSource.updateCallback(data['data']['staff']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vueStaffModal.errors = data['errors'];
    }
  });
}

$(document).ready(function() {

  vueStaffModal = new Vue({
    el : '#embeddedStaffModal',
    data : {
      modal : {
        readonly : false,
        title : '',
        csrf : '',
        country : [ ],
        action : [ ]
      },
      form : {
        id : 0,
        name : '',
        email : '',
        phone : '',
        street : '',
        unit : '',
        city : '',
        district : '',
        state : '',
        country : '',
        zipcode : '',
        file : [ ],
        file_date : [ ],
        file_title : [ ],
        file_creator : [ ],
        file_path : [ ]
      },
      errors : { }
    },
    mounted : function() {

      // attachment display modal
      $("#embeddedStaffModal div.image-group").imageBox();

      // disable ENTER
      $('#embeddedStaffModal form').keypress(function (e) {
        if (e.keyCode == 13) { e.preventDefault(); }
      });
      // ESC only works at document level and on keydown
      $(document).keydown(function (e) {
        if (e.keyCode == 27) {
          if ($('#embeddedStaffModal #downloadableAttachmentModal').hasClass("in")) {
            $('#embeddedStaffModal #downloadableAttachmentModal').modal('hide');
          } else if ($('#embeddedStaffModal').hasClass("in")) {
            $('#embeddedStaffModal').modal('hide');
          }
        }
      });
    }
  });

});
