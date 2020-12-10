// use in conjunction with ../embedded_modal/income_form.blade.php

var vueIncomeReceiptModal = null;

function updateUnappliedReceivableCredit() {
  let total = parseFloat(vueIncomeReceiptModal.form.available) + parseFloat(vueIncomeReceiptModal.form.amount_received);

  for (index in vueIncomeReceiptModal.form.transactable_id) {
    let amount = parseFloat(vueIncomeReceiptModal.form.transactable_applied[index]);

    // is amount > 0 && < balance, if so, clear error, if not, generate error
    if ((amount >= 0) && (amount <= parseFloat(vueIncomeReceiptModal.form.transactable_balance_raw[index]))) {
      // clear this error
      delete vueIncomeReceiptModal.errors['transactable.'+index];
    } else {
      // set the error
      vueIncomeReceiptModal.errors['transactable.'+index] = [ ];
      vueIncomeReceiptModal.errors['transactable.'+index].push( vueIncomeReceiptDataSource.text_not_greater_than_balance );
    }

    total += vueIncomeReceiptModal.form.transactable_credit[index] ? amount : -amount;

  }

  if (total < 0) {
    for (index in vueIncomeReceiptModal.form.transactable_id) {
      if (!('transactable.'+index in vueIncomeReceiptModal.errors)) {
        vueIncomeReceiptModal.errors['transactable.'+index] = [ ];
      }
      vueIncomeReceiptModal.errors['transactable.'+index].push( vueIncomeReceiptDataSource.text_not_enough_credit );
    }
  }

  vueIncomeReceiptModal.modal.available_display = total.toLocaleString(vueIncomeReceiptModal.modal.currency_regex, { style: 'currency', currency: vueIncomeReceiptModal.modal.currency_symbol });

  $('#embeddedIncomeReceiptModal .show-balance-hint').trigger('mouseenter');
  $('#embeddedIncomeReceiptModal .show-balance').trigger('mouseenter');
  $('#embeddedIncomeReceiptModal .show-balance').trigger('mouseleave');
}

function loadUnpaidReceivableAjax(entity_id) {
  return $.ajax({
    type: 'GET',
    url: '/income-from/' + entity_id + '/ajax',
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
      vueIncomeReceiptModal.errors = result['errors'];
    } else {
      vueIncomeReceiptModal.errors = { general : "System failure" };
    }
  });
}

function populateIncomeReceiptModalWithAjaxResult(result) {
	vueIncomeReceiptModal.modal.readonly = result['readonly'];
	vueIncomeReceiptModal.modal.title = result['title'];
	vueIncomeReceiptModal.modal.csrf = result['csrf'];
	vueIncomeReceiptModal.modal.action = result['action'];
	vueIncomeReceiptModal.modal.bank_account = result['bank_accounts'];
	vueIncomeReceiptModal.modal.currency_regex = result['currency_regex'];
	vueIncomeReceiptModal.modal.currency_symbol = result['currency_symbol'];
  vueIncomeReceiptModal.modal.available_display = parseFloat(result['available']).toLocaleString(result['currency_regex'], { style: 'currency', currency: result['currency_symbol'] });
	vueIncomeReceiptModal.modal.currency_min = result['currency_min'];
	vueIncomeReceiptModal.form.entity_id = result['entity_id'];
	vueIncomeReceiptModal.form.code = result['code'];
	vueIncomeReceiptModal.form.available = result['available'];
	vueIncomeReceiptModal.form.bank_account = result['bank_account'];
	vueIncomeReceiptModal.form.inputdate = result['inputdate'];
	vueIncomeReceiptModal.form.reference = result['reference'];
	vueIncomeReceiptModal.form.amount_received = result['amount_received'];
	vueIncomeReceiptModal.form.transactable_id = result['transactable_id'];
	vueIncomeReceiptModal.form.transactable_title = result['transactable_title'];
	vueIncomeReceiptModal.form.transactable_date = result['transactable_date'];
	vueIncomeReceiptModal.form.transactable_duedate = result['transactable_duedate'];
	vueIncomeReceiptModal.form.transactable_summary = result['transactable_summary'];
	vueIncomeReceiptModal.form.transactable_credit = result['transactable_credit'];
	vueIncomeReceiptModal.form.transactable_total = result['transactable_total'];
	vueIncomeReceiptModal.form.transactable_balance = result['transactable_balance'];
	vueIncomeReceiptModal.form.transactable_balance_raw = result['transactable_balance_raw'];
	vueIncomeReceiptModal.form.transactable_applied = new Array(result['transactable_id'].length).fill(0);
}

