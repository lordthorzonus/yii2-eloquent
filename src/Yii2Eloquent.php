<?php


namespace leinonen\Yii2Eloquent;


use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Events\Dispatcher;
use yii\base\BootstrapInterface;
use yii\base\Component;

class Yii2Eloquent extends Component implements BootstrapInterface
{

    public $driver;

    public $host;

    public $database;

    public $username;

    public $password;

    public $charset;

    public $collation;

    public $prefix;

    private $capsule;

    public function bootstrap($app)
    {
        $this->capsule = new Manager();
        $this->capsule->addConnection([
            'driver'    => $this->driver,
            'host'      => $this->host,
            'database'  => $this->database,
            'username'  => $this->username,
            'password'  => $this->password,
            'charset'   => $this->charset,
            'collation' => $this->collation,
            'prefix'    => $this->prefix,
        ]);

        $this->capsule->setEventDispatcher(new Dispatcher(new Container()));

        $this->capsule->setAsGlobal();

        $this->capsule->bootEloquent();

        $app->set('db', $this->capsule);

    }

}