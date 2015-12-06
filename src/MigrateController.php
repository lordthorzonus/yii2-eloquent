<?php

namespace leinonen\Yii2Eloquent;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;
use yii\base\Module;
use yii\console\controllers\BaseMigrateController;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

class MigrateController extends BaseMigrateController
{
    /**
     * @var string the name of the table for keeping applied migration information.
     */
    public $migrationTable = 'migration';

    /**
     * {@inheritdoc}
     */
    public $templateFile = __DIR__ . '/MigrationTemplates/MigrationTemplate.php';

    /**
     * @var Manager
     */
    protected $db;

    /**
     * Ïnitiates a new MigrateController.
     *
     * @param string $id
     * @param Module $module
     * @param Manager $capsule
     * @param array $config
     */
    public function __construct($id, Module $module, Manager $capsule, $config = [])
    {
        $this->db = $capsule;

        parent::__construct($id, $module, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function options($actionID)
    {
        return array_merge(
            parent::options($actionID),
            ['migrationTable', 'db']
        );
    }

    /**
     * Returns the migration history.
     *
     * @param int $limit the maximum number of records in the history to be returned. `null` for "no limit".
     *
     * @return array the migration history
     */
    protected function getMigrationHistory($limit)
    {
        if (! $this->db->schema()->hasTable($this->migrationTable)) {
            $this->createMigrationHistoryTable();
        }

        $rows = $this->db->table($this->migrationTable)
            ->select(['version', 'apply_time'])
            ->orderByRaw('apply_time DESC, version DESC')
            ->limit($limit)
            ->get();

        $history = ArrayHelper::map($rows, 'version', 'apply_time');

        unset($history[self::BASE_MIGRATION]);

        return $history;
    }

    /**
     * Creates the migration history table.
     */
    protected function createMigrationHistoryTable()
    {
        $tableName = $this->migrationTable;
        $this->stdout("Creating migration history table \"$tableName\"...", Console::FG_YELLOW);

        $this->db->schema()->create($this->migrationTable, function (Blueprint $table) {
            $table->string('version', 180);
            $table->primary('version');
            $table->integer('apply_time');
        });

        $this->addMigrationHistory(self::BASE_MIGRATION);

        $this->stdout("Done.\n", Console::FG_GREEN);
    }

    /**
     * Adds new migration entry to the history.
     *
     * @param string $version migration version name.
     */
    protected function addMigrationHistory($version)
    {
        $this->db->table($this->migrationTable)->insert([
            'version' => $version,
            'apply_time' => time(),
        ]);
    }

    /**
     * Removes existing migration from the history.
     *
     * @param string $version migration version name.
     */
    protected function removeMigrationHistory($version)
    {
        $this->db
            ->table($this->migrationTable)
            ->where(['version' => $version])
            ->delete();
    }
}
