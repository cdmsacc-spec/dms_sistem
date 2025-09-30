<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class CrewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        for ($i = 0; $i < 50; $i++) {
            DB::table('crew_applicants')->insert([
                'nama_crew'        => $faker->name,
                'posisi_dilamar'   => $faker->jobTitle,
                'tempat_lahir'     => $faker->city,
                'tanggal_lahir'    => $faker->date(),
                'jenis_kelamin'    => $faker->randomElement(['Laki Laki', 'Perempuan']),
                'golongan_darah'   => $faker->randomElement(['A', 'B', 'AB', 'O']),
                'status_identitas' => $faker->randomElement(['KTP', 'SIM', 'Paspor']),
                'agama'            => $faker->randomElement(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu']),
                'no_hp'            => $faker->phoneNumber,
                'no_telp_rumah'    => $faker->phoneNumber,
                'email'            => $faker->unique()->safeEmail,
                'kebangsaan'       => 'Indonesia',
                'suku'             => $faker->word,
                'alamat_ktp'       => $faker->address,
                'alamat_sekarang'  => $faker->address,
                'status_rumah'     => $faker->randomElement(['Milik Sendiri', 'Kontrak', 'Keluarga']),
                'tinggi_badan'     => $faker->numberBetween(150, 185),
                'berat_badan'      => $faker->numberBetween(45, 90),
                'ukuran_waerpack'  => $faker->randomElement(['S', 'M', 'L', 'XL']),
                'ukuran_sepatu'    => $faker->numberBetween(38, 45),
                'nok_nama'         => $faker->name,
                'nok_hubungan'     => $faker->randomElement(['Istri', 'Suami', 'Ayah', 'Ibu', 'Saudara']),
                'nok_alamat'       => $faker->address,
                'nok_hp'           => $faker->phoneNumber,
                'foto'             => null,
                'status_proses'    => 'Draft',
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }
    }
}
