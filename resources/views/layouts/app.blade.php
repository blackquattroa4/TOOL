<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">

	<!-- following prevents page cache at browser level -->
	<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
	<meta http-equiv="Pragma" content="no-cache" />
	<meta http-equiv="Expires" content="0" />

	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- CSRF Token -->
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<title>{{ config('app.name', 'Laravel') }} {{ App::environment('local') ? ("(Laravel " . app()->version() . ")") : "" }}</title>

	<!-- Fonts -->
@if (!App::environment('local'))
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha256-eZrrJcwDc/3uDhsdt61sL2oOBY362qM3lon1gyExkL0=" crossorigin="anonymous">
@else
	<link rel="stylesheet" href="{{ asset('external/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css') }}" integrity="sha256-eZrrJcwDc/3uDhsdt61sL2oOBY362qM3lon1gyExkL0=" crossorigin="anonymous">
@endif

@if (!App::environment('local'))
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700">
@else
	<link rel="stylesheet" href="{{ asset('font/css/fonts.css') }}">
@endif

@if (!App::environment('local'))
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.4.1/css/bootstrap.min.css" integrity="sha256-bZLfwXAP04zRMK2BjiO8iu9pf4FbLqX6zitd+tIvLhE=" crossorigin="anonymous">
@else
	<link rel="stylesheet" href="{{ asset('external/ajax/libs/twitter-bootstrap/3.4.1/css/bootstrap.min.css') }}" integrity="sha256-bZLfwXAP04zRMK2BjiO8iu9pf4FbLqX6zitd+tIvLhE=" crossorigin="anonymous">
@endif

	<!-- Styles -->
	<link href="{{ asset('css/app.css') }}" rel="stylesheet">

@if (!App::environment('local'))
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
@else
	<link rel="stylesheet" href="{{ asset('external/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css') }}">
@endif

@if (!App::environment('local'))
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.12/css/bootstrap-select.min.css">
@else
	<link rel="stylesheet" href="{{ asset('external/ajax/libs/bootstrap-select/1.13.12/css/bootstrap-select.min.css') }}">
@endif

@if (!App::environment('local'))
	<link rel="stylesheet" href="https://cdn.datatables.net/1.10.23/css/dataTables.bootstrap.min.css">
@else
	<link rel="stylesheet" href="{{ asset('external/datatables/1.10.23/css/dataTables.bootstrap.min.css') }}">
@endif

@if (!App::environment('local'))
	<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.6.4/css/buttons.dataTables.min.css">
@else
	<link rel="stylesheet" href="{{ asset('external/datatable-buttons/1.6.4/css/buttons.dataTables.min.css') }}">
@endif

	<style>
		body {
			font-family: 'Lato';
		@if (!Auth::user())
			background-image: url("/images/login_backdrop.jpg");
			background-attachment: fixed;
			background-position: center;
		@endif
		}

		a.navbar-brand {
			padding: 5px !important;
		}

		.fa-btn {
			margin-right: 6px;
		}

		.back-to-top {
			position: fixed;
			bottom: 0em;
			right: 0px;
			text-decoration: none;
			color: #000000;
			background-color: rgba(255, 255, 255, 0.0);
			font-size: 12px;
			padding: 1em;
			display: none;
		}

		.back-to-top:hover {
			background-color: rgba(255, 255, 255, 0.00);
		}

		.progress-animation {
			z-index: 100;
			position: absolute;
			left: 50%;
			top: 50%;
		}

		button.selectpicker:focus,
		button.selectpicker:active,
		button.selectpicker:hover {
			background-color: #FFFFFF	!important;
		}

		/* when hover, text-color should be white, otherwise black */
		div.dropdown-menu ul li a:hover {
			color: #FFFFFF !important;
			background-color: #337AB7 !important;
		}

		button.selectpicker.disabled,
		button.selectpicker.disabled:not(:hover),
		button.selectpicker.disabled:hover {
			background-color: #eee !important;
		}

		.fa-stacking[new-count]:after{
		  position:absolute;
		  left:40%;
		  top:10%;
		  content: attr(new-count);
		  font-size:50%;
		  padding:.3em;
		  border-radius:999px;
		  line-height:.75em;
		  color: white;
		  background:rgba(255,0,0,.85);
		  text-align:center;
		  min-width:2em;
		  font-weight:bold;
		}

		.ajax-processing {
			position: fixed;
			bottom: 50%;
			left: 50%;
			text-decoration: none;
			background-color: rgba(255, 255, 255, 0.0);
			font-size: 15px;
			padding: 1em;
			z-index: 10000;
		}

		.btn {
			margin-right: 2px;
		}

	</style>

	@yield('additional-style')

