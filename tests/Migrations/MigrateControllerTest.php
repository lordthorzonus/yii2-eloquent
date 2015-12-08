<?php

namespace leinonen\Yii2Eloquent\Tests\Migrations;

use Illuminate\Database\Capsule\Manager;
use leinonen\Yii2Eloquent\Tests\Helpers\BufferableMigrateController;
use leinonen\Yii2Eloquent\Tests\Helpers\MigrateControllerTestTrait;
use leinonen\Yii2Eloquent\Tests\TestCase;

class MigrateControllerTest extends TestCase
{
    use MigrateControllerTestTrait;

    public function setUp()
    {
        parent::setUp();
        $this->mockConsoleApplication();
        $this->migrateControllerClass = BufferableMigrateController::class;
        $this->migrationPath = dirname(__DIR__) . '/_files/migrations';
        $this->setUpMigrationPath();
    }

    public function tearDown()
    {
        Manager::schema()->dropIfExists('migration');
        $this->tearDownMigrationPath();
        parent::tearDown();
    }
}
