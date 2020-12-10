<?php

/*
|--------------------------------------------------------------------------
| Web-Ajax Routes
|--------------------------------------------------------------------------
|
| Here is where one can register web-ajax routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web-ajax" middleware group. Now create something great!
|
*/

Route::group(['domain' => env('COMPANY_DOMAIN')], function() {

	Route::group(['middleware' => ['auth.ajax']], function() {

		// get company logo
		// Route::get('company-logo', ['uses' => 'LogoController@getLogo']);

		Route::group(['prefix' => 'home'], function() {
			Route::get('ajax', ['uses' => 'HomeController@getHomepageUpdateAjax', 'middleware' => ['permission:iv-view']]);
		});

		// ajax functions for populating dashboard
		Route::group(['prefix' => 'dashboard'], function() {
			Route::group(['prefix' => 'processable'], function() {
				Route::group(['prefix' => '{type}'], function() {
					Route::get('ajax', ['uses' => 'TransactableController@getDashboardProcessableOrderAjax', 'middleware' => ['permission:po-list|pr-list|so-list|sr-list']]);
				});
			});
			Route::group(['prefix' => 'entity'], function() {
				Route::group(['prefix' => '{type}'], function() {
					Route::get('ajax', ['uses' => 'TaxableEntityController@getDashboardTaxableEntityAjax', 'middleware' => ['permission:supplier-list|customer-list']]);
				});
			});
			Route::group(['prefix' => 'transactable'], function() {
				Route::group(['prefix' => '{type}'], function() {
					Route::get('ajax', ['uses' => 'TransactableController@getDashboardTransactableAjax', 'middleware' => ['permission:ap-list|ar-list|rar-list|rap-list']]);
				});
			});
			Route::group(['prefix' => 'expense'], function() {
				Route::group(['prefix' => '{type}'], function() {
					Route::get('ajax', ['uses' => 'ChargeController@getDashboardExpenseAjax', 'middleware' => ['permission:ex-list']]);
				});
			});
			Route::group(['prefix' => 'account'], function() {
				Route::group(['prefix' => '{type}'], function() {
					Route::get('ajax', ['uses' => 'ChartAccountController@getDashboardAccountAjax', 'middleware' => ['permission:acct-list']]);
				});
			});
			Route::group(['prefix' => 'loan'], function() {
				Route::get('ajax', ['uses' => 'LoanController@getDashboardLoanAjax', 'middleware' => ['permission:ar-list', 'permission:ap-list']]);
			});
			Route::group(['prefix' => 'recurring-expense'], function() {
				Route::get('ajax', ['uses' => 'ChargeController@getDashboardRecurringExpenseAjax', 'middleware' => ['permission:ex-list']]);
			});
			Route::group(['prefix' => 'inventory'], function() {
				Route::get('ajax', ['uses' => 'AccountingController@getDashboardInventoryAjax', 'middleware' => ['permission:acct-list']]);
			});
			Route::group(['prefix' => 'staff'], function() {
				Route::get('ajax', ['uses' => 'HRController@getDashboardStaffAjax', 'middleware' => ['permission:user-list']]);
			});
			Route::group(['prefix' => 'purchase'], function() {
				Route::group(['prefix' => 'release'], function() {
					Route::get('ajax', ['uses' => 'PurchaseEntryController@releaseAjax', 'middleware' => ['permission:po-process|pr-process']]);
				});
			});
			Route::group(['prefix' => 'purchase-quote'], function() {
				Route::get('ajax', ['uses' => 'VRMController@getDashboardQuoteAjax', 'middleware' => ['permission:pq-list']]);
			});
			Route::group(['prefix' => 'purchase-order'], function() {
				Route::get('ajax', ['uses' => 'VRMController@getDashboardOrderAjax', 'middleware' => ['permission:po-list|pr-list']]);
			});
			Route::group(['prefix' => 'sales-quote'], function() {
				Route::get('ajax', ['uses' => 'CRMController@getDashboardQuoteAjax', 'middleware' => ['permission:sq-list']]);
			});
			Route::group(['prefix' => 'sales-order'], function() {
				Route::get('ajax', ['uses' => 'CRMController@getDashboardOrderAjax', 'middleware' => ['permission:so-list|sr-list']]);
			});
			Route::group(['prefix' => 'sales'], function() {
				Route::group(['prefix' => 'reserve'], function() {
					Route::get('ajax', ['uses' => 'SalesEntryController@reserveAjax', 'middleware' => ['permission:so-process|sr-process']]);
				});
			});
			Route::group(['prefix' => 'warehouse-order'], function() {
				Route::group(['prefix' => '{type}'], function() {
					Route::get('ajax', ['uses' => 'WarehouseController@getDashboardOrderAjax', 'middleware' => ['permission:wo-list']]);
				});
			});
			Route::group(['prefix' => 'warehouse-inventory'], function() {
				Route::get('ajax', ['uses' => 'WarehouseController@getDashboardInventoryAjax', 'middleware' => ['permission:wo-list']]);
			});
			Route::group(['prefix' => 'document'], function() {
				Route::get('ajax', ['uses' => 'DocumentController@getDashboardDocumentAjax' ]);
			});
			Route::group(['prefix' => 'tradable'], function() {
				Route::get('ajax', ['uses' => 'ProductController@getDashboardTradableAjax' ]);
			});
			Route::group(['prefix' => 'tradable-update'], function() {
				Route::get('ajax', ['uses' => 'ProductController@getDashboardTradableUpdateAjax' ]);
			});
			Route::group(['prefix' => 'tradable-faq'], function() {
				Route::get('ajax', ['uses' => 'ProductController@getDashboardTradableFAQAjax' ]);
			});
		});

		// -------------- finance related web-ajax
		Route::group(['prefix' => 'finance'], function() {
			Route::group(['prefix' => 'dividend'], function() {
				Route::get('', ['uses' => 'FinanceController@recordDividendAjax', 'middleware' => ['permission:ar-process']]);
				Route::post('', ['uses' => 'FinanceController@recordDividendPostAjax', 'middleware' => ['permission:ar-process']]);
			});
			Route::group(['prefix' => 'statement'], function() {
				Route::get('ajax', ['uses' => 'FinanceController@getStatementAjax', 'middleware' => ['permission:ap-list|ar-list|rar-list|rap-list|ex-list']]);
			});
			Route::post('earning/ajax', ['uses' => 'FinanceController@finalizeEarningAjax', 'middleware' => ['permission:ap-list|ar-list|rar-list|rap-list|ex-list']]);
			Route::group(['prefix' => 'outstanding'], function() {
				Route::get('{type}/ajax', ['uses' => 'FinanceController@getTransactableAjax', 'middleware' => ['permission:ap-list|ar-list|rar-list|rap-list|ex-list']]);
			});
			Route::group(['prefix' => 'loan'], function() {
				Route::group(['prefix' => 'interest/{id}'], function() {
					Route::get('', ['uses' => 'LoanController@recordInterestAjax', 'middleware' => ['permission:ar-process|ap-process']]);
					Route::post('', ['uses' => 'LoanController@recordInterestPostAjax', 'middleware' => ['permission:ar-process|ap-process']]);
				});
			});
		});
		Route::group(['prefix' => 'cash'], function() {
			Route::group(['prefix' => 'transfer'], function() {
				Route::get('', ['uses' => 'FinanceController@transferCashAjax', 'middleware' => ['permission:ar-process|rar-process|ap-process|rap-process']]);
				Route::post('', ['uses' => 'FinanceController@transferCashPostAjax', 'middleware' => ['permission:ar-process|rar-process|ap-process|rap-process']]);
			});
		});
		Route::group(['prefix' => 'income-from'], function() {
			Route::group(['prefix' => '{id}'], function() {
				Route::group(['prefix' => 'ajax'], function() {
					Route::get('', ['uses' => 'ReceivableController@receivePaymentAjax', 'middleware' => ['permission:ar-process|rar-process|ap-process|rap-process']]);
					Route::post('', ['uses' => 'ReceivableController@receivePaymentPostAjax', 'middleware' => ['permission:ar-process|rar-process|ap-process|rap-process']]);
				});
			});
		});
		Route::group(['prefix' => 'pay-to'], function() {
			Route::group(['prefix' => '{id}'], function() {
				Route::group(['prefix' => 'ajax'], function() {
					Route::get('', ['uses' => 'PayableController@disbursePaymentAjax', 'middleware' => ['permission:ar-process|rar-process|ap-process|rap-process']]);
					Route::post('', ['uses' => 'PayableController@disbursePaymentPostAjax', 'middleware' => ['permission:ar-process|rar-process|ap-process|rap-process']]);
				});
			});
		});

		// ------------- product related web-ajax
		Route::group(['prefix' => 'tradable'], function() {
			Route::group(['prefix' => 'faq'], function() {
				Route::group(['prefix' => 'create'], function() {
					Route::post('ajax', ['uses' => 'ProductController@createFaqPostAjax', 'middleware' => ['permission:pd-create']]);
				});
				Route::group(['prefix' => 'update'], function() {
					Route::group(['prefix' => '{id}'], function() {
						Route::post('ajax', ['uses' => 'ProductController@updateFaqPostAjax', 'middleware' => ['permission:pd-edit']]);
					});
				});
				Route::group(['prefix' => '{id}'], function() {
					Route::get('ajax', ['uses' => 'ProductController@loadProductFaqAjax', 'middleware' => ['permission:pd-view|pd-create|pd-edit']]);
				});
			});
			Route::group(['prefix' => 'notice'], function() {
				Route::group(['prefix' => 'create'], function() {
					Route::post('ajax', ['uses' => 'ProductController@createNoticePostAjax', 'middleware' => ['permission:pd-create']]);
				});
				Route::group(['prefix' => 'update'], function() {
					Route::group(['prefix' => '{id}'], function() {
						Route::post('ajax', ['uses' => 'ProductController@updateNoticePostAjax', 'middleware' => ['permission:pd-edit']]);
					});
				});
				Route::group(['prefix' => '{id}'], function() {
					Route::get('ajax', ['uses' => 'ProductController@loadProductNoticeAjax', 'middleware' => ['permission:pd-view|pd-create|pd-edit']]);
				});
			});
			Route::group(['prefix' => 'create'], function() {
				Route::post('ajax', ['uses' => 'ProductController@createPostAjax', 'middleware' => ['permission:pd-create']]);
			});
			Route::group(['prefix' => 'update'], function() {
				Route::group(['prefix' => '{id}'], function() {
					Route::post('ajax', ['uses' => 'ProductController@updatePostAjax', 'middleware' => ['permission:pd-edit']]);
				});
			});
			Route::group(['prefix' => '{id}'], function() {
				Route::get('ajax', ['uses' => 'ProductController@loadProductAjax', 'middleware' => ['permission:pd-view|pd-create|pd-edit']]);
			});
		});

		//------------- loan related web-ajax
		Route::group(['prefix' => 'loan'], function() {
			Route::group(['prefix' => 'create'], function() {
				Route::group(['prefix' => 'ajax'], function() {
					Route::post('', ['uses' => 'LoanController@createPostAjax', 'middleware' => ['permission:ar-process|ap-process']]);
				});
			});
			Route::group(['prefix' => 'update'], function() {
				Route::group(['prefix' => '{id}'], function() {
					Route::group(['prefix' => 'ajax'], function() {
						Route::post('', ['uses' => 'LoanController@updatePostAjax', 'middleware' => ['permission:ar-process|ap-process']]);
					});
				});
			});
			Route::group(['prefix' => 'forgive'], function() {
				Route::group(['prefix' => '{id}'], function() {
					Route::group(['prefix' => 'ajax'], function() {
						Route::post('', ['uses' => 'LoanController@forgivePostAjax', 'middleware' => ['permission:ar-process|ap-process']]);
					});
				});
			});
			Route::group(['prefix' => '{id}'], function() {
				Route::group(['prefix' => 'ajax'], function() {
					Route::get('', ['uses' => 'LoanController@loadAjax', 'middleware' => ['permission:ar-view|ap-view|ar-process|ap-process']]);
				});
			});
		});

		// ------------- Accounting related web-ajax
		Route::group(['prefix' => 'accounting'], function() {
			Route::group(['prefix' => 'tradable'], function() {
				Route::group(['prefix' => 'transactions'], function() {
					Route::get('ajax', ['uses' => 'AccountingController@viewTradableTransactionsAjax', 'middleware' => ['permission:ap-list|ar-list|rar-list|rap-list|ex-list']]);
				});
			});
			// aging
			Route::group(['prefix' => 'aging'], function() {
				Route::get('ajax', ['uses' => 'AccountingController@getInventoryAgingAjax', 'middleware' => ['permission:ap-list']]);
			});
		});

		// ------------- taxable-entity related web-ajax
		Route::group(['prefix' => 'taxable-entity'], function() {
			Route::group(['prefix' => 'create'], function() {
				Route::post('ajax', ['uses' => 'TaxableEntityController@createPostAjax', 'middleware' => [ 'permission.ajax.taxable-entity:create' ]]);
			});
			Route::get('{id}/ajax', ['uses' => 'TaxableEntityController@loadTaxableEntityAjax', 'middleware' => [ 'permission.ajax.taxable-entity:edit' ]]);
			Route::group(['prefix' => 'update'], function() {
				Route::group(['prefix' => '{id}'], function() {
					Route::post('ajax', ['uses' => 'TaxableEntityController@updatePostAjax', 'middleware' => [ 'permission.ajax.taxable-entity:edit' ]]);
				});
			});
		});

		// vendor related web-ajax
		Route::group(['prefix' => 'vrm'], function() {
			Route::post('releaseorder/{id}', ['uses' => 'PurchaseOrderController@releaseOrderPost', 'middleware' => ['permission:pr-process']]);
			Route::post('releasereturn/{id}', ['uses' => 'PurchaseReturnController@releaseReturnPost', 'middleware' => ['permission:pr-process']]);
			Route::get('detailsupplier/ajax', ['uses' => 'SupplierController@supplierPerformanceAjax', 'middleware' => ['permission:supplier-report']]);
		});

		// -------------- purchase quote related web-ajax
		Route::group(['prefix' => 'purchase-quote'], function() {
			Route::group(['prefix' => 'create'], function() {
				Route::post('ajax', ['uses' => 'PurchaseQuoteController@createPostAjax', 'middleware' => [ 'permission.ajax.purchase-entry:create' ]]);
			});
			Route::group(['prefix' => 'print'], function() {
				Route::group(['prefix' => '{id}'], function() {
					Route::post('ajax', ['uses' => 'PurchaseQuoteController@printPostAjax', 'middleware' => [ 'permission.ajax.purchase-entry:view' ]]);
				});
			});
			Route::get('{id}/ajax', ['uses' => 'PurchaseQuoteController@loadPurchaseQuoteAjax', 'middleware' => [ 'permission.ajax.purchase-entry:view' ]]);
			Route::group(['prefix' => 'update'], function() {
				Route::group(['prefix' => '{id}'], function() {
					Route::post('ajax', ['uses' => 'PurchaseQuoteController@updatePostAjax', 'middleware' => [ 'permission.ajax.purchase-entry:edit' ]]);
				});
			});
			Route::group(['prefix' => 'approve'], function() {
				Route::group(['prefix' => '{id}'], function() {
					Route::post('ajax', ['uses' => 'PurchaseQuoteController@approvePostAjax', 'middleware' => [ 'permission.ajax.purchase-entry:view' ]]);
				});
			});
		});

		// -------------- purchase order/return related web-ajax
		Route::group(['prefix' => 'purchase-entry'], function() {
			Route::group(['prefix' => 'create'], function() {
				Route::post('ajax', ['uses' => 'PurchaseEntryController@createPostAjax', 'middleware' => [ 'permission.ajax.purchase-entry:create' ]]);
			});
			Route::group(['prefix' => 'print'], function() {
				Route::group(['prefix' => '{id}'], function() {
					Route::post('ajax', ['uses' => 'PurchaseEntryController@printPostAjax', 'middleware' => [ 'permission.ajax.purchase-entry:view' ]]);
				});
			});
			Route::get('{id}/ajax', ['uses' => 'PurchaseEntryController@loadPurchaseEntryAjax', 'middleware' => [ 'permission.ajax.purchase-entry:view' ]]);
			Route::group(['prefix' => 'update'], function() {
				Route::group(['prefix' => '{id}'], function() {
					Route::post('ajax', ['uses' => 'PurchaseEntryController@updatePostAjax', 'middleware' => [ 'permission.ajax.purchase-entry:edit' ]]);
				});
			});
			Route::group(['prefix' => 'approve'], function() {
				Route::group(['prefix' => '{id}'], function() {
					Route::post('ajax', ['uses' => 'PurchaseEntryController@approvePostAjax', 'middleware' => [ 'permission.ajax.purchase-entry:view' ]]);
				});
			});
			Route::group(['prefix' => 'void'], function() {
				Route::group(['prefix' => '{id}'], function() {
					Route::post('ajax', ['uses' => 'PurchaseEntryController@voidPostAjax', 'middleware' => [ 'permission.ajax.purchase-entry:edit' ]]);
				});
			});
			Route::group(['prefix' => 'process'], function() {
				Route::group(['prefix' => '{id}'], function() {
					Route::post('ajax', ['uses' => 'PurchaseEntryController@processPostAjax', 'middleware' => [ 'permission.ajax.purchase-entry:process' ]]);
				});
			});
		});

		// ------------- customer related Web-Ajax
		Route::group(['prefix' => 'crm'], function() {
			Route::get('detailcustomer/ajax', ['uses' => 'CustomerController@customerPerformanceAjax', 'middleware' => ['permission:customer-report']]);
			Route::post('reserve/order/{id}', ['uses' => 'CRMController@reserveTransactableTitle', 'middleware' => ['permission:so-create|so-edit|sr-create|sr-edit']]);
		});

		// ------------- sales quote related web-ajax
		Route::group(['prefix' => 'sales-quote'], function() {
			Route::group(['prefix' => 'create'], function() {
				Route::post('ajax', ['uses' => 'SalesQuoteController@createPostAjax', 'middleware' => [ 'permission.ajax.sales-entry:create' ]]);
			});
			Route::group(['prefix' => 'print'], function() {
				Route::group(['prefix' => '{id}'], function() {
					Route::post('ajax', ['uses' => 'SalesQuoteController@printPostAjax', 'middleware' => [ 'permission.ajax.sales-entry:view' ]]);
				});
			});
			Route::get('{id}/ajax', ['uses' => 'SalesQuoteController@loadSalesQuoteAjax', 'middleware' => [ 'permission.ajax.sales-entry:view' ]]);
			Route::group(['prefix' => 'update'], function() {
				Route::group(['prefix' => '{id}'], function() {
					Route::post('ajax', ['uses' => 'SalesQuoteController@updatePostAjax', 'middleware' => [ 'permission.ajax.sales-entry:edit' ]]);
				});
			});
			Route::group(['prefix' => 'approve'], function() {
				Route::group(['prefix' => '{id}'], function() {
					Route::post('ajax', ['uses' => 'SalesQuoteController@approvePostAjax', 'middleware' => [ 'permission.ajax.sales-entry:view' ]]);
				});
			});
		});

		// ------------- sales order/return related web-ajax
		Route::group(['prefix' => 'sales-entry'], function() {
			Route::group(['prefix' => 'create'], function() {
				Route::post('ajax', ['uses' => 'SalesEntryController@createPostAjax', 'middleware' => [ 'permission.ajax.sales-entry:create' ]]);
			});
			Route::group(['prefix' => 'print'], function() {
				Route::group(['prefix' => '{id}'], function() {
					Route::post('ajax', ['uses' => 'SalesEntryController@printPostAjax', 'middleware' => [ 'permission.ajax.sales-entry:view' ]]);
				});
			});
			Route::get('{id}/ajax', ['uses' => 'SalesEntryController@loadSalesEntryAjax', 'middleware' => [ 'permission.ajax.sales-entry:view' ]]);
			Route::group(['prefix' => 'update'], function() {
				Route::group(['prefix' => '{id}'], function() {
					Route::post('ajax', ['uses' => 'SalesEntryController@updatePostAjax', 'middleware' => [ 'permission.ajax.sales-entry:edit' ]]);
				});
			});
			Route::group(['prefix' => 'approve'], function() {
				Route::group(['prefix' => '{id}'], function() {
					Route::post('ajax', ['uses' => 'SalesEntryController@approvePostAjax', 'middleware' => [ 'permission.ajax.sales-entry:view' ]]);
				});
			});
			Route::group(['prefix' => 'void'], function() {
				Route::group(['prefix' => '{id}'], function() {
					Route::post('ajax', ['uses' => 'SalesEntryController@voidPostAjax', 'middleware' => [ 'permission.ajax.sales-entry:edit' ]]);
				});
			});
			Route::group(['prefix' => 'process'], function() {
				Route::group(['prefix' => '{id}'], function() {
					Route::post('ajax', ['uses' => 'SalesEntryController@processPostAjax', 'middleware' => [ 'permission.ajax.sales-entry:process' ]]);
				});
			});
		});

		// transactable related web-ajax
		Route::group(['prefix' => 'transactable'], function() {
			Route::group(['prefix' => 'print'], function() {
				Route::group(['prefix' => '{id}'], function() {
					Route::post('ajax', ['uses' => 'TransactableController@printPostAjax', 'middleware' => [ 'permission.ajax.transactable:view' ]]);
				});
			});
			Route::get('{id}/ajax', ['uses' => 'TransactableController@loadTransactableAjax', 'middleware' => [ 'permission.ajax.transactable:view' ]]);
			Route::group(['prefix' => 'void'], function() {
				Route::group(['prefix' => '{id}'], function() {
					Route::post('ajax', ['uses' => 'TransactableController@voidPostAjax', 'middleware' => [ 'permission.ajax.transactable:process' ]]);
				});
			});
		});

		// chart account related web-ajax
		Route::group(['prefix' => 'taccount'], function() {
			Route::group(['prefix' => 'create'], function() {
				Route::post('ajax', ['uses' => 'ChartAccountController@createPostAjax', 'middleware' => [ 'permission:acct-edit' ]]);
			});
			Route::get('retrieve', ['uses' => 'ChartAccountController@retrieveAjax', 'middleware' => ['permission:ar-view|rar-view|ap-view|rap-view']]);
			Route::group(['prefix' => '{aid}'], function() {
				Route::group([ 'prefix' => 'unreconciled/ajax' ], function() {
					Route::get('', ['uses' => 'ChartAccountController@loadUnreconciledTransactionAjax', 'middleware' => [ 'permission:ar-process|rar-process|ap-process|rap-process' ]]);
					Route::post('', ['uses' => 'ChartAccountController@updateUnreconciledTransactionAjax', 'middleware' => [ 'permission:ar-process|rar-process|ap-process|rap-process' ]]);
				});
				Route::get('ajax', ['uses' => 'ChartAccountController@loadAjax', 'middleware' => [ 'permission:acct-view' ]]);
				Route::post('ajax', ['uses' => 'ChartAccountController@updatePostAjax', 'middleware' => [ 'permission:acct-edit' ]]);
			});
		});

		// charge related web-ajax
		Route::group(['prefix' => 'charge'], function() {
			Route::group(['prefix' => 'create'], function() {
				Route::group(['prefix' => '{sid}'], function() {
					Route::post('ajax', ['uses' => 'ChargeController@createPostAjax', 'middleware' => ['permission:ex-create']]);
				});
			});
			Route::get('{id}/ajax', 'ChargeController@loadAjax');
			Route::group(['prefix' => 'recurring'], function() {
				Route::group(['prefix' => '{id}'], function() {
					Route::get('ajax', 'ChargeController@loadRecurringAjax');
				});
			});
			Route::group(['prefix' => 'update'], function() {
				Route::group(['prefix' => '{id}'], function() {
					Route::post('ajax', ['uses' => 'ChargeController@updatePostAjax', 'middleware' => ['permission:ex-edit']]);
				});
			});
			Route::group(['prefix' => 'retract'], function() {
				Route::group(['prefix' => '{id}'], function() {
					Route::post('ajax', ['uses' => 'ChargeController@retractPostAjax', 'middleware' => ['permission:ex-edit']]);
				});
			});
			Route::group(['prefix' => 'submit'], function() {
				Route::group(['prefix' => '{id}'], function() {
					Route::post('ajax', ['uses' => 'ChargeController@submitPostAjax', 'middleware' => ['permission:ex-create|ex-edit']]);
				});
			});
			Route::group(['prefix' => 'approve'], function() {
				Route::group(['prefix' => '{id}'], function() {
					Route::post('ajax', ['uses' => 'ChargeController@approvePostAjax', 'middleware' => ['permission:ex-view']]);
				});
			});
			Route::group(['prefix' => 'process'], function() {
				Route::group(['prefix' => '{id}'], function() {
					Route::post('ajax', ['uses' => 'ChargeController@processPostAjax', 'middleware' => ['permission:ex-process']]);
				});
			});
		});

		// staff expense related web-ajax
		Route::group(['prefix' => 'expense'], function() {
			Route::group(['prefix' => 'dashboard'], function() {
				Route::group(['prefix' => 'in-progress'], function() {
					Route::get('', ['uses' => 'StaffExpenseController@inProgressExpenseAjax']);
				});
				Route::group(['prefix' => 'need-approval'], function() {
					Route::get('', ['uses' => 'StaffExpenseController@needApprovalExpenseAjax']);
				});
			});
		});

		// -------------- Warehouse related web-ajax
		Route::group(['prefix' => 'warehouse'], function() {
			Route::group(['prefix' => 'order'], function() {
				Route::get('ajax', ['uses' => 'WarehouseOrderController@loadOrderAjax', 'middleware' => ['permission:wo-view']]);
			});
			Route::group(['prefix' => 'voidorder'], function() {
				Route::post('ajax', ['uses' => 'WarehouseOrderController@voidOrderPostAjax', 'middleware' => ['permission:wo-process']]);
			});
			Route::group(['prefix' => 'createorder'], function() {
				Route::post('ajax', ['uses' => 'WarehouseOrderController@createOrderPostAjax', 'middleware' => ['permission:wo-process']]);
			});
			Route::group(['prefix' => 'processorder'], function() {
				Route::post('ajax', ['uses' => 'WarehouseOrderController@processOrderPostAjax', 'middleware' => ['permission:wo-process']]);
			});
			Route::group(['prefix' => 'transactions'], function() {
				Route::get('ajax', ['uses' => 'WarehouseController@viewTransactionsAjax', 'middleware' => ['permission:wo-view']]);
			});
			Route::group(['prefix' => 'inventory'], function() {
				Route::get('ajax', ['uses' => 'WarehouseController@getInventoryAjax', 'middleware' => ['permission:wo-list']]);
			});
			Route::get('consignment/ajax', ['uses' => 'WarehouseController@consignmentInventoryAjax', 'middleware' => ['permission:wo-list']]);

			// serial number
			Route::group(['prefix' => 'serial'], function() {
				Route::get('ajax', ['uses' => 'WarehouseOrderController@viewSingleSerialHistoryAjax', 'middleware' => ['permission:wo-list']]);
			});
			// aging inventory
			Route::group(['prefix' => 'aging'], function() {
				Route::get('ajax', ['uses' => 'WarehouseController@getInventoryAgingAjax', 'middleware' => ['permission:wo-list|wo-view']]);
			});
		});
		// ------------- warehouse order information used in other department
		Route::group(['prefix' => 'warehouse-order'], function() {
			Route::group(['prefix' => 'pregenerated'], function() {
				Route::get('{id}/ajax', ['uses' => 'WarehouseOrderController@loadWarehouseOrderAjax', 'middleware' => ['permission:wo-view']]);
				Route::group(['prefix' => 'process'], function() {
					Route::post('{id}/ajax', ['uses' => 'WarehouseOrderController@processWarehouseOrderAjax', 'middleware' => ['permission:wo-process']]);
				});
			});
		});

		//------------- staff related web-ajax
		Route::group(['prefix' => 'staff'], function() {
			Route::group(['prefix' => '{id}/ajax'], function() {
				Route::get('', ['uses' => 'HRController@loadAjax', 'middleware' => ['permission:hr-view']]);
				Route::post('', ['uses' => 'HRController@updatePostAjax', 'middleware' => ['permission:hr-view']]);
			});
		});

		// -------------- HR management
		Route::group(['prefix' => 'hr'], function() {
			Route::group(['prefix' => 'staff'], function() {
				Route::group(['prefix' => '{id}'], function() {
					Route::post('archive', ['uses' => 'HRController@archive', 'middleware' => ['permission:hr-edit']]);
				});
			});
			Route::group(['prefix' => 'archive'], function() {
				Route::group(['prefix' => '{id}'], function() {
					Route::get('download', ['uses' => 'HRController@download', 'middleware' => ['permission:hr-view']]);
				});
			});
		});

		// -------------- System related web-ajax
		Route::group(['prefix' => 'system'], function() {
			// backup management
			Route::group(['prefix' => 'backup'], function() {
				Route::get('progress', ['as'=>'system.backup.progress','uses'=>'BackupController@reportBackupProgress','middleware' => ['permission:db-backup']]);
			});
			// parameter management
			Route::group(['prefix' => 'parameter'], function() {
				Route::get('ajax', [ 'uses' => 'ParameterController@getDashboardParametersAjax','middleware' => [ 'permission:sy-list' ]]);
				Route::group(['prefix' => '{id}'], function() {
					Route::get('ajax', [ 'uses' => 'ParameterController@loadParameterAjax','middleware' => [ 'permission:sy-edit' ]]);
					Route::post('ajax', [ 'uses' => 'ParameterController@updatePostAjax','middleware' => [ 'permission:sy-edit' ]]);
				});
				Route::post('ajax', [ 'uses' => 'ParameterController@createPostAjax','middleware' => [ 'permission:sy-edit' ]]);
			});
			// role management
			Route::group(['prefix' => 'role'], function() {
				Route::group(['prefix' => 'create'], function() {
					Route::post('ajax', [ 'uses' => 'RoleController@createPostAjax','middleware' => [ 'permission:role-create' ]]);
				});
				Route::group(['prefix' => 'update'], function() {
					Route::group(['prefix' => '{id}'], function() {
						Route::post('ajax', [ 'uses' => 'RoleController@updatePostAjax','middleware' => [ 'permission:role-edit' ]]);
					});
				});
				Route::group(['prefix' => '{id}'], function() {
					Route::get('ajax', [ 'uses' => 'RoleController@loadRoleAjax','middleware' => [ 'permission:role-view' ]]);
				});
			});
			// user management
			Route::group(['prefix' => 'user'], function() {
				Route::group(['prefix' => 'create'], function() {
					Route::post('ajax', [ 'uses' => 'UserAccountController@createPostAjax','middleware' => [ 'permission:user-create' ]]);
				});
				Route::group(['prefix' => 'update'], function() {
					Route::group(['prefix' => '{id}'], function() {
						Route::post('ajax', [ 'uses' => 'UserAccountController@updatePostAjax','middleware' => [ 'permission:user-edit' ]]);
					});
				});
				Route::group(['prefix' => '{id}'], function() {
					Route::get('ajax', [ 'uses' => 'UserAccountController@loadUserAjax','middleware' => [ 'permission:user-view' ]]);
				});
			});
		});

		// -------------- calendar related web-ajax
		Route::group(['prefix' => 'calendar'], function() {
			Route::get('get/{id}', 'CalendarController@get');
			Route::post('save', 'CalendarController@save');
			Route::post('reply/{id}', 'CalendarController@reply');
		});

		// ------------- email related web-ajax
		Route::group(['prefix' => 'email'], function() {
			Route::group(['prefix' => 'box'], function() {
				Route::get('{box}', 'EmailController@getEmailHeaders');
			});
			Route::get('get', 'EmailController@get');
			Route::get('view/{id}', 'EmailController@view');
			Route::get('attachment/{hash}', 'EmailController@attachment');
			Route::post('send', 'EmailController@send');
			Route::post('delete/{id}', 'EmailController@delete');
			Route::get('prepare', 'EmailController@prepareCorrespondence');
			Route::post('attach', 'EmailController@attach');
		});

		// document
		Route::group(['prefix' => 'document'], function() {
			Route::group(['prefix' => '{id}'], function() {
				Route::get('ajax', 'DocumentController@loadDocumentAjax');
			});
			Route::group(['prefix' => 'create'], function() {
				Route::post('ajax', 'DocumentController@createPostAjax');
			});
			Route::group(['prefix' => 'update'], function() {
				Route::group(['prefix' => '{id}'], function() {
					Route::post('ajax', 'DocumentController@updatePostAjax');
				});
			});
			Route::group(['prefix' => 'delete'], function() {
				Route::group(['prefix' => '{id}'], function() {
					Route::post('ajax', 'DocumentController@deletePostAjax');
				});
			});
			Route::group(['prefix' => 'download'], function() {
				Route::get('{id}', 'DocumentController@download');
			});
		});

		// -------------- file-download related web-ajax
		Route::group(['prefix' => 'file'], function() {
			Route::get('download/{hash}', 'FileController@fileDownload');
		});

		// -------------- Interaction related web-ajax
		Route::group(['prefix' => 'interaction'], function() {
			Route::get('ajax', ['uses' => 'InteractionController@getDashboardInteractionAjax']);
			Route::post('upload', ['uses' => 'InteractionController@uploadFileAjax']);
			Route::post('create', ['uses' => 'InteractionController@createRequestAjax']);
			Route::post('update/{id}', ['uses' => 'InteractionController@addInfoToRequestAjax']);
			Route::group(['prefix' => '{id}'], function() {
				Route::post('', ['uses' => 'InteractionController@changePropertyAjax']);
				Route::get('ajax', ['uses' => 'InteractionController@loadInteractionAjax']);
			});
		});

		// ------------- commission related web-ajax
		Route::group(['prefix' => 'commission'], function() {
			Route::get('calculate', 'CommissionController@calculateCommission');
			Route::post('record', 'CommissionController@recordCommission');
			Route::group(['prefix' => 'profile'], function() {
				Route::get('{id}', 'CommissionController@loadCommissionProfile');
				Route::post('{id}', 'CommissionController@saveCommissionProfile');
			});
		});
  });
});

