<?php

namespace Database\Seeders;

use App\Models\Antrian;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class AntrianSeeder extends Seeder
{
    public function run(): void
    {
        Antrian::truncate();

        $data = [
            ['nama' => 'Budi Santoso',    'nomor' => 1, 'status' => 'selesai'],
            ['nama' => 'Siti Rahayu',     'nomor' => 2, 'status' => 'dipanggil'],
            ['nama' => 'Agus Priyono',    'nomor' => 3, 'status' => 'menunggu'],
            ['nama' => 'Dewi Lestari',    'nomor' => 4, 'status' => 'menunggu'],
            ['nama' => 'Eko Prasetyo',    'nomor' => 5, 'status' => 'terlambat'],
            ['nama' => 'Fitri Handayani', 'nomor' => 6, 'status' => 'menunggu'],
            ['nama' => 'Gunawan Hadi',    'nomor' => 7, 'status' => 'menunggu'],
        ];

        foreach ($data as $d) {
            Antrian::create($d);
        }

        // Rebuild cache
        $dipanggil = Antrian::where('status', 'dipanggil')->first();
        Cache::forever('antrian_data', [
            'sedang_dipanggil' => $dipanggil
                ? ['id' => $dipanggil->id, 'nomor' => $dipanggil->nomor, 'nama' => $dipanggil->nama]
                : null,
            'menunggu'  => Antrian::where('status','menunggu')->orderBy('nomor')->get(['id','nomor','nama'])->toArray(),
            'terlambat' => Antrian::where('status','terlambat')->orderBy('nomor')->get(['id','nomor','nama'])->toArray(),
        ]);

        $this->command->info('✅ Antrian seeder selesai: 7 data dummy.');
    }
}
