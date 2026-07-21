<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Medium;

use Illuminate\Support\Facades\Storage;

class MediumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $metadata = Storage::disk('s3')->get('metadata.json');
        $metadata = json_decode($metadata, true);

        foreach($metadata['media'] as $medium)
            Medium::create(['title' => $medium]);
    }
}
