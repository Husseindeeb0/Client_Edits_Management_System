<?php

namespace Database\Seeders;

use App\Features\Edits\Models\Edit;
use App\Features\Projects\Models\Project;
use Illuminate\Database\Seeder;

class EditSeeder extends Seeder
{
    /**
     * Project name => its edits:
     * [title, priority, status, price, is paid, due in (days, negative = past), description]
     */
    private const EDITS = [
        'Website redesign' => [
            ['Update homepage hero images', 'High', 'In Progress', 250, false, 5, 'Replace the hero banner with the new seasonal photos.'],
            ['Fix mobile menu spacing', 'Medium', 'Done', 80, true, -3, null],
            ['Add online order button', 'High', 'Pending', 120, false, 10, 'Link the button to the new ordering page.'],
        ],
        'Instagram launch campaign' => [
            ['Design 6 launch posts', 'High', 'Confirmed', 300, false, 7, null],
            ['Retouch product photos', 'Medium', 'Done', 150, true, -10, null],
        ],
        'Menu boards' => [
            ['New winter drinks board', 'Medium', 'Done', 90, true, -15, null],
            ['Price update on breakfast menu', 'Low', 'Done', 40, true, -20, null],
        ],
        'Portfolio website' => [
            ['Add 2026 case studies', 'Medium', 'In Progress', 200, false, 12, 'Three new case studies with before and after images.'],
            ['Compress gallery images', 'Low', 'Pending', 60, false, 14, null],
        ],
        'Brand guidelines' => [
            ['Color palette page', 'Low', 'Done', 100, true, -7, null],
            ['Typography examples', 'Low', 'Cancelled', 80, false, null, 'The client decided to keep the old fonts.'],
        ],
        'Product catalog' => [
            ['Catalog cover design', 'High', 'Confirmed', 180, false, 4, null],
            ['Retouch 40 product photos', 'Medium', 'In Progress', 400, false, 9, null],
        ],
        'Booking landing page' => [
            ['Change call-to-action text', 'Low', 'Done', 30, true, -2, null],
            ['Add price table', 'Medium', 'Pending', 110, false, 6, null],
        ],
        'Clinic brochure' => [
            ['Replace team photo', 'Medium', 'Done', 70, true, -12, null],
            ['Translate brochure to French', 'High', 'In Progress', 220, false, 3, 'French version for the new branch.'],
        ],
        'Appointment reminder emails' => [
            ['Email header banner', 'Low', 'Pending', 50, false, 15, null],
        ],
        'Annual report 2026' => [
            ['Charts for the financial section', 'High', 'In Progress', 500, false, 8, 'Six charts based on the Q4 numbers.'],
            ['Cover page options', 'Medium', 'Confirmed', 250, false, 11, null],
            ['Proofreading corrections', 'Low', 'Pending', 120, false, 18, null],
        ],
        'Corporate website' => [
            ['Leadership team page', 'Medium', 'Done', 160, true, -25, null],
            ['Careers page banner', 'Low', 'Cancelled', 60, false, null, null],
        ],
        'Property listing photos' => [
            ['Sky replacement on 15 photos', 'Medium', 'Done', 225, false, -5, null],
            ['Virtual staging for 3 rooms', 'High', 'In Progress', 450, false, 6, null],
        ],
        'Fleet branding' => [
            ['Truck side wrap mockup', 'High', 'Confirmed', 350, false, 13, null],
        ],
        'Summer collection lookbook' => [
            ['Color correction on 30 photos', 'High', 'In Progress', 300, false, 4, null],
            ['Lookbook layout', 'Medium', 'Pending', 400, false, 16, null],
        ],
        'Online store banners' => [
            ['Sale banner 30% off', 'High', 'Done', 90, true, -4, null],
            ['Homepage slider images', 'Medium', 'Done', 140, false, -1, null],
        ],
        'Promo video' => [
            ['Cut a 30-second version', 'High', 'In Progress', 350, false, 5, 'Short version for Instagram reels.'],
            ['Add subtitles', 'Medium', 'Pending', 100, false, 9, null],
        ],
        'Membership flyers' => [
            ['A5 flyer front and back', 'Low', 'Done', 75, true, -18, null],
        ],
        'Logo refresh' => [
            ['Three logo concepts', 'High', 'Confirmed', 600, false, 10, null],
            ['Favicon export', 'Low', 'Pending', 30, false, 20, null],
        ],
        'Wedding season ads' => [
            ['Facebook ad set', 'Medium', 'Done', 180, true, -9, null],
            ['Print ad for a magazine', 'Medium', 'Cancelled', 200, false, null, null],
        ],
        'Firm profile document' => [
            ['Partner bios layout', 'Low', 'Done', 150, true, -30, null],
            ['Update office photos', 'Low', 'Done', 120, true, -28, null],
        ],
        'Investor pitch deck' => [
            ['Redesign 20 slides', 'High', 'In Progress', 700, false, 3, 'Use the new brand colors on every slide.'],
            ['Market size infographic', 'Medium', 'Pending', 150, false, 7, null],
        ],
        'Black Friday campaign' => [
            ['Countdown banner animation', 'High', 'Confirmed', 220, false, 12, null],
            ['Email newsletter design', 'Medium', 'Pending', 130, false, 14, null],
        ],
        'Product unboxing video' => [
            ['Color grading', 'Medium', 'Done', 260, true, -6, null],
            ['Background music swap', 'Low', 'Done', 40, false, -6, null],
        ],
    ];

    public function run(): void
    {
        foreach (self::EDITS as $projectName => $edits) {
            $project = Project::where('name', $projectName)->firstOrFail();

            foreach ($edits as [$title, $priority, $status, $price, $isPaid, $dueInDays, $description]) {
                Edit::firstOrCreate(['project_id' => $project->id, 'title' => $title], [
                    'description' => $description,
                    'priority' => $priority,
                    'status' => $status,
                    'price' => $price,
                    'is_paid' => $isPaid,
                    'due_date' => $dueInDays === null ? null : now()->addDays($dueInDays)->toDateString(),
                ]);
            }
        }
    }
}
