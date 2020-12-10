// use in conjunction with ../embedded_modal/charge_entry.blade.php

var vueChargeModal = null;

function updateChargeSubtotal(index) {
  vueChargeModal.form.subtotal[index] = parseFloat(vueChargeModal.form.unitprice[index]) * parseFloat(vueChargeModal.form.quantity[index]);
  // force subtotal to update
  vueChargeModal.$forceUpdate();
}

function updateChargeAttachment(index) {
  let fullPath = $(vueChargeModal.$refs["attachment"+index]).val();
  vueChargeModal.form.filename[index] = fullPath.substring(fullPath.lastIndexOf('\\')+1);
  $(vueChargeModal.$refs["file_display"+index]).html(vueChargeModal.form.filename[index]);
}

function addNewChargeLine() {
  vueChargeModal.form.line.push(0);
  vueChargeModal.form.product.push(Object.keys(vueChargeModal.modal.item)[0]);
  vueChargeModal.form.incurdate.push(vueChargeDataSource.text_today);
  vueChargeModal.form.unitprice.push(0);
  vueChargeModal.form.quantity.push(1);
  vueChargeModal.form.description.push('');
  vueChargeModal.form.subtotal.push(0);
  vueChargeModal.form.filename.push(vueChargeDataSource.text_browse_file);
  vueChargeModal.form.fileurl.push('');
  // wait until next tick (component rendered) to install date selector
  let id = Object.keys(vueChargeModal.form.line).pop();
  Vue.nextTick(function () {
    $(vueChargeModal.$refs["incurdate"+id]).datepicker().bind('change', function() {
      vueChargeModal.form.incurdate[$(this).data('line')] = $(this).val();
    });
    $('#embeddedChargeModal div.modal-lg div.modal-body').scrollTop($('#embeddedChargeModal div.modal-lg div.modal-body')[0].scrollHeight);
  });
}

function loadChargeWithAjax(order_id) {
  // use ajax to load expense #id
  return $.ajax({
    type: 'GET',
    url: '/charge/' + order_id + '/ajax',
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
      vueChargeModal.errors = result['errors'];
    } else {
      vueChargeModal.errors = { general : "System failure" };
    }
  });
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
      $('#embeddedChargeModal div.image-group div img#image-canvas').data('url', result['content']);
      $('#embeddedChargeModal div.image-group div img#image-canvas').trigger('click');
    }
  }).fail(function() {
    // show failed message.
    $('#embeddedChargeModal #downloadableAttachmentModal div.modal-body div#img-preview').html(vueChargeDataSource.text_attachment_download_failed);
    $('#embeddedChargeModal #downloadableAttachmentModal div.modal-footer button').removeClass('hidden');
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
  });
}

function populateChargeModalWithAjaxResult(result) {
  // populate modal
  vueChargeModal.modal.readonly = result['readonly'];
  vueChargeModal.modal.history = result['history'];
  vueChargeModal.modal.title = result['title'];
  vueChargeModal.modal.csrf = result['csrf'];
  vueChargeModal.modal.entity = vueChargeDataSource.selection_entity;
  vueChargeModal.modal.staff = vueChargeDataSource.selection_staff;
  vueChargeModal.modal.currency = vueChargeDataSource.selection_currency;
  vueChargeModal.modal.item = vueChargeDataSource.selection_item;
  vueChargeModal.modal.action = result['action'];
  vueChargeModal.modal.post_url = result['post_url'];
  vueChargeModal.form.id = result['id'];
  vueChargeModal.form.increment = result['increment'];
  vueChargeModal.form.entity = result['entity'];
  vueChargeModal.form.staff = result['staff'];
  vueChargeModal.form.currency = result['currency'];
  vueChargeModal.form.reference = result['reference'];
  vueChargeModal.form.notes = result['notes'];
  vueChargeModal.form.line = result['line'];
  vueChargeModal.form.product = result['product'];
  vueChargeModal.form.incurdate = result['incurdate'];
  vueChargeModal.form.unitprice = result['unitprice'];
  vueChargeModal.form.quantity = result['quantity'];
  vueChargeModal.form.description = result['description'];
  vueChargeModal.form.subtotal = result['subtotal'];
  vueChargeModal.form.filename = result['filename'];
  vueChargeModal.form.fileurl = result['fileurl'];
}

