<?php

namespace leinonen\Yii2Eloquent\Tests\Migrations;

use Illuminate\Database\Capsule\Manager;
use leinonen\Yii2Eloquent\Tests\Helpers\BufferableMigrateController;
use leinonen\Yii2Eloquent\Tests\Helpers\MigrateControllerTestTrait;
use leinonen\Yii2Eloquent\Tests\TestCase;
use leinonen\Yii2Eloquent\Yii2Eloquent;

class MigrateControllerTest extends TestCase
{
    use MigrateControllerTestTrait;

    public function setUp()
    {
        $this->mockConsoleApplication([
            'bootstrap' => ['db'],
            'components' => [
                'db' => [
                    'class' => Yii2Eloquent::class,
                    'driver' => getenv('DB_DRIVER'),
                    'database' => getenv('DB_NAME'),
                    'prefix' => '',
                    'host' => getenv('DB_HOST'),
                    'username' => getenv('DB_USERNAME'),
                    'password' => getenv('DB_PASSWORD'),
                    'charset' => 'utf8',
                    'collation' => 'utf8_unicode_ci',

                ],
            ],
        ]);

        $this->migrateControllerClass = BufferableMigrateController::class;
        $this->migrationPath = dirname(__DIR__) . '/tests/migrations';
        $this->setUpMigrationPath();
        parent::setUp();
    }

    public function tearDown()
    {
        Manager::schema()->dropIfExists('migration');
        $this->tearDownMigrationPath();
        parent::tearDown();
    }
}
