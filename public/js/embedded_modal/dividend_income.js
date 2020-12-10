//  use in conjunction with ../embedded_modal/dividend_income.blade.php

var vueDividendIncomeModal = null;

function openDividendIncomeModal(accountId)
{
  $.ajax({
    type: 'GET',
    url: '/finance/dividend/',
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
      vueDividendIncomeModal.modal.csrf = result['data']['csrf'];
      vueDividendIncomeModal.modal.revenue = vueDividendIncomeDataSource.selection_revenue;
      vueDividendIncomeModal.modal.bank = vueDividendIncomeDataSource.selection_bank;
      vueDividendIncomeModal.modal.currency = vueDividendIncomeDataSource.selection_currency;
      vueDividendIncomeModal.form.date = vueDividendIncomeDataSource.text_today;
      vueDividendIncomeModal.form.revenue = '';
      vueDividendIncomeModal.form.bank = accountId;
      vueDividendIncomeModal.form.amount = 0;
      vueDividendIncomeModal.form.notes = '';
      vueDividendIncomeModal.errors = [ ];

      $('#embeddedDividendIncomeModal').modal('show');
    }
  });
}

function submitDividendIncomePost()
{
  $.ajax({
    type: 'POST',
    url: '/finance/dividend',
    enctype : 'multipart/form-data',
    data: new FormData($('#embeddedDividendIncomeModal form')[0]),
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
      vueDividendIncomeModal.errors = data['errors'];
    }
  }).done(function(data) {
    if (data['success']) {
      $('#embeddedDividendIncomeModal').modal('hide');
      // update order/return table
      if ('updateCallback' in vueDividendIncomeDataSource) {
        vueDividendIncomeDataSource.updateCallback(data['data']);
      }
    }
  });

}

$(document).ready(function() {

  vueDividendIncomeModal = new Vue({
    el : '#embeddedDividendIncomeModal',
    data : {
      modal : {
        csrf : '',
        revenue : [ ],
        bank : [ ],
        currency : [ ],
      },
      form : {
        date : '',
        revenue : '',
        bank : '',
        amount : 0,
        notes : ''
      },
      errors : { }
    },
    mounted : function() {
      $('#embeddedDividendIncomeModal #date').datepicker().bind('change', function(event) {
        vueDividendIncomeModal.form.date = $(this).val();
      });
      // disable ENTER
      $('#embeddedDividendIncomeModal form').keypress(function (e) {
        if (e.keyCode == 13) { e.preventDefault(); }
      });
      $(document).keydown(function (e) {
        if (e.keyCode == 27) {
          if ($('#embeddedDividendIncomeModal').hasClass("in")) {
            $('#embeddedDividendIncomeModal').modal('hide');
          }
        }
      });
    }
  });

});