</head>
<body>
	<div id="app">
	@if (Auth::user())
		<nav class="navbar navbar-default navbar-static-top">
			<div class="container">
				<div class="navbar-header">

					<!-- Collapsed Hamburger -->
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse" aria-expanded="false">
						<span class="sr-only">Toggle Navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>

					<!-- Branding Image -->
					<a class="navbar-brand" href="{{ url('/') }}">
						<img style="height:40px;vertical-align:center;" src="{{ url('/company_logo.png') }}" />
					</a>
				</div>

				<div class="collapse navbar-collapse" id="app-navbar-collapse">
					<!-- Left Side Of Navbar -->
					<ul class="nav navbar-nav">
						<li class="dropdown">
							<a tabindex="0" data-toggle="dropdown" >{{ trans('messages.Work') }} <span class="caret"></span></a>
							<ul class="dropdown-menu">
							@if (Auth::user()->can('ap-list') && Auth::user()->can('ar-list') && Auth::user()->can('rar-list') && Auth::user()->can('rap-list') && Auth::user()->can('ex-list'))
								<li><a tabindex="-1" href="{{ url('/finance/dashboard') }}">{{ trans('messages.Finance dashboard') }}</a></li>
							@endif
							@if (Auth::user()->can('ap-list') && Auth::user()->can('ar-list') && Auth::user()->can('rar-list') && Auth::user()->can('rap-list') && Auth::user()->can('ex-list'))
								<li><a tabindex="-1" href="{{ url('/accounting/dashboard') }}">{{ trans('messages.Accounting dashboard') }}</a></li>
							@endif
							@if (Auth::user()->can('hr-list'))
								<li><a tabindex="-1" href="{{ url('/hr/dashboard') }}">{{ trans('messages.H/R dashboard') }}</a></li>
							@endif
							@if (Auth::user()->can('pd-list'))
								<li><a tabindex="-1" href="{{ url('/product/dashboard') }}">{{ trans('messages.Product dashboard') }}</a></li>
							@endif
							@if (Auth::user()->can('supplier-list') && Auth::user()->can('pq-list') && Auth::user()->can('po-list') && Auth::user()->can('pr-list'))
								<li><a tabindex="-1" href="{{ url('/vrm/dashboard') }}">{{ trans('messages.VRM dashboard') }}</a></li>
							@endif
							@if (Auth::user()->can('customer-list') && Auth::user()->can('sq-list') && Auth::user()->can('so-list') && Auth::user()->can('sr-list'))
								<li><a tabindex="-1" href="{{ url('/crm/dashboard') }}">{{ trans('messages.CRM dashboard') }}</a></li>
							@endif
							@if (true)
								<!-- <li><a tabindex="-1" href="{{ url('/production/dashboard') }}">{{ trans('messages.Production dashboard') }}</a></li> -->
								<li><a tabindex="-1" style="opacity:0.5;cursor:not-allowed;">{{ trans('messages.Production dashboard') }}</a></li>
							@endif
							@if (Auth::user()->can('wo-list'))
								<li><a tabindex="-1" href="{{ url('/warehouse/dashboard') }}">{{ trans('messages.Warehouse dashboard') }}</a></li>
							@endif
							</ul>
						</li>
						<li class="dropdown">
							<a tabindex="0" data-toggle="dropdown" >{{ trans('messages.Employee') }} <span class="caret"></span></a>
							<ul class="dropdown-menu">
								<!-- <li><a tabindex="-1" href="{{ url('/attendance/dashboard') }}">{{ trans('messages.Attendance') }}</a></li> -->
								<li><a tabindex="-1" style="opacity:0.5;cursor:not-allowed;">{{ trans('messages.Attendance') }}</a></li>
								<li><a tabindex="-1" href="{{ url('/calendar/dashboard') }}">{{ trans('messages.Calendar') }}</a></li>
								<li><a tabindex="-1" href="{{ url('/document/dashboard') }}">{{ trans('messages.Document') }}</a></li>
								<li><a tabindex="-1" href="{{ url('/expense/dashboard') }}">{{ trans('messages.Expense') }}</a></li>
								<li><a tabindex="-1" href="{{ url('/interaction/dashboard') }}">{{ trans('messages.Interaction') }}</a></li>
								<li><a tabindex="-1" href="{{ url('/email/list') }}">{{ trans('messages.Email') }}</a></li>
								<li><a tabindex="-1" href="{{ url('/file/upload') }}">{{ trans('messages.File upload') }}</a></li>
							</ul>
						</li>
					@if (Auth::user()->can('sy-list'))
						<li class="dropdown">
							<a tabindex="0" data-toggle="dropdown" >{{ trans('messages.System') }} <span class="caret"></span></a>
							<ul class="dropdown-menu">
								<li><a tabindex="-1" href="{{ url('/system/parameter') }}">{{ trans('messages.Parameter') }}</a></li>
							@if (Auth::user()->can('role-list'))
								<li><a tabindex="-1" href="{{ url('/roles') }}">{{ trans('messages.Role') }}</a></li>
							@endif
							@if (Auth::user()->can('user-list'))
								<li><a tabindex="-1" href="{{ url('/useraccount') }}">{{ trans('messages.User') }}</a></li>
							@endif
								<li><a tabindex="-1" href="{{ url('/system/location') }}">{{ trans('forms.Location') }}</a></li>
								<li><a tabindex="-1" href="{{ url('/system/purchase-approval-rule') }}">{{ trans('forms.Purchase approval rule') }}</a></li>
								<li><a tabindex="-1" href="{{ url('/system/sales-approval-rule') }}">{{ trans('forms.Sales approval rule') }}</a></li>
								<li><a tabindex="-1" href="{{ url('/system/expense-approval-rule') }}">{{ trans('forms.Expense approval rule') }}</a></li>
								<li><a tabindex="-1" href="{{ url('/system/sales-restriction') }}">{{ trans('forms.Sales restriction') }}</a></li>
								<li><a tabindex="-1" href="{{ url('/system/interaction-user') }}">{{ trans('forms.Interaction user rule') }}</a></li>
								<li><a tabindex="-1" href="{{ url('/system/company/update') }}">{{ trans('forms.Update company information') }}</a></li>
							@if (Auth::user()->can('db-backup'))
								<li><a tabindex="-1" href="{{ url('/system/backup') }}">{{ trans('messages.Backup') }}</a></li>
							@endif
							</ul>
						</li>
					@endif
					</ul>

					<!-- Right Side Of Navbar -->
					<ul class="nav navbar-nav navbar-right">

						<!-- real-time messaging -->
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" aria-haspopup="true" onclick="notificationRead();" v-pre>
								<i id="notification-count-icon" class="fa fa-2x fa-wifi" new-count="0" aria-hidden="true"></i>
								<span class="caret"></span>
							</a>
							<ul id="notification-list" class="dropdown-menu">
							</ul>
						</li>

						<!-- Authentication Links -->
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" aria-haspopup="true" v-pre>
								{{ Auth::user()->name }} <span class="caret"></span>
							</a>

							<ul class="dropdown-menu">
								<li><a href="{{ url('/profile/update') }}"><i class="fa fa-user"></i>&emsp;{{ trans('messages.Update profile') }}</a></li>
								<li><a href="{{ url('/password/update') }}"><i class="fa fa-key"></i>&emsp;{{ trans('messages.Change password') }}</a></li>
								<li><hr></li>
								<li><i style="padding-left:1.5em;" class="fa fa-language"></i>&emsp;{{ trans('messages.Language') }}</li>
								<li><a href="{{ url('/language/en') }}">&emsp;&emsp;English</a></li>
								<li><a href="{{ url('/language/zh') }}">&emsp;&emsp;简体中文</a></li>
								<li><a href="{{ url('/language/zht') }}">&emsp;&emsp;繁體中文</a></li>
								<li><a href="{{ url('/language/es') }}">&emsp;&emsp;Espa&ntilde;ol</a></li>
								<li><hr></li>
								<li><a href="http://support.createmyorder.com" target="_blank"><i class="fa fa-life-ring"></i>&emsp;{{ trans('messages.User support') }}</a></li>
								<li><hr></li>
								<li>
									<a id="logout-link" href="{{ url('/logout') }}" onclick="loggingOut()">
										<i class="fa fa-sign-out"></i>&emsp;{{ trans('messages.Logout') }}
									</a>
									 <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
										{{ csrf_field() }}
									 </form>
								</li>
							</ul>
						</li>
					</ul>
				</div>
			</div>
		</nav>
	@endif

	<div class="col-md-8 col-md-offset-2">
		<div class="flash-message">
	@foreach (['danger', 'warning', 'success', 'info'] as $msg)
		@if(Session::has('alert-' . $msg))
			<p class="alert alert-{{ $msg }}">{!! Session::get('alert-' . $msg) !!} <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a></p>
		@endif
	@endforeach
		</div>
	</div> <!-- end .flash-message -->

	@yield('content')

	<!-- Spinning wheel animation for Ajax operation -->
	<div class="ajax-processing hidden">
		<i class="fa fa-spinner fa-spin fa-5x fa-fw"></i>
	</div>

	<!-- just some spacing and the return-to-top at the bottom of the page -->
	<br><br><br><br><a href='#' class='back-to-top'><img src="{{ asset('images/to-top.png') }}" height='40' width='40'></img></a>
