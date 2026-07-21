<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Collection;

use Illuminate\Support\Facades\Storage;

class CollectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $metadata = Storage::disk('s3')->get('metadata.json');
        $metadata = json_decode($metadata, true);

        foreach($metadata['collections'] as $collection)
            Collection::create([
                'title' => $collection['title'],
                'location' => $collection['location'],
                'start_date' => $collection['initial_date'],
                'end_date' => $collection['end_date']
            ]);
    }
}
