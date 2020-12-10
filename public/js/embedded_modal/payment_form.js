// use in conjunction with ../embedded_modal/payment_form.blade.php

var vuePaymentExpenditureModal = null;

function updateUnappliedPayableCredit() {
  let total = 0;

  for (index in vuePaymentExpenditureModal.form.transactable_id) {
    let amount = parseFloat(vuePaymentExpenditureModal.form.transactable_applied[index]);

    // is amount > 0 && < balance, if so, clear error, if not, generate error
    if ((amount >= 0) && (amount <= parseFloat(vuePaymentExpenditureModal.form.transactable_balance_raw[index]))) {
      // clear this error
      delete vuePaymentExpenditureModal.errors['transactable.'+index];
    } else {
      // set the error
      vuePaymentExpenditureModal.errors['transactable.'+index] = [ ];
      vuePaymentExpenditureModal.errors['transactable.'+index].push( vuePaymentExpenditureDataSource.text_not_greater_than_balance );
    }

    total += vuePaymentExpenditureModal.form.transactable_credit[index] ? amount : -amount;

  }

  if (total < 0) {
    for (index in vuePaymentExpenditureModal.form.transactable_id) {
      if (!('transactable.'+index in vuePaymentExpenditureModal.errors)) {
        vuePaymentExpenditureModal.errors['transactable.'+index] = [ ];
      }
      vuePaymentExpenditureModal.errors['transactable.'+index].push( vuePaymentExpenditureDataSource.text_not_enough_credit );
    }
  }

  vuePaymentExpenditureModal.modal.total_display = total.toLocaleString(vuePaymentExpenditureModal.modal.currency_regex, { style: 'currency', currency: vuePaymentExpenditureModal.modal.currency_symbol });

  $('#embeddedPayDisbursementModal .show-balance-hint').trigger('mouseenter');
  $('#embeddedPayDisbursementModal .show-balance').trigger('mouseenter');
  $('#embeddedPayDisbursementModal .show-balance').trigger('mouseleave');
}

function loadUnpaidPayableAjax(entity_id) {
  return $.ajax({
    type: 'GET',
    url: '/pay-to/' + entity_id + '/ajax',
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
      vuePaymentExpenditureModal.errors = result['errors'];
    } else {
      vuePaymentExpenditureModal.errors = { general : "System failure" };
    }
  });
}

function populatePaymentDisbursementModalWithAjaxResult(result) {
	vuePaymentExpenditureModal.modal.readonly = result['readonly'];
	vuePaymentExpenditureModal.modal.title = result['title'];
	vuePaymentExpenditureModal.modal.csrf = result['csrf'];
	vuePaymentExpenditureModal.modal.action = result['action'];
	vuePaymentExpenditureModal.modal.bank_account = result['bank_accounts'];
	vuePaymentExpenditureModal.modal.currency_regex = result['currency_regex'];
	vuePaymentExpenditureModal.modal.currency_symbol = result['currency_symbol'];
  vuePaymentExpenditureModal.modal.total_display = parseFloat("0").toLocaleString(result['currency_regex'], { style: 'currency', currency: result['currency_symbol'] });
	vuePaymentExpenditureModal.modal.currency_min = result['currency_min'];
	vuePaymentExpenditureModal.form.entity_id = result['entity_id'];
	vuePaymentExpenditureModal.form.code = result['code'];
	vuePaymentExpenditureModal.form.bank_account = result['bank_account'];
	vuePaymentExpenditureModal.form.inputdate = result['inputdate'];
	vuePaymentExpenditureModal.form.reference = result['reference'];
	vuePaymentExpenditureModal.form.transactable_id = result['transactable_id'];
	vuePaymentExpenditureModal.form.transactable_title = result['transactable_title'];
	vuePaymentExpenditureModal.form.transactable_date = result['transactable_date'];
	vuePaymentExpenditureModal.form.transactable_duedate = result['transactable_duedate'];
	vuePaymentExpenditureModal.form.transactable_summary = result['transactable_summary'];
	vuePaymentExpenditureModal.form.transactable_credit = result['transactable_credit'];
	vuePaymentExpenditureModal.form.transactable_total = result['transactable_total'];
	vuePaymentExpenditureModal.form.transactable_balance = result['transactable_balance'];
	vuePaymentExpenditureModal.form.transactable_balance_raw = result['transactable_balance_raw'];
	vuePaymentExpenditureModal.form.transactable_applied = new Array(result['transactable_id'].length).fill(0);
}