</div>

	<!-- JavaScripts -->
@if (!App::environment('local'))
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
@else
	<script src="{{ asset('external/ajax/libs/jquery/2.2.4/jquery.min.js') }}" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
@endif

@if (!App::environment('local'))
	<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.4.1/js/bootstrap.min.js" integrity="sha256-nuL8/2cJ5NDSSwnKD8VqreErSWHtnEP9E7AySL+1ev4=" crossorigin="anonymous"></script>
@else
	<script src="{{ asset('external/ajax/libs/twitter-bootstrap/3.4.1/js/bootstrap.min.js') }}" integrity="sha256-nuL8/2cJ5NDSSwnKD8VqreErSWHtnEP9E7AySL+1ev4=" crossorigin="anonymous"></script>
@endif

@if (!App::environment('local'))
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
@else
	<script type="text/javascript" src="{{ asset('external/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js') }}"></script>
@endif

@if (!App::environment('local'))
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.12/js/bootstrap-select.min.js"></script>
@else
	<script type="text/javascript" src="{{ asset('external/ajax/libs/bootstrap-select/1.13.12/js/bootstrap-select.min.js') }}"></script>
@endif

@if (!App::environment('local'))
	<script type="text/javascript" src="https://cdn.datatables.net/1.10.23/js/jquery.dataTables.min.js"></script>
