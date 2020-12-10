// use in conjunction with ../embedded_modal/taccount_transaction.blade.php

var vueTaccountTransactionModal = null;

function loadMoreTaccountTransactions(id) {
	$.ajax({
		type: 'GET',
		url: '/taccount/retrieve',
		data: {
				id : id,
				offset : vueTaccountTransactionModal.modal.offset,
				count : 10,
			},
		dataType: 'html',
		beforeSend: function(data) {
			$('#embeddedTaccountTransactionModal body div.main-view-port').append("<div class=\"progress-animation\"><i class=\"fa fa-spinner fa-pulse fa-5x fa-fw\"></i></div>");
		},
	}).done(function(data) {
		$('#embeddedTaccountTransactionModal body div.main-view-port div.progress-animation').remove();
		var result = JSON.parse(data);
		for (var i = 0; i < result.length; i++) {
      vueTaccountTransactionModal.modal.transaction.push({
        date : result[i].date,
        source : result[i].source,
        notes : result[i].notes,
        debit_title : result[i].debit_title,
        debit : result[i].debit,
        credit_title : result[i].credit_title,
        credit : result[i].credit,
        balance : result[i].balance
      });
		}
		$('#embeddedTaccountTransactionModal div.panel-body button.btn').remove();
		if (result.length == 10) {
			$('#embeddedTaccountTransactionModal div.panel-body').append("<button class=\"btn btn-primary pull-right\" onclick=\"loadMoreTaccountTransactions(" + id + ");\" ><i class=\"fa fa-download\"></i>&emsp;" + vueTaccountTransactionDataSource.button_load_more_transaction + "</button>");
		}
    vueTaccountTransactionModal.modal.offset += 10;
	}).fail(function(data) {
		$('div.panel-body button.btn').remove();
	});
}

function viewTaccountTransactionInModal(id, description, currency) {
  vueTaccountTransactionModal.modal.id = id;
  vueTaccountTransactionModal.modal.offset = 0;
  vueTaccountTransactionModal.modal.account = description;
  vueTaccountTransactionModal.modal.currency = currency;
  vueTaccountTransactionModal.modal.transaction = [ ];
  loadMoreTaccountTransactions(id);
  $('#embeddedTaccountTransactionModal').modal('show');
}

$(document).ready(function() {

  vueTaccountTransactionModal = new Vue({
    el : '#embeddedTaccountTransactionModal',
    data : {
      modal : {
        id : 0,
        offset : 0,
        account : '',
        currency : '',
        transaction : [ ]
      }
    },
    mounted : function() {
			// disable ENTER
      $('#embeddedTaccountTransactionModal form').keypress(function (e) {
        if (e.keyCode == 13) { e.preventDefault(); }
      });
      // ESC only works at document level and on keydown
      $(document).keydown(function (e) {
        if (e.keyCode == 27) {
          if ($('#embeddedTaccountTransactionModal').hasClass("in")) {
            $('#embeddedTaccountTransactionModal').modal('hide');
          }
        }
      });
    }
  });

});