function createChargeEntryInModal() {
  let jqxhr = loadChargeWithAjax(0);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      result['data']['readonly'] = false;
      result['data']['title'] = vueChargeDataSource.text_new_charge;
      // key can not be computed dynamically, create an empty object and stuff attribute
      result['data']['action'] = {};
      result['data']['action'][vueChargeDataSource.button_create] = 'createChargePost';
      result['data']['post_url'] = '';
      if ((result['data']['entity'] == 0) && ('id_default_entity' in vueChargeDataSource))  {
        result['data']['entity'] = vueChargeDataSource.id_default_entity;
      }
      populateChargeModalWithAjaxResult(result['data']);
      $('span[id^="upload-selector-label["]').html(vueChargeDataSource.text_upload_file);
      vueChargeModal.errors = [];
      Vue.nextTick(function () {
        // unbind file viewer
        $("#embeddedChargeModal button.image-button").unbind('click');
        // bind date selector
        $('#embeddedChargeModal input[name^="incurdate["]').datepicker().unbind('change');
        $('#embeddedChargeModal input[name^="incurdate["]').datepicker().bind('change', function(event) {
          vueChargeModal.form.incurdate[$(this).data('line')] = $(this).val();
        });
      });
      // show modal
      $('#embeddedChargeModal').modal('show');
    }
  });
}

function createChargePost() {
  $.ajax({
    type : 'POST',
    url : '/charge/create/' + vueChargeModal.form.entity + '/ajax',
    enctype : 'multipart/form-data',
    data : new FormData($('#embeddedChargeModal form')[0]),
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
      $('#embeddedChargeModal').modal('hide');
      // insert into expense table
      if ('insertCallback' in vueChargeDataSource) {
        vueChargeDataSource.insertCallback(data['data']['charge']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vueChargeModal.errors = data['errors'];
    }
  });
}

function viewChargeEntryInModal(order_id) {
  let jqxhr = loadChargeWithAjax(order_id);

  jqxhr.done(function (data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      result['data']['readonly'] = true;
      result['data']['title'] = vueChargeDataSource.text_view_charge;
      result['data']['action'] = { };
      result['data']['post_url'] = '';
      populateChargeModalWithAjaxResult(result['data']);
      vueChargeModal.errors = [];
      Vue.nextTick(function () {
        // bind file viewer
        $("#embeddedChargeModal button.image-button").unbind('click');
        $("#embeddedChargeModal button.image-button").bind('click', function() {
          // data('url') gets cached old result, attr('data-url') always gets new one
          let url = $(this).attr('data-url') + "?base64=1";
          executeDownloadableHandler(url);
        });
        // unbind date selector
        $('#embeddedChargeModal input[name^="incurdate["]').datepicker('destroy');
        $('#embeddedChargeModal input[name^="incurdate["]').removeClass('hasDatepicker');
      });
      // show modal
      $('#embeddedChargeModal').modal('show');
    }
  });
}

function updateChargeEntryInModal(order_id) {
  let jqxhr = loadChargeWithAjax(order_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      result['data']['readonly'] = false;
      result['data']['title'] = vueChargeDataSource.text_update_charge;
      // key can not be computed dynamically, create an empty object and stuff attribute
      result['data']['action'] = {};
      result['data']['action'][vueChargeDataSource.button_update] = 'updateChargePost';
      result['data']['post_url'] = '';
      populateChargeModalWithAjaxResult(result['data']);
      vueChargeModal.errors = [];
      Vue.nextTick(function () {
        // bind file viewer
        $("#embeddedChargeModal button.image-button").unbind('click');
        // bind date selector
        $('#embeddedChargeModal input[name^="incurdate["]').datepicker().unbind('change');
        $('#embeddedChargeModal input[name^="incurdate["]').datepicker().bind('change', function(event) {
          vueChargeModal.form.incurdate[$(this).data('line')] = $(this).val();
        });
      });
      // show modal
      $('#embeddedChargeModal').modal('show');
    }
  });
}

