<?php

namespace Database\Seeders;

use App\Models\Billing;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Créer 10 utilisateurs
        User::factory(10)->create();

        // Créer 17 entreprises
        $companies = Company::factory(17)->create();

        // Créer 2 à 17 factures par entreprise
        foreach ($companies as $company) {
            $billingCount = fake()->numberBetween(2, 17);
            Billing::factory($billingCount)->create([
                'company_id' => $company->id,
            ]);
        }
    }
}
