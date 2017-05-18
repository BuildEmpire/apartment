<?php

namespace BuildEmpire\Apartment\Commands;

use BuildEmpire\Apartment\ArtisanApartmentCommands;
use Illuminate\Console\Command;
use BuildEmpire\Apartment\Exceptions\SchemaAlreadyExistsException;
use Illuminate\Support\Composer;

class ApartmentMakeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apartment:make {schemaName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add an apartment.';

    /**
     * ApartmentMakeCommand constructor.
     * @param Composer $composer
     */
    public function __construct(Composer $composer)
    {
        parent::__construct();
        $composer->dumpAutoloads();
    }

    /**
     * Execute the console command.
     *
     * @param ArtisanApartmentCommands $artisanApartmentCommands
     * @return bool
     */
    public function handle(ArtisanApartmentCommands $artisanApartmentCommands)
    {
        $schemaName = $this->argument('schemaName');

        try {
            $artisanApartmentCommands->makeSchema($schemaName);
        } catch (SchemaAlreadyExistsException $e) {
            $this->error($e->getMessage());
            return false;
        }

        $this->line("<info>Created Apartment Schema:</info> {$schemaName}");

        return true;
    }
}