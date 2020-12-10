// use in conjunction with ../embedded_modal/warehouse_order.blade.php

var vueWarehouseOrderModal = null;

function warehouseOrderDataSource()
{
  // function that holds sourcing reference
}

function postCapture()
{
  $('#embeddedWarehouseOrderModal #product_input').val("");
  $('#embeddedWarehouseOrderModal #addProductModal').modal('hide');
}

function prepareSplit(index)
{
  let qty = vueWarehouseOrderModal.order.quantity[index];
  $('#embeddedWarehouseOrderModal #split_source').val(qty);
  $('#embeddedWarehouseOrderModal #split_source').data('prev', qty);
  $('#embeddedWarehouseOrderModal #split_source').data('index', index);
  $('#embeddedWarehouseOrderModal #split_target').val("0");
  $('#embeddedWarehouseOrderModal #split_target').data('prev', "0");
  $('#embeddedWarehouseOrderModal #split_source').bind('change', function (event) {
    let delta = parseInt($(this).data('prev')) - parseInt($(this).val());
    $(this).data('prev', $(this).val());
    let newTarget = parseInt($('#embeddedWarehouseOrderModal #split_target').val()) + delta;
    $('#embeddedWarehouseOrderModal #split_target').val(newTarget);
    $('#embeddedWarehouseOrderModal #split_target').data('prev', newTarget);
  });
  $('#embeddedWarehouseOrderModal #split_target').bind('change', function (event) {
    let delta = parseInt($(this).data('prev')) - parseInt($(this).val());
    $(this).data('prev', $(this).val());
    let newSource = parseInt($('#embeddedWarehouseOrderModal #split_source').val()) + delta;
    $('#embeddedWarehouseOrderModal #split_source').val(newSource);
    $('#embeddedWarehouseOrderModal #split_source').data('prev', newSource);
  });
  $('#embeddedWarehouseOrderModal #splitProductModal').modal('show');
}

function postSplit()
{
  $('#embeddedWarehouseOrderModal #split_source').val('');
  $('#embeddedWarehouseOrderModal #split_source').data('prev', '');
  $('#embeddedWarehouseOrderModal #split_source').data('index', '');
  $('#embeddedWarehouseOrderModal #split_target').val('');
  $('#embeddedWarehouseOrderModal #split_target').data('prev', '');
  $('#embeddedWarehouseOrderModal #splitProductModal').modal('hide');
}

function processCapture()
{
  let input = $('#embeddedWarehouseOrderModal #product_input').val();

  // lookup product id by SKU or UPC
  if (input in warehouseOrderDataSource.products) {
    // find last instead of first
    let theIndex = vueWarehouseOrderModal.order.product_id.map(o => o == warehouseOrderDataSource.products[input]["id"]).lastIndexOf(true);
    if (theIndex === -1) {
      let targetProduct = warehouseOrderDataSource.products[input];
      let locationId = $('#embeddedWarehouseOrderModal select#location').val();
      vueWarehouseOrderModal.order.product_id.push(targetProduct['id']);
      vueWarehouseOrderModal.order.sku.push(targetProduct['sku']);
      vueWarehouseOrderModal.order.description.push(targetProduct['description']);
      vueWarehouseOrderModal.order.quantity.push(1);
      vueWarehouseOrderModal.order.bins.push(targetProduct['bins'][vueWarehouseOrderModal.order.type][locationId]);
      vueWarehouseOrderModal.order.bin.push((Object.keys(targetProduct['bins'][vueWarehouseOrderModal.order.type][locationId]).length > 0) ? Object.keys(targetProduct['bins'][vueWarehouseOrderModal.order.type][locationId])[0] : 0);
      warehouseOrderDataSource.prevRowIndex = vueWarehouseOrderModal.order.product_id.length - 1;
      Vue.nextTick( function() {
        $('#embeddedWarehouseOrderModal div.modal-lg div.modal-body').scrollTop($('#embeddedWarehouseOrderModal div.modal-lg div.modal-body')[0].scrollHeight);
      });
    } else {
      vueWarehouseOrderModal.order.quantity[theIndex]++;
      warehouseOrderDataSource.prevRowIndex = theIndex;
      vueWarehouseOrderModal.$forceUpdate();
    }
  } else if (warehouseOrderDataSource.prevRowIndex !== undefined) {
    let binId = -1;
    for (idx in vueWarehouseOrderModal.order.bins[warehouseOrderDataSource.prevRowIndex]) {
      if (vueWarehouseOrderModal.order.bins[warehouseOrderDataSource.prevRowIndex][idx] == input) {
        binId = idx;
        break;
      }
    }
    if (binId != -1) {
      vueWarehouseOrderModal.order.bin[warehouseOrderDataSource.prevRowIndex] = binId;
      vueWarehouseOrderModal.$forceUpdate();
    }
  }

  $('#embeddedWarehouseOrderModal #product_input').val("");
}

