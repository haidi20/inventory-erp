<?php

namespace Database\Seeders\V1;

use App\Modules\Clinical\Classification\Models\Icd10Code;
use Illuminate\Database\Seeder;

class Icd10Seeder extends Seeder
{
    /**
     * Import ICD-10 codes from Excel file.
     *
     * Source: database/data/ICD-10 e-klaim.xlsx
     * Run:    php artisan db:seed --class="Database\\Seeders\\V1\\Icd10Seeder"
     */
    public function run(): void
    {
        /*
        $count = Icd10Code::count();
        if ($count > 0) {
            $this->command->info("ICD-10 already seeded ({$count} rows). Skipping.");
            return;
        }

        $excelPath = database_path('data/ICD-10 e-klaim.xlsx');

        if (! file_exists($excelPath)) {
            $this->command->error("File not found: {$excelPath}");
            $this->command->line('Place the file at: database/data/ICD-10 e-klaim.xlsx');
            return;
        }

        $this->command->info('Generating ICD-10 seed from Excel...');

        $categories = [
            'A' => 'Penyakit Infeksi & Parasit', 'B' => 'Penyakit Infeksi & Parasit',
            'C' => 'Neoplasma', 'D' => 'Neoplasma / Darah & Sistem Imun',
            'E' => 'Endokrin & Metabolik',
            'F' => 'Gangguan Mental',
            'G' => 'Sistem Saraf',
            'H' => 'Mata / Telinga & Mastoid',
            'I' => 'Sistem Sirkulasi',
            'J' => 'Sistem Pernapasan',
            'K' => 'Sistem Pencernaan',
            'L' => 'Kulit & Jaringan Subkutan',
            'M' => 'Otot & Jaringan Ikat',
            'N' => 'Sistem Genitourinari',
            'O' => 'Kehamilan & Persalinan',
            'P' => 'Perinatal',
            'Q' => 'Kongenital & Kromosom',
            'R' => 'Gejala & Tanda Abnormal',
            'S' => 'Cedera & Keracunan', 'T' => 'Cedera & Keracunan',
            'V' => 'Penyebab Eksternal', 'W' => 'Penyebab Eksternal',
            'X' => 'Penyebab Eksternal', 'Y' => 'Penyebab Eksternal',
            'Z' => 'Faktor Status Kesehatan',
        ];

        $jsonCats = json_encode($categories);
        // Build python script inline
        $pyScript = <<<PYTHON
import openpyxl, sys, json

cats = json.loads('''{$jsonCats}''')
wb = openpyxl.load_workbook(sys.argv[1], read_only=True)
ws = wb.active
rows = list(ws.iter_rows(values_only=True))
data = []
for row in rows[1:]:
    code, display = str(row[0] or '').strip(), str(row[1] or '').strip()
    if not code or not display: continue
    cat = cats.get(code[0].upper(), 'Lainnya')
    data.append({'code': code, 'name': display, 'cat': cat})
print(json.dumps(data))
PYTHON;

        $pyFile = tempnam(sys_get_temp_dir(), 'icd10_') . '.py';
        file_put_contents($pyFile, $pyScript);

        $escapedPath  = escapeshellarg($excelPath);

        $json = shell_exec("python {$pyFile} {$escapedPath} 2>&1");
        @unlink($pyFile);

        if (! $json || str_contains($json, 'Traceback') || str_contains($json, 'Error:')) {
            $this->command->error('Failed to extract data from Excel.');
            $this->command->line($json);
            return;
        }

        $rows = json_decode($json, true);
        if (! $rows) {
            $this->command->error('Failed to parse data from Excel.');
            return;
        }

        $now    = now();
        $chunks = array_chunk($rows, 500);
        $total  = 0;

        Icd10Code::transaction(function () use ($chunks, $now, &$total) {
            foreach ($chunks as $chunk) {
                $insert = array_map(fn ($r) => [
                    'uuid'       => (string) \Illuminate\Support\Str::uuid(),
                    'code'       => $r['code'],
                    'name_uuid'    => $r['name'],
                    'name_en'    => $r['name'],
                    'category'   => $r['cat'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ], $chunk);

                Icd10Code::insertOrIgnore($insert);
                $total += count($insert);
            }
        });

        $this->command->info("ICD-10 seeded: {$total} rows inserted.");
        */
    }
}