function updateChargePost() {
  $.ajax({
    type : 'POST',
    url : '/charge/update/' + vueChargeModal.form.id + '/ajax',
    enctype : 'multipart/form-data',
    data : new FormData($('#embeddedChargeModal form')[0]),
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
      $('#embeddedChargeModal').modal('hide');
      // update expense table
      if ('updateCallback' in vueChargeDataSource) {
        vueChargeDataSource.updateCallback(data['data']['charge']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vueChargeModal.errors = data['errors'];
    }
  });
}

function submitChargeEntryInModal(order_id) {
  let jqxhr = loadChargeWithAjax(order_id);

  jqxhr.done(function (data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      result['data']['readonly'] = true;
      result['data']['title'] = vueChargeDataSource.text_submit_charge;
      // key can not be computed dynamically, create an empty object and stuff attribute
      result['data']['action'] = {};
      result['data']['action'][vueChargeDataSource.button_submit] = 'submitChargePost';
      result['data']['post_url'] = '';
      populateChargeModalWithAjaxResult(result['data']);
      vueChargeModal.errors = [];
      Vue.nextTick(function () {
        // bind file viewer
        $("#embeddedChargeModal button.image-button").unbind('click');
        $("#embeddedChargeModal button.image-button").bind('click', function() {
          // data('url') gets cached old result, attr('data-url') always gets new one
          let url = $(this).attr('data-url') + "?base64=1";
          executeDownloadableHandler(url);
        });
        // unbind date selector
        $('#embeddedChargeModal input[name^="incurdate["]').datepicker('destroy');
        $('#embeddedChargeModal input[name^="incurdate["]').removeClass('hasDatepicker');
      });
      // show modal
      $('#embeddedChargeModal').modal('show');
    }
  });
}

function submitChargePost() {
  $.ajax({
    type : 'POST',
    url : '/charge/submit/' + vueChargeModal.form.id + '/ajax',
    data : {
      _token : vueChargeModal.modal.csrf
    },
    dataType: 'html',
    beforeSend: function(data) {
      $('.ajax-processing').removeClass('hidden');
    },
  }).done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      $('#embeddedChargeModal').modal('hide');
      // update expense table
      if ('updateCallback' in vueChargeDataSource) {
        vueChargeDataSource.updateCallback(result['data']['charge']);
      }
    }
  }).always(function(data) {
    let result = JSON.parse(data);
    $('.ajax-processing').addClass('hidden');
    if (!result['success']) {
      vueChargeModal.errors = result['errors'];
    }
  });
}

function approveChargeEntryInModal(order_id) {
  let jqxhr = loadChargeWithAjax(order_id);

  jqxhr.done(function (data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      result['data']['readonly'] = true;
      result['data']['title'] = vueChargeDataSource.text_approve_charge;
      // key can not be computed dynamically, create an empty object and stuff attribute
      result['data']['action'] = {};
      result['data']['action'][vueChargeDataSource.button_reject] = 'disapproveChargePost';
      result['data']['action'][vueChargeDataSource.button_approve] = 'approveChargePost';
      result['data']['post_url'] = '';
      populateChargeModalWithAjaxResult(result['data']);
      vueChargeModal.errors = [];
      Vue.nextTick(function () {
        // bind file viewer
        $("#embeddedChargeModal button.image-button").unbind('click');
        $("#embeddedChargeModal button.image-button").bind('click', function() {
          // data('url') gets cached old result, attr('data-url') always gets new one
          let url = $(this).attr('data-url') + "?base64=1";
          executeDownloadableHandler(url);
        });
        // unbind date selector
        $('#embeddedChargeModal input[name^="incurdate["]').datepicker('destroy');
        $('#embeddedChargeModal input[name^="incurdate["]').removeClass('hasDatepicker');
      });
      // show modal
      $('#embeddedChargeModal').modal('show');
    }
  });
}

function approveChargePost() {
  $.ajax({
    type : 'POST',
    url : '/charge/approve/' + vueChargeModal.form.id + '/ajax',
    data : {
      _token : vueChargeModal.modal.csrf,
      submit : 'approve'
    },
    dataType: 'html',
    beforeSend: function(data) {
      $('.ajax-processing').removeClass('hidden');
    },
  }).done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      $('#embeddedChargeModal').modal('hide');
      // update expense table
      if ('updateCallback' in vueChargeDataSource) {
        vueChargeDataSource.updateCallback(result['data']['charge']);
      }
    }
  }).always(function(data) {
    let result = JSON.parse(data);
    $('.ajax-processing').addClass('hidden');
    if (!result['success']) {
      vueChargeModal.errors = result['errors'];
    }
  });
}

