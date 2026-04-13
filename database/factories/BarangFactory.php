<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Barang;

class BarangFactory extends Factory
{
    protected $model = Barang::class;

    public function definition()
    {
        return [
            'nama' => $this->faker->words(2, true),
            'kategori' => $this->faker->randomElement(['Makanan','Minuman','ATK','Pakaian','Elektronik']),
            'harga' => $this->faker->numberBetween(1000, 200000),
        ];
    }
}