function deleteRow(index)
{
  vueWarehouseOrderModal.order.product_id.splice(index, 1);
  vueWarehouseOrderModal.order.sku.splice(index, 1);
  vueWarehouseOrderModal.order.description.splice(index, 1);
  vueWarehouseOrderModal.order.quantity.splice(index, 1);
  vueWarehouseOrderModal.order.bins.splice(index, 1);
  vueWarehouseOrderModal.order.bin.splice(index, 1);
}

function processSplit()
{
  let index = $('#embeddedWarehouseOrderModal #split_source').data('index');
  vueWarehouseOrderModal.order.quantity[index] = $('#embeddedWarehouseOrderModal #split_source').val();
  let locationId = $('#embeddedWarehouseOrderModal select#location').val();
  vueWarehouseOrderModal.order.product_id.splice(index+1, 0, vueWarehouseOrderModal.order.product_id[index]);
  vueWarehouseOrderModal.order.sku.splice(index+1, 0, vueWarehouseOrderModal.order.sku[index]);
  vueWarehouseOrderModal.order.description.splice(index+1, 0, vueWarehouseOrderModal.order.description[index]);
  vueWarehouseOrderModal.order.quantity.splice(index+1, 0, $('#embeddedWarehouseOrderModal #split_target').val());
  vueWarehouseOrderModal.order.bins.splice(index+1, 0, vueWarehouseOrderModal.order.bins[index]);
  vueWarehouseOrderModal.order.bin.splice(index+1, 0, (Object.keys(vueWarehouseOrderModal.order.bins[index]).length > 0) ? Object.keys(vueWarehouseOrderModal.order.bins[index])[0] : 0);

  postSplit();
}

function binLocationRefresh()
{
  let locationId = $('#embeddedWarehouseOrderModal select#location').val();
  for (index in vueWarehouseOrderModal.order.sku) {
    vueWarehouseOrderModal.order.bins[index] = warehouseOrderDataSource.products[vueWarehouseOrderModal.order.sku[index]].bins[vueWarehouseOrderModal.order.type][locationId];
    // just grab first visible option
    vueWarehouseOrderModal.order.bin[index] = Object.keys(vueWarehouseOrderModal.order.bins[index])[0];
  }
}

function loadWarehouseOrderInModal(order_id)
{
  // ajax call to pull records
  return $.ajax({
    type: 'GET',
    url: '/warehouse/order/ajax',
    data: {
        id : order_id
      },
    dataType: 'html',
    beforeSend: function(data) {
      $('.ajax-processing').removeClass('hidden');
    },
  }).done(function (data) {
    let result = JSON.parse(data)
    if (result['success']) {
      // populate datafield
      //vueWarehouseOrderModal.modal.action = result['data']['action'];
      vueWarehouseOrderModal.modal.csrf = result['data']['csrf'];
      //vueWarehouseOrderModal.modal.post_url = result['data']['post_url'];
      // vueWarehouseOrderModal.modal.data_table = '';
      //vueWarehouseOrderModal.modal.readonly = true;
      vueWarehouseOrderModal.modal.title = result['data']['title'];
      vueWarehouseOrderModal.modal.addresses = result['data']['addresses'];
      //vueWarehouseOrderModal.modal.detail_required = false;
      //vueWarehouseOrderModal.modal.bins = [ ];
      vueWarehouseOrderModal.order.id = result['data']['id'];
      vueWarehouseOrderModal.order.increment = result['data']['increment'];
      vueWarehouseOrderModal.order.type = result['data']['type'];
      vueWarehouseOrderModal.order.reference = result['data']['reference'];
      vueWarehouseOrderModal.order.process_date = result['data']['process_date'];
      vueWarehouseOrderModal.order.staff = result['data']['staff'];
      vueWarehouseOrderModal.order.via = result['data']['via'];
      vueWarehouseOrderModal.order.location = result['data']['location'];
      vueWarehouseOrderModal.order.entity = result['data']['entity'];
      vueWarehouseOrderModal.order.address = result['data']['address'];
      vueWarehouseOrderModal.order.product_id = result['data']['product_id'];
      vueWarehouseOrderModal.order.sku = result['data']['sku'];
      vueWarehouseOrderModal.order.description = result['data']['description'];
      vueWarehouseOrderModal.order.quantity = result['data']['quantity'];
      vueWarehouseOrderModal.order.bins = result['data']['sku'].map(
        x => warehouseOrderDataSource.products[x].bins[vueWarehouseOrderModal.order.type][result['data']['location']]
      );
      vueWarehouseOrderModal.order.bin = result['data']['bin'];
      vueWarehouseOrderModal.errors = { };
    }
  }).always(function (data) {
    $('.ajax-processing').addClass('hidden');
    let result = JSON.parse(data);
    if (!result['success']) {
      if ('errors' in result) {
        vueWarehouseOrderModal.errors = result['errors'];
      } else {
        vueWarehouseOrderModal.errors = { general : "System failure" };
      }
    }
  });
}

