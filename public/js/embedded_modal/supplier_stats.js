// use in conjunction with ../embedded_modal/supplier_stats.blade.php

var vueSupplierStatsModal = null;

var supplierRunrateChart = null;

var supplierRunrateChartConfig = null;

function loadSupplierStats(supplier_id) {
  vueSupplierStatsModal.form.supplier_id = supplier_id;
  vueSupplierStatsModal.form.history = 12;
  $('#embeddedSupplierStatsModal').modal('show');
}

$(document).ready(function() {

  vueSupplierStatsModal = new Vue({
    el : "#embeddedSupplierStatsModal",
    data : {
      form : {
        supplier_id : 0,
        history : 0,
      },
      errors : { }
    },
    mounted : function() {
      // disable ENTER
      $('#embeddedSupplierStatsModal form').keypress(function (e) {
        if (e.keyCode == 13) { e.preventDefault(); }
      });
      // ESC only works at document level and on keydown
      $(document).keydown(function (e) {
        if (e.keyCode == 27) {
          if ($('#embeddedSupplierStatsModal').hasClass("in")) {
            $('#embeddedSupplierStatsModal').modal('hide');
          }
        }
      });

      $('#embeddedSupplierStatsModal #supplier,#history').bind('change', function() {
        supplierRunrateChartConfig.data.labels = [ ];
        supplierRunrateChartConfig.data.datasets = [ ];
        supplierRunrateChart.update();
      });

      $('#embeddedSupplierStatsModal').on('shown.bs.modal', function(event) {
        $('#embeddedSupplierStatsModal #refresh').click();
      });

      // Ajax call to pull reports
  		$('#embeddedSupplierStatsModal #refresh').bind('click', function() {
  			$.ajax({
  				type: 'GET',
  				url: '/vrm/detailsupplier/ajax',
  				data: {
  						supplier : $('#embeddedSupplierStatsModal select#supplier').val(),
  						history : $('#embeddedSupplierStatsModal select#history').val(),
  					},
  				dataType: 'html',
  				beforeSend: function(data) {
  					supplierRunrateChartConfig.data.labels = [ ];
  					supplierRunrateChartConfig.data.datasets = [ ];
  					supplierRunrateChart.update();
  					$('.ajax-processing').removeClass('hidden');
  				},
  			}).always(function(data) {
  				let report = JSON.parse(data);
  				$('.ajax-processing').addClass('hidden');
  				if (!report['success']) {
  					$('div.flash-message').append('<p class="alert alert-warning">' + vueSupplierStatsDataSource.text_chart_error + '<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a></p>');
  				}
  			}).done(function(data) {
  				var report = JSON.parse(data);
  				if (report['success']) {
  					supplierRunrateChartConfig.data.labels = report['data']['labels'];
  					supplierRunrateChartConfig.data.datasets = report['data']['runrate'];
  					supplierRunrateChart.update();
  				}
  			});
  		});

    }
  });

});

$(document).ready(function() {
  supplierRunrateChartConfig = {
      type : 'line',
      data : {
        labels : [  ],
        datasets : [  ]
      },
      options : {
        responsive : true,
        title : {
          display : true,
          text : vueSupplierStatsDataSource.text_runrate
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
  supplierRunrateChart = new Chart(document.getElementById('supplier_product_runrate_chart').getContext('2d'), supplierRunrateChartConfig);
  supplierRunrateChart.update();
});
