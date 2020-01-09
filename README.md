BuildEmpire/Apartment
=========================

Database multi-tenancy for Laravel applications using PostgreSQL and Eloquent

This package provides the ability to utilise PostgreSQL's schemas within your Laravel application. Apartment facilitates
the creation, update, and management of your multi tenants while using many of the artisan migration commands you
are already familiar with.

Installation
------------

First, install the package using composer and dump composer's auto-load.
```sh
composer require buildempire/apartment
composer dump-autoload
```

If you wish to use Apartment's middleware you can also add that to your /app/Http/Kernel.php file.
```php
protected $routeMiddleware = [
        'apartment'      => \BuildEmpire\Apartment\Middleware\ApartmentMiddleware::class,
        ...
    ];
```

Command Line Usage with Artisan
===============================

All commands for apartment are available within artisan.

Apartment uses the same process to manage migrations as in Laravel, which is with the artisan migration commands:

```sh
migrate
migrate:install      Create the migration repository
migrate:refresh      Reset and re-run all migrations
migrate:reset        Rollback all database migrations
migrate:rollback     Rollback the last database migration
migrate:status       Show the status of each migration
```

Important: Your migrations can consist of two types of migration files. A standard Laravel migration or an
Apartment migration.

A standard Laravel migration file will ONLY be applied to the public schema.

An apartment migration will be applied to all apartments/schemas EXCEPT public.

To learn how to create an Apartment migration see the

[Creating a new Apartment Migration](#creating-a-new-apartment-migration)


Creating a new Apartment
------------------------

To create your first apartment/schema enter:
```sh
php artisan apartment:make [name of schema/apartment]
```
Important: Apartment names can only start with a letter and may only contain lowercase letters, numbers, hyphen, or underscores.
You cannot create a schema named public, as this is the default schema used by PostgreSQL which Laravel uses by default.

Listing Apartments
------------------

Sometimes it's important to view what apartments have already been created and this can be achieved using the command:
```sh
php artisan apartment:list

```

which will return a list of the existing apartments/schemas.

Creating a new Apartment Migration
----------------------------------
Apartment tries to use the same process to manage migrations as in Laravel, which is with the artisan migration commands.
The only difference occurs when creating an apartment migration file using the command:

```
php artisan apartment:migration [name of migration]
```

which will create a file within your migrations folder. In this example we've created a migration called CreateProductsTable
which created the file: 2017_05_17_203315_CreateProductsTable.php

```php
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

```php
protected $table = '';
```
and the schema calls
```php
Schema::create($this->table, functi....
Schema::drop($this->table);
```


You must specify the table name that you wish to update, create or any other operation you wish to do in the property table.
You also must make sure that any references that Schema makes to a table is using the `$this->table` property.

In many cases this is simply a case of updating the $table property. In this example we've updated it to:

```php
protected $table = 'products';
```

The schemas in this example are already pointed to the $this->table property so we don't need to change anything related
to the Schema calls.

If you need to access tables within the current schema as part of a migration (e.g. migrating data), the current schema
name is passed as the first argument to both `apartmentUp` and `apartmentDown`, allowing you to do something like:

```php
class MyDataMigration extends ApartmentMigration
{
    protected $apartmentSchema;

    public function __construct(ApartmentSchema $apartmentSchema)
    {
        $this->apartmentSchema = $apartmentSchema;
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function apartmentUp(string $schemaName)
    {
        $this->apartmentSchema->setSchemaName($schemaName);

        $models = SomeApartmentModel::all();

        $this->migrateModels($models);
    }

    private function migrateModels($models)
    {
        // ...Migrate the model data and save the changes...
    }
}

```

Migrating an Apartment
----------------------

Migration, from a user's standpoint, runs exactly the same as a normal laravel application. Simple run:

```sh
php artisan migrate
```

This will apply all the standard Laravel migrations to the public schema ONLY and all the apartment migrations to all the
schemas available and NOT to public.

Important: The public database will not contain any of the apartment migrations. If you want the public schema to
contain similar migrations to your apartments simply create a standard migration matching your apartment's migrations.

Deleting an Apartment
---------------------
You can delete an apartment and its contents by using the command:

```sh
php artisan apartment:drop [apartment name]
```

This will only delete the schema and data for that apartment.

Within your Laravel Application
=============================

You can also use your apartments within your Laravel application for creating apartments, using models and etc.

Setting the Global Apartment per Request
----------------------------------------
Apartment registers the Schema class as a singleton within Laravel allowing it to keep a consistent object throughout the
application's request.

To apply a global schema within your own application you could do something like this:

```php
/**
* Import Schema singleton instance.
*
* Laravel will handle this via the dependency injection
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

Make a model as normal, then modify it to extend `ApartmentModel`, e.g.

```php
<?php
namespace App;
use BuildEmpire\Apartment\ApartmentModel;
class Product extends ApartmentModel
{
...
```

When you access the model, apartment will automatically set the model to use the schema set in Global Apartment. You
can also specify what apartment you want to use instead of using the global schema by doing:

```php
$products = new App\Product(['apartment' => 'nameofapartment']);
```

or

```php
$products->setApartment('nameofapartment');
```


Middleware
----------
Apartment provides a middleware feature out of the box by adding the following to your /app/Http/Kernel.php file:

```php
protected $routeMiddleware = [
        'apartment'      => \BuildEmpire\Apartment\Middleware\ApartmentMiddleware::class,
        ...
    ];
```

The middleware takes the first subdomain and checks if that apartment exists and then sets the current global apartment
schema to that value. Any calls to any apartment models, unless specified during the model's creation, will automatically use the apartment set within the global apartment schema.

```php
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

Important: You should write your own authentication method before apartment to make sure that the user has
permission to access it. This falls outside of apartment's scope of work.

Creating a new apartment programmatically
-----------------------------------------
You can create a new apartment within your application:

```php
$schema = new BuildEmpire\Apartment\Schema;
$apartmentArtisan = new BuildEmpire\Apartment\ArtisanApartmentCommands($schema);

$apartmentArtisan->makeSchema($apartmentName);

if ($schema->doesSchemaExist($apartmentName)) {
    return 'Schema created!';
}
```

This will create the apartment/schema and run all the migrations. For an application with a large set of migrations this might best be done within a background job.

Seeding Data to a new Apartment
-------------------------------

Coming soon.

Support
-------

BuildEmpire will continue to fix any issues with the package when possible. However, if you require a more custom
support for your business please contact us directly at: http://www.buildempire.co.uk.
