<?php

namespace Database\Seeders;

use App\Features\Clients\Models\Client;
use App\Features\Companies\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Client name => its companies
     */
    private const COMPANIES = [
        'Ahmad Khalil' => ['Khalil Bakery', 'Cedar Coffee House'],
        'Sara Mansour' => ['Mansour Designs'],
        'Omar Fares' => ['Fares Auto Parts', 'Fares Car Rental'],
        'Maya Nassar' => ['Nassar Dental Clinic'],
        'Karim Haddad' => ['Haddad Group', 'Haddad Real Estate', 'Haddad Logistics'],
        'Nour Saleh' => ['Saleh Fashion'],
        'Rami Aoun' => ['Aoun Fitness'],
        'Lea Chamoun' => ['Chamoun Studio', 'Bloom Florist'],
        'Hadi Sabbagh' => ['Sabbagh Law Firm'],
        'Rana Ghanem' => ['Ghanem & Co'],
        'Tarek Daher' => ['Daher Electronics'],
    ];

    public function run(): void
    {
        foreach (self::COMPANIES as $clientName => $companyNames) {
            $client = Client::where('name', $clientName)->firstOrFail();

            foreach ($companyNames as $companyName) {
                Company::firstOrCreate(['client_id' => $client->id, 'name' => $companyName]);
            }
        }
    }
}