@else
	<script type="text/javascript" src="{{ asset('external/datatables/1.10.23/js/jquery.dataTables.min.js') }}"></script>
@endif

@if (!App::environment('local'))
	<script type="text/javascript" src="https://cdn.datatables.net/1.10.23/js/dataTables.bootstrap.min.js"></script>
@else
	<script type="text/javascript" src="{{ asset('external/datatables/1.10.23/js/dataTables.bootstrap.min.js') }}"></script>
@endif

@if (!App::environment('local'))
	<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.4/js/dataTables.buttons.min.js"></script>
@else
	<script type="text/javascript" src="{{ asset('external/datatable-buttons/1.6.4/js/dataTables.buttons.min.js') }}"></script>
@endif

@if (!App::environment('local'))
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.6.11/vue.min.js"></script>
@else
	<script type="text/javascript" src="{{ asset('external/ajax/libs/vue/2.6.11/js/vue.min.js') }}"></script>
@endif

	<script>
		function loggingOut() {
			event.preventDefault();
			localStorage.removeItem("new-notification-count-{{ Session::getId() }}");
			localStorage.removeItem("notifications-{{ Session::getId() }}");
		 	document.getElementById('logout-form').submit();
		}

		function notificationRead() {
			localStorage.setItem("new-notification-count-{{ Session::getId() }}", 0);
			$('i#notification-count-icon').removeClass('fa-stacking');
			$('i#notification-count-icon').attr('new-count', '0');
		}

		jQuery(document).ready(function() {
			var offset = 220;
			var duration = 500;
			jQuery(window).scroll(function() {
				if (jQuery(this).scrollTop() > offset) {
					jQuery('.back-to-top').fadeIn(duration);
				} else {
					jQuery('.back-to-top').fadeOut(duration);
				}
			});
			jQuery('.back-to-top').click(function(event) {
				event.preventDefault();
				jQuery('html, body').animate({scrollTop: 0}, duration);
				return false;
			});
			var notifications = localStorage.getItem("notifications-{{ Session::getId() }}");
			if (notifications != null) {
				jQuery('ul#notification-list').prepend(notifications);
			} else {
				jQuery('ul#notification-list').prepend("<li id=\"empty\"><p class=\"text-center\">{{ trans('messages.No message')}}</p></li>");
			}
			var newNotificationCount = localStorage.getItem("new-notification-count-{{ Session::getId() }}");
			if ((newNotificationCount != null) && (newNotificationCount != 0)) {
				jQuery('#notification-count-icon').attr("new-count", newNotificationCount);
				jQuery('#notification-count-icon').addClass("fa-stacking");
			}
		});
	</script>

	<!-- Scripts -->
	<!-- <script src="{{ elixir('js/app.js') }}"></script> -->

	@yield('post-content')

