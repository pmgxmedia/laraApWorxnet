<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\ContentPost;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the WorxNet marketplace with foundational reference data
     * and representative South African demo records.
     */
    public function run(): void
    {
        $categories = $this->seedServiceCategories();

        $admin = User::updateOrCreate(
            ['email' => 'admin@worxnet.co.za'],
            [
                'name' => 'WorxNet Admin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'phone' => '+27 10 000 0000',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $customer = User::updateOrCreate(
            ['email' => 'customer@worxnet.co.za'],
            [
                'name' => 'Thabo Nkosi',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'phone' => '+27 82 000 0001',
                'preferred_area' => 'Sandton, Gauteng',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $this->seedServiceRequests($customer, $categories);
        $this->seedContent($admin, $categories);

        AuditLog::record($admin->id, 'database.seeded', null, 'WorxNet reference data seeded.');
    }

    private function seedServiceCategories(): array
    {
        $definitions = [
            ['name' => 'CCTV & Surveillance', 'slug' => 'cctv', 'icon' => 'ðŸ“¹', 'supports_emergency' => true,
                'description' => 'Cameras, DVR/NVR, remote viewing, night vision, and monitoring setup.'],
            ['name' => 'Alarm Systems', 'slug' => 'alarms', 'icon' => 'ðŸš¨', 'supports_emergency' => true,
                'description' => 'Intruder alarms, electric fencing, beams, and armed-response integration.'],
            ['name' => 'Access Control', 'slug' => 'access', 'icon' => 'ðŸ”', 'supports_emergency' => true,
                'description' => 'Biometrics, intercoms, gate motors, visitor management, keypads.'],
            ['name' => 'NDI & AV Systems', 'slug' => 'ndi', 'icon' => 'ðŸŽ›ï¸', 'supports_emergency' => false,
                'description' => 'Video-over-IP, streaming, conferencing, broadcast and production setups.'],
            ['name' => 'Smart Home Automation', 'slug' => 'smarthome', 'icon' => 'ðŸ ', 'supports_emergency' => false,
                'description' => 'Lighting, climate, blinds, voice control, integrated home systems.'],
            ['name' => 'Wi-Fi & Connectivity', 'slug' => 'wifi', 'icon' => 'ðŸ“¶', 'supports_emergency' => false,
                'description' => 'Fibre readiness, mesh Wi-Fi, wireless links, dead-zone elimination.'],
            ['name' => 'IP Network Infrastructure', 'slug' => 'network', 'icon' => 'ðŸ–§', 'supports_emergency' => false,
                'description' => 'Structured cabling, switching, routing, firewalls, server rooms.'],
            ['name' => 'Audio & Sound Technology', 'slug' => 'audio', 'icon' => 'ðŸ”Š', 'supports_emergency' => false,
                'description' => 'Home theatre, multi-room audio, commercial PA, acoustic tuning.'],
        ];

        $categories = [];
        foreach ($definitions as $i => $def) {
            $categories[$def['slug']] = ServiceCategory::updateOrCreate(
                ['slug' => $def['slug']],
                array_merge($def, ['is_active' => true, 'sort_order' => $i])
            );
        }

        return $categories;
    }

    private function seedServiceRequests(User $customer, array $categories): void
    {
        ServiceRequest::updateOrCreate(
            ['customer_id' => $customer->id, 'title' => 'Camera not recording at night'],
            [
                'service_category_id' => $categories['cctv']->id,
                'description' => 'Two of four outdoor cameras stopped recording after load-shedding. Need urgent diagnosis.',
                'property_type' => 'home',
                'urgency' => '24h',
                'area_label' => 'Sandton, Gauteng',
                'lat' => -26.1076,
                'lng' => 28.0567,
                'budget_min' => 800,
                'budget_max' => 2500,
                'status' => 'submitted',
            ]
        );
    }

    private function seedContent(User $admin, array $categories): void
    {
        ContentPost::updateOrCreate(
            ['slug' => 'load-shedding-cctv-checklist'],
            [
                'title' => 'Load-Shedding CCTV Checklist: Keep Your Cameras Recording',
                'excerpt' => 'Five things to check when cameras stop recording after power outages.',
                'body' => 'A practical checklist covering UPS backup, NVR time sync, and network switch power resilience.',
                'service_category_id' => $categories['cctv']->id ?? null,
                'author_id' => $admin->id,
                'status' => 'published',
                'published_at' => now()->subDays(10),
            ]
        );
    }
}
