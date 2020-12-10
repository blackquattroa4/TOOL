<?php

use Illuminate\Database\Seeder;

use App\ChartAccount;
use App\Tradable;
use App\TaxableEntity;
use App\UniqueTradable;
use App\UniqueTradableRestriction;

class TradablesTableSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{

		$tradableItems = [
			[
				'account' => null,
				'stockable' => true,
				'expendable' => false,
				'mnemonic' => 'unknown product',
				'name' => 'Unknown Product',
				'description' => 'Unknown product'
			],
			[
				'account' => null,
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'A/P transfer',
				'name' => 'A/P transfer',
				'description' => 'A/P transfer'
			],
			[
				'account' => '60100',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Advertising',
				'name' => 'Expense - Advertising',
				'description' => 'Advertising expense'
			],
			[
				'account' => '61000',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Auto(maintenance)',
				'name' => 'Expense - Auto(maintenance)',
				'description' => 'Auto(maintenance) expense'
			],
			[
				'account' => '61010',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Auto(gasoline)',
				'name' => 'Expense - Auto(gasoline)',
				'description' => 'Auto(gasoline) expense'
			],
			[
				'account' => '61020',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Auto(registration)',
				'name' => 'Expense - Auto(registration)',
				'description' => 'Auto(registration) expense'
			],
			[
				'account' => '61500',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Bad Debt',
				'name' => 'Expense - Bad Debt',
				'description' => 'Bad Debt expense'
			],
			[
				'account' => '62000',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Bank Charges',
				'name' => 'Expense - Bank Charges',
				'description' => 'Bank Charges expense'
			],
			[
				'account' => '62100',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Credit Card',
				'name' => 'Expense - Credit Card',
				'description' => 'Credit Card expense'
			],
			[
				'account' => '63000',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Commission',
				'name' => 'Expense - Commission',
				'description' => 'Commission expense'
			],
			[
				'account' => '63500',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Depreciation',
				'name' => 'Expense - Depreciation',
				'description' => 'Depreciation expense'
			],
			[
				'account' => '64000',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Employee(salary)',
				'name' => 'Expense - Employee(salary)',
				'description' => 'Employee(salary) expense'
			],
			[
				'account' => '64010',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Employee(bonus)',
				'name' => 'Expense - Employee(bonus)',
				'description' => 'Employee(bonus) expense'
			],
			[
				'account' => '65500',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Freight-in',
				'name' => 'Expense - Freight-in',
				'description' => 'Freight-in expense'
			],
			[
				'account' => '65510',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Freight-out',
				'name' => 'Expense - Freight-out',
				'description' => 'Freight-out expense'
			],
			[
				'account' => '66000',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Gifts',
				'name' => 'Expense - Gifts',
				'description' => 'Gifts expense'
			],
			[
				'account' => '66100',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Donation',
				'name' => 'Expense - Donation',
				'description' => 'Donation expense'
			],
			[
				'account' => '66500',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'General/Liability Insurance',
				'name' => 'Expense - General/Liability Insurance',
				'description' => 'General/Liability Insurance expense'
			],
			[
				'account' => '66510',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Health Insurance',
				'name' => 'Expense - Health Insurance',
				'description' => 'Health Insurance expense'
			],
			[
				'account' => '66520',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Vehicle Insurance',
				'name' => 'Expense - Vehicle Insurance',
				'description' => 'Vehicle Insurance expense'
			],
			[
				'account' => '66530',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Other Insurance',
				'name' => 'Expense - Other Insurance',
				'description' => 'Other Insurance expense'
			],
			[
				'account' => '67000',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Interest/Finance',
				'name' => 'Expense - Interest/Finance',
				'description' => 'Interest/Finance expense'
			],
			[
				'account' => '67500',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Professional(CPA)',
				'name' => 'Expense - Professional(CPA)',
				'description' => 'Professional(CPA) expense'
			],
			[
				'account' => '67510',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Professional(administration)',
				'name' => 'Expense - Professional(administration)',
				'description' => 'Professional(administration) expense'
			],
			[
				'account' => '67520',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Professional(legal)',
				'name' => 'Expense - Professional(legal)',
				'description' => 'Professional(legal) expense'
			],
			[
				'account' => '67530',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Professional(business & consulting)',
				'name' => 'Expense - Professional(business & consulting)',
				'description' => 'Professional(business & consulting) expense'
			],
			[
				'account' => '68000',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Registration/Permit/License',
				'name' => 'Expense - Registration/Permit/License',
				'description' => 'Registration/Permit/License expense'
			],
			[
				'account' => '68500',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Maintenance',
				'name' => 'Expense - Maintenance',
				'description' => 'Maintenance expense'
			],
			[
				'account' => '69000',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Meals & Entertainment',
				'name' => 'Expense - Meals & Entertainment',
				'description' => 'Meals & Entertainment expense'
			],
			[
				'account' => '69100',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Travel(lodging)',
				'name' => 'Expense - Travel(lodging)',
				'description' => 'Travel(lodging) expense'
			],
			[
				'account' => '69110',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Travel(airfare)',
				'name' => 'Expense - Travel(airfare)',
				'description' => 'Travel(airfare) expense'
			],
			[
				'account' => '69120',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Travel(ground-transportation)',
				'name' => 'Expense - Travel(ground-transportation)',
				'description' => 'Travel(ground-transportation) expense'
			],
			[
				'account' => '69900',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Office',
				'name' => 'Expense - Office',
				'description' => 'Office expense'
			],
			[
				'account' => '69910',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Janitorial',
				'name' => 'Expense - Janitorial',
				'description' => 'Janitorial expense'
			],
			[
				'account' => '70000',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Payroll Taxes',
				'name' => 'Expense - Payroll Taxes',
				'description' => 'Payroll Taxes expense'
			],
			[
				'account' => '70500',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Printing & Material',
				'name' => 'Expense - Printing & Material',
				'description' => 'Printing & Material expense'
			],
			[
				'account' => '71500',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Postage',
				'name' => 'Expense - Postage',
				'description' => 'Postage expense'
			],
			[
				'account' => '72000',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Rent/Lease',
				'name' => 'Expense - Rent/Lease',
				'description' => 'Rent/Lease expense'
			],
			[
				'account' => '75000',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Utilities(water)',
				'name' => 'Expense - Utilities(water)',
				'description' => 'Utilities(water) expense'
			],
			[
				'account' => '75010',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Utilities(electricity)',
				'name' => 'Expense - Utilities(electricity)',
				'description' => 'Utilities(electricity) expense'
			],
			[
				'account' => '75030',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Utilities(others)',
				'name' => 'Expense - Utilities(others)',
				'description' => 'Utilities(others) expense'
			],
			[
				'account' => '75100',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Telephone',
				'name' => 'Expense - Telephone',
				'description' => 'Telephone expense'
			],
			[
				'account' => '75200',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Internet',
				'name' => 'Expense - Internet',
				'description' => 'Internet expense'
			],
			[
				'account' => '79000',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Fines/Penalty',
				'name' => 'Expense - Fines/Penalty',
				'description' => 'Fines/Penalty expense'
			],
			[
				'account' => '79990',
				'stockable' => false,
				'expendable' => true,
				'mnemonic' => 'Unclassified',
				'name' => 'Expense - Unclassified',
				'description' => 'Unclassified expense'
			],
		];

