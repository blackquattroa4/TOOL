
function loadRecurringChargeWithAjax(recurring_id) {
  // use ajax to load expense #id
  return $.ajax({
    type: 'GET',
    url: '/charge/recurring/' + recurring_id + '/ajax',
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

function createChargeEntryFromRecurringInModal(recurring_id) {
  let jqxhr = loadRecurringChargeWithAjax(recurring_id);

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
      // if entity-id is not set and there's a preferred default entity-id specified, we use that id
      if ((result['data']['entity'] == 0) && ('id_default_entity' in vueChargeDataSource)) {
        result['data']['entity'] = vueChargeDataSource.id_default_entity;
      }
      populateChargeModalWithAjaxResult(result['data']);
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
