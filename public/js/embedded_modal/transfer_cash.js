//  use in conjunction with ../embedded_modal/transfer_cash.blade.php

var vueTransferCashModal = null;

function openTransferCashModal() {
  $.ajax({
    type: 'GET',
    url: '/cash/transfer',
    data: {
      },
    dataType: 'html',
    beforeSend: function(data) {
      $('.ajax-processing').removeClass('hidden');
    },
  }).always(function (data) {
    $('.ajax-processing').addClass('hidden');
  }).done(function (data) {
    let result = JSON.parse(data);
    if (result['success']) {
      vueTransferCashModal.modal.csrf = result['data']['csrf'];
      vueTransferCashModal.modal.account = vueTransferCashDataSource.selection_bank_account;
      vueTransferCashModal.modal.currency = vueTransferCashDataSource.selection_currency;
      vueTransferCashModal.form.source = '';
      vueTransferCashModal.form.target = '';
      vueTransferCashModal.form.date = vueTransferCashDataSource.text_today;
      vueTransferCashModal.form.amount = 0;
      vueTransferCashModal.errors = { };

      $('#embeddedTransferCashModal').modal('show');
    }
  }).fail(function (data) {
    if ('errors' in result) {
      vueTransferCashModal.errors = result['errors'];
    } else {
      vueTransferCashModal.errors = { general : "System failure" };
    }
  });

}

function transferCashPost() {

  $.ajax({
    type: 'POST',
    url: '/cash/transfer',
    enctype : 'multipart/form-data',
    data: new FormData($('#embeddedTransferCashModal form')[0]),
    processData : false,
    contentType : false,
    cache : false,
    // dataType: 'html',
    beforeSend: function(data) {
      $('.ajax-processing').removeClass('hidden');
    },
  }).always(function (data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vueTransferCashModal.errors = data['errors'];
    }
  }).done(function (data) {
    if (data['success']) {
      $('#embeddedTransferCashModal').modal('hide');
      // update order/return table
      if ('updateCallback' in vueTransferCashDataSource) {
        vueTransferCashDataSource.updateCallback(data['data']);
      }
    }
  });

}

$(document).ready(function() {

  vueTransferCashModal = new Vue({
    el : '#embeddedTransferCashModal',
    data : {
      modal : {
        account : [ ],
        currency : [ ]
      },
      form : {
        source : '',
        target : '',
        date : '',
        amount : 0
      },
      errors : { }
    },
    mounted : function() {
      $('#embeddedTransferCashModal #transfer_date').datepicker().bind('change', function(event) {
        vueTransferCashModal.form.date = $(this).val();
      });
      // disable ENTER
      $('#embeddedTransferCashModal form').keypress(function (e) {
        if (e.keyCode == 13) { e.preventDefault(); }
      });
      $(document).keydown(function (e) {
        if (e.keyCode == 27) {
          if ($('#embeddedTransferCashModal').hasClass("in")) {
            $('#embeddedTransferCashModal').modal('hide');
          }
        }
      });
    }
  });

});
