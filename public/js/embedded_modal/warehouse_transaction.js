// use in conjunction with ../embedded_modal/warehouse_transaction.blade.php

var vueWarehouseTransactionModal = null;

function loadMoreTransactions() {
  $.ajax({
    type: 'GET',
    url: '/warehouse/transactions/ajax',
    data: {
        location : vueWarehouseTransactionModal.transaction.location,
        sku: vueWarehouseTransactionModal.transaction.product,
        offset : vueWarehouseTransactionModal.transaction.offset,
        count : vueWarehouseTransactionModal.transaction.per_page,
      },
    dataType: 'html',
    beforeSend: function(data) {
      $('.ajax-processing').removeClass('hidden')
    },
  }).done(function(data) {
    vueWarehouseTransactionModal.transaction.offset+=vueWarehouseTransactionModal.transaction.per_page;
    $('.ajax-processing').addClass('hidden');
    var result = JSON.parse(data);
    for (var i = 0; i < result.data.length; i++) {
      var chrono = result.data[i].date.split(" ");
      $('#embeddedWarehouseTransactionModal table#trxtable tbody').append("<tr><td title=\"" + chrono[1] + "\">" + chrono[0] + "</td><td title=\"" + result.data[i].notes + "\">" + result.data[i].source + "</td><td style=\"text-align:right;\" >" + result.data[i].quantity + "</td><td style=\"text-align:right;\">" + result.data[i].balance + "</td></tr>");
    }
    $('div#embeddedWarehouseTransactionModal .modal-body button.btn-primary').detach();
    if (result.data.length == vueWarehouseTransactionModal.transaction.per_page) {
      $('div#embeddedWarehouseTransactionModal .modal-body').append("<button class=\"btn btn-primary pull-right\" onclick=\"loadMoreTransactions();\" ><i class=\"fa fa-download\"></i>&emsp;" + result.text['load_more_transactions'] + "</button>");
    }
  }).fail(function(data) {
    $('.ajax-processing').addClass('hidden');
  });
}

function viewWarehouseTransactionInModal(location, product)
{
  $('#embeddedWarehouseTransactionModal table#trxtable tbody').html("");
  vueWarehouseTransactionModal.transaction.location = location;
  vueWarehouseTransactionModal.transaction.product = product;
  vueWarehouseTransactionModal.transaction.offset = 0;
  $('#embeddedWarehouseTransactionModal').modal('show');
  loadMoreTransactions();
}

$(document).ready(function() {

  vueWarehouseTransactionModal = new Vue({
    el : '#embeddedWarehouseTransactionModal',
    data : {
      transaction : {
        location : 0,
        product : 0,
        offset : 0,
        per_page : 10
      },
      errors : { }
    },
    mounted : function() {
      // disable ENTER
      $('#embeddedWarehouseTransactionModal form').keypress(function (e) {
        if (e.keyCode == 13) { e.preventDefault(); }
      });

      $('#embeddedWarehouseTransactionModal #location1,#product1').bind('change', function(e) {
        $('#embeddedWarehouseTransactionModal table#trxtable tbody').html("");
        $('div#embeddedWarehouseTransactionModal .modal-body button').detach();
        vueWarehouseTransactionModal.transaction.offset = 0;
      });

      $('#embeddedWarehouseTransactionModal #stats_update').bind('click', function(e) {
        loadMoreTransactions();
      });
    }
  });

});