function payReceivableInModal(entity_id) {
  let jqxhr = loadUnpaidReceivableAjax(entity_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      result['data']['readonly'] = false;
      result['data']['title'] = vueIncomeReceiptDataSource.text_title;
      result['data']['action'] = {};
			result['data']['action'][vueIncomeReceiptDataSource.button_update] = "payReceivablePostAjax";
      result['data']['bank_accounts'] = vueIncomeReceiptDataSource.selection_bank_account;
			result['data']['bank_account'] = '';
			result['data']['inputdate'] = vueIncomeReceiptDataSource.text_today;
			result['data']['reference'] = '',
			result['data']['amount_received'] = 0;
      populateIncomeReceiptModalWithAjaxResult(result['data']);
      vueIncomeReceiptModal.errors = [];

      Vue.nextTick(function () {
				// unbind date-picker
        $('#embeddedIncomeReceiptModal #inputdate').datepicker('destroy');
        $('#embeddedIncomeReceiptModal #inputdate').removeClass('hasDatepicker');
        // re-bind date-picker
        $('#embeddedIncomeReceiptModal #inputdate').datepicker().bind('change', function(event) {
          vueIncomeReceiptModal.form.inputdate = $(this).val();
        });
      });

      // show modal
      $('#embeddedIncomeReceiptModal').modal('show');
    }
  });

}

function payReceivablePostAjax() {

  if (vueIncomeReceiptModal.form.bank_account == "") {
    if (!('bank_account' in vueIncomeReceiptModal.errors)) {
      vueIncomeReceiptModal.errors['bank_account'] = [ ];
    }
    vueIncomeReceiptModal.errors['bank_account'].push( vueIncomeReceiptDataSource.text_select_a_bank_account );
    vueIncomeReceiptModal.$forceUpdate();
    return;
  }

  // delete bank account error from previous failed attempt, if any.
  delete vueIncomeReceiptModal.errors['bank_account'];

  $.ajax({
    type : 'POST',
    url : '/income-from/' + vueIncomeReceiptModal.form.entity_id + '/ajax',
    enctype : 'multipart/form-data',
    data : new FormData($('#embeddedIncomeReceiptModal form')[0]),
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
      $('#embeddedIncomeReceiptModal').modal('hide');
      // update customer table
      if ('updateCallback' in vueIncomeReceiptDataSource) {
        vueIncomeReceiptDataSource.updateCallback(data['data']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vueIncomeReceiptModal.errors = data['errors'];
    }
  });
}

$(document).ready(function() {

  vueIncomeReceiptModal = new Vue({
    el : "#embeddedIncomeReceiptModal",
    data : {
      modal : {
        readonly : false,
        title : '',
				csrf : '',
        action : [ ],
        bank_account : [ ],
				currency_regex : '',
				currency_symbol : '',
				available_display : '',
				currency_min : 0.01
      },
      form : {
				entity_id : 0,
        code : '',
        available : 0,
        bank_account : 0,
        inputdate : '',
        reference : '',
        amount_received : 0,
        transactable_id : [ ],
        transactable_title : [ ],
        transactable_date : [ ],
        transactable_duedate : [ ],
        transactable_summary : [ ],
        transactable_credit : [ ],
        transactable_total : [ ],
        transactable_balance : [ ],
				transactable_balance_raw : [ ],
        transactable_applied : [ ]
      },
      errors : { }
    },
    mounted : function() {
      // disable ENTER
      $('#embeddedIncomeReceiptModal form').keypress(function (e) {
        if (e.keyCode == 13) { e.preventDefault(); }
      });
      // ESC only works at document level and on keydown
      $(document).keydown(function (e) {
        if (e.keyCode == 27) {
          if ($('#embeddedIncomeReceiptModal').hasClass("in")) {
            $('#embeddedIncomeReceiptModal').modal('hide');
          }
        }
      });

      $('#embeddedIncomeReceiptModal').on('shown.bs.modal', function (e) {
				$('#embeddedIncomeReceiptModal .show-balance-hint').trigger('mouseenter');
				$('#embeddedIncomeReceiptModal .show-balance').trigger('mouseleave');
      });

			$('#embeddedIncomeReceiptModal .show-balance').hover(
				function() {  // mouse-enter
					$(this).stop();
				  $('#embeddedIncomeReceiptModal .show-balance-hint').hide();
				  $(this).css("opacity", 1);
				  $(this).show();
				},
				function() {  // mouse-leave
					$(this).fadeOut({
				      duration: 3000,
				      complete: function() {
				        $('#embeddedIncomeReceiptModal .show-balance-hint').show();
				      },
				    });
				}
			);
			$('#embeddedIncomeReceiptModal .show-balance-hint').hover(
				function() {  // mouse-enter
					$(this).hide();
					$('#embeddedIncomeReceiptModal .show-balance').show();
				},
				function() {  // mouse-leave
					// nothing to be done here
				}
			);
    }
  });

});
