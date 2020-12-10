// use in conjunction with ../embedded_modal/tradable_analysis.blade.php

var vueTradableAnalysisModal = null;

function loadTradableAnalysisWithAjax(tradable_id) {
  // use ajax to load analysis of tradable #id
  return $.ajax({
    type: 'GET',
    url: '/tradable/' + tradable_id + '/ajax',
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

function analyzeTradableInModal(tradable_id) {
  let jqxhr = loadTradableAnalysisWithAjax(tradable_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      vueTradableAnalysisModal.modal.title = vueTradableAnalysisDataSource.text_analyze_product;
      vueTradableAnalysisModal.form.sku = result['data']['sku'];
      $('#embeddedTradableAnalysisModal').modal('show');
    }
  });
}

$(document).ready(function() {

  vueTradableAnalysisModal = new Vue({
    el : "#embeddedTradableAnalysisModal",
    data : {
      modal : {
        title : '',
      },
      form : {
        sku : ''
      },
      errors : { }
    },
    mounted : function() {
      // disable ENTER
      $('#embeddedTradableAnalysisModal form').keypress(function (e) {
        if (e.keyCode == 13) { e.preventDefault(); }
      });
      // ESC only works at document level and on keydown
      $(document).keydown(function (e) {
        if (e.keyCode == 27) {
          if ($('#embeddedTradableAnalysisModal').hasClass("in")) {
            $('#embeddedTradableAnalysisModal').modal('hide');
          }
        }
      });
    }
  });

});
