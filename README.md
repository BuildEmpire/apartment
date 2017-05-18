BuildEmpire/Apartment
=========================

Database multi-tenancy for lumen applications using PostgreSQL and Eloquent

This package provides the ability to utilise PostgreSQL's schemas within your Lumen application. Apartment facilitates 
the creation, update, and management of your multi multi tenants while using many of the artisan migration commands you 
are already familiar using already.
    
Installation
------------

First, install the package using composer and dump composer's auto-load.
```
composer require buildempire/apartment
composer dump-autoload
```

Then add the BuildEmpire/Apartment provider to your /bootstrap/app.php file.
```
$app->register(BuildEmpire\Apartment\Providers\ApartmentServiceProvider::class);
```

You must also enable Facades and Eloquent:

```
$app->withFacades();

$app->withEloquent();
```

If you wish to use Apartment's middleware you can also add that to your /bootstrap/app.php file.
```
$app->routeMiddleware([
    'apartment' => BuildEmpire\Apartment\Middleware\ApartmentMiddleware::class
]);
```

<p align="center">

Command Line Usage using Artisan
--------------------------------

All commands for apartment are available within artisan. 

Apartment uses the same process to manage migrations as in Lumen, which is with the artisan migration commands:

```
migrate
migrate:install      Create the migration repository
migrate:refresh      Reset and re-run all migrations
migrate:reset        Rollback all database migrations
migrate:rollback     Rollback the last database migration
migrate:status       Show the status of each migration
```

<b>Important:</b> Your migrations can consist of two types of migration files. A standard Lumen migration or an 
Apartment migration. A standard Lumen migration file will ONLY be applied to the public schema whereas the Apartment
migration files will ONLY be applied to any available schemas. To learn how to create an Apartment migration see the 
Creating a new Apartment Migration section. 


</p>

Creating a new Apartment
------------------------

To create your first apartment/schema simple enter:
```
php artisan apartment:make [name of schema/apartment]
```
<b>Important:</b> Apartment names can only start with a letter and may only contain lowercase letters, numbers, or underscores.

Listing Apartments
------------------

Sometimes it's important to view what apartments have already been created and this can be achived by using the command:
```
php artisan apartment:list

```

which will return a list of schemas.

Creating a new Apartment Migration
----------------------------------
Apartment tries to use the same process to manage migrations as in Lumen, which is with the artisan migration commands. 
The only difference occurs when creating an apartment migration file using the command:
 
```
php artisan apartment:migration [name of migration]
```

which will create a file within your migrations folder. In this example I created a migration called CreateProductsTable 
which created the file: 2017_05_17_203315_CreateProductsTable.php

```
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use BuildEmpire\Apartment\ApartmentMigration;

/**
 * Apartment migration file.
 *
 * You must set the table name with the table property, and place it anywhere you reference the table in your schemas.
 */
class CreateProductsTable extends ApartmentMigration
{
    /**
    * You must use this table variable in your schema migrations as the table name.
    */
    protected $table = '';

    /**
     * Run the migrations.
     *
     * @return  void
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
     * @return  void
     */
    public function apartmentDown()
    {
        Schema::drop($this->table);
    }
}
```
The important things to notice in the apartment migration file are these two areas:

```
protected $table = '';
```
and the schema calls
```
Schema::create($this->table, functi....
Schema::drop($this->table);
```


You must specify the table name that you wish to update, create or any other operation you wish to do to the table and 
make sure that any references that Schema makes to a table is using the $this->table property.
   
In many cases this is simply a case of updating the $table property. In this example we've updated it to:

```
protected $table = 'products';
```

The schemas in this example are already pointed to the $this->table property so we don't need to change anything related 
to the Schema calls.

Migrating an Apartment
----------------------

Migration, from a user's standpoint, runs exactly the same as a normal laravel application. Simple run:

```
php artisan migrate
```

This will apply all the standard Lumen migrations to the public schema ONLY and the apartment migrations to all the 
apartment schemas available and NOT the public one.

<b>Important:</b> The public database will not contain any of the apartment migrations. If you want the public schema to 
contain similar migrations to your apartments simple create a standard migration matching your apartments migrations. 

Deleting an Apartment
---------------------
You can delete an apartment and its contents by using the command:

```
php artisan apartment:drop [apartment name]
```

This will only delete the schema and data for that apartment.

<p align="center">

Within your Lumen Application
-----------------------------

</p>

Setting the Global Apartment per Request
----------------------------------------
Apartment registers the Schema class as a singleton within Lumen allowing it to keep a consistent object throughout the 
application's request.

To apply a global schema within your own application you could do somethig like this:

```
/**
* Import Schema singleton instance.
* 
* Lumen will handle this via the dependency injection
*/
public function __construct(Schema $apartmentSchema)
{   
    $subdomain = 'buildempire';
    
    if ($apartmentSchema->doesSchemaExist($subdomain)) {
        $this->apartmentSchema->setSchemaName($subdomain);
    }
}
```

Using Eloquent Models to Access Apartments
------------------------------------------

Make a model as normal but extend the model class to ApartmentModel eg:

```
<?php
namespace App;
use BuildEmpire\Apartment\ApartmentModel;
class Product extends ApartmentModel
{
...
```

When you access the model apartment will automatically set the model to use the schema set in Global Apartment. You 
can also specify what apartment you want to use instead of using the global schema by doing:

```
$products = new App\Product(['apartment' => 'nameofapartment']);
```

or

```
$products->setApartment('nameofapartment');
```


Middleware
----------
Apartment provides a middleware feature out of the box by adding the following to your /bootstrap/app.php file:

```
$app->routeMiddleware([
    'apartment' => BuildEmpire\Apartment\Middleware\ApartmentMiddleware::class
]);
```

The middleware takes the first subdomain and checks if that apartment exists and then sets the current global apartment 
schema to that value. Any calls to any apartment models, unless specified instantiation, will automatically use the 
apartment set within the global apartment schema.

```
public function __construct(Schema $apartmentSchema)
{
        $this->apartmentSchema = $apartmentSchema;
}

/**
* Handle an incoming request.
*
* @param  \Illuminate\Http\Request  $request
* @param  \Closure  $next
* @return mixed
*/
public function handle($request, Closure $next)
{
    $subdomain = explode('.', $request->getHost())[0];
    if ($this->apartmentSchema->doesSchemaExist($subdomain)) {
        // Sets the global apartment schema
        $this->apartmentSchema->setSchemaName($subdomain);
    }
    return $next($request);
}

```

<b>Important:</b> You should write your own authentication method before apartment to make sure that the user has 
permission to access it. This falls outside of the apartment's scope of work.

Creating a new apartment
------------------------
You can create a new apartment within your application:

```
$schema = new BuildEmpire\Apartment\Schema;
$apartmentArtisan = new BuildEmpire\Apartment\ArtisanApartmentCommands($schema);

$apartmentArtisan->makeSchema($apartmentName);

if ($schema->doesSchemaExist($apartmentName)) {
    return 'Schema created!';
}
```

This will create the apartment and run all the migrations. For an application with a large set of migrations this might
best be done within a background job.

Seeding Data to a new Apartment
-------------------------------

Coming soon.

Support
-------

BuildEmpire will continue to fix any issues with the package when possible. However, if you require a more custom 
support for your business please contact us directly at: http://www.buildempire.co.uk.
