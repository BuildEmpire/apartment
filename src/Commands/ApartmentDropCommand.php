<?php

namespace BuildEmpire\Apartment\Commands;

use BuildEmpire\Apartment\ArtisanApartmentCommands;
use Illuminate\Console\Command;
use BuildEmpire\Apartment\Exceptions\SchemaDoesntExistException;

class ApartmentDropCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apartment:drop {schemaName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drop an apartment.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(ArtisanApartmentCommands $artisanApartmentCommands)
    {
        $schemaName = $this->argument('schemaName');

        try {
            $artisanApartmentCommands->dropSchema($schemaName);
        } catch (SchemaDoesntExistException $e) {
            $this->error($e->getMessage());
            return false;
        }

        $this->line("<info>Dropped Apartment Schema:</info> {$schemaName}");

        return true;
    }
}