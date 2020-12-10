const DATATABLE_PROCESSING_INDICATOR = '<i class="fa fa-spinner fa-spin fa-5x fa-fw" aria-hidden="true"></i>';

function showDataTableProcessingTransient(tableName) {
  $(tableName).DataTable().clear().draw();
  let loadingDisplay = $(tableName).DataTable().init().language.loadingRecords;
  let columnCount = $(tableName + ' thead th').size();
  $(tableName + ' tbody').html('<tr class="odd"><td valign="top" colspan="' + columnCount + '" class="dataTables_empty">' + loadingDisplay + '</td></tr>');
}

// use built-in datatable function; following is no longer necessary; keep it for reference
/*
function registerDataTablePreference(pageUrl, tableId, dataTableObj) {
  // grab previously recorded search-key
  let searchKey = localStorage.getItem(pageUrl + "@" + tableId) || "";
  // register function to record phrase entered into search box
  dataTableObj.on('search.dt', function() { localStorage.setItem(pageUrl + "@" + tableId, dataTableObj.search()); });
  // force table to re-display according to search-key
  dataTableObj.search(searchKey).draw();
  // fill in the search box
  $("#"+tableId+"_filter input").val(searchKey);
}
*/

function dataTableDefaultConfig(reloadButtonText, requestMethod, requestUrl, requestData, tableSelector, templateSelector, searchPlaceholder, searchTips, pageLength) {
  return {
    dom : '<"col-md-6"<"col-md-0"B><"col-md-11"l>><"col-md-6"f>rt<"col-md-4"i><"col-md-8"p>',
    buttons : [
      {
        text : '<i class="fa fa-refresh" aria-hidden="true"></i>',
        attr : {
          class : 'btn btn-xs btn-info',
          title : reloadButtonText
        },
        action : function(e, dt, node, config) {
          dt.ajax.reload();
        }
      }
    ],
    ajax : function(data, callack, settings) {
      $.ajax({
        type: requestMethod,
        url: requestUrl,
        data: requestData,
        dataType: 'html',
      }).always(function (data) {
        $('div' + tableSelector + '_filter input[type="search"]').attr('title', searchTips);
      }).fail(function (data) {
      }).done(function (data) {
        let result = JSON.parse(data);
        if (result['success']) {
          let theDataTable = $(tableSelector).DataTable();
          for (index in result['data']) {
            let htmltext = populateHtmlTemplateWithData($(templateSelector).html().toString(), result['data'][index]);
            theDataTable.row.add($(htmltext));
          }
          theDataTable.columns.adjust();
          theDataTable.draw();
        }
      });
    },
    language : {
      searchPlaceholder : searchPlaceholder,
      paginate : {
        first : "&laquo;",
        last : "&raquo;",
        next : "&rsaquo;",
        previous : "&lsaquo;"
      },
      emptyTable : "<i class=\"fa fa-times fa-5x\" aria-hidden=\"true\"></i>",
      info : " _START_ ~ _END_ / _TOTAL_ ",
      infoEmpty : "0 / 0",
      infoFiltered : " / _MAX_",
      lengthMenu : "<i class=\"fa fa-bars\" aria-hidden=\"true\"></i> _MENU_ ",
      loadingRecords : DATATABLE_PROCESSING_INDICATOR,
      processing : DATATABLE_PROCESSING_INDICATOR,
      search : "<i class=\"fa fa-search\" aria-hidden=\"true\"></i>",
      zeroRecords : "<i class=\"fa fa-times fa-5x\" aria-hidden=\"true\"></i>"
    },
    stateSave : true,
    fixedColumns: false,
    pageLength : pageLength
  };
}
