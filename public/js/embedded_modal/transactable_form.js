// use in conjunction with ../../../resources/views/embedded_modal/charge_entry.blade.php

var vueTransactableModal = null;

function loadTransactableWithAjax(trx_id) {
  // use ajax to load transactable #id
  return $.ajax({
    type: 'GET',
    url: '/transactable/' + trx_id + '/ajax',
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
      vueTransactableModal.errors = result['errors'];
    } else {
      vueTransactableModal.errors = { general : "System failure" };
    }
  });
}

function populateTransactableModalWithAjaxResult(result) {
  vueTransactableModal.modal.readonly =  result.readonly;
  vueTransactableModal.modal.history = result.history;
  vueTransactableModal.modal.csrf = result.csrf;
  vueTransactableModal.modal.action  = result.action;
  vueTransactableModal.modal.post_url = result.post_url;
  vueTransactableModal.modal.title = result.title;
  vueTransactableModal.modal.entity = result.entities;
  vueTransactableModal.modal.payment = result.payments;
  vueTransactableModal.modal.contact = result.contacts;
  vueTransactableModal.modal.staff = result.staffs;
  vueTransactableModal.modal.currency = result.currencies;
  vueTransactableModal.modal.item = result.items;
  vueTransactableModal.modal.billing = result.billings;
  vueTransactableModal.modal.shipping = result.shippings;

  vueTransactableModal.form.id = result.id;
  vueTransactableModal.form.type = result.type;
  vueTransactableModal.form.status = result.status;
  vueTransactableModal.form.increment = result.increment;
  vueTransactableModal.form.show_discount = result.show_discount;
  vueTransactableModal.form.entity = result.entity;
  vueTransactableModal.form.incur_date = result.incur_date;
  vueTransactableModal.form.payment = result.payment;
  vueTransactableModal.form.due_date = result.due_date;
  vueTransactableModal.form.source = result.source;
  vueTransactableModal.form.reference = result.reference;
  vueTransactableModal.form.staff = result.staff;
  vueTransactableModal.form.contact = result.contact;
  vueTransactableModal.form.currency = result.currency;
  vueTransactableModal.form.currencyFormat.min = result.currency_format.min;
  vueTransactableModal.form.currencyFormat.regex = result.currency_format.regex;
  vueTransactableModal.form.currencyFormat.symbol = result.currency_format.symbol;
  vueTransactableModal.form.billing = result.billing;
  vueTransactableModal.form.shipping = result.shipping;
  vueTransactableModal.form.notes = result.notes;
  vueTransactableModal.form.grand_subtotal = result.grand_subtotal;
  vueTransactableModal.form.tax_amount = result.tax_amount;
  vueTransactableModal.form.grand_total = result.grand_total;
  vueTransactableModal.form.balance = result.balance;
  vueTransactableModal.form.line = result.line;
  vueTransactableModal.form.product = result.product;
  vueTransactableModal.form.display = result.display;
  vueTransactableModal.form.unitprice = result.unitprice;
  vueTransactableModal.form.discount = result.discount;
  vueTransactableModal.form.disctype = result.disctype;
  vueTransactableModal.form.description = result.description;
  vueTransactableModal.form.quantity = result.quantity;
  vueTransactableModal.form.linetax = result.linetax;
  vueTransactableModal.form.subtotal = result.subtotal;
}

