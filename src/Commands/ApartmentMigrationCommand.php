<?php

namespace BuildEmpire\Apartment\Commands;

use BuildEmpire\Apartment\ArtisanApartmentCommands;
use Illuminate\Console\Command;
use BuildEmpire\Apartment\Exceptions\SchemaDoesntExistException;
use Carbon\Carbon;
use Illuminate\Support\Composer;

class ApartmentMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apartment:migration {migrationName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new apartment migration file.';

    /**
     * Composer command.
     *
     * @var Composer
     */
    protected $composer;

    public function __construct(Composer $composer)
    {
        parent::__construct();
        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(ArtisanApartmentCommands $artisanApartmentCommands)
    {
        $migrationName = $this->argument('migrationName');
        $phpTag = '<?php';

        $migrationFileContent = View('apartment::migration', compact('migrationName', 'phpTag'))->render();

        $this->createMigrationFile($migrationFileContent, $migrationName);

        $this->line("<info>Apartment Migration Created:</info> {$migrationName}");

        $this->composer->dumpAutoloads();

        return true;
    }

    /**
     * Try to create migration file.
     *
     * @param $contents
     * @param $migrationName
     */
    protected function createMigrationFile($contents, $migrationName) {
        $currentDateTime = Carbon::now()->format('Y_m_d_His');
        $migrationFileName = $currentDateTime . '_' . $migrationName . '.php';

        $file = join(DIRECTORY_SEPARATOR, [base_path() , 'database', 'migrations', $migrationFileName]);

        $migrationFile = fopen($file, "w");
        fwrite($migrationFile, $contents);
        fclose($migrationFile);
    }
}