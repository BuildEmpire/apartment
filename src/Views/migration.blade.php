{!! $phpTag !!}

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use BuildEmpire\Apartment\ApartmentMigration;

class {{ $migrationName }} extends ApartmentMigration
{

    /**
    * You must use this table variable in your schema migrations as we prefix the schema name.
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