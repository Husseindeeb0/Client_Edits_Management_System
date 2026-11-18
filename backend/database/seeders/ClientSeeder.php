<?php

namespace Database\Seeders;

use App\Features\Clients\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * [name, phone number, email, last follow-up (days ago)]
     */
    private const CLIENTS = [
        ['Ahmad Khalil', '+96170123456', 'ahmad.khalil@example.com', 3],
        ['Sara Mansour', '+96171234567', 'sara@mansourdesigns.example', 10],
        ['Omar Fares', '+96176345678', null, null],
        ['Maya Nassar', '+96178456789', 'maya.nassar@example.com', 1],
        ['Karim Haddad', '+96103567890', 'karim@haddadgroup.example', 20],
        ['Nour Saleh', '+96170678901', null, 5],
        ['Rami Aoun', '+96171789012', 'rami.aoun@example.com', null],
        ['Lea Chamoun', '+96176890123', 'lea@chamounstudio.example', 14],
        ['Hadi Sabbagh', '+96178901234', 'hadi.sabbagh@example.com', 30],
        ['Rana Ghanem', '+96170012345', 'rana@ghanemco.example', 2],
        ['Tarek Daher', '+96181123456', null, 7],
        ['Lina Haddad', '+96171098765', 'lina.haddad@example.com', null], // has no companies yet
    ];

    public function run(): void
    {
        foreach (self::CLIENTS as [$name, $phone, $email, $followUpDaysAgo]) {
            Client::firstOrCreate(['name' => $name], [
                'phone_number' => $phone,
                'email' => $email,
                'last_follow_up_date' => $followUpDaysAgo === null ? null : now()->subDays($followUpDaysAgo)->toDateString(),
            ]);
        }
    }
}