@if (env('PUSHER_ENABLE'))
@if (!App::environment('local'))
	<script src="https://js.pusher.com/5.0.2/pusher.min.js"></script>
@else
	<script src="{{ asset('external/pusher/5.0.2/pusher.min.js') }}"></script>
@endif
  <script>

    // Enable pusher logging - don't include this in production
    //Pusher.logToConsole = true;

    var pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
      cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
      forceTLS: true
    });

    var channel = pusher.subscribe('{{ env('COMPANY_DOMAIN') }}');

		var channelHandler = function(data) {
			$('#notification-list li#empty').detach();
			var now = new Date();
			var count = parseInt($('#notification-count-icon').attr("new-count"));
			var content = "<li><i style=\"padding-left:10px;padding-right:10px;\" class=\"fa " + this.icon + "\" aria-hidden=\"true\"></i>" +
					"<span>" + now.toLocaleString(undefined, { day: 'numeric', month: 'numeric', hour: '2-digit',	minute: '2-digit' }) +
					"</span>" + "<br><p style=\"padding-left:10px;\">" + data + "</p></li>";
			$('#notification-count-icon').attr("new-count", count + 1);
			$('#notification-count-icon').addClass("fa-stacking");
      $('#notification-list').prepend(content);
			localStorage.setItem("new-notification-count-{{ Session::getId() }}", count + 1);
			localStorage.setItem("notifications-{{ Session::getId() }}",  content + (localStorage.getItem("notifications-{{ Session::getId() }}") || ""));
		};

    channel.bind('notification', channelHandler, { icon : 'fa-info-circle'});

		var channel = pusher.subscribe('system');

    channel.bind('notification', channelHandler, { icon : 'fa-cogs' });

		channel.bind('action', function(data) {
			var action = data;
			if (action.match(/^logout$/i)) {
				$('a#logout-link').click();
			}
    });

  </script>
@endif
</body>
</html>