function viewTransactableEntryInModal(trx_id) {
  let jqxhr = loadTransactableWithAjax(trx_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      result['data']['readonly'] = true;
      // key can not be computed dynamically, create an empty object and stuff attribute
      result['data']['action'] = {};
      result['data']['action'][vueTransactableDataSource.button_view] = 'viewTransactablePost';
      result['data']['post_url'] = '';
      result['data']['title'] = vueTransactableDataSource["text_view_" + result['data']['type']];
      result['data']['entities'] = vueTransactableDataSource["selection_" + result['data']['type']];
      result['data']['payments'] = vueTransactableDataSource.selection_payment;
      result['data']['contacts'] = vueTransactableDataSource.selection_contact[result['data']['entity']];
      result['data']['staffs'] = vueTransactableDataSource.selection_staff;
      result['data']['currencies'] = vueTransactableDataSource.selection_currency;
      result['data']['items'] = vueTransactableDataSource.selection_item;
      result['data']['billings'] = vueTransactableDataSource.selection_billing[result['data']['entity']];
      result['data']['shippings'] = vueTransactableDataSource.selection_shipping[result['data']['entity']];
      populateTransactableModalWithAjaxResult(result['data']);
      vueTransactableModal.errors = [];

      Vue.nextTick(function () {
        // unbind date-picker
        $('#embeddedTransactableModal #incurdate').datepicker('destroy');
        $('#embeddedTransactableModal #incurdate').removeClass('hasDatepicker');
        $('#embeddedTransactableModal #duedate').datepicker('destroy');
        $('#embeddedTransactableModal #duedate').removeClass('hasDatepicker');
        // re-bind date-pciker
        // $('#embeddedTransactableModal #incurdate').datepicker().bind('change', function(event) {
        //   vueTransactableModal.form.incur_date = $(this).val();
        // });
        // $('#embeddedTransactableModal #duedate').datepicker().bind('change', function(event) {
        //   vueTransactableModal.form.due_date = $(this).val();
        // });
      });

      // show modal
      $('#embeddedTransactableModal').modal('show');
    }
  });
}

function viewTransactablePost() {
  $.ajax({
    url: '/transactable/print/' + vueTransactableModal.form.id + '/ajax',
    data: {
      _token : vueTransactableModal.modal.csrf
    },
    method: 'POST',
    xhrFields: {
        responseType: 'blob'
    },
    success: function (data) {
      console.log(data);
      var a = document.createElement('a');
      var url = window.URL.createObjectURL(data);
      console.log(a);
      a.href = url;
      a.download = vueTransactableModal.form.type.charAt(0).toUpperCase() + vueTransactableModal.form.type.slice(1) + ' #' + vueTransactableModal.form.increment + '.pdf';
      document.body.append(a);
      a.click();
      a.remove();
      window.URL.revokeObjectURL(url);
    }
  });
}

function voidTransactableEntryInModal(trx_id) {
  let jqxhr = loadTransactableWithAjax(trx_id);

  jqxhr.done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      // populate modal
      result['data']['readonly'] = true;
      // key can not be computed dynamically, create an empty object and stuff attribute
      result['data']['action'] = {};
      result['data']['action'][vueTransactableDataSource.button_void] = 'voidTransactablePost';
      result['data']['post_url'] = '';
      result['data']['title'] = vueTransactableDataSource["text_void_" + result['data']['type']];
      result['data']['entities'] = vueTransactableDataSource["selection_" + result['data']['type']];
      result['data']['payments'] = vueTransactableDataSource.selection_payment;
      result['data']['contacts'] = vueTransactableDataSource.selection_contact[result['data']['entity']];
      result['data']['staffs'] = vueTransactableDataSource.selection_staff;
      result['data']['currencies'] = vueTransactableDataSource.selection_currency;
      result['data']['items'] = vueTransactableDataSource.selection_item;
      result['data']['billings'] = vueTransactableDataSource.selection_billing[result['data']['entity']];
      result['data']['shippings'] = vueTransactableDataSource.selection_shipping[result['data']['entity']];
      populateTransactableModalWithAjaxResult(result['data']);
      vueTransactableModal.errors = [];

      Vue.nextTick(function () {
        // unbind date-picker
        $('#embeddedTransactableModal #incurdate').datepicker('destroy');
        $('#embeddedTransactableModal #incurdate').removeClass('hasDatepicker');
        $('#embeddedTransactableModal #duedate').datepicker('destroy');
        $('#embeddedTransactableModal #duedate').removeClass('hasDatepicker');
        // re-bind date-pciker
        // $('#embeddedTransactableModal #incurdate').datepicker().bind('change', function(event) {
        //   vueTransactableModal.form.incur_date = $(this).val();
        // });
        // $('#embeddedTransactableModal #duedate').datepicker().bind('change', function(event) {
        //   vueTransactableModal.form.due_date = $(this).val();
        // });
      });

      // show modal
      $('#embeddedTransactableModal').modal('show');
    }
  });
}

