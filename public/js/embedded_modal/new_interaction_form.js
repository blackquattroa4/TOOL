// use in conjunction with ../embedded_modal/new_interaction_form.blade.php

var vueNewInteractionModal = null;

// functions prepares archive modal
function openNewInteractionModal() {
  // capture a csrf token for POST
  let jqxhr = loadInteractionWithAjax(0);
  jqxhr.done(function (data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      result['data']['readonly'] = true;
      populateInteractionModalWithAjaxResult(result['data']);
    }
  });

  vueInteractionModal.errors = { };

  $('div#embeddedNewInteractionModal div.modal-body input').val('');
  $('div#embeddedNewInteractionModal div.modal-body textarea').val('');
  Dropzone.forElement("div#embeddedNewInteractionModal #uploaded-files").removeAllFiles(true);			// clear out dropbox
  $('div#embeddedNewInteractionModal').modal('show');
}

// Ajax function to add new request
function submitNewRequest()
{
  vueInteractionModal.errors = { };

  // do some error checking
  if ($('div#embeddedNewInteractionModal div.modal-body input#title').val() === "") {
    vueInteractionModal.errors.title = [ vueInteractionDataSource.text_validation_required.replace(":attribute", "title") ];
  }

  if ($('div#embeddedNewInteractionModal div.modal-body textarea').val() === "") {
    vueInteractionModal.errors.description = [ vueInteractionDataSource.text_validation_required.replace(":attribute", "description") ];
  }

  if (('title' in vueInteractionModal.errors) || ('description' in vueInteractionModal.errors)) return;

  $.ajax({
    type: 'POST',
    url: '/interaction/create',
    data: {
        title: $('div#embeddedNewInteractionModal div.modal-body input#title').val(),
        description: $('div#embeddedNewInteractionModal div.modal-body textarea').val(),
        files: $('div#embeddedNewInteractionModal div.modal-body input#files').val(),
        _token: vueInteractionModal.modal.csrf,
      },
    dataType: 'html',
    beforeSend: function(data) {
      $('.ajax-processing').removeClass('hidden');
    },
  }).done(function(data) {
    var result = JSON.parse(data);
    if (result['success']) {
      $('div#embeddedNewInteractionModal').modal('hide');
      // add to table.
      if ('insertCallback' in vueInteractionDataSource) {
        vueInteractionDataSource.insertCallback(result['data']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    var result = JSON.parse(data);
    if (!result['success']) {
      vueInteractionModal.errors = result['errors'];
    }
  });
}

$(document).ready(function() {

  vueNewInteractionModal = new Vue({
    el : "#embeddedNewInteractionModal",
    data : {
      modal : {
        csrf : '',
      },
      errors : { }
    }
  });

});
