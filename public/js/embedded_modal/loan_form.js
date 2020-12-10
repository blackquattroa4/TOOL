// use in conjunction with ../embedded_modal/loan_form.blade.php

var vueLoanModal = null;

function loadLoanWithAjax(loan_id) {
  // use ajax to load loan #id
  return $.ajax({
    type: 'GET',
    url: '/loan/' + loan_id + '/ajax',
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
      vueLoanModal.errors = result['errors'];
    } else {
      vueLoanModal.errors = { general : "System failure" };
    }
  });
}

function populateLoanModalWithAjaxResult(result) {
  vueLoanModal.modal.readonly = result['readonly'];
  vueLoanModal.modal.title = result['modal_title'];
  vueLoanModal.modal.csrf = result['csrf'];
  vueLoanModal.modal.history = result['history'];
  vueLoanModal.modal.entity = result['entities'];
  vueLoanModal.modal.currency = result['currencies'];
  vueLoanModal.modal.cash_account = result['cash_accounts'];
  vueLoanModal.modal.expense_account = result['expense_accounts'];
  vueLoanModal.modal.revenue_account = result['revenue_accounts'];
  vueLoanModal.modal.transaction = result['transaction'];
  vueLoanModal.modal.action = result['action'];
  vueLoanModal.form.id = result['id'];
  vueLoanModal.form.title = result['title'];
  vueLoanModal.form.role = result['role'];
  vueLoanModal.form.entity = result['entity'];
  vueLoanModal.form.principal = result['principal'];
  vueLoanModal.form.currency = result['currency'];
  vueLoanModal.form.apr = result['apr'];
  vueLoanModal.form.cash_account = result['cash_account'];
  vueLoanModal.form.notes = result['notes'];
}

function createLoanInModal() {
  let jqxhr = loadLoanWithAjax(0);

  jqxhr.done(function (data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
       result['data']['readonly'] = false;
       result['data']['modal_title'] = vueLoanDataSource.text_create_loan;
       result['data']['entities'] = vueLoanDataSource.selection_entity;
       result['data']['currencies'] = vueLoanDataSource.selection_currency;
       result['data']['cash_accounts'] = vueLoanDataSource.selection_cash_account;
       result['data']['expense_accounts'] = vueLoanDataSource.selection_expense_account;
       result['data']['revenue_accounts'] = vueLoanDataSource.selection_revenue_account;
       result['data']['action'] = {};
       result['data']['action'][vueLoanDataSource.button_create] = "createPostAjax";

       populateLoanModalWithAjaxResult(result['data']);
       vueLoanModal.errors = [ ];

       // Vue.nextTick(function () {
       // });

       // show modal
      $('#embeddedLoanModal').modal('show');
    }
  });
}

function createPostAjax() {
  $.ajax({
    type : 'POST',
    url : '/loan/create/ajax',
    enctype : 'multipart/form-data',
    data : new FormData($('#embeddedLoanModal form#loan_form')[0]),
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
      $('#embeddedLoanModal').modal('hide');
      // update loan table
      if ('insertCallback' in vueLoanDataSource) {
        vueLoanDataSource.insertCallback(data['data']['loan']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vueLoanModal.errors = data['errors'];
    }
  });
}

function viewLoanInModal(loan_id) {
  let jqxhr = loadLoanWithAjax(loan_id);

  jqxhr.done(function (data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
       result['data']['readonly'] = true;
       result['data']['modal_title'] = vueLoanDataSource.text_view_loan;
       result['data']['entities'] = vueLoanDataSource.selection_entity;
       result['data']['currencies'] = vueLoanDataSource.selection_currency;
       result['data']['cash_accounts'] = vueLoanDataSource.selection_cash_account;
       result['data']['expense_accounts'] = vueLoanDataSource.selection_expense_account;
       result['data']['revenue_accounts'] = vueLoanDataSource.selection_revenue_account;
       result['data']['action'] = {};

       populateLoanModalWithAjaxResult(result['data']);
       vueLoanModal.errors = [ ];

       // Vue.nextTick(function () {
       // });

       // show modal
      $('#embeddedLoanModal').modal('show');
    }
  });
}

function updateLoanInModal(loan_id) {
  let jqxhr = loadLoanWithAjax(loan_id);

  jqxhr.done(function (data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
       result['data']['readonly'] = false;
       result['data']['modal_title'] = vueLoanDataSource.text_update_loan;
       result['data']['entities'] = vueLoanDataSource.selection_entity;
       result['data']['currencies'] = vueLoanDataSource.selection_currency;
       result['data']['cash_accounts'] = vueLoanDataSource.selection_cash_account;
       result['data']['expense_accounts'] = vueLoanDataSource.selection_expense_account;
       result['data']['revenue_accounts'] = vueLoanDataSource.selection_revenue_account;
       result['data']['action'] = {};
       result['data']['action'][vueLoanDataSource.button_update] = "updatePostAjax";

       populateLoanModalWithAjaxResult(result['data']);
       vueLoanModal.errors = [ ];

       // Vue.nextTick(function () {
       // });

       // show modal
      $('#embeddedLoanModal').modal('show');
    }
  });
}

