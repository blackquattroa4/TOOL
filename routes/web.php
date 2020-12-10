<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Auth::routes();
//
// Route::get('/home', 'HomeController@index')->name('home');

Route::group(['domain' => env('COMPANY_DOMAIN')], function() {

	Route::get('', 'HomeController@index');

	Route::auth();

	Route::get('home', 'HomeController@index');

	Route::group(['middleware' => ['auth']], function() {

		// -------------- account
		// profile management
		Route::group(['prefix' => 'password'], function() {
			Route::group(['prefix' => 'update'], function() {
				Route::get('', ['uses' => 'UserController@changePassword']);
				Route::post('', ['uses' => 'UserController@changePasswordUpdate']);
			});
		});

		Route::group(['prefix' => 'profile'], function() {
			Route::group(['prefix' => 'update'], function() {
				Route::get('', ['uses' => 'UserController@update']);
				Route::post('', ['uses' => 'UserController@updatePost']);
			});
		});

		Route::group(['prefix' => 'language'], function() {
			Route::get('{lang}', ['uses' => 'LanguageController@index']);
		});

		// -------------- finance related
		Route::group(['prefix' => 'finance'], function() {
			Route::get('dashboard', ['uses' => 'FinanceController@index', 'middleware' => ['permission:ap-list|ar-list|rar-list|rap-list|ex-list']]);
			Route::group(['prefix' => 'statement'], function() {
				Route::get('', ['uses' => 'FinanceController@statement', 'middleware' => ['permission:ap-list|ar-list|rar-list|rap-list|ex-list']]);
				Route::get('print', ['uses' => 'FinanceController@printStatement', 'middleware' => ['permission:ap-list|ar-list|rar-list|rap-list|ex-list']]);
			});
			Route::get('inventory', ['uses' => 'FinanceController@inventory', 'middleware' => ['permission:ap-list|ar-list|rar-list|rap-list|ex-list']]);
			Route::group(['prefix' => 'outstanding'], function() {
				Route::get('{type}', ['uses' => 'FinanceController@outstandingTransactable', 'middleware' => ['permission:ap-list|ar-list|rar-list|rap-list|ex-list']]);
				Route::get('{type}/print', ['uses' => 'FinanceController@printOutstandingTransactable', 'middleware' => ['permission:ap-list|ar-list|rar-list|rap-list|ex-list']]);
			});
			Route::group(['prefix' => 'adjust-inventory'], function() {
				Route::get('', ['uses' => 'FinanceController@adjustInventory', 'middleware' => ['permission:ar-process|ap-process|rar-process|rap-process']]);
				Route::post('', ['uses' => 'FinanceController@adjustInventoryPost', 'middleware' => ['permission:ar-process|ap-process|rar-process|rap-process']]);
			});
			Route::group(['prefix' => 'loan'], function() {
				Route::get('create', ['uses' => 'LoanController@create', 'middleware' => ['permission:ar-process|ap-process']]);
				Route::post('create', ['uses' => 'LoanController@createPost', 'middleware' => ['permission:ar-process|ap-process']]);
				Route::get('update/{id}', ['uses' => 'LoanController@update', 'middleware' => ['permission:ar-process|ap-process']]);
				Route::post('update/{id}', ['uses' => 'LoanController@updatePost', 'middleware' => ['permission:ar-process|ap-process']]);
				Route::get('view/{id}', ['uses' => 'LoanController@view', 'middleware' => ['permission:ar-list|ap-list|ar-process|ap-process']]);
				Route::get('baddebt/{id}', ['uses' => 'LoanController@badDebt', 'middleware' => ['permission:ar-process|ap-process']]);
				Route::post('baddebt/{id}', ['uses' => 'LoanController@badDebtPost', 'middleware' => ['permission:ar-process|ap-process']]);
			});
		});

		Route::group(['prefix' => 'ap'], function() {
			Route::group(['prefix' => 'create'], function() {
				Route::get('{id}', ['uses' => 'PayableController@create', 'middleware' => ['permission:ap-create']]);
				Route::post('{id}', ['uses' => 'PayableController@createPost', 'middleware' => ['permission:ap-create']]);
			});
			Route::group(['prefix' => 'view'], function() {
				Route::get('{id}', ['uses' => 'PayableController@view', 'middleware' => ['permission:ap-view']]);
				Route::post('{id}', ['uses' => 'PayableController@printInvoice', 'middleware' => ['permission:ap-view']]);
			});
			// Route::group(['prefix' => 'update'], function() {
			// 	Route::get('{id}', ['uses' => 'PayableController@update', 'middleware' => ['permission:ap-edit']]);
			// 	Route::post('{id}', ['uses' => 'PayableController@updatePost', 'middleware' => ['permission:ap-edit']]);
			// });
			Route::group(['prefix' => 'void'], function() {
				Route::get('{id}', ['uses' => 'PayableController@void', 'middleware' => ['permission:ap-process']]);
				Route::post('{id}', ['uses' => 'PayableController@voidPost', 'middleware' => ['permission:ap-process']]);
			});
			Route::group(['prefix' => 'pay'], function() {
				Route::get('{id}', ['uses' => 'PayableController@issuePayment', 'middleware' => ['permission:ap-process']]);
				Route::post('{id}', ['uses' => 'PayableController@issuePaymentPost', 'middleware' => ['permission:ap-process']]);
			});
			Route::group(['prefix' => 'createcredit'], function() {
				Route::get('{id}', ['uses' => 'PayableController@createCredit', 'middleware' => ['permission:rap-create']]);
				Route::post('{id}', ['uses' => 'PayableController@createCreditPost', 'middleware' => ['permission:rap-create']]);
			});
			Route::group(['prefix' => 'viewcredit'], function() {
				Route::get('{id}', ['uses' => 'PayableController@viewCredit', 'middleware' => ['permission:rap-view']]);
				Route::post('{id}', ['uses' => 'PayableController@printCredit', 'middleware' => ['permission:rap-view']]);
			});
			// Route::group(['prefix' => 'updatecredit'], function() {
			// 	Route::get('{id}', ['uses' => 'PayableController@updateCredit', 'middleware' => ['permission:rap-edit']]);
			// 	Route::post('{id}', ['uses' => 'PayableController@updateCreditPost', 'middleware' => ['permission:rap-edit']]);
			// });
			Route::group(['prefix' => 'voidcredit'], function() {
				Route::get('{id}', ['uses' => 'PayableController@voidCredit', 'middleware' => ['permission:rap-process']]);
				Route::post('{id}', ['uses' => 'PayableController@voidCreditPost', 'middleware' => ['permission:rap-process']]);
			});
		});

		Route::group(['prefix' => 'charge'], function() {
			Route::group(['prefix' => 'create'], function() {
				Route::group(['prefix' => '{sid}'], function() {
					Route::get('', ['uses' => 'ChargeController@create', 'middleware' => ['permission:ex-create']]);
					Route::post('', ['uses' => 'ChargeController@createPost', 'middleware' => ['permission:ex-create']]);
					// Route::post('ajax', ['uses' => 'ChargeController@createPostAjax', 'middleware' => ['permission:ex-create']]);
				});
			});
			Route::get('view/{id}', ['uses' => 'ChargeController@view', 'middleware' => ['permission:ex-view']]);
			// Route::get('{id}/ajax', 'ChargeController@loadAjax');
			Route::group(['prefix' => 'update'], function() {
				Route::group(['prefix' => '{id}'], function() {
					Route::get('', ['uses' => 'ChargeController@update', 'middleware' => ['permission:ex-edit']]);
					Route::post('', ['uses' => 'ChargeController@updatePost', 'middleware' => ['permission:ex-edit']]);
					// Route::post('ajax', ['uses' => 'ChargeController@updatePostAjax', 'middleware' => ['permission:ex-edit']]);
				});
			});
			Route::group(['prefix' => 'retract'], function() {
				Route::group(['prefix' => '{id}'], function() {
					Route::get('', ['uses' => 'ChargeController@retract', 'middleware' => ['permission:ex-edit']]);
					Route::post('', ['uses' => 'ChargeController@retractPost', 'middleware' => ['permission:ex-edit']]);
					// Route::post('ajax', ['uses' => 'ChargeController@retractPostAjax', 'middleware' => ['permission:ex-edit']]);
				});
			});
			Route::group(['prefix' => 'submit'], function() {
				Route::group(['prefix' => '{id}'], function() {
					Route::get('', ['uses' => 'ChargeController@submit', 'middleware' => ['permission:ex-create|ex-edit']]);
					Route::post('', ['uses' => 'ChargeController@submitPost', 'middleware' => ['permission:ex-create|ex-edit']]);
					// Route::post('ajax', ['uses' => 'ChargeController@submitPostAjax', 'middleware' => ['permission:ex-create|ex-edit']]);
				});
			});
			Route::group(['prefix' => 'approve'], function() {
				Route::group(['prefix' => '{id}'], function() {
					Route::get('', ['uses' => 'ChargeController@approve', 'middleware' => ['permission:ex-view']]);
					Route::post('', ['uses' => 'ChargeController@approvePost', 'middleware' => ['permission:ex-view']]);
					// Route::post('ajax', ['uses' => 'ChargeController@approvePostAjax', 'middleware' => ['permission:ex-view']]);
				});
			});
			Route::group(['prefix' => 'process'], function() {
				Route::group(['prefix' => '{id}'], function() {
					Route::get('', ['uses' => 'ChargeController@process', 'middleware' => ['permission:ex-process']]);
					Route::post('', ['uses' => 'ChargeController@processPost', 'middleware' => ['permission:ex-process']]);
					// Route::post('ajax', ['uses' => 'ChargeController@processPostAjax', 'middleware' => ['permission:ex-process']]);
				});
			});
			Route::get('recurring/{id}', ['uses' => 'ChargeController@recurring', 'middleware' => ['permission:ex-create']]);
			Route::group(['prefix' => 'manage-recurring'], function() {
				Route::get('', ['uses' => 'ChargeController@manageRecurring', 'middleware' => ['permission:ex-create']]);
				Route::post('', ['uses' => 'ChargeController@manageRecurringPost', 'middleware' => ['permission:ex-create']]);
			});
		});

		Route::group(['prefix' => 'ar'], function() {
			Route::group(['prefix' => 'create'], function() {
				Route::get('{id}', ['uses' => 'ReceivableController@create', 'middleware' => ['permission:ar-create']]);
				Route::post('{id}', ['uses' => 'ReceivableController@createPost', 'middleware' => ['permission:ar-create']]);
			});
			Route::group(['prefix' => 'view'], function() {
				Route::get('{id}', ['uses' => 'ReceivableController@view', 'middleware' => ['permission:ar-view']]);
				Route::post('{id}', ['uses' => 'ReceivableController@printInvoice', 'middleware' => ['permission:ar-view']]);
			});
			// Route::group(['prefix' => 'update'], function() {
			// 	Route::get('{id}', ['uses' => 'ReceivableController@update', 'middleware' => ['permission:ar-edit']]);
			// 	Route::post('{id}', ['uses' => 'ReceivableController@updatePost', 'middleware' => ['permission:ar-edit']]);
			// });
			Route::group(['prefix' => 'void'], function() {
				Route::get('{id}', ['uses' => 'ReceivableController@void', 'middleware' => ['permission:ar-process']]);
				Route::post('{id}', ['uses' => 'ReceivableController@voidPost', 'middleware' => ['permission:ar-process']]);
			});
			Route::group(['prefix' => 'receive'], function() {
				Route::get('{id}', ['uses' => 'ReceivableController@receivePayment', 'middleware' => ['permission:ar-process']]);
				Route::post('{id}', ['uses' => 'ReceivableController@receivePaymentPost', 'middleware' => ['permission:ar-process']]);
			});
			Route::group(['prefix' => 'createcredit'], function() {
				Route::get('{id}', ['uses' => 'ReceivableController@createCredit', 'middleware' => ['permission:rar-create']]);
				Route::post('{id}', ['uses' => 'ReceivableController@createCreditPost', 'middleware' => ['permission:rar-create']]);
			});
			Route::group(['prefix' => 'viewcredit'], function() {
				Route::get('{id}', ['uses' => 'ReceivableController@viewCredit', 'middleware' => ['permission:rar-view']]);
				Route::post('{id}', ['uses' => 'ReceivableController@printCredit', 'middleware' => ['permission:rar-view']]);
			});
			// Route::group(['prefix' => 'updatecredit'], function() {
			// 	Route::get('{id}', ['uses' => 'ReceivableController@updateCredit', 'middleware' => ['permission:rar-edit']]);
			// 	Route::post('{id}', ['uses' => 'ReceivableController@updateCreditPost', 'middleware' => ['permission:rar-edit']]);
			// });
			Route::group(['prefix' => 'voidcredit'], function() {
				Route::get('{id}', ['uses' => 'ReceivableController@voidCredit', 'middleware' => ['permission:rar-process']]);
				Route::post('{id}', ['uses' => 'ReceivableController@voidCreditPost', 'middleware' => ['permission:rar-process']]);
			});
		});

		Route::group(['prefix' => 'taccount'], function() {
			Route::group(['prefix' => 'create'], function() {
				Route::get('', ['uses' => 'ChartAccountController@create', 'middleware' => ['permission:ar-process|rar-process|ap-process|rap-process']]);
				Route::post('', ['uses' => 'ChartAccountController@createPost', 'middleware' => ['permission:ar-process|rar-process|ap-process|rap-process']]);
			});
			Route::group(['prefix' => 'edit'], function() {
				Route::get('{id}', ['uses' => 'ChartAccountController@edit', 'middleware' => ['permission:ar-process|rar-process|ap-process|rap-process']]);
				Route::post('{id}', ['uses' => 'ChartAccountController@editPost', 'middleware' => ['permission:ar-process|rar-process|ap-process|rap-process']]);
			});
			Route::get('view/{id}', ['uses' => 'ChartAccountController@view', 'middleware' => ['permission:ar-view|rar-view|ap-view|rap-view']]);
			Route::group(['prefix' => 'reconcile'], function() {
				Route::get('{id}', ['uses' => 'ChartAccountController@reconcile', 'middleware' => ['permission:ar-process|rar-process|ap-process|rap-process']]);
				Route::post('{id}', ['uses' => 'ChartAccountController@reconcilePost', 'middleware' => ['permission:ar-process|rar-process|ap-process|rap-process']]);
			});
		});

		// -------------- accounting related
		Route::group(['prefix' => 'accounting'], function() {
			Route::get('dashboard', ['uses' => 'AccountingController@index', 'middleware' => ['permission:ap-list|ar-list|rar-list|rap-list|ex-list']]);
			Route::group(['prefix' => 'adjust-consignment'], function() {
				Route::get('', ['uses' => 'AccountingController@adjustConsignment', 'middleware' => ['permission:po-process|pr-process']]);
				Route::post('', ['uses' => 'AccountingController@adjustConsignmentPost', 'middleware' => ['permission:po-process|pr-process']]);
			});
			Route::group(['prefix' => 'tradable'], function() {
				Route::group(['prefix' => 'transactions'], function() {
					Route::get('{location}/{entity}/{sku}', ['uses' => 'AccountingController@viewTradableTransactions', 'middleware' => ['permission:ap-list|ar-list|rar-list|rap-list|ex-list']]);
				});
			});
			// inventory
			Route::group(['prefix' => 'inventory'], function() {
				Route::get('print', ['uses' => 'AccountingController@printInventory', 'middleware' => ['permission:ap-list']]);
			});
			// aging
			Route::group(['prefix' => 'aging'], function() {
				Route::get('', ['uses' => 'AccountingController@getInventoryAging', 'middleware' => ['permission:ap-list']]);
			});
		});

		// -------------- HR management
		Route::group(['prefix' => 'hr'], function() {
			Route::get('dashboard', ['uses' => 'HRController@index', 'middleware' => ['permission:hr-list']]);
			Route::get('view/{id}', ['uses' => 'HRController@view', 'middleware' => ['permission:hr-view']]);
			Route::group(['prefix' => 'update'], function() {
				Route::get('{id}', ['uses' => 'HRController@edit', 'middleware' => ['permission:hr-edit']]);
				Route::post('{id}', ['uses' => 'HRController@editPost', 'middleware' => ['permission:hr-edit']]);
			});
		});

		// -------------- Product related
		Route::group(['prefix' => 'product'], function() {
			Route::get('dashboard', ['uses' => 'ProductController@index', 'middleware' => ['permission:pd-list|pd-create|pd-edit']]);
			Route::group(['prefix' => 'createproduct'], function() {
				Route::get('', ['uses' => 'ProductController@createProduct', 'middleware' => ['permission:pd-create']]);
				Route::post('', ['uses' => 'ProductController@createProductPost', 'middleware' => ['permission:pd-create']]);
			});
			Route::get('viewproduct/{id}', ['uses' => 'ProductController@viewProduct', 'middleware' => ['permission:pd-view']]);
			Route::group(['prefix' => 'updateproduct'], function() {
				Route::get('{id}', ['uses' => 'ProductController@updateProduct', 'middleware' => ['permission:pd-edit']]);
				Route::post('{id}', ['uses' => 'ProductController@updateProductPost', 'middleware' => ['permission:pd-edit']]);
			});
			Route::group(['prefix' => 'createnotice'], function() {
				Route::get('', ['uses' => 'ProductController@createProductNotice', 'middleware' => ['permission:pd-edit']]);
				Route::post('', ['uses' => 'ProductController@createProductNoticePost', 'middleware' => ['permission:pd-edit']]);
			});
			Route::group(['prefix' => 'updatenotice'], function() {
				Route::get('{id}', ['uses' => 'ProductController@updateProductNotice', 'middleware' => ['permission:pd-edit']]);
				Route::post('{id}', ['uses' => 'ProductController@updateProductNoticePost', 'middleware' => ['permission:pd-edit']]);
			});
			Route::group(['prefix' => 'createfaq'], function() {
				Route::get('', ['uses' => 'ProductController@createProductFaq', 'middleware' => ['permission:pd-edit']]);
				Route::post('', ['uses' => 'ProductController@createProductFaqPost', 'middleware' => ['permission:pd-edit']]);
			});
			Route::group(['prefix' => 'updatefaq'], function() {
				Route::get('{id}', ['uses' => 'ProductController@updateProductFaq', 'middleware' => ['permission:pd-edit']]);
				Route::post('{id}', ['uses' => 'ProductController@updateProductFaqPost', 'middleware' => ['permission:pd-edit']]);
			});
		});

		// -------------- Vendor related
		Route::group(['prefix' => 'vrm'], function() {
			Route::get('dashboard', ['uses' => 'VRMController@index', 'middleware' => ['permission:supplier-list|pq-list|po-list|pr-list']]);
			Route::group(['prefix' => 'createsupplier'], function() {
				Route::get('', ['uses' => 'SupplierController@createSupplier', 'middleware' => ['permission:supplier-create']]);
				Route::post('', ['uses' => 'SupplierController@createSupplierPost', 'middleware' => ['permission:supplier-create']]);
			});
			Route::get('viewsupplier/{id}', ['uses' => 'SupplierController@viewSupplier', 'middleware' => ['permission:supplier-report']]);
			Route::group(['prefix' => 'updatesupplier'], function() {
				Route::get('{id}', ['uses' => 'SupplierController@updateSupplier', 'middleware' => ['permission:supplier-edit']]);
				Route::post('{id}', ['uses' => 'SupplierController@updateSupplierPost', 'middleware' => ['permission:supplier-edit']]);
			});
			Route::group(['prefix' => 'createquote'], function() {
				Route::get('{supplierId}', ['uses' => 'PurchaseQuoteController@createQuote', 'middleware' => ['permission:pq-create']]);
				Route::post('{supplierId}', ['uses' => 'PurchaseQuoteController@createQuotePost', 'middleware' => ['permission:pq-create']]);
			});
			Route::group(['prefix' => 'viewquote'], function() {
				Route::get('{id}', ['uses' => 'PurchaseQuoteController@viewQuote', 'middleware' => ['permission:pq-view']]);
				Route::post('{id}', ['uses' => 'PurchaseQuoteController@printQuote', 'middleware' => ['permission:pq-view']]);
			});
			Route::group(['prefix' => 'updatequote'], function() {
				Route::get('{id}', ['uses' => 'PurchaseQuoteController@updateQuote', 'middleware' => ['permission:pq-edit']]);
				Route::post('{id}', ['uses' => 'PurchaseQuoteController@updateQuotePost', 'middleware' => ['permission:pq-edit']]);
			});
			Route::group(['prefix' => 'approvequote'], function() {
				Route::get('{id}', ['uses' => 'PurchaseQuoteController@approveQuote', 'middleware' => ['permission:pq-view']]);
				Route::post('{id}', ['uses' => 'PurchaseQuoteController@approveQuotePost', 'middleware' => ['permission:pq-view']]);
			});
			Route::group(['prefix' => 'createorder'], function() {
				Route::get('{supplierId}', ['uses' => 'PurchaseOrderController@createOrder', 'middleware' => ['permission:po-create']]);
				Route::post('{supplierId}', ['uses' => 'PurchaseOrderController@createOrderPost', 'middleware' => ['permission:po-create']]);
			});
			Route::group(['prefix' => 'updateorder'], function() {
				Route::get('{id}', ['uses' => 'PurchaseOrderController@updateOrder', 'middleware' => ['permission:po-edit']]);
				Route::post('{id}', ['uses' => 'PurchaseOrderController@updateOrderPost', 'middleware' => ['permission:po-edit']]);
			});
			Route::group(['prefix' => 'vieworder'], function() {
				Route::get('{id}', ['uses' => 'PurchaseOrderController@viewOrder', 'middleware' => ['permission:po-view']]);
				Route::post('{id}', ['uses' => 'PurchaseOrderController@printOrder', 'middleware' => ['permission:po-view']]);
			});
			Route::group(['prefix' => 'approveorder'], function() {
				Route::get('{id}', ['uses' => 'PurchaseOrderController@approveOrder', 'middleware' => ['permission:po-view']]);
				Route::post('{id}', ['uses' => 'PurchaseOrderController@approveOrderPost', 'middleware' => ['permission:po-view']]);
			});
			Route::group(['prefix' => 'processorder'], function() {
				Route::get('{id}', ['uses' => 'PurchaseOrderController@processOrder', 'middleware' => ['permission:po-process']]);
				Route::post('{id}', ['uses' => 'PurchaseOrderController@processOrderPost', 'middleware' => ['permission:po-process']]);
			});
			Route::group(['prefix' => 'createreturn'], function() {
				Route::get('{supplierId}', ['uses' => 'PurchaseReturnController@createReturn', 'middleware' => ['permission:pr-create']]);
				Route::post('{supplierId}', ['uses' => 'PurchaseReturnController@createReturnPost', 'middleware' => ['permission:pr-create']]);
			});
			Route::group(['prefix' => 'updatereturn'], function() {
				Route::get('{id}', ['uses' => 'PurchaseReturnController@updateReturn', 'middleware' => ['permission:pr-edit']]);
				Route::post('{id}', ['uses' => 'PurchaseReturnController@updateReturnPost', 'middleware' => ['permission:pr-edit']]);
			});
			Route::group(['prefix' => 'viewreturn'], function() {
				Route::get('{id}', ['uses' => 'PurchaseReturnController@viewReturn', 'middleware' => ['permission:pr-view']]);
				Route::post('{id}', ['uses' => 'PurchaseReturnController@printReturn', 'middleware' => ['permission:pr-view']]);
			});
			Route::group(['prefix' => 'approvereturn'], function() {
				Route::get('{id}', ['uses' => 'PurchaseReturnController@approveReturn', 'middleware' => ['permission:pr-view']]);
				Route::post('{id}', ['uses' => 'PurchaseReturnController@approveReturnPost', 'middleware' => ['permission:pr-view']]);
			});
			Route::group(['prefix' => 'processreturn'], function() {
				Route::get('{id}', ['uses' => 'PurchaseReturnController@processReturn', 'middleware' => ['permission:pr-process']]);
				Route::post('{id}', ['uses' => 'PurchaseReturnController@processReturnPost', 'middleware' => ['permission:pr-process']]);
			});
			Route::group(['prefix' => 'inventory-alert'], function() {
				Route::get('', ['uses' => 'InventoryAlertController@index', 'middleware' => ['permission:pq-create|po-create|pr-create|pq-edit|po-edit|pr-edit']]);
				Route::post('', ['uses' => 'InventoryAlertController@indexPost', 'middleware' => ['permission:pq-create|po-create|pr-create|pq-edit|po-edit|pr-edit']]);
			});
		});

		// -------------- Customer related
		Route::group(['prefix' => 'crm'], function() {
			Route::get('dashboard', ['uses' => 'CRMController@index', 'middleware' => ['permission:customer-list|sq-list|so-list|sr-list']]);
			Route::group(['prefix' => 'createcustomer'], function() {
				Route::get('', ['uses' => 'CustomerController@createCustomer', 'middleware' => ['permission:customer-create']]);
				Route::post('', ['uses' => 'CustomerController@createCustomerPost', 'middleware' => ['permission:customer-create']]);
			});
			Route::get('viewcustomer/{id}', ['uses' => 'CustomerController@viewCustomer', 'middleware' => ['permission:customer-report']]);
			Route::group(['prefix' => 'updatecustomer'], function() {
				Route::get('{id}', ['uses' => 'CustomerController@updateCustomer', 'middleware' => ['permission:customer-edit']]);
				Route::post('{id}', ['uses' => 'CustomerController@updateCustomerPost', 'middleware' => ['permission:customer-edit']]);
			});
			Route::group(['prefix' => 'createquote'], function() {
				Route::get('{customerId}', ['uses' => 'SalesQuoteController@createQuote', 'middleware' => ['permission:sq-create']]);
				Route::post('{customerId}', ['uses' => 'SalesQuoteController@createQuotePost', 'middleware' => ['permission:sq-create']]);
			});
			Route::group(['prefix' => 'viewquote'], function() {
				Route::get('{id}', ['uses' => 'SalesQuoteController@viewQuote', 'middleware' => ['permission:sq-view']]);
				Route::post('{id}', ['uses' => 'SalesQuoteController@printQuote', 'middleware' => ['permission:sq-view']]);
			});
			Route::group(['prefix' => 'updatequote'], function() {
				Route::get('{id}', ['uses' => 'SalesQuoteController@updateQuote', 'middleware' => ['permission:sq-edit']]);
				Route::post('{id}', ['uses' => 'SalesQuoteController@updateQuotePost', 'middleware' => ['permission:sq-edit']]);
			});
			Route::group(['prefix' => 'approvequote'], function() {
				Route::get('{id}', ['uses' => 'SalesQuoteController@approveQuote', 'middleware' => ['permission:sq-view']]);
				Route::post('{id}', ['uses' => 'SalesQuoteController@approveQuotePost', 'middleware' => ['permission:sq-view']]);
			});
			Route::group(['prefix' => 'createorder'], function() {
				Route::get('{customerId}', ['uses' => 'SalesOrderController@createOrder', 'middleware' => ['permission:so-create']]);
				Route::post('{customerId}', ['uses' => 'SalesOrderController@createOrderPost', 'middleware' => ['permission:so-create']]);
			});
			Route::group(['prefix' => 'updateorder'], function() {
				Route::get('{id}', ['uses' => 'SalesOrderController@updateOrder', 'middleware' => ['permission:so-edit']]);
				Route::post('{id}', ['uses' => 'SalesOrderController@updateOrderPost', 'middleware' => ['permission:so-edit']]);
			});
			Route::group(['prefix' => 'vieworder'], function() {
				Route::get('{id}', ['uses' => 'SalesOrderController@viewOrder', 'middleware' => ['permission:so-view']]);
				Route::post('{id}', ['uses' => 'SalesOrderController@printOrder', 'middleware' => ['permission:so-view']]);
			});
			Route::group(['prefix' => 'approveorder'], function() {
				Route::get('{id}', ['uses' => 'SalesOrderController@approveOrder', 'middleware' => ['permission:so-view']]);
				Route::post('{id}', ['uses' => 'SalesOrderController@approveOrderPost', 'middleware' => ['permission:so-view']]);
			});
			Route::group(['prefix' => 'processorder'], function() {
				Route::get('{id}', ['uses' => 'SalesOrderController@processOrder', 'middleware' => ['permission:so-process']]);
				Route::post('{id}', ['uses' => 'SalesOrderController@processOrderPost', 'middleware' => ['permission:so-process']]);
			});
			Route::group(['prefix' => 'createreturn'], function() {
				Route::get('{customerId}', ['uses' => 'SalesReturnController@createReturn', 'middleware' => ['permission:sr-create']]);
				Route::post('{customerId}', ['uses' => 'SalesReturnController@createReturnPost', 'middleware' => ['permission:sr-create']]);
			});
			Route::group(['prefix' => 'updatereturn'], function() {
				Route::get('{id}', ['uses' => 'SalesReturnController@updateReturn', 'middleware' => ['permission:sr-edit']]);
				Route::post('{id}', ['uses' => 'SalesReturnController@updateReturnPost', 'middleware' => ['permission:sr-edit']]);
			});
			Route::group(['prefix' => 'viewreturn'], function() {
				Route::get('{id}', ['uses' => 'SalesReturnController@viewReturn', 'middleware' => ['permission:sr-view']]);
				Route::post('{id}', ['uses' => 'SalesReturnController@printReturn', 'middleware' => ['permission:sr-view']]);
			});
			Route::group(['prefix' => 'approvereturn'], function() {
				Route::get('{id}', ['uses' => 'SalesReturnController@approveReturn', 'middleware' => ['permission:sr-view']]);
				Route::post('{id}', ['uses' => 'SalesReturnController@approveReturnPost', 'middleware' => ['permission:sr-view']]);
			});
			Route::group(['prefix' => 'processreturn'], function() {
				Route::get('{id}', ['uses' => 'SalesReturnController@processReturn', 'middleware' => ['permission:sr-process']]);
				Route::post('{id}', ['uses' => 'SalesReturnController@processReturnPost', 'middleware' => ['permission:sr-process']]);
			});
		});

		// -------------- Production related
		Route::group(['prefix' => 'production'], function() {
			Route::get('dashboard', 'ProductionController@index');
		});

		// -------------- Warehouse related
		Route::group(['prefix' => 'warehouse'], function() {
			Route::get('dashboard', ['uses' => 'WarehouseController@index', 'middleware' => ['permission:wo-list']]);
			Route::group(['prefix' => 'bins'], function() {
				Route::get('', ['uses' => 'WarehouseController@showBin', 'middleware' => ['permission:wo-list|wo-process']]);
				Route::post('', ['uses' => 'WarehouseController@updateBin', 'middleware' => ['permission:wo-list|wo-process']]);
			});
			Route::group(['prefix' => 'vieworder'], function() {
				Route::get('{id}', ['uses' => 'WarehouseOrderController@viewOrder', 'middleware' => ['permission:wo-view']]);
				Route::post('{id}', ['uses' => 'WarehouseOrderController@printOrder', 'middleware' => ['permission:wo-view']]);
			});
			Route::group(['prefix' => 'download-serial-pdf'], function() {
				Route::get('{id}', ['uses' => 'WarehouseOrderController@downloadOrderSerialPdf', 'middleware' => ['permission:wo-view']]);
			});
			Route::group(['prefix' => 'scanorder'], function() {
				Route::get('{id}', ['uses' => 'WarehouseOrderController@scanOrder', 'middleware' => ['permission:wo-process']]);
				Route::post('{id}', ['uses' => 'WarehouseOrderController@scanOrderPost', 'middleware' => ['permission:wo-process']]);
			});
			Route::group(['prefix' => 'createorder'], function() {
				Route::group(['prefix' => '{order_type}'], function() {
					Route::group(['prefix' => '{entity_id}'], function() {
						Route::get('', ['uses' => 'WarehouseOrderController@createOrder', 'middleware' => ['permission:wo-process']]);
						Route::post('', ['uses' => 'WarehouseOrderController@createOrderPost', 'middleware' => ['permission:wo-process']]);
					});
				});
			});
			Route::group(['prefix' => 'updatereceiveorder'], function() {
				Route::get('{id}', ['uses' => 'WarehouseOrderController@updateReceiveOrder', 'middleware' => ['permission:wo-process']]);
				Route::post('{id}', ['uses' => 'WarehouseOrderController@updateOrderPost', 'middleware' => ['permission:wo-process']]);
			});
			Route::group(['prefix' => 'updatedeliverorder'], function() {
				Route::get('{id}', ['uses' => 'WarehouseOrderController@updateDeliverOrder', 'middleware' => ['permission:wo-process']]);
				Route::post('{id}', ['uses' => 'WarehouseOrderController@updateOrderPost', 'middleware' => ['permission:wo-process']]);
			});
			Route::group(['prefix' => 'updatetransferorder'], function() {
				Route::get('{id}', ['uses' => 'WarehouseOrderController@updateTransferOrder', 'middleware' => ['permission:wo-process']]);
				Route::post('{id}', ['uses' => 'WarehouseOrderController@updateOrderPost', 'middleware' => ['permission:wo-process']]);
			});
			Route::group(['prefix' => 'transactions'], function() {
				Route::get('{location}/{sku}', ['uses' => 'WarehouseController@viewTransactions', 'middleware' => ['permission:wo-view']]);
			});
			Route::group(['prefix' => 'inventory'], function() {
				Route::get('print', ['uses' => 'WarehouseController@printInventory', 'middleware' => ['permission:wo-list']]);
			});

			// serial number
			Route::group(['prefix' => 'serial'], function() {
				Route::get('view', ['uses' => 'WarehouseOrderController@viewSingleSerialHistory', 'middleware' => ['permission:wo-view']]);
			});
			// aging inventory
			Route::group(['prefix' => 'aging'], function() {
				Route::get('', ['uses' => 'WarehouseController@getInventoryAging', 'middleware' => ['permission:wo-list|wo-view']]);
			});
			// label
			Route::group(['prefix' => 'label'], function() {
				Route::get('', ['uses' => 'WarehouseController@printLabel', 'middleware' => ['permission:wo-process']]);
				Route::post('upc', ['uses' => 'WarehouseController@printUpcLabel', 'middleware' => ['permission:wo-process']]);
				Route::post('serial', ['uses' => 'WarehouseController@printSerialLabel', 'middleware' => ['permission:wo-process']]);
				Route::post('carton', ['uses' => 'WarehouseController@printCartonLabel', 'middleware' => ['permission:wo-process']]);
				Route::post('bin', ['uses' => 'WarehouseController@printBinLabel', 'middleware' => ['permission:wo-process']]);
			});
		});

		// -------------- Tool related
		// file transfer management
		Route::group(['prefix' => 'file'], function() {
			Route::group(['prefix' => 'upload'], function() {
				Route::get('', 'FileController@fileUpload');
				Route::post('', 'FileController@fileUploadPost');
			});
		});

		// commission
		Route::group(['prefix' => 'commission'], function() {
			Route::get('wizard', 'CommissionController@commissionWizard');
		});

		// email index
		Route::group(['prefix' => 'email'], function() {
			Route::get('list', 'EmailController@index');
		});

		// attendance
		Route::group(['prefix' => 'attendance'], function() {
			Route::get('dashboard', 'AttendanceController@index');
		});

		// calendar
		Route::group(['prefix' => 'calendar'], function() {
			Route::get('dashboard', 'CalendarController@index');
			Route::get('dashboard?event={id}', 'CalendarController@index');
		});

		// staff expense
		Route::group(['prefix' => 'expense'], function() {
			Route::get('dashboard', 'StaffExpenseController@index');
		});

		// document
		Route::group(['prefix' => 'document'], function() {
			Route::get('dashboard', 'DocumentController@index');
			Route::group(['prefix' => 'create'], function() {
				Route::get('', 'DocumentController@create');
				Route::post('', 'DocumentController@createPost');
			});
			Route::get('view/{id}', 'DocumentController@viewDocument');
			Route::group(['prefix' => 'update'], function() {
				Route::get('{id}', 'DocumentController@update');
				Route::post('{id}', 'DocumentController@updatePost');
			});
			Route::post('delete/{id}', 'DocumentController@deletePost');
		});

		// -------------- System related
		Route::group(['prefix' => 'system'], function() {
			// parameter management
			Route::group(['prefix' => 'parameter'], function() {
				Route::get('', ['as'=>'parameter.list','uses'=>'ParameterController@index','middleware' => ['permission:sy-list']]);
				Route::group(['prefix' => 'create'], function() {
					Route::get('', ['as'=>'parameter.create','uses'=>'ParameterController@create','middleware' => ['permission:sy-edit']]);
					Route::post('', ['as'=>'parameter.createpost','uses'=>'ParameterController@createPost','middleware' => ['permission:sy-edit']]);
				});
				Route::group(['prefix' => 'edit'], function() {
					Route::get('{id}', ['as'=>'parameter.edit','uses'=>'ParameterController@edit','middleware' => ['permission:sy-edit']]);
					Route::post('{id}', ['as'=>'parameter.editpost','uses'=>'ParameterController@editPost','middleware' => ['permission:sy-edit']]);
				});
			});
			Route::group(['prefix' => 'company'], function() {
				Route::group(['prefix' => 'update'], function() {
					Route::get('', ['as'=>'company.update','uses'=>'CompanyController@update','middleware' => ['permission:sy-edit']]);
					Route::post('', ['as'=>'company.updatepost','uses'=>'CompanyController@updatePost','middleware' => ['permission:sy-edit']]);
				});
			});
			Route::group(['prefix' => 'location'], function() {
				Route::get('', ['as'=>'location.update','uses'=>'LocationController@update','middleware' => ['permission:sy-edit']]);
				Route::post('', ['as'=>'location.updatepost','uses'=>'LocationController@updatePost','middleware' => ['permission:sy-edit']]);
			});
			// approval rules
			Route::group(['prefix' => 'purchase-approval-rule'], function() {
				Route::get('', ['as'=>'purchase-approval-rule','uses'=>'PurchaseApprovalRuleController@index','middleware' => ['permission:sy-edit']]);
				Route::post('', ['as'=>'purchase-approval-rule-post','uses'=>'PurchaseApprovalRuleController@indexPost','middleware' => ['permission:sy-edit']]);
			});
			Route::group(['prefix' => 'sales-approval-rule'], function() {
				Route::get('', ['as'=>'sales-approval-rule','uses'=>'SalesApprovalRuleController@index','middleware' => ['permission:sy-edit']]);
				Route::post('', ['as'=>'sales-approval-rule-post','uses'=>'SalesApprovalRuleController@indexPost','middleware' => ['permission:sy-edit']]);
			});
			Route::group(['prefix' => 'expense-approval-rule'], function() {
				Route::get('', ['as'=>'expense-approval-rule','uses'=>'ExpenseApprovalRuleController@index','middleware' => ['permission:sy-edit']]);
				Route::post('', ['as'=>'expense-approval-rule-post','uses'=>'ExpenseApprovalRuleController@indexPost','middleware' => ['permission:sy-edit']]);
			});
			Route::group(['prefix' => 'interaction-user'], function() {
				Route::get('', ['as'=>'interaction-user-rule','uses'=>'InteractionUserRuleController@index','middleware' => ['permission:sy-edit']]);
				Route::post('', ['as'=>'interaction-user-rule-post','uses'=>'InteractionUserRuleController@indexPost','middleware' => ['permission:sy-edit']]);
			});
			Route::group(['prefix' => 'sales-restriction'], function() {
				Route::get('', ['as'=>'sales-restriction','uses'=>'UniqueTradableRestrictionController@index','middleware' => ['permission:sy-edit']]);
				Route::post('', ['as'=>'sales-restriction-post','uses'=>'UniqueTradableRestrictionController@indexPost','middleware' => ['permission:sy-edit']]);
			});
			// backup management
			Route::group(['prefix' => 'backup'], function() {
				Route::get('', ['as'=>'system.backup','uses'=>'BackupController@createBackup','middleware' => ['permission:db-backup']]);
				Route::post('', ['as'=>'system.backup','uses'=>'BackupController@createBackupPost','middleware' => ['permission:db-backup']]);
				Route::get('download/{hash}', ['as'=>'system.backup.download','uses'=>'BackupController@downloadBackup','middleware' => ['permission:db-backup']]);
			});
		});

		// permission role management
		Route::group(['prefix' => 'roles'], function() {
			Route::get('', ['as'=>'role.index','uses'=>'RoleController@index','middleware' => ['permission:role-list|role-create|role-edit']]);
			Route::group(['prefix' => 'create'], function() {
				Route::get('',['as'=>'role.create','uses'=>'RoleController@create','middleware' => ['permission:role-create']]);
				Route::post('',['as'=>'role.store','uses'=>'RoleController@store','middleware' => ['permission:role-create']]);
			});
			Route::get('{id}',['as'=>'role.show','uses'=>'RoleController@show','middleware' => ['permission:role-view']]);
			Route::get('{id}/edit',['as'=>'role.edit','uses'=>'RoleController@edit','middleware' => ['permission:role-edit']]);
			Route::patch('{id}',['as'=>'role.update','uses'=>'RoleController@update','middleware' => ['permission:role-edit']]);
			//Route::delete('{id}',['as'=>'role.destroy','uses'=>'RoleController@destroy','middleware' => ['permission:role-delete']]);
		});

		// user account management
		Route::group(['prefix' => 'useraccount'], function() {
			Route::get('', ['as' => 'user.index', 'uses' => 'UserAccountController@index', 'middleware' => ['permission:user-list|user-create|user_edit']]);
			Route::group(['prefix' => 'create'], function() {
				Route::get('', ['as' => 'user.create', 'uses' => 'UserAccountController@create', 'middleware' => ['permission:user-create']]);
				Route::post('', ['as' => 'user.store', 'uses' => 'UserAccountController@store', 'middleware' => ['permission:user-create']]);
			});
			Route::get('{id}', ['as' => 'user.show', 'uses' => 'UserAccountController@show', 'middleware' => ['permission:user-view']]);
			Route::get('{id}/edit',['as'=>'user.edit','uses'=>'UserAccountController@edit', 'middleware' => ['permission:user-edit']]);
			Route::patch('{id}',['as'=>'user.update','uses'=>'UserAccountController@update', 'middleware' => ['permission:user-edit']]);
			//Route::delete('{id}',['as'=>'user.destroy','uses'=>'UserAccountController@destroy', 'middleware' => ['permission:user-delete']]);
		});

		// -------------- Interaction related
		Route::group(['prefix' => 'interaction'], function() {
			Route::get('dashboard', ['uses' => 'InteractionController@index']);
			Route::get('view/{id}', ['uses' => 'InteractionController@view']);
		});

		// a test controller for trial/experiment purpose.  Comment out if not used.
		// Route::group(['prefix' => 'test'], function() {
		// 	Route::get('{id}', 'TestController@test');
		// 	Route::post('{id}', 'TestController@testPost');
		// });
	});
});

