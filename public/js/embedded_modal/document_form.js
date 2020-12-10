// use in conjunction with ../embedded_modal/document_form.blade.php

var vueDocumentModal = null;

function loadDocumentWithAjax(document_id) {
  // use ajax to load document #id
  return $.ajax({
    type: 'GET',
    url: '/document/' + document_id + '/ajax',
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
      vueDocumentModal.errors = result['errors'];
    } else {
      vueDocumentModal.errors = { general : "System failure" };
    }
  });
}

function populateDocumentModalWithAjaxResult(result) {
  vueDocumentModal.modal.readonly = result.readonly;
	vueDocumentModal.modal.title = result.modal_title;
	vueDocumentModal.modal.csrf = result.csrf;
	vueDocumentModal.modal.action = result.action;
	vueDocumentModal.modal.poast_url = '';
	vueDocumentModal.modal.history = result.history
	vueDocumentModal.form.id = result.id;
	vueDocumentModal.form.title = result.title;
	vueDocumentModal.form.version = result.version;
	vueDocumentModal.form.description = result.description;
	vueDocumentModal.form.creator_id = result.creator_id;
	vueDocumentModal.form.file_name = result.file_name;
	vueDocumentModal.form.permission = result.permission;
}

function createDocumentInModal() {
	let jqxhr = loadDocumentWithAjax(0);

	jqxhr.done(function(data) {
		let result = JSON.parse(data);
		if (result['success']) {
			result['data']['readonly'] = false;
			result['data']['modal_title'] = vueDocumentDataSource.text_create_document;
			result['data']['action'] = { };
			result['data']['action'][vueDocumentDataSource.button_create] = "createDocumentPostAjax";

      populateDocumentModalWithAjaxResult(result['data']);
      // remedial fix, reset file-browse button
      $('#embeddedDocumentModal #thefile').val('');
      $('#embeddedDocumentModal #upload-selector-label').html(vueDocumentDataSource.text_browse_file);
      vueDocumentModal.errors = [];

			Vue.nextTick(function () {
      });

      // show modal
      $('#embeddedDocumentModal').modal('show');
		}
	});
}

function createDocumentPostAjax() {
	// finish this function
	$.ajax({
		type : 'POST',
		url : '/document/create/ajax',
		enctype : 'multipart/form-data',
		data : new FormData($('#embeddedDocumentModal form')[0]),
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
			$('#embeddedDocumentModal').modal('hide');
			// update order/return table
			if ('refreshCallback' in vueDocumentDataSource) {
				vueDocumentDataSource.refreshCallback(data['data']);
			}
		}
	}).always(function(data) {
		$('.ajax-processing').addClass('hidden');
		if (!data['success']) {
			vueDocumentModal.errors = data['errors'];
		}
	});
}

function updateDocumentInModal(document_id) {
	let jqxhr = loadDocumentWithAjax(document_id);

	jqxhr.done(function(data) {
		let result = JSON.parse(data);
		if (result['success']) {
			result['data']['readonly'] = false;
			result['data']['modal_title'] = vueDocumentDataSource.text_update_document;
			result['data']['action'] = { };
			result['data']['action'][vueDocumentDataSource.button_update] = "updateDocumentPostAjax";

      populateDocumentModalWithAjaxResult(result['data']);
      // remedial fix, reset file-browse button
      $('#embeddedDocumentModal #thefile').val('');
      $('#embeddedDocumentModal #upload-selector-label').html(vueDocumentDataSource.text_browse_file);
      vueDocumentModal.errors = [];

			Vue.nextTick(function () {
      });

      // show modal
      $('#embeddedDocumentModal').modal('show');
		}
	});
}

function updateDocumentPostAjax() {
	// finish this function
	$.ajax({
		type : 'POST',
		url : '/document/update/' + vueDocumentModal.form.id + '/ajax',
		enctype : 'multipart/form-data',
		data : new FormData($('#embeddedDocumentModal form')[0]),
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
			$('#embeddedDocumentModal').modal('hide');
			// update order/return table
			if ('refreshCallback' in vueDocumentDataSource) {
				vueDocumentDataSource.refreshCallback(data['data']);
			}
		}
	}).always(function(data) {
		$('.ajax-processing').addClass('hidden');
		if (!data['success']) {
			vueDocumentModal.errors = data['errors'];
		}
	});
}

function viewDocumentInModal(document_id) {
	let jqxhr = loadDocumentWithAjax(document_id);

	jqxhr.done(function(data) {
		let result = JSON.parse(data);
		if (result['success']) {
			result['data']['readonly'] = true;
			result['data']['modal_title'] = vueDocumentDataSource.text_view_document;
			result['data']['action'] = { };

      populateDocumentModalWithAjaxResult(result['data']);
      vueDocumentModal.errors = [];

			Vue.nextTick(function () {
      });
      // show modal
      $('#embeddedDocumentModal').modal('show');
		}
	});
}

function deleteDocumentInModal(document_id) {
	let jqxhr = loadDocumentWithAjax(document_id);

	jqxhr.done(function(data) {
		let result = JSON.parse(data);
		if (result['success']) {
			result['data']['readonly'] = true;
			result['data']['modal_title'] = vueDocumentDataSource.text_delete_document;
			result['data']['action'] = { };
			result['data']['action'][vueDocumentDataSource.button_delete] = "deleteDocumentPostAjax";

      populateDocumentModalWithAjaxResult(result['data']);
      vueDocumentModal.errors = [];

			Vue.nextTick(function () {
      });

      // show modal
      $('#embeddedDocumentModal').modal('show');
		}
	});
}

function deleteDocumentPostAjax() {
	// finish this function
	$.ajax({
		type : 'POST',
		url : '/document/delete/' + vueDocumentModal.form.id + '/ajax',
		data : {
			_token : vueDocumentModal.modal.csrf,
		},
		dataType: 'json',
		beforeSend: function(data) {
			$('.ajax-processing').removeClass('hidden');
		},
	}).done(function(data) {
		// data is already an object, no need to parse it.
		if (data['success']) {
			$('#embeddedDocumentModal').modal('hide');
			// update order/return table
			if ('refreshCallback' in vueDocumentDataSource) {
				vueDocumentDataSource.refreshCallback(data['data']);
			}
		}
	}).always(function(data) {
		$('.ajax-processing').addClass('hidden');
		if (!data['success']) {
			vueDocumentModal.errors = data['errors'];
		}
	});
}

$(document).ready(function() {

  vueDocumentModal = new Vue({
    el : '#embeddedDocumentModal',
    data : {
      modal : {
        readonly : true,
        title : '',
        csrf : '',
        action : [ ],
        post_url : '',
        history : [ ]
      },
      form : {
        id : 0,
				title : '',
				version : 1,
				creator_id : 0,
				description : '',
				file_name : '',
				permission : [ ]
      },
      errors : { }
    },
    mounted : function() {
      // disable ENTER
      $('#embeddedDocumentModal form').keypress(function (e) {
        if (e.keyCode == 13) { e.preventDefault(); }
      });
      $(document).keydown(function (e) {
        if (e.keyCode == 27) {
          if ($('#embeddedDocumentModal #historyModal').hasClass("in")) {
            $('#embeddedDocumentModal #historyModal').modal('hide');
          } else if ($('#embeddedDocumentModal').hasClass("in")) {
            $('#embeddedDocumentModal').modal('hide');
          }
        }
      });
    }
  });

});
