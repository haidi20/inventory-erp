<?php

namespace Database\Seeders\V1;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Operational\Master\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

class SupplierDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('=== Supplier Data Seeder ===');

        $branch = Branch::orderBy('id')->first();
        if (! $branch) {
            throw new RuntimeException('Supplier data seeder requires at least one branch.');
        }

        $suppliers = [
            ['name' => 'MUSTANG FARMA JAYA', 'phone' => '021-55510001', 'email' => 'cs@mustangfarma.co.id', 'term_days' => 30],
            ['name' => 'KEBAYORAN PHARMA', 'phone' => '021-55520002', 'email' => 'order@kebayoranpharma.co.id', 'term_days' => 30],
            ['name' => 'METTA KARYA HUTAMA', 'phone' => '021-55530003', 'email' => 'sales@mettakarya.co.id', 'term_days' => 30],
            ['name' => 'ANUGRAH ARGON MEDICA', 'phone' => '021-55540004', 'email' => 'order@argonmedica.co.id', 'term_days' => 30],
            ['name' => 'MILLENIUM PHARMACON INTERNASIONAL', 'phone' => '021-55550005', 'email' => 'cs@milleniumpharmacon.co.id', 'term_days' => 30],
            ['name' => 'PT KALLISTA PRIMA', 'phone' => '021-55560006', 'email' => 'sales@kallistaprima.co.id', 'term_days' => 30],
            ['name' => 'PENTA VALENT', 'phone' => '021-55570007', 'email' => 'order@pentavalent.co.id', 'term_days' => 30],
            ['name' => 'ENSEVAL PUTERA MEGATRADING', 'phone' => '021-55580008', 'email' => 'cs@enseval.co.id', 'term_days' => 30],
            ['name' => 'ANTARMITRA SEMBADA', 'phone' => '021-55590009', 'email' => 'sales@antarmitra.co.id', 'term_days' => 30],
            ['name' => 'MENSA BINA SUKSES', 'phone' => '021-55500010', 'email' => 'order@mensabina.co.id', 'term_days' => 30],
            ['name' => 'BINA SAN PRIMA', 'phone' => '021-55510011', 'email' => 'cs@binasanprima.co.id', 'term_days' => 30],
            ['name' => 'GLOBAL MULTI PHARMATAMA', 'phone' => '021-55520012', 'email' => 'sales@globalmulti.co.id', 'term_days' => 30],
            ['name' => 'INDOFARMA GLOBAL MEDIKA', 'phone' => '021-55530013', 'email' => 'cs@indofarmag.co.id', 'term_days' => 30],
            ['name' => 'KIMIA FARMA TRADING & DISTRIBUTION', 'phone' => '021-55540014', 'email' => 'order@kimiafarma.co.id', 'term_days' => 30],
            ['name' => 'MERSIFARMA TIRMAKU MERCUSANA', 'phone' => '021-55550015', 'email' => 'sales@mersifarma.co.id', 'term_days' => 30],
            ['name' => 'PARIT PADANG GLOBAL', 'phone' => '021-55560016', 'email' => 'cs@paritpadang.co.id', 'term_days' => 30],
            ['name' => 'SAPTA SARI TAMA', 'phone' => '021-55570017', 'email' => 'order@saptasari.co.id', 'term_days' => 30],
            ['name' => 'TEMPO SCAN PACIFIC', 'phone' => '021-55580018', 'email' => 'sales@tempopacific.co.id', 'term_days' => 30],
            ['name' => 'TIGA PILAR MEDIKA', 'phone' => '021-55590019', 'email' => 'cs@tigapilar.co.id', 'term_days' => 30],
            ['name' => 'USAHA UTAMA JAYA MAKMUR', 'phone' => '021-55500020', 'email' => 'order@uujm.co.id', 'term_days' => 30],
            ['name' => 'VARIA SEKATA', 'phone' => '021-55510021', 'email' => 'sales@variasekata.co.id', 'term_days' => 30],
            ['name' => 'ZENITH PHARMA', 'phone' => '021-55520022', 'email' => 'cs@zenithpharma.co.id', 'term_days' => 30],
        ];

        Supplier::transaction(function () use ($suppliers, $branch) {
            foreach ($suppliers as $supplier) {
                Supplier::firstOrCreate(
                    [
                        'branch_uuid' => $branch->uuid,
                        'name' => $supplier['name'],
                    ],
                    [
                        'uuid' => (string) Str::uuid(),
                        'company_uuid' => $branch->company_uuid,
                        'phone' => $supplier['phone'],
                        'email' => $supplier['email'],
                        'address' => 'Jakarta',
                        'term_days' => $supplier['term_days'],
                        'is_active' => true,
                    ]
                );
            }
        });

        $this->command->info('  Suppliers: '.Supplier::count().' available');
        $this->command->info('=== Supplier Data Seeder DONE ===');
    }
}
