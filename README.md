# Very short description of the package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rennypoz/eavquent.svg?style=flat-square)](https://packagist.org/packages/rennypoz/eavquent)
[![Build Status](https://img.shields.io/travis/rennypoz/eavquent/master.svg?style=flat-square)](https://travis-ci.org/rennypoz/eavquent)
[![Quality Score](https://img.shields.io/scrutinizer/g/rennypoz/eavquent.svg?style=flat-square)](https://scrutinizer-ci.com/g/rennypoz/eavquent)
[![Total Downloads](https://img.shields.io/packagist/dt/rennypoz/eavquent.svg?style=flat-square)](https://packagist.org/packages/rennypoz/eavquent)

Needs EAV Models in Laravel? Easy! With this package you can implement Entity Attribute Values Models using the Original Eloquent Api's!

Let's see an example of the result:

|Entity ID       |Entity Attribute               |Entity Value                 |
|----------------|-------------------------------|-----------------------------|
|1				 |name           				 |John           			   |
|1          	 |surname        				 |Doe           			   |
|1               |job							 |Laravel Developer			   |
|2				 |name           				 |Donald           			   |
|2          	 |surname        				 |Trump         			   |
|2               |job							 |USA President			   	   |
|2				 |height						 |1.9 Meters				   |

As you can see, with the EAV Model, you can add dynamically the fields in your entity, without worrying about you table Schema.

Check out the **Usage** Section in order to start make the Magic!

## Installation

You can install the package via composer:

```bash
composer require rennypoz/eavquent
```

## Usage
First we need to do make the proper migration for our Model, for example the Car Model:


``` php
public function up()
{
    Schema::create('cars', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->integer('entity_id');
        $table->string('entity_attribute');
        $table->text('entity_value');
        // $table->timestamps(); We don't need this.
    });
}
```

Next, we have to create our Model:

``` php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    
}
```

When you finished, import the Eavquent trait to your model, and you're Done!

``` php
<?php

namespace App;

use Rennypoz\Eavquent\Traits\Eavquent;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use Eavquent;
}
```

## Available Features

Currently this package supports some Eloquent Features, i will add extra features in the next versions of the package. All the contributions are really apprecciated! :)

### Create 

``` php
public function create(Request $request)
{
    $c = new Car();
    $c->brand = $request->brand;
    $c->model = $request->model;
    $c->anyKeyYouWant = $anyValueYouWant;
    $c->save();
}
```

### Find

``` php
public function show($id)
{
    return Car::find($id);
}
```

### Update

``` php
public function update(Request $request, $id)
{
    $c = Car::find($id);
    $c->brand = 'Bmw';
    $c->model = 'M4 GTS';
    $c->save();
}
```

### Delete

``` php
public function delete($id)
{
    Car::find($id)->delete();
}
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please open an issue on the issue tracker.

## Credits

- [Renato Pozzi](https://github.com/rennypoz)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).