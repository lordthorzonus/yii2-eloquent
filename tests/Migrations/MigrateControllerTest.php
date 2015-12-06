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
        parent::setUp();
        $this->mockConsoleApplication();
        $this->migrateControllerClass = BufferableMigrateController::class;
        $this->migrationPath = dirname(__DIR__) . '/tests/migrations';
        $this->setUpMigrationPath();
    }

    public function tearDown()
    {
        Manager::schema()->dropIfExists('migration');
        $this->tearDownMigrationPath();
        parent::tearDown();
    }
}
