<?php

namespace leinonen\Yii2Eloquent\Tests\Helpers;

use Illuminate\Database\Capsule\Manager;
use leinonen\Yii2Eloquent\Migrations\MigrateController;
use Yii;
use yii\helpers\FileHelper;

trait MigrateControllerTestTrait
{
    use \yiiunit\framework\console\controllers\MigrateControllerTestTrait;

    /**
     * {@inheritdoc}
     */
    public function setUpMigrationPath()
    {
        FileHelper::createDirectory($this->migrationPath);
        if (! file_exists($this->migrationPath)) {
            $this->markTestIncomplete('Unit tests runtime directory should have writable permissions!');
        }
    }

    /**
     * {@inehritDoc}
     * Overriden from parent to use Illuminate/database for querying.
     * @return array applied migration entries
     */
    protected function getMigrationHistory()
    {
        $history = [];

        $rows = Manager::table('migration')->get();

        foreach ($rows as $row) {
            $history[] = [
                'version' => $row->version,
                'apply_time' => $row->apply_time,
            ];
        }

        return $history;
    }

    /**
     * {@inehritDoc}
     * Overriden from parent to change the template.
     * @param string $name
     * @param string|null $date
     * @return string generated class name
     */
    protected function createMigration($name, $date = null)
    {
        if ($date === null) {
            $date = gmdate('ymd_His');
        }
        $class = 'm' . $date . '_' . $name;

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
     * Overridden from parent to inject the CapsuleManager.
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
