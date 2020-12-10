<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(MeasurementsTableSeeder::class);
        $this->call(CurrenciesTableSeeder::class);
        $this->call(PaymentTermsTableSeeder::class);
        $this->call(PermissionTableSeeder::class);
        $this->call(TaxableEntitiesTableSeeder::class);
        $this->call(ParametersTableSeeder::class);
        $this->call(TradablesTableSeeder::class);
    }
}
