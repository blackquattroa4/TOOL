// use in conjunction with ../embedded_modal/tradable_transaction.blade.php

var vueTradableTransactionModal = null;

function loadMoreTradableTransactions() {
  $.ajax({
    type: 'GET',
    url: '/accounting/tradable/transactions/ajax',
    data: {
        location : vueTradableTransactionModal.transaction.location,
        owner : vueTradableTransactionModal.transaction.owner,
        sku: vueTradableTransactionModal.transaction.product,
        offset : vueTradableTransactionModal.transaction.offset,
        count : vueTradableTransactionModal.transaction.per_page,
      },
    dataType: 'html',
    beforeSend: function(data) {
      $('.ajax-processing').removeClass('hidden')
    },
  }).done(function(data) {
    vueTradableTransactionModal.transaction.offset+=vueTradableTransactionModal.transaction.per_page;
    $('.ajax-processing').addClass('hidden');
    var result = JSON.parse(data);
    for (var i = 0; i < result.length; i++) {
      var chrono = result[i].date.split(" ");
      $('#embeddedTradableTransactionModal table#trxtable tbody').append("<tr><td title=\"" + chrono[1] + "\">" + chrono[0] + "</td><td title=\"" + result[i].notes + "\">" + result[i].source + "</td><td style=\"text-align:right;\" >" + result[i].quantity + "</td><td style=\"text-align:right;\">" + result[i].balance + "</td></tr>");
    }
    $('div#embeddedTradableTransactionModal .modal-body button.btn-primary').detach();
    if (result.length == vueTradableTransactionModal.transaction.per_page) {
      $('div#embeddedTradableTransactionModal .modal-body').append("<button class=\"btn btn-primary pull-right\" onclick=\"loadMoreTradableTransactions();\" ><i class=\"fa fa-download\"></i>&emsp;" + vueTradableTransactionDataSource.button_load_more_transaction + "</button>");
    }
  }).fail(function(data) {
    $('.ajax-processing').addClass('hidden');
  });
}

function viewTradableTransactionsInModal(location_id, entity_id, sku_id) {
  $('#embeddedTradableTransactionModal table#trxtable tbody').html("");
  vueTradableTransactionModal.transaction.location = location_id;
  vueTradableTransactionModal.transaction.owner = entity_id;
  vueTradableTransactionModal.transaction.product = sku_id;
  vueTradableTransactionModal.transaction.offset = 0;
  $('#embeddedTradableTransactionModal').modal('show');
  loadMoreTradableTransactions();
}

$(document).ready(function() {

  vueTradableTransactionModal = new Vue({
    el : '#embeddedTradableTransactionModal',
    data : {
      transaction : {
        location : 0,
        owner : 0,
        product : 0,
        offset : 0,
        per_page : 10
      },
      errors : { }
    },
    mounted : function() {
      // disable ENTER
      $('#embeddedTradableTransactionModal form').keypress(function (e) {
        if (e.keyCode == 13) { e.preventDefault(); }
      });

      $('#embeddedTradableTransactionModal #location1,#product1').bind('change', function(e) {
        $('#embeddedTradableTransactionModal table#trxtable tbody').html("");
        $('div#embeddedTradableTransactionModal .modal-body button').detach();
        vueTradableTransactionModal.transaction.offset = 0;
      });

      $('#embeddedTradableTransactionModal #stats_update').bind('click', function(e) {
        loadMoreTradableTransactions();
      });
    }
  });

});