function createWarehouseOrderInModal(type)
{
  let jqxhr = loadWarehouseOrderInModal(0);

  jqxhr.done(function (data) {
    let actions = {};
    actions[warehouseOrderDataSource.button_create_order] = 'submitWarehouseOrderPost';

    vueWarehouseOrderModal.modal.title = warehouseOrderDataSource.newOrderType[type];
    vueWarehouseOrderModal.modal.action = actions;
    vueWarehouseOrderModal.modal.post_url = '';
    vueWarehouseOrderModal.modal.readonly = false;
    vueWarehouseOrderModal.modal.detail_required = true;
    vueWarehouseOrderModal.order.type = type;

    $('#embeddedWarehouseOrderModal').modal('show');
  });
}

function processWarehouseOrderInModal(order_id)
{
  let jqxhr = loadWarehouseOrderInModal(order_id);

  jqxhr.done(function (data) {
    vueWarehouseOrderModal.modal.action = {};
    vueWarehouseOrderModal.modal.action[warehouseOrderDataSource.button_create_order] = "processWarehouseOrderPost";
    vueWarehouseOrderModal.modal.post_url = '';
    vueWarehouseOrderModal.modal.readonly = false;
    vueWarehouseOrderModal.modal.detail_required = true;

    // zero out processing quantity
    vueWarehouseOrderModal.order.quantity.fill(0);
    // select default bin
    vueWarehouseOrderModal.order.bin = vueWarehouseOrderModal.order.bins.map( x => Object.keys(x)[0] );

    $('#embeddedWarehouseOrderModal').modal('show');
  });
}

function viewWarehouseOrderInModal(order_id)
{
  let jqxhr = loadWarehouseOrderInModal(order_id);

  jqxhr.done(function (data) {
    vueWarehouseOrderModal.modal.action = {};
    vueWarehouseOrderModal.modal.post_url = '';
    vueWarehouseOrderModal.modal.readonly = true;
    vueWarehouseOrderModal.modal.detail_required = true;

    $('#embeddedWarehouseOrderModal').modal('show');
  });
}

function voidWarehouseOrderInModal(order_id)
{
  let jqxhr = loadWarehouseOrderInModal(order_id);

  jqxhr.done(function (data) {
    let actions = {};
    actions[warehouseOrderDataSource.button_void_order] = 'voidWarehouseOrderPost';

    vueWarehouseOrderModal.modal.action = actions;
    vueWarehouseOrderModal.modal.post_url = '';
    vueWarehouseOrderModal.modal.readonly = true;
    vueWarehouseOrderModal.modal.detail_required = true;

    $('#embeddedWarehouseOrderModal').modal('show');
  });
}

function submitWarehouseOrderPost() {
  $.ajax({
    type: 'POST',
    url: '/warehouse/createorder/ajax',
    enctype : 'multipart/form-data',
    data: new FormData($('#embeddedWarehouseOrderModal form')[0]),
    //dataType: 'html',
    processData : false,
    contentType : false,
    cache : false,
    beforeSend: function(data) {
      $('.ajax-processing').removeClass('hidden');
    },
  }).done(function (data) {
    if (data['success']) {
      if ('insertCallback' in warehouseOrderDataSource) {
        warehouseOrderDataSource.insertCallback(data['data']);
      }
      $('#embeddedWarehouseOrderModal').modal('hide');
    }
  }).always(function (data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vueWarehouseOrderModal.errors = data['errors'];
    }
  });
}

