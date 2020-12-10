// use in conjunction with ../embedded_modal/taccount_reconciliation.blade.php

var vueTaccountReconciliationModal = null;

function processTaccountReconciliationInModal(account_id) {
  $.ajax({
		type: 'GET',
		url: '/taccount/' + account_id + '/unreconciled/ajax',
		data: {	},
		dataType: 'html',
		beforeSend: function(data) {
			$('.ajax-processing').removeClass('hidden');
		},
	}).done(function(data) {
		var result = JSON.parse(data);
    if (result['success']) {
      vueTaccountReconciliationModal.modal.csrf = result['data']['csrf'];
      vueTaccountReconciliationModal.modal.account = result['data']['account'];
      vueTaccountReconciliationModal.modal.currency = result['data']['currency'];
      vueTaccountReconciliationModal.modal.regex = result['data']['regex'];
      vueTaccountReconciliationModal.modal.action = {};
      vueTaccountReconciliationModal.modal.action[vueTaccountReconciliationDataSource.button_update] = "processTaccountReconciliationPost";
      vueTaccountReconciliationModal.modal.display_value = result['data']['display_value'];
      vueTaccountReconciliationModal.form.id = account_id;
      vueTaccountReconciliationModal.form.original_value = result['data']['original_value'];
      vueTaccountReconciliationModal.form.line = result['data']['line'];
      vueTaccountReconciliationModal.form.date = result['data']['date'];
      vueTaccountReconciliationModal.form.source = result['data']['source'];
      vueTaccountReconciliationModal.form.debit_title = result['data']['debit_title'];
      vueTaccountReconciliationModal.form.debit = result['data']['debit'];
      vueTaccountReconciliationModal.form.credit_title = result['data']['credit_title'];
      vueTaccountReconciliationModal.form.credit = result['data']['credit'];
      vueTaccountReconciliationModal.form.value = result['data']['amount'];
      vueTaccountReconciliationModal.form.reconciled = new Array(result['data']['line'].length).fill(0);
      vueTaccountReconciliationModal.errors = [ ];

      $('#embeddedTaccountReconciliationModal').modal('show');
    }
  }).always(function (data) {
    $('.ajax-processing').addClass('hidden');
  }).fail(function (data) {
    if ('errors' in result) {
      vueSalesProcessModal.errors = result['errors'];
    } else {
      vueSalesProcessModal.errors = { general : "System failure" };
    }
  });

}

function adjustTaccountReconciliationBalance() {
  let total = parseFloat(vueTaccountReconciliationModal.form.original_value);
  for (let index in vueTaccountReconciliationModal.form.reconciled) {
    if (vueTaccountReconciliationModal.form.reconciled[index]) {
      total += parseFloat(vueTaccountReconciliationModal.form.value[index]);
    }
  }
  vueTaccountReconciliationModal.modal.display_value = total.toLocaleString(vueTaccountReconciliationModal.modal.regex, { style: 'currency', currency: vueTaccountReconciliationModal.modal.currency });

  //hideThenShowHint('.show-balance', '.show-balance-hint');
  $('#embeddedTaccountReconciliationModal .show-balance-hint').trigger('mouseenter');
  $('#embeddedTaccountReconciliationModal .show-balance').trigger('mouseenter');
  $('#embeddedTaccountReconciliationModal .show-balance').trigger('mouseleave');

}

function processTaccountReconciliationPost() {
  $.ajax({
    type : 'POST',
    url : '/taccount/' + vueTaccountReconciliationModal.form.id + '/unreconciled/ajax',
    enctype : 'multipart/form-data',
    data : new FormData($('#embeddedTaccountReconciliationModal form')[0]),
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
      $('#embeddedTaccountReconciliationModal').modal('hide');
      // update account table
      if ('updateCallback' in vueTaccountReconciliationDataSource) {
        vueTaccountReconciliationDataSource.updateCallback(data['account']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vueTaccountReconciliationModal.errors = data['errors'];
    }
  });

}

$(document).ready(function() {

  vueTaccountReconciliationModal = new Vue({
    el : '#embeddedTaccountReconciliationModal',
    data : {
      modal : {
        csrf : '',
        account : '',
        currency : '',
        regex : '',
        action : [ ],
        display_value : ''
      },
      form : {
        id : 0,
        original_value : 0,
        line : [ ],
        date : [ ],
        source : [ ],
        debit_title : [ ],
        debit : [ ],
        credit_title : [ ],
        credit : [ ],
        value : [ ],
        reconciled : [ ]
      },
      errors : { }
    },
    mounted : function() {
      // disable ENTER
      $('#embeddedTaccountReconciliationModal form').keypress(function (e) {
        if (e.keyCode == 13) { e.preventDefault(); }
      });
      // ESC only works at document level and on keydown
      $(document).keydown(function (e) {
        if (e.keyCode == 27) {
          if ($('#embeddedTaccountReconciliationModal').hasClass("in")) {
            $('#embeddedTaccountReconciliationModal').modal('hide');
          }
        }
      });

      $('#embeddedTaccountReconciliationModal').on('shown.bs.modal', function (e) {
        $('#embeddedTaccountReconciliationModal .show-balance-hint').trigger('mouseenter');
        $('#embeddedTaccountReconciliationModal .show-balance').trigger('mouseleave');
      });

      $('#embeddedTaccountReconciliationModal .show-balance').hover(
        function() {  // mouse-enter
          $(this).stop();
				  $('#embeddedTaccountReconciliationModal .show-balance-hint').hide();
				  $(this).css("opacity", 1);
				  $(this).show();
        },
        function() {  // mouse-leave
          $(this).fadeOut({
				      duration: 3000,
				      complete: function() {
				        $('#embeddedTaccountReconciliationModal .show-balance-hint').show();
				      },
				    });
        }
      );
      $('#embeddedTaccountReconciliationModal .show-balance-hint').hover(
        function() {  // mouse-enter
          $(this).hide();
					$('#embeddedTaccountReconciliationModal .show-balance').show();
        },
        function() {  // mouse-leave
          // nothing to be done here
        }
      );
    }
  });

});