		foreach ($tradableItems as $entry) {
			// create expense item
			$cogsAccount = ChartAccount::create([	// cost of good sold
					'account' => '50000',
					'type' => 'cogs',
					'currency_id' => 1,
					'description' => 'cost-of-good-sold ' . $entry['mnemonic'] . ' (useless)',
					'active' => 1,
				]);
			$expenseAccount = $entry['account'] ?
												ChartAccount::create([
													'account' => $entry['account'],
													'type' => 'expense',
													'currency_id' => 1,
													'description' => $entry['description'],
													'active' => 1,
												]) :
												ChartAccount::where('type', 'unknown')->first();
			$uniqueTradable = UniqueTradable::create([
				'sku' => $entry['name'],
				'description' => $entry['description'],
				'product_id' => '',
				'current' => 1,
				'phasing_out' => 0,
				'stockable' => $entry['stockable'],
				'expendable' => $entry['expendable'],
				'forecastable' => 0,
				'replacing_unique_tradable_id' => -1,
				'replaced_by_unique_tradable_id' => -1,
				'expense_t_account_id' => $expenseAccount->id,
				'cogs_t_account_id' => $cogsAccount->id,
			]);
			$cogsAccount->update([
					'account' => '5' . sprintf('%04u', $uniqueTradable->id),
				]);
			UniqueTradableRestriction::create([
				'unique_tradable_id' => $uniqueTradable->id,
				'action' => 'exclude',
				'associated_attribute' => 'entity',
				'associated_id' => 0,
				'enforce' => 1,
			]);
			$tradable = Tradable::create([
				'unique_tradable_id' => $uniqueTradable->id,
				'serial_pattern' => '',
				'supplier_entity_id' => TaxableEntity::theCompany()->id,
				'unit_weight' => 0,
				'unit_length' => 0,
				'unit_width' => 0,
				'unit_height' => 0,
				'unit_per_carton' => 0,
				'carton_weight' => 0,
				'carton_length' => 0,
				'carton_width' => 0,
				'carton_height' => 0,
				'carton_per_pallet' => 0,
				'lead_days' => 0,
				'content' => '',
				'manufacture_origin' => 'US',
				'current' => 1,
			]);
		}
	}
}