function disapproveChargePost() {
  $.ajax({
    type : 'POST',
    url : '/charge/approve/' + vueChargeModal.form.id + '/ajax',
    data : {
      _token : vueChargeModal.modal.csrf,
      submit : 'disapprove'
    },
    dataType: 'html',
    beforeSend: function(data) {
      $('.ajax-processing').removeClass('hidden');
    },
  }).done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      $('#embeddedChargeModal').modal('hide');
      // update expense table
      if ('updateCallback' in vueChargeDataSource) {
        vueChargeDataSource.updateCallback(result['data']['charge']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    let result = JSON.parse(data);
    if (!result['success']) {
      vueChargeModal.errors = result['errors'];
    }
  });
}

function retractChargeEntryInModal(order_id) {
  let jqxhr = loadChargeWithAjax(order_id);

  jqxhr.done(function (data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      result['data']['readonly'] = true;
      result['data']['title'] = vueChargeDataSource.text_retract_charge;
      // key can not be computed dynamically, create an empty object and stuff attribute
      result['data']['action'] = {};
      result['data']['action'][vueChargeDataSource.button_retract] = 'retractChargePost';
      result['data']['post_url'] = '';
      populateChargeModalWithAjaxResult(result['data']);
      vueChargeModal.errors = [];
      Vue.nextTick(function () {
        // bind file viewer
        $("#embeddedChargeModal button.image-button").unbind('click');
        $("#embeddedChargeModal button.image-button").bind('click', function() {
          // data('url') gets cached old result, attr('data-url') always gets new one
          let url = $(this).attr('data-url') + "?base64=1";
          executeDownloadableHandler(url);
        });
        // unbind date selector
        $('#embeddedChargeModal input[name^="incurdate["]').datepicker('destroy');
        $('#embeddedChargeModal input[name^="incurdate["]').removeClass('hasDatepicker');
      });
      // show modal
      $('#embeddedChargeModal').modal('show');
    }
  });
}

function retractChargePost() {
  $.ajax({
    type : 'POST',
    url : '/charge/retract/' + vueChargeModal.form.id + '/ajax',
    data : {
      _token : vueChargeModal.modal.csrf
    },
    dataType: 'html',
    beforeSend: function(data) {
      $('.ajax-processing').removeClass('hidden');
    },
  }).done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      $('#embeddedChargeModal').modal('hide');
      // update expense table
      if ('updateCallback' in vueChargeDataSource) {
        vueChargeDataSource.updateCallback(result['data']['charge']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    let result = JSON.parse(data);
    if (!result['success']) {
      vueChargeModal.errors = result['errors'];
    }
  });
}

$(document).ready(function() {

  vueChargeModal = new Vue({
    el : "#embeddedChargeModal",
    data : {
      modal : {
        readonly : false,
        history : [],
        csrf : '',
        action : '',
        post_url : '',
        title : '',
        entity : [],
        staff : [],
        currency : [],
        item : []
      },
      form : {
        id : 0,
        increment : '',
        entity : 0,
        staff : 0,
        currency : 0,
        reference : '',
        notes : '',
        line : [],
        product : [],
        incurdate : [],
        unitprice : [],
        quantity : [],
        description : [],
        subtotal : [],
        filename : [],
        fileurl : []
      },
      errors : { }
    },
    mounted : function() {

      // attachment display modal
      $("#embeddedChargeModal div.image-group").imageBox();

      // disable ENTER
      $('#embeddedChargeModal form#charge_entry').keypress(function (e) {
        if (e.keyCode == 13) { e.preventDefault(); }
      });
      // ESC only works at document level and on keydown
      $(document).keydown(function (e) {
        if (e.keyCode == 27) {
          if ($('#embeddedChargeModal #downloadableAttachmentModal').hasClass("in")) {
            $('#embeddedChargeModal #downloadableAttachmentModal').modal('hide');
          }  else if ($('#embeddedChargeModal #historyModal').hasClass("in")) {
            $('#embeddedChargeModal #historyModal').modal('hide');
          } else if ($('#embeddedChargeModal').hasClass("in")) {
            $('#embeddedChargeModal').modal('hide');
          }
        }
      });
    }
  });

});