Route::group(['domain' => env('SUPPLIER_DOMAIN'), 'namespace' => 'Supplier', 'as' => 'supplier.'], function() {

	Route::group(['middleware' => ['auth'], 'name' => 'supplier.'], function() {

		// get company logo
		// Route::get('company-logo', ['uses' => 'LogoController@getLogo']);

		Route::post('shipping/upload', ['uses' => 'OrderController@uploadAjax', 'middleware' => ['permission:vendor']]);

		// -------------- Product related
		Route::group(['prefix' => 'product'], function() {
			Route::get('ajax', ['uses' => 'ProductController@getProductAjax', 'middleware' => ['permission:vendor']]);
		});

		Route::group(['prefix' => 'accounting'], function() {
			Route::group(['prefix' => 'tradable'], function() {
				Route::group(['prefix' => 'transactions'], function() {
					Route::get('ajax', ['uses' => 'ProductController@transactionAjax', 'middleware' => ['permission:vendor']]);
				});
			});
		});

		Route::get('taccount/retrieve', ['uses' => 'TransactionController@retrieveAjax', 'middleware' => ['permission:vendor']]);

	});
});

Route::group(['domain' => env('CUSTOMER_DOMAIN'), 'namespace' => 'Customer', 'as' => 'customer.'], function() {

	Route::group(['middleware' => ['auth']], function() {

		// get company logo
		// Route::get('company-logo', ['uses' => 'LogoController@getLogo']);

		Route::get('taccount/retrieve', ['uses' => 'TransactionController@retrieveAjax', 'middleware' => ['permission:client']]);

	});
});

