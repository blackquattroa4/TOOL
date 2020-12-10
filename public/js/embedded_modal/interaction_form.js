// use in conjunction with ../embedded_modal/interaction_form.blade.php

var vueInteractionModal = null;

var mediaButtonHandler = function() {

  let url = '/file/download/' + $(this).data('hash') + '?base64=1';

  $.ajax({
    type: 'GET',
    url: url,
    dataType: 'html',
    beforeSend: function(data) {
      $('.ajax-processing').removeClass('hidden');
    },
  }).done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      $('div#embeddedInteractionModal div.image-group div img#image-canvas').data('url', result['content']);
      $('div#embeddedInteractionModal div.image-group div img#image-canvas').trigger('click');
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    let result = JSON.parse(data);
    if (!result['success']) {
      vueInteractionModal.errors = result['errors'];
    }
  });
};

// function prepares log-entry modal
function openNewLogModal() {
  vueInteractionModal.errors = { };

  $('div#embeddedInteractionModal div#newLogModal div.modal-body input').val('');
  $('div#embeddedInteractionModal div#newLogModal div.modal-body textarea').val('');
  Dropzone.forElement("div#embeddedInteractionModal div#uploaded-files").removeAllFiles(true);			// clear out dropbox
  $('div#embeddedInteractionModal div#newLogModal').modal('show');
}

// Ajax function to add new request
function submitNewLog()
{
  vueInteractionModal.errors = { };

  // error checking
  if ($('div#embeddedInteractionModal div#newLogModal div.modal-body input#title').val() === "") {
    vueInteractionModal.errors.title = [ vueInteractionDataSource.text_validation_required.replace(":attribute", "title") ];
  }

  if ($('div#embeddedInteractionModal div#newLogModal div.modal-body textarea').val() === "") {
    vueInteractionModal.errors.description = [ vueInteractionDataSource.text_validation_required.replace(":attribute", "description") ];
  }

  if (('title' in vueInteractionModal.errors) || ('description' in vueInteractionModal.errors)) return;

  $.ajax({
    type: 'POST',
    url: '/interaction/update/' + vueInteractionModal.form.id,
    data: {
        description: $('div#embeddedInteractionModal div#newLogModal div.modal-body textarea').val(),
        files: $('div#embeddedInteractionModal div#newLogModal div.modal-body input#files').val(),
        _token: vueInteractionModal.modal.csrf,
      },
    dataType: 'html',
    beforeSend: function(data) {
      $('.ajax-processing').removeClass('hidden');
    },
  }).done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      $('div#embeddedInteractionModal div#newLogModal').modal('hide');
      // refresh modal.
      vueInteractionModal.form.groupLog = result['data']['groupLogs'];
      // update table
      if ('updateCallback' in vueInteractionDataSource) {
        vueInteractionDataSource.updateCallback(result['data']['interaction']);
      }
      Vue.nextTick(function () {
        // unbind all media-button
        $("div#embeddedInteractionModal button.media-button").unbind('click');
        // rebind all media-button
        $("div#embeddedInteractionModal button.media-button").bind('click', mediaButtonHandler);
      });
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    let result = JSON.parse(data);
    if (!result['success']) {
      vueInteractionModal.errors = result['errors'];
    }
  });
}

function openUpdateModal()
{
  vueInteractionModal.errors = { };

  $('div#embeddedInteractionModal div#updateModal').modal('show');
}

