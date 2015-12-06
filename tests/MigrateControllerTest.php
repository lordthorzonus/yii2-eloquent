<?php


namespace leinonen\Yii2Eloquent\Tests;


use Illuminate\Database\Capsule\Manager;
use leinonen\Yii2Eloquent\MigrateController;
use leinonen\Yii2Eloquent\Yii2Eloquent;
use Yii;
use yii\helpers\FileHelper;
use yiiunit\framework\console\controllers\MigrateControllerTestTrait;

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

                ]
            ]
        ]);

        $this->migrateControllerClass = MigrateController::class;
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

    public function setUpMigrationPath()
    {
        FileHelper::createDirectory($this->migrationPath);
        if (!file_exists($this->migrationPath)) {
            $this->markTestIncomplete('Unit tests runtime directory should have writable permissions!');
        }
    }

    public function testHistory()
    {
        // Somehow there is output on the console so cant figure out how to test this
    }

    public function testNew()
    {
        // Somehow there is output on the console so cant figure out how to test this
    }


    /**
     * @return array applied migration entries
     */
    protected function getMigrationHistory()
    {
        $history = [];

        $rows = Manager::table('migration')->get();

        foreach ($rows as $row) {
            $history[] = [
                'version' => $row->version,
                'apply_time' => $row->apply_time
            ];
        }

        return $history;
    }

    /**
     * @param string $name
     * @param string|null $date
     * @return string generated class name
     */
    protected function createMigration($name, $date = null)
    {
        if ($date === null) {
            $date = gmdate('ymd_His');
        }
        $class = 'm' . $date . '_' . $name;;

        $code = <<<CODE
<?php
use yii\db\MigrationInterface;

class {$class} implements  MigrationInterface
{
    public function up()
    {
    }

    public function down()
    {
    }
}
CODE;
        file_put_contents($this->migrationPath . DIRECTORY_SEPARATOR . $class . '.php', $code);
        return $class;
    }

    /**
     * Creates test migrate controller instance.
     * @return MigrateController migrate command instance.
     */
    protected function createMigrateController()
    {
        $module = $this->getMock('yii\\base\\Module', ['fake'], ['console']);
        $class = $this->migrateControllerClass;
        $migrateController = new $class('migrate', $module, Yii::$app->db);
        $migrateController->interactive = false;
        $migrateController->migrationPath = $this->migrationPath;
        return $migrateController;
    }
}