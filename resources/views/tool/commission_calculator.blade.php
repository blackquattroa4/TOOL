@extends('layouts.app')

@section('additional-style')
	<style>
		div.progress-indicator {
			position: fixed;
			left: 40%;
			top: 40%;
			z-index: 100;
		}
	</style>
@endsection

@section('content')
<div class="container">
  <div class="progress-indicator hide">
    <i class="fa fa-spinner fa-pulse fa-5x fa-fw"></i>
  </div>
  <div class="row">
    <div class="col-md-10 col-md-offset-1">
      <!-- profile name modal -->
      <div class="modal fade" id="profile-name-modal" tabindex="-1" role="dialog" aria-labelledby="profileModalLabel" data-backdrop="static" aria-hidden="false">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
              <h4 class="modal-title">{{ trans('tool.Enter a name for this profile') }}</h4>
            </div>
            <div class="modal-body">
              <div class="form-group">
                {{ trans('tool.Profile name') }}
                <input id="profile-name" class="form-control"></input>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans('forms.Close') }}</button>
              <button type="button" id="writeback-profile" class="btn btn-primary">{{ trans('forms.Update') }}</button>
            </div>
          </div>
        </div>
      </div>

      <div id="calculatorwindow" class="panel panel-default">
				<div class="panel-heading">
          <h4>{{ trans('tool.Commission calculator') }}</h4>
        </div>
        <div class="panel-body">
          <div class="panel-group" id="accordion">
            <!-- profile step -->
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title">
                  <a data-toggle="collapse" data-parent="#accordion" href="#collapseProfile"><span class="profile-step">1</span>. {{ trans('tool.Profile') }}</a>
                </h4>
              </div>
              <div id="collapseProfile" class="panel-collapse collapse in">
                <div class="panel-body">
                  <p>{{ trans('tool.Is this commission profile already saved? If so, select the profile.') }}</p>
                  <div class="col-md-6">
                    <select id="profile_id" class="form-control" name="profile_id">
                      <option value="0" data-name="">{{ trans('tool.New profile') }}</option>
                    @foreach ($profiles as $profile)
                      <option value="{{ $profile->id }}" data-name="{{ $profile->name }}">{{ $profile->name }}&emsp;&emsp;{{ sprintf(trans('tool.last saved on %s'), \App\Helpers\DateHelper::dbToGuiDate($profile->updated_at)) }}</option>
                    @endforeach
                    </select>
                  </div>
                  <div class="col-md-6">
                    <button class="btn btn-info next-step pull-right">{{ trans('pagination.next')}}</button>
                  </div>
                </div>
              </div>
            </div>

            <!-- calculation-base step -->
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title">
                  <a data-toggle="collapse" data-parent="#accordion" href="#collapseBase">2. {{ trans('tool.Base') }}</a>
                </h4>
              </div>
              <div id="collapseBase" class="panel-collapse collapse">
                <div class="panel-body">
                  <p>{{ trans('tool.Commission is calculated based on') }}</p>
                  <div class="col-md-6">
                    <select id="commission_base" class="form-control" name="commission_base">
                    @foreach (['purchase-basis' => 'Purchase amount/quantity', 'sales-basis' => 'Sales amount/quantity', 'payment-basis' => 'Payment amount', /*'gross-profit-basis' => 'Gross-profit amount'*/] as $key => $display)
                      <option value="{{ $key }}">{{ trans('tool.'.$display) }}</option>
                    @endforeach
                    </select>
										<span class="help-block"><strong></strong></span>
                  </div>
                  <div class="col-md-6">
                    <button class="btn btn-info next-step pull-right">{{ trans('pagination.next')}}</button>
                  </div>
                </div>
              </div>
            </div>

						<!-- payable entity step -->
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title">
                  <a data-toggle="collapse" data-parent="#accordion" href="#collapsePayable">3. {{ trans('tool.Payable entity') }}</a>
                </h4>
              </div>
              <div id="collapsePayable" class="panel-collapse collapse">
                <div class="panel-body">
                  <p>{{ trans('tool.Commission is payable to') }}</p>
                  <div class="col-md-6">
                    <select id="payable_entity" class="form-control" name="payable_entity">
                    @foreach ($payees as $payee)
											<option value="{{ $payee->id }}">{{ $payee->code }}&emsp;{{ $payee->name}}</option>
                    @endforeach
                    </select>
										<span class="help-block"><strong></strong></span>
                  </div>
                  <div class="col-md-6">
                    <button class="btn btn-info next-step pull-right">{{ trans('pagination.next')}}</button>
                  </div>
                </div>
              </div>
            </div>

            <!-- entity, product, and rate step 1 -->
            <div class="panel panel-default profile-source">
              <div class="panel-heading">
                <h4 class="panel-title">
                  <a data-toggle="collapse" data-parent="#accordion" href="#collapse-source-1">4. {{ trans('tool.Entity, product, & rate') }}&emsp;(1/<span class="total-sources">1</span>)</a>
                </h4>
              </div>
              <div id="collapse-source-1" class="panel-collapse collapse">
                <div class="panel-body">
                  <p>{{ trans('tool.Select entity, product and rate included in calculation') }}</p>
                  <div class="col-md-6">
                    <select id="entity_ids[0][]" class="form-control" name="entity_ids[0][]" multiple="multiple" size="10">
                    @foreach ($entities as $entity)
                      <option value="{{ $entity->id }}">{{ $entity->code }}&emsp;{{ $entity->name}}</option>
                    @endforeach
                    </select>
										<span class="help-block"><strong></strong></span>
                  </div>
                  <div class="col-md-6">
                    <select id="product_ids[0][]" class="form-control" name="product_ids[0][]" multiple="multiple" size="10">
                    @foreach ($products as $product)
                      <option value="{{ $product->id }}">{{ $product->sku }}</option>
                    @endforeach
                    </select>
										<span class="help-block"><strong></strong></span>
                  </div>
                  <div class="col-md-3">
                    {{ trans('forms.Rate') }}
                    <div class="input-group">
    									<div class="input-group-btn">
    										<button id="calctype[0]" name="calctype[0]" type="button" value="" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">%&emsp;<span class="caret"></span></button>
    										<ul class="dropdown-menu">
    											<li><a class="dropdown-item"><i class="fa fa-percent" aria-hidden="true"></i>&emsp;{{ trans('forms.Percent of subtotal') }}</a></li>
    											<li><a class="dropdown-item"><strong>{{ $currencySymbol }}</strong>&emsp;{{ trans('forms.Fixed amount') }}</a></li>
    										</ul>
    									</div>
    									<input id="calcrate[0]" type="number" min="0.00" step="0.01" class="form-control text-right" name="calcrate[0]" value="0.00"></input>
    									<input id="calc_type[0]" type="hidden" name="calc_type[0]" value="percent"></input>
    								</div>
										<span class="help-block"><strong></strong></span>
                  </div>
                  <div class="col-md-9">
                    <span>&emsp;</span><br/>
                    <button class="btn btn-info next-step pull-right">{{ trans('pagination.next')}}</button>
                    <span class="pull-right">&nbsp;</span>
                    <button id="add-more-source" class="btn btn-info pull-right">{{ trans('forms.Add more')}}</button>
                  </div>
                </div>
              </div>
            </div>
            <!-- date-range step -->
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title">
                  <a data-toggle="collapse" data-parent="#accordion" href="#collapseFinal"><span class="date-range-step">5</span>. {{ trans('tool.Date-range') }}</a>
                </h4>
              </div>
              <div id="collapseFinal" class="panel-collapse collapse">
                <div class="panel-body">
                  <p>{{ trans('tool.Select date-range') }}</p>
                  <div class="col-md-3">
                    {{ trans('forms.Start date') }}
                    <div class="input-group date" data-provide="datepicker">
    									<input id="from-date" type="text" class="form-control" name="from-date" value="" >
    									<div class="input-group-addon">
    										<span class="glyphicon glyphicon-calendar"></span>
    									</div>
    								</div>
										<span class="help-block"><strong></strong></span>
                  </div>
                  <div class="col-md-3">
                    {{ trans('forms.End date') }}
                    <div class="input-group date" data-provide="datepicker">
    									<input id="to-date" type="text" class="form-control" name="to-date" value="" >
    									<div class="input-group-addon">
    										<span class="glyphicon glyphicon-calendar"></span>
    									</div>
    								</div>
										<span class="help-block"><strong></strong></span>
                  </div>
                  <div class="col-md-6">
                    <span>&emsp;</span><br/>
                    <button id="calculate-commission" class="btn btn-info pull-right">{{ trans('tool.Calculate') }}</button>
                    <span class="pull-right">&nbsp;</span>
                    <button id="save-profile" class="btn btn-info pull-right">{{ trans('tool.Save profile') }}</button>
                  </div>
                </div>
              </div>
            </div>
            <!-- final calculation step -->
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title">
                  <span class="result-step">6</span>. {{ trans('tool.Result') }}
                </h4>
              </div>
              <div id="collapseResult" class="panel-collapse collapse">
                <div class="panel-body">
                  <div id="calc-criteria" class="col-md-12">
                  </div>
                  <div id="calc-result" class="col-md-12">
                  </div>
                  <div id="calc-post-action" class="col-md-12">
										<button id="record-expense" name="record-expense" class="btn btn-info pull-right" disabled>{{ trans('forms.Record') }}</button>
										<div class="col-md-4 pull-right">
											<select id='expense-account' name='expense-account' class="form-control">
												<option value="">{{ trans('finance.Select an expense account') }}</option>
											@foreach (\App\ChartAccount::getActiveExpenseAccount('description', 'asc')->get() as $expenseAccount)
												<option value="{{ $expenseAccount->id }}">{{ $expenseAccount->description }}&emsp;({{ $expenseAccount->account }})</option>
											@endforeach
											</select>
										</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('post-content')
  <script type="text/javascript">

		// elem is a jquery object
		function toggleRateMethod(elem) {
			if (elem.has('i.fa-percent').length) {
				elem.parent().parent().prev().html('%&emsp;<span class=\"caret\"></span>');
				elem.parent().parent().parent().next().next().val('percent');
			} else {
				elem.parent().parent().prev().html('{{ $currencySymbol }}&emsp;<span class=\"caret\"></span>'); elem.parent().parent().parent().next().next().val('amount');
			}
		}

    // load previously-saved profile, and populate accordingly.
    function loadProfile() {
      $.ajax({
        type: 'GET',
        url: '/commission/profile/' + $('select#profile_id').val(),
        dataType: 'html',
        beforeSend: function(data) {
          $('div.progress-indicator').removeClass('hide');
        },
      }).success(function(data) {
				var result = JSON.parse(data);
				if (result['success']) {
					for (var i in result['source']['entity']) {
						if (i > 0) {
							$('button#add-more-source').trigger('click');
						}
	          $('select#commission_base').val(result['source']['base']);
						$('select#payable_entity').val(result['source']['payable_entity']);
	          $('select#entity_ids\\[' + i + '\\]\\[\\]').val(result['source']['entity'][i]);
	          $('select#product_ids\\[' + i + '\\]\\[\\]').val(result['source']['product'][i]);
	          $('button#calctype\\[' + i + '\\]').html(((result['source']['method'][i] == "percent") ? "%" : "{{ $currencySymbol }}") + "&emsp;<span class=\"caret\"></span>");
	          $('input#calcrate\\[' + i + '\\]').val(result['source']['rate'][i]);
	          $('input#calc_type\\[' + i + '\\]').val(result['source']['method'][i]);
	          $('input#from-date').val(result['source']['last_unrecorded_date']);
						$('input#to-date').val('{{ \App\Helpers\DateHelper::dbToGuiDate(date('Y-m-d')) }}');
					}
					// jump to date-range-step
	        $('span.date-range-step').parent().trigger('click');
				} else {
					$('div.flash-message').append('<p class="alert alert-danger">' + result['message'] + '<a href="#" class="close" data-dismiss="alert" aria-label="close">×</a></p>');
				}
      }).error(function(data) {
        $('div.flash-message').append('<p class="alert alert-danger">{{ trans('tool.Commission profile failed to load') }}<a href="#" class="close" data-dismiss="alert" aria-label="close">×</a></p>');
      }).always(function(data) {
        $('div.progress-indicator').addClass('hide');
      });
    }

    function saveProfile()
    {
      $.ajax({
        type: 'POST',
        url: '/commission/profile/' + $('select#profile_id').val(),
        data: {
            _token: '{{ csrf_token() }}',
            source: {
              name: $('input#profile-name').val(),
              base: $('select#commission_base').val(),
							payable_entity: $('select#payable_entity').val(),
              entity: $.map($('select[id^=entity_ids\\[]'), function(obj) { return [ $(obj).val() ]; }),
              product: $.map($('select[id^=product_ids\\[]'), function(obj) { return [ $(obj).val() ]; }),
              rate: $.map($('input[id^=calcrate\\[]'), function(obj) { return $(obj).val(); }),
              method: $.map($('input[id^=calc_type\\[]'), function(obj) { return $(obj).val(); })
            }
          },
        dataType: 'html',
        beforeSend: function(data) {
          $('div.progress-indicator').removeClass('hide');
        },
      }).success(function(data) {
        var result = JSON.parse(data);
        $('div.flash-message').append('<p class="alert alert-' + (result['success'] ? 'success' : 'danger') + '">' + result['message'] + '<a href="#" class="close" data-dismiss="alert" aria-label="close">×</a></p>');
      }).error(function(data) {
        $('div.flash-message').append('<p class="alert alert-danger">{{ trans('tool.Commission profile failed to save') }}<a href="#" class="close" data-dismiss="alert" aria-label="close">×</a></p>');
      }).always(function(data) {
        $('div.progress-indicator').addClass('hide');
      });
    }

    // add one more collapsable to accordion
    function addMoreSource() {
      $('button#add-more-source').remove();
      var count = $('div#accordion div.profile-source').size();
      var lastSource = $('div#accordion div.profile-source:last');
      $('span.total-sources').html(count+1);
      $("<div class=\"panel panel-default profile-source\"><div class=\"panel-heading\"><h4 class=\"panel-title\"><a data-toggle=\"collapse\" data-parent=\"#accordion\" href=\"#collapse-source-" + (count+1) + "\">" + (count+4) + ". {{ trans('tool.Entity, product, & rate') }}&emsp;(" + (count+1) + "/<span class=\"total-sources\">" + (count+1) + "</span>)</a></h4></div><div id=\"collapse-source-" + (count+1) + "\" class=\"panel-collapse collapse\"><div class=\"panel-body\"><p>{{ trans('tool.Select entity, product and rate included in calculation') }}</p><div class=\"col-md-6\"><select id=\"entity_ids[" + count + "][]\" class=\"form-control\" name=\"entity_ids[" + count + "][]\" multiple=\"multiple\" size=\"10\">" +
    @foreach ($entities as $entity)
      "<option value=\"{{ $entity->id }}\">{{ $entity->code }}&emsp;{{ $entity->name}}</option>" +
    @endforeach
      "</select><span class=\"help-block\"><strong></strong></span></div><div class=\"col-md-6\"><select id=\"product_ids[" + count + "][]\" class=\"form-control\" name=\"product_ids[" + count + "][]\" multiple=\"multiple\" size=\"10\">" +
    @foreach ($products as $product)
      "<option value=\"{{ $product->id }}\">{{ $product->sku }}</option>" +
    @endforeach
      "</select><span class=\"help-block\"><strong></strong></span></div><div class=\"col-md-3\">{{ trans('forms.Rate') }}<div class=\"input-group\"><div class=\"input-group-btn\"><button id=\"calctype[" + count + "]\" name=\"calctype[" + count + "]\" type=\"button\" value=\"\" class=\"btn btn-info dropdown-toggle\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">%&emsp;<span class=\"caret\"></span></button><ul class=\"dropdown-menu\"><li><a class=\"dropdown-item\"><i class=\"fa fa-percent\" aria-hidden=\"true\"></i>&emsp;{{ trans('forms.Percent of subtotal') }}</a></li><li><a class=\"dropdown-item\" ><strong>{{ $currencySymbol }}</strong>&emsp;{{ trans('forms.Fixed amount') }}</a></li></ul></div><input id=\"calcrate[" + count + "]\" type=\"number\" min=\"0.00\" step=\"0.01\" class=\"form-control text-right\" name=\"calcrate[" + count + "]\" value=\"0.00\"></input><input id=\"calc_type[" + count + "]\" type=\"hidden\" name=\"calc_type[" + count + "]\" value=\"percent\" ></input></div><span class=\"help-block\"><strong></strong></span></div><div class=\"col-md-9\"><span>&emsp;</span><br/><button class=\"btn btn-info next-step pull-right\">{{ trans('pagination.next')}}</button><span class=\"pull-right\">&nbsp;</span><button id=\"add-more-source\" class=\"btn btn-info pull-right\">{{ trans('forms.Add more')}}</button></div></div></div></div>").insertAfter(lastSource);
      // update step count
      $('span.date-range-step').html(count+5);
      $('span.result-step').html(count+6);
      // bind 'next' within newly created step.
      $('div#accordion div.profile-source:last button.next-step').bind('click', function() {
        $(this).parent().parent().parent().parent().next().find('div.panel-heading a').click();
      });
			// bind rate method
			$('div#accordion div.profile-source:last a.dropdown-item').bind('click', function() {
				toggleRateMethod($(this));
			});
      // bind 'add more' within newly created step
      $('button#add-more-source').bind('click', function() {
        addMoreSource();
      });
    }

		function validateInput() {
			var errors = [];
			var target = null;

			target = $('select#commission_base');
			if (!!target.val()) {
				target.parent().removeClass('has-error');
				target.parent().find('span.help-block strong').html('');
			} else {
				target.parent().addClass('has-error');
				target.parent().find('span.help-block strong').html('{{ trans('tool.This field must be entered') }}');
				errors.push('collapseBase');
			}

			target = $('select#payable_entity');
			if (!!target.val()) {
				target.parent().removeClass('has-error');
				target.parent().find('span.help-block strong').html('');
			} else {
				target.parent().addClass('has-error');
				target.parent().find('span.help-block strong').html('{{ trans('tool.This field must be entered') }}');
				errors.push('collapsePayable');
			}

			$('select[id^=entity_ids\\[]').each(function(idx) {
				if (!!$(this).val()) {
					$(this).parent().removeClass('has-error');
					$(this).parent().find('span.help-block strong').html('');
				} else {
					$(this).parent().addClass('has-error');
					$(this).parent().find('span.help-block strong').html('{{ trans('tool.This field must be entered') }}');
					errors.push($(this).parent().parent().parent().attr('id'));
				}
			});

			$('select[id^=product_ids\\[]').each(function(idx) {
				if ((!!$(this).val()) || (['payment-amount', 'gross-profit-amount'].includes($('select#commission_base').val()))) {
					$(this).parent().removeClass('has-error');
					$(this).parent().find('span.help-block strong').html('');
				} else {
					$(this).parent().addClass('has-error');
					$(this).parent().find('span.help-block strong').html('{{ trans('tool.This field must be entered') }}');
					errors.push($(this).parent().parent().parent().attr('id'));
				}
			});

			$('input[id^=calcrate\\[]').each(function(idx) {
				if (parseFloat($(this).val()) > 0.00) {
					$(this).parent().parent().removeClass('has-error');
					$(this).parent().parent().find('span.help-block strong').html('');
				} else {
					$(this).parent().parent().addClass('has-error');
					$(this).parent().parent().find('span.help-block strong').html('{{ trans('tool.This field must be entered') }}');
					errors.push($(this).parent().parent().parent().parent().attr('id'));
				}
			});

			target = $('input#from-date');
			if (!!target.val()) {
				target.parent().parent().removeClass('has-error');
				target.parent().parent().find('span.help-block strong').html('');
			} else {
				target.parent().parent().addClass('has-error');
				target.parent().parent().find('span.help-block strong').html('{{ trans('tool.This field must be entered') }}');
				errors.push('collapseFinal');
			}

			target = $('input#to-date');
			if (!!target.val()) {
				target.parent().parent().removeClass('has-error');
				target.parent().parent().find('span.help-block strong').html('');
			} else {
				target.parent().parent().addClass('has-error');
				target.parent().parent().find('span.help-block strong').html('{{ trans('tool.This field must be entered') }}');
				errors.push('collapseFinal');
			}

			return errors;
		}

    function calculateCommission () {
			var errors = validateInput();

			if (errors.length > 0) {
				$('div.flash-message').append('<p class="alert alert-danger">{{ trans('tool.Please correct all errors') }}<a href="#" class="close" data-dismiss="alert" aria-label="close">×</a></p>');
				// open first error.
				if (errors[0] != 'collapseFinal') {
					$('a[href=\\#' + errors[0] + ']').trigger('click');
				}
				return false;
			}

      $.ajax({
        type: 'GET',
        url: '/commission/calculate',
        data: {
            base: $('select#commission_base').val(),
            entity: $.map($('select[id^=entity_ids\\[]'), function(obj) { return [ $(obj).val() ]; }),
            product: $.map($('select[id^=product_ids\\[]'), function(obj) { return [ $(obj).val() ]; }),
            rate: $.map($('input[id^=calcrate\\[]'), function(obj) { return $(obj).val(); }),
            method: $.map($('input[id^=calc_type\\[]'), function(obj) { return $(obj).val(); }),
            date: [
              $('input#from-date').val(),
              $('input#to-date').val()
            ]
          },
        dataType: 'html',
        beforeSend: function(data) {
          $('div.progress-indicator').removeClass('hide');
        },
      }).success(function(data) {
        var result = JSON.parse(data);
				if (result['success']) {
	        // write in div#calc-criteria
	        $('div#calc-criteria').html(result['criteria']);
	        // write in div#calc-result
	        $('div#calc-result').html(result['result']);
	        // show calculation result
	        $('div#collapseFinal').removeClass('in');
	        $('div#collapseResult').addClass('in');
				} else {
					$('div.flash-message').append('<p class="alert alert-danger">' + result['message'] + '<a href="#" class="close" data-dismiss="alert" aria-label="close">×</a></p>');
				}
      }).error(function(data) {
        $('div.flash-message').append('<p class="alert alert-danger">{{ trans('tool.Commission can not be calculated') }}<a href="#" class="close" data-dismiss="alert" aria-label="close">×</a></p>');
      }).always(function(data) {
        $('div.progress-indicator').addClass('hide');
      });

    }

		function recordCommission () {
			var errors = validateInput();

			if (errors.length > 0) {
				$('div.flash-message').append('<p class="alert alert-danger">{{ trans('tool.Please correct all errors') }}<a href="#" class="close" data-dismiss="alert" aria-label="close">×</a></p>');
				// open first error.
				if (errors[0] != 'collapseFinal') {
					$('a[href=\\#' + errors[0] + ']').trigger('click');
				}
				return false;
			}

      $.ajax({
        type: 'POST',
        url: '/commission/record',
        data: {
						_token: '{{ csrf_token() }}',
            base: $('select#commission_base').val(),
						payee: $('select#payable_entity').val(),
            entity: $.map($('select[id^=entity_ids\\[]'), function(obj) { return [ $(obj).val() ]; }),
            product: $.map($('select[id^=product_ids\\[]'), function(obj) { return [ $(obj).val() ]; }),
            rate: $.map($('input[id^=calcrate\\[]'), function(obj) { return $(obj).val(); }),
            method: $.map($('input[id^=calc_type\\[]'), function(obj) { return $(obj).val(); }),
            date: [
              $('input#from-date').val(),
              $('input#to-date').val()
            ],
						account: $('select#expense-account').val(),
						profile: $('select#profile_id').val(),
          },
        dataType: 'html',
        beforeSend: function(data) {
          $('div.progress-indicator').removeClass('hide');
        },
      }).success(function(data) {
        var result = JSON.parse(data);
				if (result['success']) {
					$('div.flash-message').append('<p class="alert alert-success">' + result['message'] + '<a href="#" class="close" data-dismiss="alert" aria-label="close">×</a></p>');
					$('span.profile-step').parent().trigger('click');
				} else {
					$('div.flash-message').append('<p class="alert alert-danger">' + result['message'] + '<a href="#" class="close" data-dismiss="alert" aria-label="close">×</a></p>');
				}
      }).error(function(data) {
        $('div.flash-message').append('<p class="alert alert-danger">{{ trans('tool.Commission can not be calculated') }}<a href="#" class="close" data-dismiss="alert" aria-label="close">×</a></p>');
      }).always(function(data) {
        $('div.progress-indicator').addClass('hide');
				window.scrollTo({ top: 0, left: 0, behavior: 'smooth' });
      });

    }

    $(document).ready(function() {
      $('select#profile_id').bind('change', function() {
        // detach add-more button.
        var addMoreButton = $('button#add-more-source').detach();
        // remove all profile-sources but first one.
        $("div#accordion div.profile-source:not(:first)").remove();
        // adjust step index
        $('span.date-range-step').html(5);
        $('span.result-step').html(6);
        $('span.total-sources').html(1);
        // add-more back to first profile-source
        $('div#collapse-source-1 div.panel-body > div:last').append(addMoreButton);
				// remove 'has-error' class and clear out help-block
				$('div.has-error').removeClass('has-error');
				$('span.help-block strong').html('');

        if ($(this).val() == 0) {
          // clear out first source for clean-slate
          $('input#profile-name').val("");
          $('select#commission_base').val("");
					$('select#payable_entity').val("");
          $('select#entity_ids\\[0\\]\\[\\]').val("");
          $('select#product_ids\\[0\\]\\[\\]').val("");
          $('button#calctype\\[0\\]').html("%&emsp;<span class=\"caret\"></span>");
          $('input#calcrate\\[0\\]').val("0.00");
          $('input#calc_type\\[0\\]').val("percent");
          $('input#from-date').val("");
          $('input#to-date').val("");
        } else {
          $('input#profile-name').val($(this).find('option:selected').data('name'));
          loadProfile();
        }
      });

			$('a.dropdown-item').bind('click', function() {
				toggleRateMethod($(this));
			});

      $('button.next-step').bind('click', function() {
        $(this).parent().parent().parent().parent().next().find('div.panel-heading a').click();
      });

      $('button#add-more-source').bind('click', function() {
        addMoreSource();
      });

      $("button#save-profile").bind('click', function() {
        $('div#profile-name-modal').modal('show');
        // button in profile-name-modal will trigger actual save function
      });

      $("button#writeback-profile").bind('click', function() {
        $('div#profile-name-modal').modal('hide');
        saveProfile();
      });

      $("button#calculate-commission").bind('click', function() {
        calculateCommission();
      });

			$('select#expense-account').bind('change', function() {
				if ($(this).val() != "") {
					$('button[id="record-expense"]').prop('disabled', false);
				} else {
					$('button[id="record-expense"]').prop('disabled', true);
				}
			});

			$('button#record-expense').bind('click', function() {
				recordCommission();
			});
    });
  </script>
@endsection
