<?php

namespace BuildEmpire\Apartment\Commands;

use BuildEmpire\Apartment\Schema;
use Illuminate\Console\Command;
use BuildEmpire\Apartment\Exceptions\SchemaDoesntExist;
use BuildEmpire\Apartment\Migrator;

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
        $apartments = $schema->listAllSchemas();
        foreach($apartments as $apartment) {
            $this->line("<info>{$apartment->schema_name}</info> - created: {$apartment->created_at}");
        }

        return true;
    }
}