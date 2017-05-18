<?php

namespace BuildEmpire\Apartment\Commands;

use BuildEmpire\Apartment\Schema;
use Illuminate\Console\Command;
use BuildEmpire\Apartment\Exceptions\SchemaDoesntExistException;
use BuildEmpire\Apartment\Migrator;
use Carbon\Carbon;

class ApartmentListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apartment:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all apartments';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(Schema $schema)
    {
        $apartments = $schema->getAllSchemas();

        $tableHeader = [
            'Apartment',
            'Created At'
        ];

        foreach($apartments as $apartment) {
            $metadata = app('db')->table($apartment->name . '.apartment_metadata')
                ->select()
                ->first();

            $createdAt = Carbon::createFromTimestamp($metadata->created_at);

            $apartmentsTableResults[] = [
                'apartment' => $apartment->name,
                'created_at' => $createdAt
            ];
        }

        if (count($apartments) == 0) {
            $this->info('No apartments have been created');
            return false;
        }

        $this->table($tableHeader, $apartmentsTableResults);

        return true;
    }
}