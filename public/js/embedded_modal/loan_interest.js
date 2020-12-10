//  use in conjunction with ../embedded_modal/loan_interest.blade.php

var vueLoanInterestModal = null;

function openLoanInterestModal(loanId, isRevenue)
{
  $.ajax({
    type: 'GET',
    url: '/finance/loan/interest/' + loanId,
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
      vueLoanInterestModal.modal.csrf = result['data']['csrf'];
      vueLoanInterestModal.modal.is_revenue = isRevenue;
      vueLoanInterestModal.modal.revenue = vueLoanInterestDataSource.selection_revenue;
      vueLoanInterestModal.modal.expense = vueLoanInterestDataSource.selection_expense;
      vueLoanInterestModal.modal.currency_min = vueLoanInterestDataSource.selection_currency_min;
      vueLoanInterestModal.form.id = loanId;
      vueLoanInterestModal.form.date = vueLoanInterestDataSource.text_today;
      vueLoanInterestModal.form.account = '';
      vueLoanInterestModal.form.amount = 0;
      vueLoanInterestModal.errors = { };

      $('div#embeddedLoanInterestModal').modal('show');
    }
  });
}

function submitLoanInterestPost(loanId)
{
  $.ajax({
    type: 'POST',
    url: '/finance/loan/interest/' + vueLoanInterestModal.form.id,
    enctype : 'multipart/form-data',
    data: new FormData($('#embeddedLoanInterestModal form')[0]),
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
      vueLoanInterestModal.errors = data['errors'];
    }
  }).done(function (data) {
    if (data['success']) {
      $('#embeddedLoanInterestModal').modal('hide');
      // update order/return table
      if ('updateCallback' in vueLoanInterestDataSource) {
        vueLoanInterestDataSource.updateCallback(data['data']);
      }
    }

  });

}

$(document).ready(function() {

  vueLoanInterestModal = new Vue({
    el : '#embeddedLoanInterestModal',
    data : {
      modal : {
        csrf : '',
        is_revenue : false,
        revenue : [ ],
        expense : [ ],
        currency_min : [ ]
      },
      form : {
        id : 0,
        date : '',
        account : 0,
        amount : 0
      },
      errors : { }
    },
    mounted : function() {
      $('#embeddedLoanInterestModal #date').datepicker().bind('change', function(event) {
        vueLoanInterestModal.form.date = $(this).val();
      });
      // disable ENTER
      $('#embeddedLoanInterestModal form').keypress(function (e) {
        if (e.keyCode == 13) { e.preventDefault(); }
      });
      $(document).keydown(function (e) {
        if (e.keyCode == 27) {
          if ($('#embeddedLoanInterestModal').hasClass("in")) {
            $('#embeddedLoanInterestModal').modal('hide');
          }
        }
      });
    }

  });

});
