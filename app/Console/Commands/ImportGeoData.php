<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Models\Street;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportGeoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geo:import {file : The path to the CSV file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import city and street data from a CSV file';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = $this->argument('file');

        if (! File::exists($filePath)) {
            $this->error("File not found: {$filePath}");

            return 1;
        }

        $file = fopen($filePath, 'r');
        $header = fgetcsv($file);

        if (! $header) {
            $this->error('CSV file is empty.');

            return 1;
        }

        // Expected columns: city_code, city_name, street_code, street_name
        $expectedColumns = ['city_code', 'city_name', 'street_code', 'street_name'];
        if (count(array_intersect($header, $expectedColumns)) < 4) {
            $this->error('CSV must have: city_code, city_name, street_code, street_name');

            return 1;
        }

        $columnIndex = array_flip($header);
        $count = 0;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($file)) !== false) {
                $cityCode = $row[$columnIndex['city_code']];
                $cityName = $row[$columnIndex['city_name']];
                // streetCode is transient, not stored
                $streetName = $row[$columnIndex['street_name']];

                // City logic
                $city = City::firstOrCreate(
                    ['code' => $cityCode],
                    ['name' => $cityName]
                );

                if ($city->name !== $cityName) {
                    $city->update(['name' => $cityName]);
                }

                // Street logic
                Street::firstOrCreate(
                    ['city_id' => $city->id, 'name' => $streetName]
                );

                $count++;
            }
            DB::commit();
            $this->info("Imported {$count} lines successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Import failed: '.$e->getMessage());

            return 1;
        }

        fclose($file);

        return 0;
    }
}
