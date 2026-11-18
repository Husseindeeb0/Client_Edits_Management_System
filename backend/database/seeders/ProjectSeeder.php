<?php

namespace Database\Seeders;

use App\Features\Companies\Models\Company;
use App\Features\Projects\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Company name => its projects
     */
    private const PROJECTS = [
        'Khalil Bakery' => ['Website redesign', 'Instagram launch campaign'],
        'Cedar Coffee House' => ['Menu boards'],
        'Mansour Designs' => ['Portfolio website', 'Brand guidelines'],
        'Fares Auto Parts' => ['Product catalog'],
        'Fares Car Rental' => ['Booking landing page'],
        'Nassar Dental Clinic' => ['Clinic brochure', 'Appointment reminder emails'],
        'Haddad Group' => ['Annual report 2026', 'Corporate website'],
        'Haddad Real Estate' => ['Property listing photos'],
        'Haddad Logistics' => ['Fleet branding'],
        'Saleh Fashion' => ['Summer collection lookbook', 'Online store banners'],
        'Aoun Fitness' => ['Promo video', 'Membership flyers'],
        'Chamoun Studio' => ['Logo refresh'],
        'Bloom Florist' => ['Wedding season ads'],
        'Sabbagh Law Firm' => ['Firm profile document'],
        'Ghanem & Co' => ['Investor pitch deck'],
        'Daher Electronics' => ['Black Friday campaign', 'Product unboxing video'],
    ];

    public function run(): void
    {
        foreach (self::PROJECTS as $companyName => $projectNames) {
            $company = Company::where('name', $companyName)->firstOrFail();

            foreach ($projectNames as $projectName) {
                Project::firstOrCreate(['company_id' => $company->id, 'name' => $projectName]);
            }
        }
    }
}