Route::group(['domain' => env('SUPPLIER_DOMAIN'), 'namespace' => 'Supplier', 'as' => 'supplier.'], function() {

	Route::get('', 'HomeController@index');

	Route::auth();

	Route::match(['get', 'post'], 'register', function() { return redirect('login'); });

	Route::get('home', 'HomeController@index');

	Route::group(['middleware' => ['auth'], 'name' => 'supplier.'], function() {

		// -------------- self account
		// profile management
		Route::group(['prefix' => 'password'], function() {
			Route::group(['prefix' => 'update'], function() {
				Route::get('', ['uses' => 'UserController@changePassword', 'middleware' => ['permission:vendor']]);
				Route::post('', ['uses' => 'UserController@changePasswordUpdate', 'middleware' => ['permission:vendor']]);
			});
		});

		Route::group(['prefix' => 'profile'], function() {
			Route::group(['prefix' => 'update'], function() {
				Route::get('', ['uses' => 'UserController@update', 'middleware' => ['permission:vendor']]);
				Route::post('', ['uses' => 'UserController@updatePost', 'middleware' => ['permission:vendor']]);
			});
		});

		Route::get('language/{lang}', ['uses' => 'LanguageController@index', 'middleware' => ['permission:vendor']]);

		// -------------- Order related
		Route::group(['prefix' => 'order'], function() {
			Route::group(['prefix' => 'dashboard'], function() {
				Route::get('', ['uses' => 'OrderController@index', 'middleware' => ['permission:vendor']]);
				Route::post('', ['uses' => 'OrderController@indexPost', 'middleware' => ['permission:vendor']]);
			});
			Route::post('shipment', ['uses' => 'OrderController@shipmentPost', 'middleware' => ['permission:vendor']]);
		});

		// -------------- Product related
		Route::group(['prefix' => 'product'], function() {
			Route::get('dashboard', ['uses' => 'ProductController@index', 'middleware' => ['permission:vendor']]);
			Route::get('viewproduct/{location_id}/{id}', ['uses' => 'ProductController@viewProduct', 'middleware' => ['permission:vendor']]);
		});

		// -------------- Transaction related
		Route::get('transaction/dashboard', ['uses' => 'TransactionController@index', 'middleware' => ['permission:vendor']]);

	});
});