function submitUpdate()
{
  vueInteractionModal.errors = { };

  // error checking
  if (($('div#embeddedInteractionModal div#updateModal div.modal-body select#interaction_type').val() === "") ||
      ($('div#embeddedInteractionModal div#updateModal div.modal-body select#interaction_type').val() == null)) {
    vueInteractionModal.errors.type = [ vueInteractionDataSource.text_validation_required.replace(":attribute", "type") ];
  }

  if (($('div#embeddedInteractionModal div#updateModal div.modal-body select#interaction_status').val() === "") ||
      ($('div#embeddedInteractionModal div#updateModal div.modal-body select#interaction_status').val() == null)) {
    vueInteractionModal.errors.status = [ vueInteractionDataSource.text_validation_required.replace(":attribute", "status") ];
  }

  if (($('div#embeddedInteractionModal div#updateModal div.modal-body select#interaction_responsibility').val() === "") ||
      ($('div#embeddedInteractionModal div#updateModal div.modal-body select#interaction_responsibility').val() == null)) {
    vueInteractionModal.errors.responder_id = [ vueInteractionDataSource.text_validation_required.replace(":attribute", "responder_id") ];
  }

  if (('type' in vueInteractionModal.errors) || ('status' in vueInteractionModal.errors) || ('responder_id' in vueInteractionModal.errors)) return;

  $.ajax({
    type: 'POST',
    url: '/interaction/' + vueInteractionModal.form.id,
    data: {
        type: $('div#embeddedInteractionModal #updateModal div.modal-body select#interaction_type').val(),
        status: $('div#embeddedInteractionModal #updateModal div.modal-body select#interaction_status').val(),
        responsibility: $('div#embeddedInteractionModal #updateModal div.modal-body select#interaction_responsibility').val(),
        _token: vueInteractionModal.modal.csrf,
      },
    dataType: 'html',
    beforeSend: function(data) {
      $('.ajax-processing').removeClass('hidden');
    },
  }).done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      $('div#embeddedInteractionModal div#updateModal').modal('hide');
      // refresh is not needed since newly entered data is reflected on screen, thanks to VUE
      // update table
      if ('updateCallback' in vueInteractionDataSource) {
        vueInteractionDataSource.updateCallback(result['data']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    let result = JSON.parse(data);
    if (!result['success']) {
      vueInteractionModal.errors = result['errors'];
    }
  });
}

function loadInteractionWithAjax(interaction_id) {
  // use ajax to load interaction #id
  return $.ajax({
    type: 'GET',
    url: '/interaction/' + interaction_id + '/ajax',
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
      vueInteractionModal.errors = result['errors'];
    } else {
      vueInteractionModal.errors = { general : "System failure" };
    }
  });
}

function populateInteractionModalWithAjaxResult(data) {
  vueInteractionModal.modal.readonly = data.readonly;
  vueInteractionModal.modal.csrf = data.csrf;
  vueInteractionModal.modal.allTypes = vueInteractionDataSource.selection_type;
  vueInteractionModal.modal.allStatuses = vueInteractionDataSource.selection_status;
  vueInteractionModal.modal.allParticipants = data.participants;
  vueInteractionModal.modal.canUpdate = data.can_update;
  vueInteractionModal.form.id = data.id;
  vueInteractionModal.form.description = data.description;
  vueInteractionModal.form.type = data.type;
  vueInteractionModal.form.status = data.status;
  vueInteractionModal.form.responder_id = data.responder_id;
  vueInteractionModal.form.groupLog = data.groupLog;
}

function accessInteractionInModal(interaction_id) {
  let jqxhr = loadInteractionWithAjax(interaction_id);

  jqxhr.done(function (data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      result['data']['readonly'] = false;
      populateInteractionModalWithAjaxResult(result['data']);
      vueInteractionModal.errors = [];

      Vue.nextTick(function () {
        // unbind all media-button
        $("div#embeddedInteractionModal div#embeddedInteractionModal button.media-button").unbind('click');
        // rebind all media-button
        $("div#embeddedInteractionModal button.media-button").bind('click', mediaButtonHandler);
      });

      // show modal
      $('#embeddedInteractionModal').modal('show');
    }
  });
}

$(document).ready(function() {

  vueInteractionModal = new Vue({
    el : "#embeddedInteractionModal",
    data : {
      modal : {
        readonly : true,
        csrf : '',
        allTypes : [ ],
        allStatuses : [ ],
        allParticipants : [ ],
        canUpadte : false
      },
      form : {
        id : '',
        description : '',
        type : '',
        status : '',
        responder_id : 0,
        groupLog : [ ]
      },
      errors : { }
    },
    mounted : function() {

      // attachment display modal
      $("#embeddedInteractionModal div.image-group").imageBox();

      // disable ENTER
      $('#embeddedInteractionModal form').keypress(function (e) {
        if (e.keyCode == 13) { e.preventDefault(); }
      });
      // ESC only works at document level and on keydown
      $(document).keydown(function (e) {
        if (e.keyCode == 27) {
          if ($('#embeddedInteractionModal #newLogModal').hasClass("in")) {
            $('#embeddedInteractionModal #newLogModal').modal('hide');
          }  else if ($('#embeddedInteractionModal #updateModal').hasClass("in")) {
            $('#embeddedInteractionModal #updateModal').modal('hide');
          }  else if ($('#embeddedInteractionModal #downloadableAttachmentModal').hasClass("in")) {
            $('#embeddedInteractionModal #downloadableAttachmentModal').modal('hide');
          } else if ($('#embeddedInteractionModal').hasClass("in")) {
            $('#embeddedInteractionModal').modal('hide');
          }
        }
      });
    }
  });

});
