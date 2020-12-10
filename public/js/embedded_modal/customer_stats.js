// use in conjunction with ../embedded_modal/customer_stats.blade.php

var vueCustomerStatsModal = null;

var customerProfitLossChart = null;

var customerProfitLossChartConfig = null;

var customerRunrateChart = null;

var customerRunrateChartConfig = null;

function loadCustomerStats(customer_id) {
  vueCustomerStatsModal.form.customer_id = customer_id;
  vueCustomerStatsModal.form.history = 12;
  $('#embeddedCustomerStatsModal').modal('show');
}

$(document).ready(function() {

  vueCustomerStatsModal = new Vue({
    el : "#embeddedCustomerStatsModal",
    data : {
      form : {
        customer_id : 0,
        history : 0,
      },
      errors : { }
    },
    mounted : function() {
      // disable ENTER
      $('#embeddedCustomerStatsModal form').keypress(function (e) {
        if (e.keyCode == 13) { e.preventDefault(); }
      });
      // ESC only works at document level and on keydown
      $(document).keydown(function (e) {
        if (e.keyCode == 27) {
          if ($('#embeddedCustomerStatsModal').hasClass("in")) {
            $('#embeddedCustomerStatsModal').modal('hide');
          }
        }
      });

      $('#embeddedCustomerStatsModal #customer,#history').bind('change', function() {
        customerProfitLossChartConfig.data.labels = [ ];
        customerProfitLossChartConfig.data.datasets = [ ];
        customerProfitLossChart.update();
      });

      $('#embeddedCustomerStatsModal #customer,#history').bind('change', function() {
        customerRunrateChartConfig.data.labels = [ ];
        customerRunrateChartConfig.data.datasets = [ ];
        customerRunrateChart.update();
      });

      $('#embeddedCustomerStatsModal').on('shown.bs.modal', function(event) {
        $('#embeddedCustomerStatsModal #refresh').click();
      });

      // Ajax call to pull reports
      $('#embeddedCustomerStatsModal #refresh').bind('click', function() {
        $.ajax({
          type: 'GET',
          url: '/crm/detailcustomer/ajax',
          data: {
              customer : $('#embeddedCustomerStatsModal select#customer').val(),
              history : $('#embeddedCustomerStatsModal select#history').val(),
            },
          dataType: 'html',
          beforeSend: function(data) {
            customerProfitLossChartConfig.data.labels = [ ];
            customerProfitLossChartConfig.data.datasets = [ ];
            customerProfitLossChart.update();
            customerRunrateChartConfig.data.labels = [ ];
            customerRunrateChartConfig.data.datasets = [ ];
            customerRunrateChart.update();
            $('.ajax-processing').removeClass('hidden');
          },
        }).always(function(data) {
          let report = JSON.parse(data);
          $('.ajax-processing').addClass('hidden');
          if (!report['success']) {
            $('div.flash-message').append('<p class="alert alert-warning">' + vueCustomerStatsDataSource.text_chart_error + '<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a></p>');
          }
        }).done(function(data) {
          let report = JSON.parse(data);
          if (report['success']) {
            customerProfitLossChartConfig.data.labels = report['data']['labels'];
            customerProfitLossChartConfig.data.datasets.push(report['data']['sales']);
            customerProfitLossChartConfig.data.datasets.push(report['data']['expense']);
            customerProfitLossChartConfig.data.datasets.push(report['data']['profit']);
            customerProfitLossChart.update();
            customerRunrateChartConfig.data.labels = report['data']['labels'];
            customerRunrateChartConfig.data.datasets = report['data']['runrate'];
            customerRunrateChart.update();
          }
        });
      });

    }
  });

});

$(document).ready(function() {
  customerProfitLossChartConfig = {
      type : 'line',
      data : {
        labels : [  ],
        datasets : [  ]
      },
      options : {
        responsive : true,
        title : {
          display : true,
          text : vueCustomerStatsDataSource.text_performance
        },
        scales : {
          yAxes : [
						{
							display : true,
							type : 'linear',
							position : 'left',
							ticks : {
								callback: function (value, index, values) {
									return Intl.NumberFormat('en-US', { notation: "compact" , compactDisplay: "short" }).format(value)
								}
							}
						}
          ]
        }
      }
    };
  customerProfitLossChart = new Chart(document.getElementById('customer_profit_loss_chart').getContext('2d'), customerProfitLossChartConfig);
  customerProfitLossChart.update();
});

$(document).ready(function() {
  customerRunrateChartConfig = {
      type : 'line',
      data : {
        labels : [  ],
        datasets : [  ]
      },
      options : {
        responsive : true,
        title : {
          display : true,
          text : vueCustomerStatsDataSource.text_runrate
        },
        scales : {
          yAxes : [
						{
							display : true,
							type : 'linear',
							position : 'left',
							ticks : {
								callback: function (value, index, values) {
									return Intl.NumberFormat('en-US', { notation: "compact" , compactDisplay: "short" }).format(value)
								}
							}
						}
          ]
        }
      }
    };
  customerRunrateChart = new Chart(document.getElementById('customer_product_runrate_chart').getContext('2d'), customerRunrateChartConfig);
  customerRunrateChart.update();
});