function payPayableInModal(entity_id) {
  let jqxhr = loadUnpaidPayableAjax(entity_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      result['data']['readonly'] = false;
      result['data']['title'] = vuePaymentExpenditureDataSource.text_title;
      result['data']['action'] = {};
			result['data']['action'][vuePaymentExpenditureDataSource.button_update] = "payPayablePostAjax";
      result['data']['bank_accounts'] = vuePaymentExpenditureDataSource.selection_bank_account;
			result['data']['bank_account'] = '';
			result['data']['inputdate'] = vuePaymentExpenditureDataSource.text_today;
			result['data']['reference'] = '';
      populatePaymentDisbursementModalWithAjaxResult(result['data']);
      vuePaymentExpenditureModal.errors = [];

      Vue.nextTick(function () {
				// unbind date-picker
        $('#embeddedPayDisbursementModal #inputdate').datepicker('destroy');
        $('#embeddedPayDisbursementModal #inputdate').removeClass('hasDatepicker');
        // re-bind date-picker
        $('#embeddedPayDisbursementModal #inputdate').datepicker().bind('change', function(event) {
          vuePaymentExpenditureModal.form.inputdate = $(this).val();
        });
      });

      // show modal
      $('#embeddedPayDisbursementModal').modal('show');
    }
  });

}

function payPayablePostAjax() {

  if (vuePaymentExpenditureModal.form.bank_account == "") {
    if (!('bank_account' in vuePaymentExpenditureModal.errors)) {
      vuePaymentExpenditureModal.errors['bank_account'] = [ ];
    }
    vuePaymentExpenditureModal.errors['bank_account'].push( vuePaymentExpenditureDataSource.text_select_a_bank_account );
    vuePaymentExpenditureModal.$forceUpdate();
    return;
  }

  // delete bank account error from previous failed attempt, if any.
  delete vuePaymentExpenditureModal.errors['bank_account'];

  $.ajax({
    type : 'POST',
    url : '/pay-to/' + vuePaymentExpenditureModal.form.entity_id + '/ajax',
    enctype : 'multipart/form-data',
    data : new FormData($('#embeddedPayDisbursementModal form')[0]),
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
      $('#embeddedPayDisbursementModal').modal('hide');
      // update customer table
      if ('updateCallback' in vuePaymentExpenditureDataSource) {
        vuePaymentExpenditureDataSource.updateCallback(data['data']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vuePaymentExpenditureModal.errors = data['errors'];
    }
  });
}

$(document).ready(function() {

  vuePaymentExpenditureModal = new Vue({
    el : "#embeddedPayDisbursementModal",
    data : {
      modal : {
        readonly : false,
        title : '',
				csrf : '',
        action : [ ],
        bank_account : [ ],
				currency_regex : '',
				currency_symbol : '',
				total_display : '',
				currency_min : 0.01
      },
      form : {
				entity_id : 0,
        code : '',
        bank_account : 0,
        inputdate : '',
        reference : '',
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
      $('#embeddedPayDisbursementModal form').keypress(function (e) {
        if (e.keyCode == 13) { e.preventDefault(); }
      });
      // ESC only works at document level and on keydown
      $(document).keydown(function (e) {
        if (e.keyCode == 27) {
          if ($('#embeddedPayDisbursementModal').hasClass("in")) {
            $('#embeddedPayDisbursementModal').modal('hide');
          }
        }
      });

      $('#embeddedPayDisbursementModal').on('shown.bs.modal', function (e) {
				$('#embeddedPayDisbursementModal .show-balance-hint').trigger('mouseenter');
				$('#embeddedPayDisbursementModal .show-balance').trigger('mouseleave');
      });

			$('#embeddedPayDisbursementModal .show-balance').hover(
				function() {  // mouse-enter
					$(this).stop();
				  $('#embeddedPayDisbursementModal .show-balance-hint').hide();
				  $(this).css("opacity", 1);
				  $(this).show();
				},
				function() {  // mouse-leave
					$(this).fadeOut({
				      duration: 3000,
				      complete: function() {
				        $('#embeddedPayDisbursementModal .show-balance-hint').show();
				      },
				    });
				}
			);
			$('#embeddedPayDisbursementModal .show-balance-hint').hover(
				function() {  // mouse-enter
					$(this).hide();
					$('#embeddedPayDisbursementModal .show-balance').show();
				},
				function() {  // mouse-leave
					// nothing to be done here
				}
			);
    }
  });

});
