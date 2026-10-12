<?php

namespace Database\Seeders\V1;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\LookupSet;
use App\Modules\Operational\Master\Models\LookupValue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LookupValueSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RuntimeCompanySeeder::class);

        $branch = Branch::where('short_name', 'KDM')->first()
            ?? Branch::where('short_name', RuntimeCompanySeeder::SANDBOX_BRANCH_SHORT_NAME)->first();
        $companyUuid = $branch?->company_uuid
            ?: (string) Company::query()->orderBy('id')->value('uuid')
            ?: (string) Str::uuid();

        $sets = [
            [
                'code'              => 'E_ORDER',
                'name'              => 'E-ORDER',
                'user_can_edit_flag'=> 'Y',
                'values'            => [
                    ['display_name' => 'E-ORDER STOK VISIBLE', 'description' => 'N', 'primary_flag' => 'N', 'seq' => 1, 'active_flag' => 'Y'],
                    ['display_name' => 'E-ORDER DIKSON BERTINGKAT', 'description' => 'N', 'primary_flag' => 'N', 'seq' => 2, 'active_flag' => 'Y'],
                    ['display_name' => 'E-ORDER MAX STOCK', 'description' => 'n', 'primary_flag' => 'N', 'seq' => 3, 'active_flag' => 'Y'],
                    ['display_name' => 'E-ORDER EDIT HARGA', 'description' => 'Y', 'primary_flag' => 'N', 'seq' => 4, 'active_flag' => 'Y'],
                ],
            ],
            [
                'code'              => 'GRADE',
                'name'              => 'GRADE',
                'user_can_edit_flag'=> 'Y',
                'values'            => [
                    ['display_name' => 'BAHAN BAKU', 'description' => 'GRADE A', 'primary_flag' => 'Y', 'seq' => 1, 'active_flag' => 'Y'],
                    ['display_name' => 'NO PPN', 'description' => 'JGN DIHAPUS, INI UTK TIDAK KENA PPN', 'primary_flag' => 'N', 'seq' => 2, 'active_flag' => 'Y'],
                    ['display_name' => 'BAHAN SETENGAH JADI', 'description' => 'GRADE B', 'primary_flag' => 'N', 'seq' => 2, 'active_flag' => 'Y'],
                    ['display_name' => 'D', 'description' => 'GRADE D', 'primary_flag' => 'N', 'seq' => 4, 'active_flag' => 'N'],
                    ['display_name' => 'E', 'description' => 'GRADE E', 'primary_flag' => 'N', 'seq' => 5, 'active_flag' => 'N'],
                    ['display_name' => 'BHP', 'description' => 'GARADE A+', 'primary_flag' => 'N', 'seq' => 6, 'active_flag' => 'Y'],
                ],
            ],
            [
                'code'              => 'MADE_IN',
                'name'              => 'MADE IN',
                'user_can_edit_flag'=> 'Y',
                'values'            => [
                    ['display_name' => 'TAIWAN', 'description' => 'BUATAN TAIWAN', 'primary_flag' => 'N', 'seq' => 1, 'active_flag' => 'Y'],
                    ['display_name' => 'CHINA', 'description' => 'BUATAN CHINA', 'primary_flag' => 'N', 'seq' => 2, 'active_flag' => 'Y'],
                    ['display_name' => 'US', 'description' => 'BUATAN US', 'primary_flag' => 'N', 'seq' => 3, 'active_flag' => 'Y'],
                    ['display_name' => 'JAPAN', 'description' => 'BUATAN JEPANG', 'primary_flag' => 'N', 'seq' => 4, 'active_flag' => 'Y'],
                    ['display_name' => 'GERMAN', 'description' => 'BUATAN JERMAN', 'primary_flag' => 'N', 'seq' => 5, 'active_flag' => 'Y'],
                    ['display_name' => 'INDONESIA', 'description' => 'BUATAN INDONESIA', 'primary_flag' => 'Y', 'seq' => 6, 'active_flag' => 'Y'],
                    ['display_name' => 'THAILAND', 'description' => 'BUATAN THAILAND', 'primary_flag' => 'N', 'seq' => 7, 'active_flag' => 'Y'],
                ],
            ],
            [
                'code'              => 'JENIS',
                'name'              => 'JENIS',
                'user_can_edit_flag'=> 'N',
                'values'            => [
                    ['display_name' => 'BARANG', 'description' => 'BARANG', 'primary_flag' => 'Y', 'seq' => 1, 'active_flag' => 'Y'],
                    ['display_name' => 'JASA', 'description' => 'JASA', 'primary_flag' => 'N', 'seq' => 2, 'active_flag' => 'Y'],
                    ['display_name' => 'UANG MUKA', 'description' => '', 'primary_flag' => 'N', 'seq' => 3, 'active_flag' => 'Y'],
                ],
            ],
            [
                'code'              => 'KELOMPOK',
                'name'              => 'KELOMPOK',
                'user_can_edit_flag'=> 'Y',
                'values'            => [
                    ['display_name' => 'KOMPUTER', 'description' => 'KOMPUTER', 'primary_flag' => 'N', 'seq' => 1, 'active_flag' => 'N'],
                    ['display_name' => 'HP', 'description' => 'HP', 'primary_flag' => 'N', 'seq' => 2, 'active_flag' => 'N'],
                    ['display_name' => 'ATK', 'description' => '', 'primary_flag' => 'N', 'seq' => 3, 'active_flag' => 'N'],
                    ['display_name' => 'BAK AIR', 'description' => 'BAK AIR', 'primary_flag' => 'Y', 'seq' => 4, 'active_flag' => 'Y'],
                    ['display_name' => 'CAT', 'description' => 'CAT', 'primary_flag' => 'N', 'seq' => 5, 'active_flag' => 'Y'],
                    ['display_name' => 'GENTENG', 'description' => 'GENTENG', 'primary_flag' => 'N', 'seq' => 6, 'active_flag' => 'Y'],
                    ['display_name' => 'PERLENGKAPAN', 'description' => 'PERLENGKAPAN', 'primary_flag' => 'N', 'seq' => 7, 'active_flag' => 'Y'],
                    ['display_name' => 'KAWAT', 'description' => 'KAWAT', 'primary_flag' => 'N', 'seq' => 8, 'active_flag' => 'Y'],
                    ['display_name' => 'non steril', 'description' => 'non steril', 'primary_flag' => 'N', 'seq' => 9, 'active_flag' => 'Y'],
                ],
            ],
            [
                'code'              => 'TIPE',
                'name'              => 'TIPE',
                'user_can_edit_flag'=> 'Y',
                'values'            => [
                    ['display_name' => 'NOTEBOOK', 'description' => '', 'primary_flag' => 'N', 'seq' => 1, 'active_flag' => 'N'],
                    ['display_name' => 'MOUSE', 'description' => '', 'primary_flag' => 'N', 'seq' => 2, 'active_flag' => 'N'],
                    ['display_name' => 'MONITOR', 'description' => '', 'primary_flag' => 'N', 'seq' => 3, 'active_flag' => 'N'],
                    ['display_name' => 'SUDUT', 'description' => '', 'primary_flag' => 'N', 'seq' => 4, 'active_flag' => 'Y'],
                    ['display_name' => 'PAKU KAPAL', 'description' => '', 'primary_flag' => 'Y', 'seq' => 5, 'active_flag' => 'N'],
                    ['display_name' => 'KAWAT', 'description' => '', 'primary_flag' => 'N', 'seq' => 6, 'active_flag' => 'Y'],
                    ['display_name' => 'SENG', 'description' => '', 'primary_flag' => 'N', 'seq' => 7, 'active_flag' => 'Y'],
                    ['display_name' => 'PVC', 'description' => '', 'primary_flag' => 'N', 'seq' => 8, 'active_flag' => 'Y'],
                    ['display_name' => 'SELANG', 'description' => '', 'primary_flag' => 'N', 'seq' => 9, 'active_flag' => 'Y'],
                    ['display_name' => 'PLYWOOD', 'description' => '', 'primary_flag' => 'N', 'seq' => 10, 'active_flag' => 'Y'],
                    ['display_name' => 'GALON', 'description' => 'GALON', 'primary_flag' => 'N', 'seq' => 11, 'active_flag' => 'Y'],
                ],
            ],
            [
                'code'              => 'MERK',
                'name'              => 'MERK',
                'user_can_edit_flag'=> 'Y',
                'values'            => [
                    ['display_name' => 'SONY', 'description' => '', 'primary_flag' => 'N', 'seq' => 1, 'active_flag' => 'N'],
                    ['display_name' => 'LG', 'description' => '', 'primary_flag' => 'N', 'seq' => 2, 'active_flag' => 'Y'],
                    ['display_name' => 'SAMSUNG', 'description' => '', 'primary_flag' => 'N', 'seq' => 3, 'active_flag' => 'Y'],
                    ['display_name' => 'WALRUS', 'description' => '', 'primary_flag' => 'N', 'seq' => 4, 'active_flag' => 'Y'],
                    ['display_name' => 'NICE 2', 'description' => '', 'primary_flag' => 'N', 'seq' => 5, 'active_flag' => 'N'],
                    ['display_name' => 'GRANICA', 'description' => '', 'primary_flag' => 'N', 'seq' => 6, 'active_flag' => 'Y'],
                    ['display_name' => 'TUNGGAL DJAYA', 'description' => '', 'primary_flag' => 'N', 'seq' => 7, 'active_flag' => 'Y'],
                    ['display_name' => 'GAJAH MADA', 'description' => '', 'primary_flag' => 'N', 'seq' => 8, 'active_flag' => 'Y'],
                    ['display_name' => 'SUPRA ROOF', 'description' => '', 'primary_flag' => 'N', 'seq' => 9, 'active_flag' => 'Y'],
                    ['display_name' => 'Prohex', 'description' => 'Prohex', 'primary_flag' => 'Y', 'seq' => 10, 'active_flag' => 'Y'],
                ],
            ],
            [
                'code'              => 'STATUS_DOKUMEN',
                'name'              => 'STATUS DOKUMEN',
                'user_can_edit_flag'=> 'N',
                'values'            => [
                    ['display_name' => 'PENDING', 'description' => '', 'primary_flag' => 'N', 'seq' => 1000, 'active_flag' => 'Y'],
                    ['display_name' => 'SYSTEM', 'description' => 'DARI SYSTEM', 'primary_flag' => 'N', 'seq' => 10, 'active_flag' => 'Y'],
                    ['display_name' => 'COMPLETE', 'description' => 'Complete (Lengkap)', 'primary_flag' => 'N', 'seq' => 8, 'active_flag' => 'Y'],
                    ['display_name' => 'NEW', 'description' => '', 'primary_flag' => 'N', 'seq' => 1, 'active_flag' => 'Y'],
                    ['display_name' => 'PARTIAL', 'description' => '', 'primary_flag' => 'N', 'seq' => 7, 'active_flag' => 'Y'],
                    ['display_name' => 'LUNAS', 'description' => '', 'primary_flag' => 'N', 'seq' => 13, 'active_flag' => 'Y'],
                    ['display_name' => 'BELUM LUNAS', 'description' => '', 'primary_flag' => 'N', 'seq' => 12, 'active_flag' => 'Y'],
                    ['display_name' => 'DELETE', 'description' => '', 'primary_flag' => 'N', 'seq' => 5, 'active_flag' => 'Y'],
                    ['display_name' => 'CANCEL', 'description' => '', 'primary_flag' => 'N', 'seq' => 6, 'active_flag' => 'Y'],
                    ['display_name' => 'APPROVE', 'description' => '', 'primary_flag' => 'N', 'seq' => 4, 'active_flag' => 'Y'],
                    ['display_name' => 'REJECT', 'description' => '', 'primary_flag' => 'N', 'seq' => 3, 'active_flag' => 'Y'],
                    ['display_name' => 'POSTING', 'description' => '', 'primary_flag' => 'N', 'seq' => 2, 'active_flag' => 'Y'],
                    ['display_name' => 'VOID', 'description' => '', 'primary_flag' => 'N', 'seq' => 9, 'active_flag' => 'Y'],
                    ['display_name' => 'TRANSFER', 'description' => '', 'primary_flag' => 'N', 'seq' => 11, 'active_flag' => 'Y'],
                ],
            ],
            [
                'code'              => 'REASON',
                'name'              => 'REASON',
                'user_can_edit_flag'=> 'Y',
                'values'            => [
                    ['display_name' => 'HILANG', 'description' => 'HILANG KRN TDK TAHU', 'primary_flag' => 'N', 'seq' => 1, 'active_flag' => 'Y'],
                    ['display_name' => 'DICURI', 'description' => 'DI AMBIL OL ORG', 'primary_flag' => 'N', 'seq' => 2, 'active_flag' => 'Y'],
                    ['display_name' => 'KETEMU', 'description' => 'KETEMU', 'primary_flag' => 'N', 'seq' => 3, 'active_flag' => 'Y'],
                    ['display_name' => 'BONUS', 'description' => 'BONUS', 'primary_flag' => 'N', 'seq' => 4, 'active_flag' => 'Y'],
                    ['display_name' => 'SALDO AWAL', 'description' => 'SALDO AWAL', 'primary_flag' => 'Y', 'seq' => 5, 'active_flag' => 'Y'],
                    ['display_name' => 'DAUR ULANG', 'description' => 'DAUR ULANG', 'primary_flag' => 'N', 'seq' => 6, 'active_flag' => 'Y'],
                    ['display_name' => 'KEMBALI', 'description' => 'KEMBALI', 'primary_flag' => 'N', 'seq' => 7, 'active_flag' => 'Y'],
                    ['display_name' => 'RETUR', 'description' => 'RETUR', 'primary_flag' => 'N', 'seq' => 8, 'active_flag' => 'Y'],
                ],
            ],
            [
                'code'              => 'BAGIAN',
                'name'              => 'BAGIAN',
                'user_can_edit_flag'=> 'Y',
                'values'            => [
                    ['display_name' => 'SALES', 'description' => '', 'primary_flag' => 'Y', 'seq' => 1, 'active_flag' => 'Y'],
                    ['display_name' => 'GUDANG', 'description' => '', 'primary_flag' => 'N', 'seq' => 2, 'active_flag' => 'Y'],
                    ['display_name' => 'ADMINISTRASI', 'description' => '', 'primary_flag' => 'N', 'seq' => 3, 'active_flag' => 'Y'],
                ],
            ],
            [
                'code'              => 'TIPE_INVOICE',
                'name'              => 'TIPE INVOICE',
                'user_can_edit_flag'=> 'N',
                'values'            => [
                    ['display_name' => 'MATCHING PO', 'description' => '', 'primary_flag' => 'Y', 'seq' => 1, 'active_flag' => 'Y'],
                    ['display_name' => 'STANDARD', 'description' => '', 'primary_flag' => 'N', 'seq' => 2, 'active_flag' => 'Y'],
                    ['display_name' => 'UANG MUKA', 'description' => '', 'primary_flag' => 'N', 'seq' => 3, 'active_flag' => 'Y'],
                ],
            ],
            [
                'code'              => 'TIPE_ACCOUNT',
                'name'              => 'TIPE ACCOUNT',
                'user_can_edit_flag'=> 'N',
                'values'            => [
                    ['display_name' => 'AKTIVA LAIN - LAIN', 'description' => '', 'primary_flag' => 'N', 'seq' => 1, 'active_flag' => 'Y'],
                    ['display_name' => 'KAS/BANK', 'description' => '', 'primary_flag' => 'N', 'seq' => 11, 'active_flag' => 'Y'],
                    ['display_name' => 'INVESTASI JANGKA PANJANG', 'description' => '', 'primary_flag' => 'N', 'seq' => 3, 'active_flag' => 'Y'],
                    ['display_name' => 'AKTIVA LANCAR', 'description' => '', 'primary_flag' => 'Y', 'seq' => 10, 'active_flag' => 'Y'],
                    ['display_name' => 'BEBAN', 'description' => '', 'primary_flag' => 'N', 'seq' => 60, 'active_flag' => 'Y'],
                    ['display_name' => 'AKTIVA TETAP', 'description' => '', 'primary_flag' => 'N', 'seq' => 20, 'active_flag' => 'Y'],
                    ['display_name' => 'AKTIVA TAK BERWUJUD', 'description' => '', 'primary_flag' => 'N', 'seq' => 7, 'active_flag' => 'Y'],
                ],
            ],
            [
                'code'              => 'METODE_DEPRESIASI',
                'name'              => 'METODE DEPRESIASI',
                'user_can_edit_flag'=> 'N',
                'values'            => [
                    ['display_name' => 'NON DEPRECIABLE', 'description' => '', 'primary_flag' => 'N', 'seq' => 2, 'active_flag' => 'Y'],
                    ['display_name' => 'STRAIGHT LINE', 'description' => '', 'primary_flag' => 'Y', 'seq' => 1, 'active_flag' => 'Y'],
                    ['display_name' => 'DOUBLE DECLINING METHOD', 'description' => '', 'primary_flag' => 'N', 'seq' => 3, 'active_flag' => 'Y'],
                ],
            ],
            [
                'code'              => 'TIPE_ASSET',
                'name'              => 'TIPE ASSET',
                'user_can_edit_flag'=> 'Y',
                'values'            => [
                    ['display_name' => 'BANGUNAN', 'description' => '', 'primary_flag' => 'N', 'seq' => 1, 'active_flag' => 'Y'],
                    ['display_name' => 'KENDARAAN', 'description' => '', 'primary_flag' => 'N', 'seq' => 2, 'active_flag' => 'Y'],
                    ['display_name' => 'PERALATAN PRODUKSI', 'description' => '', 'primary_flag' => 'N', 'seq' => 3, 'active_flag' => 'Y'],
                    ['display_name' => 'INVENTARIS KANTOR', 'description' => '', 'primary_flag' => 'N', 'seq' => 4, 'active_flag' => 'Y'],
                    ['display_name' => 'MESIN & PERLENGKAPAN', 'description' => '', 'primary_flag' => 'N', 'seq' => 5, 'active_flag' => 'Y'],
                    ['display_name' => 'AKTIVA TAK BERWUJUD', 'description' => '', 'primary_flag' => 'N', 'seq' => 6, 'active_flag' => 'Y'],
                ],
            ],
            [
                'code'              => 'VIA_KIRIM',
                'name'              => 'VIA KIRIM',
                'user_can_edit_flag'=> 'Y',
                'values'            => [
                    ['display_name' => 'JNE', 'description' => '', 'primary_flag' => 'N', 'seq' => 1, 'active_flag' => 'Y'],
                    ['display_name' => 'SI CEPAT', 'description' => '', 'primary_flag' => 'N', 'seq' => 2, 'active_flag' => 'Y'],
                ],
            ],
            [
                'code'              => 'CHOISE',
                'name'              => 'CHOISE',
                'user_can_edit_flag'=> 'Y',
                'values'            => [
                    ['display_name' => 'YA', 'description' => '', 'primary_flag' => 'N', 'seq' => 1, 'active_flag' => 'Y'],
                    ['display_name' => 'TIDAK', 'description' => '', 'primary_flag' => 'N', 'seq' => 2, 'active_flag' => 'Y'],
                ],
            ],
            [
                'code'              => 'NO_PAJAK',
                'name'              => 'NO PAJAK',
                'user_can_edit_flag'=> 'Y',
                'values'            => [
                    ['display_name' => '23612350', 'description' => 'PERINGATAN', 'primary_flag' => 'N', 'seq' => 3, 'active_flag' => 'Y'],
                    ['display_name' => '23612419', 'description' => 'AKHIR - NO PAJAK', 'primary_flag' => 'N', 'seq' => 2, 'active_flag' => 'Y'],
                    ['display_name' => '23610832', 'description' => 'AWAL - NO PAJAK', 'primary_flag' => 'N', 'seq' => 1, 'active_flag' => 'Y'],
                    ['display_name' => '010.019-', 'description' => 'NO DEPAN', 'primary_flag' => 'N', 'seq' => 4, 'active_flag' => 'Y'],
                ],
            ],
            [
                'code'              => 'DURASI_WARNING',
                'name'              => 'DURASI WARNING',
                'user_can_edit_flag'=> 'Y',
                'values'            => [
                    ['display_name' => '1', 'description' => 'WA Terimakasih atas pelunasan', 'primary_flag' => 'Y', 'seq' => 1, 'active_flag' => 'Y'],
                    ['display_name' => '1', 'description' => '1 = PENJUALAN 0 = S.JALAN', 'primary_flag' => 'Y', 'seq' => 5, 'active_flag' => 'Y'],
                    ['display_name' => '09:00', 'description' => 'Jam mulai ambil pengiriman', 'primary_flag' => 'Y', 'seq' => 7, 'active_flag' => 'Y'],
                    ['display_name' => '09:00', 'description' => 'Jam broadcast untuk TAGIHAN', 'primary_flag' => 'N', 'seq' => 10, 'active_flag' => 'Y'],
                ],
            ],
        ];

        foreach ($sets as $index => $setData) {
            $set = LookupSet::withTrashed()->updateOrCreate(
                ['program_code' => $setData['code'], 'company_uuid' => $companyUuid],
                [
                    'uuid'               => (string) Str::uuid(),
                    'id'                 => $index + 200,
                    'lookup_set_uuid'    => $index + 200,
                    'code'               => $setData['code'],
                    'name'               => $setData['name'],
                    'source'             => 'app',
                    'program_code'       => $setData['code'],
                    'company_uuid'       => $companyUuid,
                    'user_can_edit_flag' => $setData['user_can_edit_flag'],
                    'is_active'          => true,
                    'deleted_at'         => 0,
                ]
            );

            foreach ($setData['values'] as $vIndex => $valData) {
                LookupValue::withTrashed()->updateOrCreate(
                    ['lookup_set_uuid' => $set->uuid, 'display_name' => $valData['display_name'], 'company_uuid' => $companyUuid],
                    [
                        'uuid'         => (string) Str::uuid(),
                        'value'        => $valData['display_name'],
                        'description'  => $valData['description'],
                        'program_code' => $valData['display_name'],
                        'seq'          => $valData['seq'] ?: ($vIndex + 1),
                        'order_priority'=> $valData['seq'] ?: ($vIndex + 1),
                        'is_active'    => $valData['active_flag'] === 'Y',
                        'active_flag'  => $valData['active_flag'],
                        'primary_flag' => $valData['primary_flag'],
                        'company_uuid' => $companyUuid,
                        'deleted_at'   => 0,
                    ]
                );
            }
        }
    }
}