Route::group(['domain' => env('CUSTOMER_DOMAIN'), 'namespace' => 'Customer', 'as' => 'customer.'], function() {

	Route::get('', 'HomeController@index');

	Route::auth();

	Route::match(['get', 'post'], 'register', function() { return redirect('login'); });

	Route::get('home', 'HomeController@index');

	Route::group(['middleware' => ['auth']], function() {

		// -------------- self account
		// profile management
		Route::group(['prefix' => 'password'], function() {
			Route::group(['prefix' => 'update'], function() {
				Route::get('', ['uses' => 'UserController@changePassword', 'middleware' => ['permission:client']]);
				Route::post('', ['uses' => 'UserController@changePasswordUpdate', 'middleware' => ['permission:client']]);
			});
		});

		Route::group(['prefix' => 'profile'], function() {
			Route::group(['prefix' => 'update'], function() {
				Route::get('', ['uses' => 'UserController@update', 'middleware' => ['permission:client']]);
				Route::post('', ['uses' => 'UserController@updatePost', 'middleware' => ['permission:client']]);
			});
		});

		Route::get('language/{lang}', ['uses' => 'LanguageController@index', 'middleware' => ['permission:client']]);

		// -------------- Order related
		Route::get('order/dashboard', ['uses' => 'OrderController@index', 'middleware' => ['permission:client']]);

		// -------------- Invoice related
		Route::get('invoice/dashboard', ['uses' => 'InvoiceController@index', 'middleware' => ['permission:client']]);

		// -------------- Transaction related
		Route::get('transaction/dashboard', ['uses' => 'TransactionController@index', 'middleware' => ['permission:client']]);

	});
});