function voidTransactablePost() {
  $.ajax({
    url : '/transactable/void/' + vueTransactableModal.form.id + '/ajax',
    type : 'POST',
    data : {
      _token : vueTransactableModal.modal.csrf,
    },
    dataType: 'html',
    beforeSend: function(data) {
      $('.ajax-processing').removeClass('hidden');
    },
  }).done(function(data) {
    let result = JSON.parse(data);
    if (result['success']) {
      $('#embeddedTransactableModal').modal('hide');
      // insert into expense table
      if ('insertCallback' in vueTransactableDataSource) {
        vueTransactableDataSource.updateCallback(result['data']['transactable']);
      }
    }
  }).always(function(data) {
    $('.ajax-processing').addClass('hidden');
    let result = JSON.parse(data);
    if (!result['success']) {
      vueTransactableModal.errors = result['errors'];
    }
  });
}

$(document).ready(function() {

  vueTransactableModal = new Vue({
    el : "#embeddedTransactableModal",
		data : {
      modal : {
        readonly : false,
        history : [ ],
        csrf : '',
        action : '',
        post_url : '',
        title : '',
        entity : [ ],
        payment : [ ],
        contact : [ ],
        staff : [ ],
        currency : [ ],
        item : [ ],
        billing : [ ],
        shipping : [ ]
      },
			form : {
        id : 0,
        type : '',
        status : '',
        increment : '',
  			show_discount : false,
  			entity : 0,
  			incur_date : '',
  			payment : 0,
  			due_date : '',
  			source : '',
  			reference : '',
  			staff : 0,
  			contact : 0,
  			currency : 0,
  			currencyFormat : {
            min : 1,
  					regex : '',
  					symbol : '',
  				},
  			billing : 0,
  			shipping : 0,
  			notes : '',
  			grand_subtotal : '',
  			tax_amount : '',
  			grand_total : '',
  			balance : '',
  			line : [ ],
  			product : [ ],
  			display : [ ],
  			unitprice : [ ],
  			discount : [ ],
  			disctype : [ ],
  			description : [ ],
  			quantity : [ ],
  			linetax : [ ],
  			subtotal : [ ]
      },
			errors : { }
		},
    computed : {
      billing_display : function() {
        let address = this.modal.billing.find(o => { return o.id == this.form.billing; });
        return (address === undefined) ? { id : 0, street : "", unit : "", city : "", district : "", state : "", country : "", zipcode : "" } : address;
      },
      shipping_display : function() {
        let address = this.modal.shipping.find(o => { return o.id == this.form.shipping; });
        return (address === undefined) ? { id : 0, street : "", unit : "", city : "", district : "", state : "", country : "", zipcode : "" } : address;
      }
    },
		mounted : function() {
			$('#embeddedTransactableModal #billing').selectpicker().bind('change', function(event) {
				vueTransactableModal.form.billing = $(this).val();
			});
			$('#embeddedTransactableModal #shipping').selectpicker().bind('change', function(event) {
				vueTransactableModal.form.shipping = $(this).val();
			});
      // disable ENTER
      $('#embeddedTransactableModal form#transactable_form').keypress(function (e) {
        if (e.keyCode == 13) { e.preventDefault(); }
      });
      // ESC only works at document level and on keydown
      $(document).keydown(function (e) {
        if (e.keyCode == 27) {
          if ($('#embeddedTransactableModal #historyModal').hasClass("in")) {
            $('#embeddedTransactableModal #historyModal').modal('hide');
          } else if ($('#embeddedTransactableModal #aggregationModal').hasClass("in")) {
              $('#embeddedTransactableModal #aggregationModal').modal('hide');
          } else if ($('#embeddedTransactableModal').hasClass("in")) {
            $('#embeddedTransactableModal').modal('hide');
          }
        }
      });
		}
	});
});