function processWarehouseOrderPost() {
  $.ajax({
    type: 'POST',
    url: '/warehouse/processorder/ajax',
    enctype : 'multipart/form-data',
    data: new FormData($('#embeddedWarehouseOrderModal form')[0]),
    //dataType: 'html',
    processData : false,
    contentType : false,
    cache : false,
    beforeSend: function(data) {
      $('.ajax-processing').removeClass('hidden');
    },
  }).done(function (data) {
    if (data['success']) {
      $('#embeddedWarehouseOrderModal').modal('hide');
      if ('updateCallback' in warehouseOrderDataSource) {
        warehouseOrderDataSource.updateCallback(data['data']);
      }
    }
  }).always(function (data) {
    $('.ajax-processing').addClass('hidden');
    if (!data['success']) {
      vueWarehouseOrderModal.errors = data['errors'];
    }
  });
}

function voidWarehouseOrderPost() {
  $.ajax({
    type: 'POST',
    url: '/warehouse/voidorder/ajax',
    data: {
      _token : vueWarehouseOrderModal.modal.csrf,
      id : vueWarehouseOrderModal.order.id
    },
    dataType: 'html',
    beforeSend: function(data) {
      $('.ajax-processing').removeClass('hidden');
    },
  }).done(function (data) {
    let result = JSON.parse(data);
    if (result['success']) {
      if ('updateCallback' in warehouseOrderDataSource) {
        warehouseOrderDataSource.updateCallback(result['data']);
      }
      $('#embeddedWarehouseOrderModal').modal('hide');
    }
  }).always(function (data) {
    $('.ajax-processing').addClass('hidden');
    let result = JSON.parse(data);
    if (!result['success']) {
      vueWarehouseOrderModal.errors = result['errors'];
    }
  });
}

$(document).ready(function() {

  vueWarehouseOrderModal = new Vue({
    el : '#embeddedWarehouseOrderModal',
    data : {
      modal : {
        readonly : true,
        action : '',
        csrf : '',
        post_url : '',
        // data_table : '',
        title : '',
        addresses : [[{ id : 0, street : "" }]],
        detail_required : false,
        //bins : []
      },
      order : {
        id : 0,
        increment : '',
        type : '',
        reference : '',
        process_date : '',
        staff : '',
        via : '',
        location : 0,
        entity : 0,
        address : 0,
        product_id : [],
        sku : [],
        description : [],
        quantity : [],
        bins : [],
        bin : []
      },
      errors : { }
    },
    computed : {
      address_display : function() {
        let address = this.modal.addresses[this.order.entity].find(o => { return o.id == this.order.address; });
        return (address === undefined) ? { id : 0, street : "", unit : "", city : "", district : "", state : "", country : "", zipcode : "" } : address;
      }
    },
    mounted : function() {
      $('#embeddedWarehouseOrderModal #process_date').datepicker().bind('change', function(event) {
        vueWarehouseOrderModal.order.process_date = $(this).val();
      });
      $('#embeddedWarehouseOrderModal').on('shown.bs.modal', function(event) {
        if (!vueWarehouseOrderModal.modal.readonly) {
          $('#embeddedWarehouseOrderModal #addProductModal').modal('show');
        }
      });
      $('#embeddedWarehouseOrderModal #addProductModal').on('shown.bs.modal', function(event) {
        $('#embeddedWarehouseOrderModal #addProductModal #product_input').focus();
      });
      $('#embeddedWarehouseOrderModal form#warehouse_form').keypress(function (e) {
        if (e.keyCode == 13) { e.preventDefault(); }
      });
      // ESC only works at document level and on keydown
      $(document).keydown(function (e) {
        if (e.keyCode == 27) {
          if ($('#embeddedWarehouseOrderModal #addProductModal').hasClass("in")) {
            $('#embeddedWarehouseOrderModal #addProductModal').modal('hide');
          }  else if ($('#embeddedWarehouseOrderModal #splitProductModal').hasClass("in")) {
            $('#embeddedWarehouseOrderModal #splitProductModal').modal('hide');
          } else if ($('#embeddedWarehouseOrderModal').hasClass("in")) {
            $('#embeddedWarehouseOrderModal').modal('hide');
          }
        }
      });
    }
  });
});
