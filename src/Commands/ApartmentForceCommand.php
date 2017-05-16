<?php

namespace BuildEmpire\Apartment\Commands;

use BuildEmpire\Apartment\Schema;
use Illuminate\Console\Command;
use BuildEmpire\Apartment\Exceptions\SchemaDoesntExist;

class ApartmentForceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apartment:force';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add any existing schemas missing from the apartment table.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(Schema $schema)
    {

        try {
            $schema->tryDropSchema($schemaName);
        } catch (SchemaDoesntExist $e) {
            $this->error($e->getMessage());
            return false;
        }

        $this->line("<info>Dropped Apartment Schema:</info> {$schemaName}");

        return true;
    }
}