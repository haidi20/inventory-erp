<?php

namespace Database\Seeders\V1;

use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\LookupSet;
use App\Modules\Operational\Master\Models\LookupValue;
use App\Modules\Core\Organization\Models\Branch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LookupSeeder extends Seeder
{
    public function run(): void
    {
        $sets = [
            // From LookupSeeder.go
            [
                'code' => 'GENDER', 'name' => 'Jenis Kelamin', 'description' => '', 
                'values' => [
                    ['code' => 'MALE', 'value' => 'Laki-laki'],
                    ['code' => 'FEMALE', 'value' => 'Perempuan'],
                    ['code' => 'UNKNOWN', 'value' => 'Tidak Diketahui'],
                ]
            ],
            [
                'code' => 'BLOOD_TYPE', 'name' => 'Golongan Darah', 'description' => '',
                'values' => [
                    ['code' => 'A', 'value' => 'A'], ['code' => 'B', 'value' => 'B'], 
                    ['code' => 'AB', 'value' => 'AB'], ['code' => 'O', 'value' => 'O'],
                    ['code' => 'UNKNOWN', 'value' => 'Tidak Tahu'],
                ]
            ],
            [
                'code' => 'RELIGION', 'name' => 'Agama', 'description' => '',
                'values' => [
                    ['code' => 'ISLAM', 'value' => 'Islam'], 
                    ['code' => 'PROTESTANT', 'value' => 'Kristen Protestan'],
                    ['code' => 'CATHOLIC', 'value' => 'Katolik'], 
                    ['code' => 'HINDU', 'value' => 'Hindu'],
                    ['code' => 'BUDDHA', 'value' => 'Buddha'], 
                    ['code' => 'KHONGHUCU', 'value' => 'Khonghucu'],
                ]
            ],
            // From LookupErpSeeder.go
            [
                'code' => 'ITEM_GRADE', 'name' => 'GRADE', 'description' => 'KUALITAS ITEM',
                'values' => [
                    ['code' => 'A', 'value' => 'A', 'description' => 'GRADE A', 'active' => 'N'],
                    ['code' => 'D', 'value' => 'D', 'description' => 'GRADE D', 'active' => 'N'],
                    ['code' => 'E', 'value' => 'E', 'description' => 'GRADE E', 'active' => 'N'],
                    ['code' => 'A_PLUS', 'value' => 'A+', 'description' => 'GARADE A+', 'active' => 'N'],
                    ['code' => 'NOPPN', 'value' => 'NOPPN', 'description' => 'NOPPN'],
                ]
            ],
            [
                'code' => 'MADE_IN', 'name' => 'MADE IN', 'description' => 'BUATAN',
                'values' => [
                    ['code' => 'TAIWAN', 'value' => 'TAIWAN', 'description' => 'BUATAN TAIWAN'],
                    ['code' => 'CHINA', 'value' => 'CHINA', 'description' => 'BUATAN CHINA'],
                    ['code' => 'US', 'value' => 'US', 'description' => 'BUATAN US'],
                    ['code' => 'JAPAN', 'value' => 'JAPAN', 'description' => 'BUATAN JEPANG'],
                    ['code' => 'GERMAN', 'value' => 'GERMAN', 'description' => 'BUATAN JERMAN'],
                    ['code' => 'INDONESIA', 'value' => 'INDONESIA', 'description' => 'BUATAN INDONESIA'],
                ]
            ],
            [
                'code' => 'ITEM_TYPE', 'name' => 'JENIS', 'description' => 'JENIS ITEM',
                'values' => [
                    ['code' => 'GOODS', 'value' => 'BARANG', 'description' => 'BARANG'],
                    ['code' => 'SERVICE', 'value' => 'JASA', 'description' => 'JASA'],
                    ['code' => 'UM', 'value' => 'UANG MUKA'],
                ]
            ],
            [
                'code' => 'ITEM_GROUP', 'name' => 'KELOMPOK', 'description' => 'KELOMPOK',
                'values' => [
                    ['code' => 'PENDUKUNG', 'value' => 'BAHAN PENDUKUNG'],
                    ['code' => 'BAKU', 'value' => 'BAHAN BAKU'],
                    ['code' => 'ACCESSORIES', 'value' => 'ACCESSORIES'],
                    ['code' => 'BAHAN_JADI', 'value' => 'BAHAN JADI'],
                ]
            ],
            [
                'code' => 'DOCUMENT_STATUS', 'name' => 'STATUS DOKUMEN', 'description' => 'STATUS DOKUMEN',
                'values' => [
                    ['code' => 'NEW', 'value' => 'NEW', 'seq' => 1],
                    ['code' => 'POSTED', 'value' => 'POSTED', 'seq' => 2],
                    ['code' => 'APPROVED', 'value' => 'APPROVED', 'seq' => 4],
                    ['code' => 'DELETE', 'value' => 'DELETE', 'seq' => 5],
                    ['code' => 'CANCEL', 'value' => 'CANCEL APPROVED', 'seq' => 6],
                    ['code' => 'PARTIAL', 'value' => 'PARTIAL', 'seq' => 7],
                    ['code' => 'COMPLETE', 'value' => 'COMPLETE', 'seq' => 8],
                    ['code' => 'CLOSED', 'value' => 'CLOSED', 'seq' => 3],
                    ['code' => 'SYSTEM', 'value' => 'SYSTEM', 'seq' => 10],
                    ['code' => 'BILLED', 'value' => 'TERTAGIH', 'seq' => 11],
                    ['code' => 'FULL_PAID', 'value' => 'LUNAS', 'seq' => 13],
                    ['code' => 'PARTIAL_PAID', 'value' => 'BELUM LUNAS', 'seq' => 12],
                    ['code' => 'CLEAR', 'value' => 'CAIR', 'seq' => 15],
                    ['code' => 'TOLAK', 'value' => 'TOLAKAN', 'seq' => 14],
                    ['code' => 'ERROR', 'value' => 'ERROR', 'seq' => 16],
                    ['code' => 'BATAL', 'value' => 'BATAL', 'seq' => 17],
                ]
            ],
            [
                'code' => 'ADJUSTMENT_REASON', 'name' => 'REASON', 'description' => 'ALASAN ADJUSTMENT ITEM',
                'values' => [
                    ['code' => 'HILANG', 'value' => 'HILANG', 'description' => 'HILANG KRN TDK TAHU'],
                    ['code' => 'DICURI', 'value' => 'DICURI', 'description' => 'DI AMBIL OL ORG'],
                    ['code' => 'KETEMU', 'value' => 'KETEMU'],
                    ['code' => 'BONUS', 'value' => 'BONUS'],
                    ['code' => 'SALDO', 'value' => 'SALDO AWAL'],
                    ['code' => 'DAUR_ULANG', 'value' => 'DAUR ULANG'],
                    ['code' => 'KEMBALI', 'value' => 'KEMBALI'],
                    ['code' => 'PENGGUNAAN', 'value' => 'PENGGUNAAN'],
                ]
            ],
            [
                'code' => 'TAX_TYPE', 'name' => 'TIPE_PAJAK', 'description' => 'TIPE PAJAK',
                'values' => [
                    ['code' => 'PPN', 'value' => 'PPN'],
                    ['code' => 'PPH', 'value' => 'PPH'],
                ]
            ],
        ];

        $branch = Branch::where('short_name', 'KDM')->first();
        $companyUuid = $branch?->company_uuid
            ?: Branch::where('short_name', RuntimeCompanySeeder::SANDBOX_BRANCH_SHORT_NAME)->value('company_uuid')
            ?: (string) Company::query()->orderBy('id')->value('uuid')
            ?: (string) Str::uuid();

        foreach ($sets as $index => $setData) {
            $set = LookupSet::query()->updateOrCreate(
                ['program_code' => $setData['code']],
                [
                    'uuid' => (string) Str::uuid(),
                    'id' => $index + 100, 
                    'code' => $setData['code'],
                    'name' => $setData['name'],
                    'description' => $setData['description'],
                    'source' => 'app',
                    'company_uuid' => '',
                    'user_can_edit_flag' => 'Y',
                    'is_active' => true,
                ]
            );

            foreach ($setData['values'] as $vIndex => $valData) {
                LookupValue::query()->updateOrCreate(
                    [
                        'lookup_set_uuid' => $set->uuid,
                        'program_code' => $valData['code'],
                    ],
                    [
                        'uuid' => (string) Str::uuid(),
                        'display_name' => $valData['value'],
                        'value' => $valData['value'],
                        'description' => $valData['description'] ?? null,
                        'seq' => $valData['seq'] ?? ($vIndex + 1),
                        'order_priority' => $valData['seq'] ?? ($vIndex + 1),
                        'is_active' => ($valData['active'] ?? 'Y') === 'Y',
                        'active_flag' => $valData['active'] ?? 'Y',
                        'company_uuid' => $companyUuid,
                    ]
                );
            }
        }
    }
}
