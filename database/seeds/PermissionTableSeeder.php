<?php

use Illuminate\Database\Seeder;
use App\Permission;
use App\Role;

class PermissionTableSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$permission = [
			// role management
			[
				'name' => 'role-list',
				'display_name' => 'Display role listing',
				'description' => 'See only listing of role'
			],
			[
				'name' => 'role-view',
				'display_name' => 'View role',
				'description' => 'View role detail'
			],
			[
				'name' => 'role-create',
				'display_name' => 'Create role',
				'description' => 'Create new role'
			],
			[
				'name' => 'role-edit',
				'display_name' => 'Edit role',
				'description' => 'Edit role'
			],
			/*[
				'name' => 'role-delete',
				'display_name' => 'Delete role',
				'description' => 'Delete role'
			],*/
			// user management
			[
				'name' => 'user-list',
				'display_name' => 'Display user listing',
				'description' => 'See only listing of user'
			],
			[
				'name' => 'user-view',
				'display_name' => 'View user',
				'description' => 'View user detail'
			],
			[
				'name' => 'user-create',
				'display_name' => 'Create user',
				'description' => 'Create new user'
			],
			[
				'name' => 'user-edit',
				'display_name' => 'Edit user',
				'description' => 'Edit user'
			],
			/*[
				'name' => 'user-delete',
				'display_name' => 'Delete user',
				'description' => 'Delete user'
			],*/
			[
				'name' => 'db-backup',
				'display_name' => 'Backup database',
				'description' => 'Backup database'
			],
			// supplier management
			[
				'name' => 'supplier-list',
				'display_name' => 'Display supplier listing',
				'description' => 'See only listing of supplier'
			],
			[
				'name' => 'supplier-report',
				'display_name' => 'Display supplier detail report',
				'description' => 'Display comprehensive report/analysis of supplier'
			],
			[
				'name' => 'supplier-create',
				'display_name' => 'Create supplier',
				'description' => 'Create new supplier'
			],
			[
				'name' => 'supplier-edit',
				'display_name' => 'Edit supplier',
				'description' => 'Edit supplier'
			],
			/*[
				'name' => 'supplier-delete',
				'display_name' => 'Delete supplier',
				'description' => 'Delete supplier'
			],*/
			// customer management
			[
				'name' => 'customer-list',
				'display_name' => 'Display customer listing',
				'description' => 'See only listing of customer'
			],
			[
				'name' => 'customer-report',
				'display_name' => 'Display customer detail report',
				'description' => 'Display comprehensive report/analysis of customer'
			],
			[
				'name' => 'customer-create',
				'display_name' => 'Create customer',
				'description' => 'Create new customer'
			],
			[
				'name' => 'customer-edit',
				'display_name' => 'Edit customer',
				'description' => 'Edit customer'
			],
			/*[
				'name' => 'customer-delete',
				'display_name' => 'Delete customer',
				'description' => 'Delete customer'
			],*/
			// product
			[
				'name' => 'pd-list',
				'display_name' => 'Display product listing',
				'description' => 'See only listing of product'
			],
			[
				'name' => 'pd-view',
				'display_name' => 'View product',
				'description' => 'View product detail'
			],
			[
				'name' => 'pd-create',
				'display_name' => 'Create product',
				'description' => 'Create new product'
			],
			[
				'name' => 'pd-edit',
				'display_name' => 'Edit product',
				'description' => 'Edit product'
			],
			// purchase quote
			[
				'name' => 'pq-list',
				'display_name' => 'Display purchase quote listing',
				'description' => 'See only listing of purchase quote'
			],
			[
				'name' => 'pq-view',
				'display_name' => 'View purchase quote',
				'description' => 'View purchase quote detail'
			],
			[
				'name' => 'pq-create',
				'display_name' => 'Create purchase quote',
				'description' => 'Create new purchase quote'
			],
			[
				'name' => 'pq-edit',
				'display_name' => 'Edit purchase quote',
				'description' => 'Edit purchase quote'
			],
			[
				'name' => 'pq-process',
				'display_name' => 'Process purchase quote',
				'description' => 'Process purchase quote'
			],
			// purchase order
			[
				'name' => 'po-list',
				'display_name' => 'Display purchase order listing',
				'description' => 'See only listing of purchase order'
			],
			[
				'name' => 'po-view',
				'display_name' => 'View purchase order',
				'description' => 'View purchase order detail'
			],
			[
				'name' => 'po-create',
				'display_name' => 'Create purchase order',
				'description' => 'Create new purchase order'
			],
			[
				'name' => 'po-edit',
				'display_name' => 'Edit purchase order',
				'description' => 'Edit purchase order'
			],
			[
				'name' => 'po-process',
				'display_name' => 'Process purchase order',
				'description' => 'Process purchase order'
			],
			// purchase return
			[
				'name' => 'pr-list',
				'display_name' => 'Display purchase return listing',
				'description' => 'See only listing of purchase return'
			],
			[
				'name' => 'pr-view',
				'display_name' => 'View purchase return',
				'description' => 'View purchase return detail'
			],
			[
				'name' => 'pr-create',
				'display_name' => 'Create purchase return',
				'description' => 'Create new purchase return'
			],
			[
				'name' => 'pr-edit',
				'display_name' => 'Edit purchase return',
				'description' => 'Edit purchase return'
			],
			[
				'name' => 'pr-process',
				'display_name' => 'Process purchase return',
				'description' => 'Process purchase return'
			],
			// sales quote
			[
				'name' => 'sq-list',
				'display_name' => 'Display sales quote listing',
				'description' => 'See only listing of sales quote'
			],
			[
				'name' => 'sq-view',
				'display_name' => 'View sales quote',
				'description' => 'View sales quote detail'
			],
			[
				'name' => 'sq-create',
				'display_name' => 'Create sales quote',
				'description' => 'Create new sales quote'
			],
			[
				'name' => 'sq-edit',
				'display_name' => 'Edit sales quote',
				'description' => 'Edit sales quote'
			],
			[
				'name' => 'sq-process',
				'display_name' => 'Process sales quote',
				'description' => 'Process sales quote'
			],
			// sales order
			[
				'name' => 'so-list',
				'display_name' => 'Display sales order listing',
				'description' => 'See only listing of sales order'
			],
			[
				'name' => 'so-view',
				'display_name' => 'View sales order',
				'description' => 'View sales order detail'
			],
			[
				'name' => 'so-create',
				'display_name' => 'Create sales order',
				'description' => 'Create new sales order'
			],
			[
				'name' => 'so-edit',
				'display_name' => 'Edit sales order',
				'description' => 'Edit sales order'
			],
			[
				'name' => 'so-process',
				'display_name' => 'Process sales order',
				'description' => 'Process sales order'
			],
			// sales return
			[
				'name' => 'sr-list',
				'display_name' => 'Display sales return listing',
				'description' => 'See only listing of sales return'
			],
			[
				'name' => 'sr-view',
				'display_name' => 'View sales return',
				'description' => 'View sales return detail'
			],
			[
				'name' => 'sr-create',
				'display_name' => 'Create sales return',
				'description' => 'Create new sales return'
			],
			[
				'name' => 'sr-edit',
				'display_name' => 'Edit sales return',
				'description' => 'Edit sales return'
			],
			[
				'name' => 'sr-process',
				'display_name' => 'Process sales return',
				'description' => 'Process sales return'
			],
			// warehouse order
			[
				'name' => 'wo-list',
				'display_name' => 'Display warehouse order listing',
				'description' => 'See only listing of warehouse order'
			],
			[
				'name' => 'wo-view',
				'display_name' => 'View warehouse order',
				'description' => 'View warehouse detail'
			],
			[
				'name' => 'wo-create',
				'display_name' => 'Create warehouse order',
				'description' => 'Create new warehouse order'
			],
			[
				'name' => 'wo-edit',
				'display_name' => 'Edit warehouse order',
				'description' => 'Edit warehouse order'
			],
			[
				'name' => 'wo-process',
				'display_name' => 'Process warehouse order',
				'description' => 'Process warehouse order'
			],
			// account receivable (invoice) management
			[
				'name' => 'ar-list',
				'display_name' => 'Display account receivable(invoice) listing',
				'description' => 'See only listing of account receivable(invoice)'
			],
			[
				'name' => 'ar-view',
				'display_name' => 'View account receivable(invoice)',
				'description' => 'View account receivable(invoice) detail'
			],
			[
				'name' => 'ar-create',
				'display_name' => 'Create account receivable(invoice)',
				'description' => 'Create new account receivable(invoice)'
			],
			[
				'name' => 'ar-edit',
				'display_name' => 'Edit account receivable(invoice)',
				'description' => 'Edit account receivable(invoice)'
			],
			[
				'name' => 'ar-process',
				'display_name' => 'Process account receivable(invoice)',
				'description' => 'Process account receivable(invoice)'
			],
			// reverse account receivable (credit) management
			[
				'name' => 'rar-list',
				'display_name' => 'Display account receivable(credit) listing',
				'description' => 'See only listing of account receivable(credit)'
			],
			[
				'name' => 'rar-view',
				'display_name' => 'View account receivable(credit)',
				'description' => 'View account receivable(credit) detail'
			],
			[
				'name' => 'rar-create',
				'display_name' => 'Create account receivable(credit)',
				'description' => 'Create new account receivable(credit)'
			],
			[
				'name' => 'rar-edit',
				'display_name' => 'Edit account receivable(credit)',
				'description' => 'Edit account receivable(credit)'
			],
			[
				'name' => 'rar-process',
				'display_name' => 'Process account receivable(credit)',
				'description' => 'Process account receivable(credit)'
			],
			// account payable (purchase invoice) management
			[
				'name' => 'ap-list',
				'display_name' => 'Display account payable(purchase invoice) listing',
				'description' => 'See only listing of account payable(purchase invoice)'
			],
			[
				'name' => 'ap-view',
				'display_name' => 'View account payable(purchase invoice)',
				'description' => 'View account payable(purchase invoice) detail'
			],
			[
				'name' => 'ap-create',
				'display_name' => 'Create account payable(purchase invoice)',
				'description' => 'Create new account payable(purchase invoice)'
			],
			[
				'name' => 'ap-edit',
				'display_name' => 'Edit account payable(purchase invoice)',
				'description' => 'Edit account payable(purchase invoice)'
			],
			[
				'name' => 'ap-process',
				'display_name' => 'Process account payable(purchase invoice)',
				'description' => 'Process account payable(purchase invoice)'
			],
			// reverse account payable (purchase credit) management
			[
				'name' => 'rap-list',
				'display_name' => 'Display account payable(purchase credit) listing',
				'description' => 'See only listing of account payable(purchase credit)'
			],
		 	[
				'name' => 'rap-view',
				'display_name' => 'View account payable(purchase credit)',
				'description' => 'View account payable(purchase credit) detail'
			],
			[
				'name' => 'rap-create',
				'display_name' => 'Create account payable(purchase credit)',
				'description' => 'Create new account payable(purchase credit)'
			],
			[
				'name' => 'rap-edit',
				'display_name' => 'Edit account payable(purchase credit)',
				'description' => 'Edit account payable(purchase credit)'
			],
			[
				'name' => 'rap-process',
				'display_name' => 'Process account payable(purchase credit)',
				'description' => 'Process account payable(purchase credit)'
			],
			// RMA
			[
				'name' => 'rma-list',
				'display_name' => 'Display RMA listing',
				'description' => 'See only listing of RMA'
			],
			[
				'name' => 'rma-view',
				'display_name' => 'View RMA',
				'description' => 'View RMA detail'
			],
			[
				'name' => 'rma-create',
				'display_name' => 'Create RMA',
				'description' => 'Create new RMA'
			],
			[
				'name' => 'rma-edit',
				'display_name' => 'Edit RMA',
				'description' => 'Edit RMA'
			],
			[
				'name' => 'rma-process',
				'display_name' => 'Process RMA',
				'description' => 'Process RMA'
			],
			// expense / charges
			[
				'name' => 'ex-list',
				'display_name' => 'Display expense listing',
				'description' => 'See only listing of expense'
			],
			[
				'name' => 'ex-view',
				'display_name' => 'View expense',
				'description' => 'View expense detail'
			],
			[
				'name' => 'ex-create',
				'display_name' => 'Create expense',
				'description' => 'Create new expense'
			],
			[
				'name' => 'ex-edit',
				'display_name' => 'Edit expense',
				'description' => 'Edit expense'
			],
			[
				'name' => 'ex-process',
				'display_name' => 'Process expense',
				'description' => 'Process expense'
			],
			[
				'name' => 'sy-list',
				'display_name' => 'List system parameters',
				'description' => 'List system parameters'
			],
			[
				'name' => 'sy-edit',
				'display_name' => 'Edit system parameters',
				'description' => 'Edit system parameters'
			],
			[
				'name' => 'vendor',
				'display_name' => 'Vendor-portal permission',
				'description' => 'Vendor-portal permission'
			],
			[
				'name' => 'client',
				'display_name' => 'Customer-portal permission',
				'description' => 'Customer-portal permission'
			],
			[
				'name' => 'acct-list',
				'display_name' => 'T-account listing',
				'description' => 'T-account listing'
			],
			[
				'name' => 'acct-view',
				'display_name' => 'T-account view',
				'description' => 'T-account view'
			],
			[
				'name' => 'acct-edit',
				'display_name' => 'T-account edit',
				'description' => 'T-account edit'
			],
			[
				'name' => 'hr-list',
				'display_name' => 'HR listing',
				'description' => 'HR listing'
			],
			[
				'name' => 'hr-view',
				'display_name' => 'HR view',
				'description' => 'HR view'
			],
			[
				'name' => 'hr-edit',
				'display_name' => 'HR edit',
				'description' => 'HR edit'
			],
			[
				'name' => 'iv-manage',
				'display_name' => 'Investment management',
				'description' => 'Investment management'
			],
			[
				'name' => 'iv-view',
				'display_name' => 'Investment view',
				'description' => 'Investment view'
			]
		];

		$result = array();
		foreach ($permission as $key => $value) {
			$idx = Permission::create($value);
			$result[] = $idx->id;
		}

		$role = Role::create([
				'name' => 'administrator',
				'display_name' => 'System administrator',
				'description' => 'System administrator',
			]);

		foreach ($result as $onePermissionId) {
			DB::table("permission_role")->insert([
					'permission_id' => $onePermissionId,
					'role_id' => $role->id,
				]);
		}
	}
}
