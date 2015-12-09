[![Latest Stable Version](https://poser.pugx.org/leinonen/yii2-eloquent/v/stable)](https://packagist.org/packages/leinonen/yii2-eloquent)  [![Total Downloads](https://poser.pugx.org/leinonen/yii2-eloquent/downloads)](https://packagist.org/packages/leinonen/yii2-eloquent)  [![Latest Unstable Version](https://poser.pugx.org/leinonen/yii2-eloquent/v/unstable)](https://packagist.org/packages/leinonen/yii2-eloquent) [![License](https://poser.pugx.org/leinonen/yii2-eloquent/license)](https://packagist.org/packages/leinonen/yii2-eloquent) [![Build Status](https://travis-ci.org/lordthorzonus/yii2-eloquent.svg?branch=master)](https://travis-ci.org/lordthorzonus/yii2-eloquent)  [![SensioLabsInsight](https://insight.sensiolabs.com/projects/26eba504-654a-420b-bf66-594773b20218/mini.png)](https://insight.sensiolabs.com/projects/26eba504-654a-420b-bf66-594773b20218)

# Yii2-eloquent
A drop in Laravels Eloquent and Illuminate/Database implementation for Yii2

## Features: ##
- [x] Working extension (Still need to check Eloquent events and pagination)
- [x] Migrations
- [x] Fixtures
- [x] Yii style model validation for Eloquent models
- [x] Ability to feed Eloquent models to ActiveForm widget
- [ ] Model factories for use with testing instead of Fixtures?
- [ ] Other Laravel test helpers for Eloquent models?
- [ ] Adapter for Yii::$app->db? It's confusing to use it now in IDE with autocompletion and creating an own Yii base class is too much work
- [ ] Better Docs

## Configuration: ##
To configure the package just override and bootstrap your Yii db component in the application config. 
```php
use leinonen\Yii2Eloquent\Yii2Eloquent;
...
'bootstrap' => ['db'],
'components' => [
    'db' => [
        'class' => Yii2Eloquent::class,
        'driver' => 'mysql',
        'database' => 'yii2basic',
        'prefix' => '',
        'host' => 'localhost',
        'username' => 'root',
        'password' => 'secret',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ];
```

## Usage: ##
Like said, by default the package overrides Yii's database component. So one you can access `Illuminate\Database\Capsule\Manager` like this:

```php
use Illuminate\Database\Schema\Blueprint;

Yii::$app->db->schema()->create('user', function (Blueprint $table) {
    $table->increments('id');
    $table->string('email')->unique();
    $table->timestamps();
});
```

However its much more preferable to access the capsule with static methods or inject it via dependency injection. IDEs cannot autocomplete accessing the db component from the Yii god object.

```php
use Illuminate\Database\Capsule\Manager as Capsule;

$users = Capsule::table('users')->where('votes', '>', 100)->get();

$results = Capsule::select('select * from users where id = ?', array(1));
```

For complete docs please refer to: [Illuminate\Database](https://github.com/illuminate/database) and [Laravel](http://laravel.com/docs/master/database)

### Eloquent ###
Using Eloquent is covered in [Laravels documentation](http://laravel.com/docs/master/eloquent). However this package provides also an altenrative base model which to extend form. By extending from `leinonen\Yii2Eloquent\Eloquent\Model` instead of `Illuminate\Database\Eloquent\Model` you'll get some Yii functionalities to your Eloquent models. For example you can then declare `rules()` `scenarios()` etc. as you are used with Yiis AR models. These can then be also fed to ActiveForm widget, validated using `$model->validate()` etc.

Simple example of Eloquent Model:
```php
use leinonen\Yii2Eloquent\Eloquent\Model;

class Order extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'order';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['address', 'required'],
            ['address', 'string', 'min' => 3],
        ];
    }
}
```

The package also provides a simple trait to make your Eloquent user model compatible with Yii's IdentityInterface. Just `use leinonen\Yii2Eloquent\Eloquent\AuthenticatableTrait;`

## Migrations ##
If you want to use Yiis basic migration commands you have overwrite the default migration controller in the console app config. 
```php
use leinonen\Yii2Eloquent\Migrations\MigrateController;

'controllerMap' => [
      'migrate' => [
            'class' => MigrateController::class,
      ],
],
```
Then all the commands `php yii migrate/create` etc. will work as expected. Note that you have to use the `Capsule`in migrations instead of refering to Yiis base migration class:

```php
use Illuminate\Database\Capsule\Manager as Capsule;
use yii\db\MigrationInterface;

class myMigration implements MigrationInterface
{
    public function up()
    {
        Capsule::schema()->create('my_table', function($table){
            $table->increments('id');
        });
    }
      
    public function down()
    {
        Capsule::schema()->dropIfExists('my_table');
    }
}
```

## Fixtures ##
If you like Yiis fixtures no worries! Just extend `leinonen\Yii2Eloquent\Fixtures\EloquentFixture` when creating the Fixture class and everything will work as usual:

```php
use leinonen\Yii2Eloquent\Fixtures\EloquentFixture;

class OrderFixture extends EloquentFixture
{
    public $modelClass = Order::class;

    public function getData()
    {
        return [
            'example1' => [
                'address' => 'Test address',
            ],
        ];
    }
}
```