Route::group(['domain' => env('TENANT_DOMAIN'), 'namespace' => 'Tenant', 'as' => 'tenant.'], function() {

	Route::group(['middleware' => ['auth']], function() {

		// get company logo
		// Route::get('company-logo', ['uses' => 'LogoController@getLogo']);

		// -------------- Document related web-ajax
		Route::group(['prefix' => 'document'], function() {
			Route::get('ajax', ['uses' => 'DocumentController@getDocumentAjax', 'middleware' => ['permission:client']]);
			Route::group(['prefix' => '{id}'], function() {
				Route::get('ajax', ['uses' => 'DocumentController@view', 'middleware' => ['permission:client']]);
			});
		});

		// -------------- Request related web-ajax
		Route::group(['prefix' => 'interaction'], function() {
			Route::get('ajax', ['uses' => 'RequestController@getDashboardInteractionAjax', 'middleware' => ['permission:client']]);
			Route::post('create', ['uses' => 'RequestController@createRequestAjax']);
			Route::post('update/{id}', ['uses' => 'RequestController@addInfoToRequestAjax']);
			Route::group(['prefix' => '{id}'], function() {
				Route::get('ajax', ['uses' => 'RequestController@loadInteractionAjax', 'middleware' => ['permission:client']]);
			});
		});

		// -------------- file-download related web-ajax
		Route::group(['prefix' => 'file'], function() {
			Route::get('download/{hash}', ['uses' => 'FileController@download', 'middleware' => ['permission:client']]);
		});

		Route::get('taccount/retrieve', ['uses' => 'TransactionController@retrieveAjax', 'middleware' => ['permission:client']]);

		Route::get('invoice/prepare-ach-transfer', ['uses' => 'InvoiceController@prepareAchTransferAjax', 'middleware' => ['permission:client']]);

		Route::get('invoice/{transactable_id}/agreement', ['uses' => 'InvoiceController@getAchAgreementAjax', 'middleware' => ['permission:client']]);

		Route::post('invoice/process-ach-tranfer', ['uses' => 'InvoiceController@processAchTransferAjax', 'middleware' => ['permission:client']]);

	});
});
