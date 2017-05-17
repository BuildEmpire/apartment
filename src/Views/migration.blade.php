{!! $phpTag !!}

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use BuildEmpire\Apartment\ApartmentMigration;

/**
 * Apartment migration file.
 *
 * You must set the table name with the table property, and place it anywhere you reference the table in your schemas.
 */
class {{ $migrationName }} extends ApartmentMigration
{
    /**
    * You must use this table variable in your schema migrations as the table name.
    */
    protected $table = '';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function apartmentUp()
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->increments('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function apartmentDown()
    {
        Schema::drop($this->table);
    }
}