Route::group(['domain' => env('TENANT_DOMAIN'), 'namespace' => 'Tenant', 'as' => 'tenant.'], function() {

	Route::get('', 'HomeController@index');

	Route::auth();

	Route::match(['get', 'post'], 'register', function() { return redirect('login'); });

	Route::get('home', 'HomeController@index');

	Route::group(['middleware' => ['auth']], function() {

		// -------------- self account
		// profile management
		Route::group(['prefix' => 'password'], function() {
			Route::group(['prefix' => 'update'], function() {
				Route::get('', ['uses' => 'UserController@changePassword', 'middleware' => ['permission:client']]);
				Route::post('', ['uses' => 'UserController@changePasswordUpdate', 'middleware' => ['permission:client']]);
			});
		});

		Route::group(['prefix' => 'profile'], function() {
			Route::group(['prefix' => 'update'], function() {
				Route::get('', ['uses' => 'UserController@update', 'middleware' => ['permission:client']]);
				Route::post('', ['uses' => 'UserController@updatePost', 'middleware' => ['permission:client']]);
			});
		});

		Route::get('language/{lang}', ['uses' => 'LanguageController@index', 'middleware' => ['permission:client']]);

		// -------------- Invoice related
		Route::get('invoice/dashboard', ['uses' => 'InvoiceController@index', 'middleware' => ['permission:client']]);

		// -------------- Transaction related
		Route::get('transaction/dashboard', ['uses' => 'TransactionController@index', 'middleware' => ['permission:client']]);

		// -------------- Request related
		Route::group(['prefix' => 'request'], function() {
			Route::get('dashboard', ['uses' => 'RequestController@index', 'middleware' => ['permission:client']]);
			Route::post('upload', ['uses' => 'RequestController@uploadFileAjax', 'middleware' => ['permission:client']]);
			Route::get('view/{id}', ['uses' => 'RequestController@view', 'middleware' => ['permission:client']]);
			Route::post('create', ['uses' => 'RequestController@createRequestAjax', 'middleware' => ['permission:client']]);
			Route::post('update/{id}', ['uses' => 'RequestController@addInfoToRequestAjax', 'middleware' => ['permission:client']]);
		});

		// -------------- Document related
		Route::group(['prefix' => 'document'], function() {
			Route::get('dashboard', ['uses' => 'DocumentController@index', 'middleware' => ['permission:client']]);
		});

	});
});
