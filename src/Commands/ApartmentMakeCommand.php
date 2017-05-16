<?php

namespace BuildEmpire\Apartment\Commands;

use BuildEmpire\Apartment\Schema;
use Illuminate\Console\Command;
use BuildEmpire\Apartment\Exceptions\SchemaAlreadyExists;

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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(Schema $schema)
    {
        $schemaName = $this->argument('schemaName');

        try {
            $schema->tryMakeSchema($schemaName);
        } catch (SchemaAlreadyExists $e) {
            $this->error($e->getMessage());
            return false;
        }

        $this->line("<info>Created Apartment Schema:</info> {$schemaName}");

        return true;
    }
}