function updatePostAjax(loan_id) {
  $.ajax({
    type : 'POST',
    url : '/loan/update/' + vueLoanModal.form.id + '/ajax',
    enctype : 'multipart/form-data',
    data : new FormData($('#embeddedLoanModal form#loan_form')[0]),
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
      $('#embeddedLoanModal').modal('hide');
      // update loan table
      if ('updateCallback' in vueLoanDataSource) {
        vueLoanDataSource.updateCallback(data['data']['loan']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vueLoanModal.errors = data['errors'];
    }
  });
}

function forgiveLoanInModal(loan_id) {
  let jqxhr = loadLoanWithAjax(loan_id);

  jqxhr.done(function (data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
       result['data']['readonly'] = true;
       result['data']['modal_title'] = vueLoanDataSource.text_forgive_loan;
       result['data']['entities'] = vueLoanDataSource.selection_entity;
       result['data']['currencies'] = vueLoanDataSource.selection_currency;
       result['data']['cash_accounts'] = vueLoanDataSource.selection_cash_account;
       result['data']['expense_accounts'] = vueLoanDataSource.selection_expense_account;
       result['data']['revenue_accounts'] = vueLoanDataSource.selection_revenue_account;
       result['data']['action'] = {};
       result['data']['action'][vueLoanDataSource.button_baddebt] = "forgiveBadDebtStep1";

       populateLoanModalWithAjaxResult(result['data']);
       vueLoanModal.errors = [ ];

       // Vue.nextTick(function () {
       // });

       // show modal
      $('#embeddedLoanModal').modal('show');
    }
  });
}

function forgiveBadDebtStep1() {
  vueLoanModal.form.baddebt_account = '';
  vueLoanModal.form.baddebt_date = vueLoanDataSource.text_today;
  $('#embeddedLoanModal #baddebtModal').modal('show');
}

function forgivePostAjax() {
  $.ajax({
    type : 'POST',
    url : '/loan/forgive/' + vueLoanModal.form.id + '/ajax',
    enctype : 'multipart/form-data',
    data : new FormData($('#embeddedLoanModal form#baddebt_form')[0]),
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
      $('#embeddedLoanModal #baddebtModal').modal('hide');
      $('#embeddedLoanModal').modal('hide');
      // update loan table
      if ('updateCallback' in vueLoanDataSource) {
        vueLoanDataSource.updateCallback(data['data']['loan']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      $('#embeddedLoanModal #baddebtModal').modal('hide');
      vueLoanModal.errors = data['errors'];
    }
  });
}

$(document).ready(function() {

  vueLoanModal = new Vue({
    el : "#embeddedLoanModal",
    data : {
      modal : {
        readonly : false,
        title : '',
        csrf : '',
        history : [ ],
        entity : [ ],
        currency : [ ],
        cash_account : [ ],
        expense_account : [ ],
        revenue_account : [ ],
        transaction : [ ],
        action : [ ]
      },
      form : {
        id : 0,
        baddebt_account : 0,
        baddebt_date : '',
        title : '',
        role : '',
        entity : 0,
        principal : 0,
        currency : 0,
        apr : 0,
        cash_account : 0,
        notes : ''
      },
      errors : { }
    },
    mounted : function() {
      // disable ENTER
      $('#embeddedLoanModal form').keypress(function (e) {
        if (e.keyCode == 13) { e.preventDefault(); }
      });
      // ESC only works at document level and on keydown
      $(document).keydown(function (e) {
        if (e.keyCode == 27) {
          if ($('#embeddedLoanModal #historyModal').hasClass("in")) {
            $('#embeddedLoanModal #historyModal').modal('hide');
          } else if ($('#embeddedLoanModal #baddebtModal').hasClass("in")) {
              $('#embeddedLoanModal #baddebtModal').modal('hide');
          } else if ($('#embeddedLoanModal').hasClass("in")) {
            $('#embeddedLoanModal').modal('hide');
          }
        }
      });
      // bad-debt date picker
      $('#embeddedLoanModal #baddebtModal #baddebt_date').datepicker().bind('change', function(event) {
        vueLoanModal.form.baddebt_date = $(this).val();
      });
    }
  });

});
