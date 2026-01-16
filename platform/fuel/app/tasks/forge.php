<?php

namespace Fuel\Tasks;

use Container;
use Doctrine\Inflector\Inflector;
use Exception;
use Fuel\Core\Cli;
use Helper_File;
use Helpers\CaseHelper;
use Helpers\TypeHelper;
use Helpers_Time;
use Classes\Orm\AbstractOrmModel;
use Task_Cli;
use Fuel\Tasks\ForgeWordpressSeeder;

final class Forge extends Task_Cli
{
    // 'short_type' => 'long_type'
    public const DATA_TYPES = [
        'int'       => 'integer',
        'float'     => 'float',
        'bool'      => 'boolean',
        'string'    => 'string',
        'datetime'  => 'datetime',
        'array'     => 'array'
    ];

    public const CAST_DATA_TYPES = [
        'int'       => 'self::CAST_INT',
        'float'     => 'self::CAST_FLOAT',
        'bool'      => 'self::CAST_BOOL',
        'string'    => 'self::CAST_STRING',
        'datetime'  => 'self::CAST_CARBON',
        'array'     => 'self::CAST_ARRAY'
    ];

    public const DATABASE_TYPES = [
        'integer'   => "['type' => 'int', 'constraint' => 3, 'unsigned' => true]",
        'float'     => "['type' => 'decimal', 'constraint' => [5,2]]",
        'boolean'   => "['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true]",
        'string'    => "['type' => 'varchar', 'constraint' => 255]",
        'datetime'  => "['type' => 'datetime']",
        'array'     => "['type' => 'text']"
    ];

    public const RELATIONS = [
        AbstractOrmModel::BELONGS_TO,
        AbstractOrmModel::HAS_ONE,
        AbstractOrmModel::HAS_MANY
    ];

    public const RELATIONS_VARIABLES_NAMES = [
        AbstractOrmModel::BELONGS_TO => 'BELONGS_TO',
        AbstractOrmModel::HAS_ONE => 'HAS_ONE',
        AbstractOrmModel::HAS_MANY => 'HAS_MANY'
    ];

    private Inflector $inflector;
    private ForgeWordpressSeeder $forgeWordpressSeeder;

    public function __construct()
    {
        $this->disableOnProduction();

        $this->inflector = Container::get(Inflector::class);
        $this->forgeWordpressSeeder = Container::get(ForgeWordpressSeeder::class);
    }

    /**
     * Create fuel migration.
     * E.g. php oil r forge:migration create_test_table
     */
    public function migration(string $migration_name): void
    {
        $classname = preg_replace_callback('/(?<=_)[a-z]/', function (array $matches): string {
            return strtoupper($matches[0]);
        }, ucfirst($migration_name));
        $template =
            "<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class $classname extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::modify_fields(
            'raffle_log',
            [
                'message' => [
                    'type' => 'text',
                ],
            ]
        );
        DBUtil::add_fields(
            'whitelabel_transaction',
            [
                'payment_attempts_count' => [
                    'type' => 'smallint',
                    'default' => 0,
                    'after' => 'payment_attempt_date'
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::modify_fields(
            'raffle_log',
            [
                'message' => [
                    'type' => 'varchar',
                    'constraint'=> 255
                ],
            ]
        );
        DBUtil::drop_fields(
            'whitelabel_transaction',
            [
                'payment_attempts_count',
            ]
        );
    }
}
";
        $migration_filename = Helpers_Time::migration_time_prefix() . '_' . $migration_name . '.php';
        $migration_absolute_filepath = Helper_File::app_path('migrations', $migration_filename);
        file_put_contents($migration_absolute_filepath, $template);
        echo "Created $migration_absolute_filepath" . PHP_EOL;
    }

    public function wpSeeder(string $filename): void
    {
        $this->forgeWordpressSeeder->before($filename);
        $this->forgeWordpressSeeder->createFile();
    }

    /**
     * Create new ORM model.
     * E.g. php oil r forge:model lottorisq_log
     * @param string $tableName
     */
    public function model(string $tableName): void
    {
        ############## CREATE MAIN MODEL ################

        $properties = [];
        $imports = '';
        $relations = '';
        $usedImports = [];

        $properties[] = [
            'name' => 'id',
            'annotationType' => 'int',
            'castType' => 'self::CAST_INT',
            'longType' => 'integer'
        ];

        $classname = preg_replace_callback("/(?:^|_)([a-z])/", function ($matches) {
            return strtoupper($matches[1]);
        }, $tableName);
        $modelFilename = $classname . '.php';
        $modelAbsoluteFilepath = Helper_File::app_path('classes', 'models', $modelFilename);

        if (file_exists($modelAbsoluteFilepath)) {
            Cli::write('This model already exists!', 'white', 'red');
            return;
        }

        while ($newPropertyName = Cli::prompt('Add new property (leave empty to exit)')) {
            $newPropertyType = Cli::prompt('What type is this property?', array_keys(self::DATA_TYPES));
            $newPropertyDefaultValue = Cli::prompt('Set default value (leave empty to skip)');
            $longPropertyType = self::DATA_TYPES[$newPropertyType];
            $castType = self::CAST_DATA_TYPES[$newPropertyType];

            if ($longPropertyType === 'datetime') {
                $carbonImportString = "use Carbon\Carbon;";

                if (!in_array($carbonImportString, $usedImports)) {
                    $imports .= "
$carbonImportString";
                    $usedImports[] = $carbonImportString;
                }

                $newPropertyType = 'Carbon';
            }

            $data = [
                'name' => $newPropertyName,
                'annotationType' => $newPropertyType,
                'longType' => $longPropertyType,
                'castType' => $castType
            ];

            if (!empty($newPropertyDefaultValue)) {
                if ($longPropertyType === TypeHelper::STRING) {
                    $data['default'] = "'$newPropertyDefaultValue'";
                } else {
                    try {
                        $defaultValue = TypeHelper::cast($newPropertyDefaultValue, $longPropertyType);
                        $isBool = $longPropertyType === TypeHelper::BOOLEAN;
                        if ($isBool) {
                            $defaultValue = $defaultValue ? 'true' : 'false';
                        }
                        $data['default'] = $defaultValue;
                    } catch (Exception $e) {
                        $data['default'] = "'$newPropertyDefaultValue'";
                    }
                }
            }

            $properties[] = $data;
        }

        $annotationString = '';
        $usedRelationImport = [];

        while ($newRelation = Cli::prompt('Add new relation, enter table name (leave empty to exit)')) {
            $relationClassname = preg_replace_callback("/(?:^|_)([a-z])/", function ($matches) {
                return strtoupper($matches[1]);
            }, $newRelation);
            $newRelationType = Cli::prompt('What type is this relation?', self::RELATIONS);
            $relationModelAbsoluteUrl = Helper_File::app_path('classes', 'models', "{$relationClassname}.php");

            if (empty(file_exists($relationModelAbsoluteUrl))) {
                Cli::write("This model doesn't exists", 'white', 'red');
                continue;
            }

            $propertyNotExist = true;
            foreach ($properties as $property) {
                if ($property['name'] === "{$newRelation}_id") {
                    $propertyNotExist = false;
                    break;
                }
            }

            $biggerRelationName = ucfirst($newRelationType);
            $newRelationName = $newRelationType === AbstractOrmModel::HAS_MANY ? $this->inflector->pluralize($newRelation) : $newRelation;

            $newRelationNameInCamelCase = CaseHelper::snakeToCamel($newRelationName);
            $annotationString .= "
 * @property {$biggerRelationName}|{$relationClassname} \$$newRelationNameInCamelCase";


            if (!in_array($newRelationType, $usedRelationImport)) {
                $imports .= "
use Orm\\$biggerRelationName;";
                $usedRelationImport[] = $newRelationType;
            }

            if ($propertyNotExist) {
                $properties[] = [
                    'name' => "{$newRelation}_id",
                    'annotationType' => 'int',
                    'longType' => 'integer',
                    'castType' => 'self::CAST_INT'
                ];
            }

            $variableName = 'self::' . self::RELATIONS_VARIABLES_NAMES[$newRelationType];

            $relations .= "
        {$relationClassname}::class => {$variableName},";
        }

        $properties = array_values($properties);

        $castString = '';
        $propertiesString = '';

        foreach ($properties as $key => $property) {
            ['name' => $name, 'annotationType' => $annotationType, 'castType' => $castType] = $property;

            $propertiesString .= "
        '$name'";

            if (isset($property['default'])) {
                $propertiesString .= " => ['default' => {$property['default']}]";
            }

            $castString .= "
        '$name' => $castType";

            $nameInCamelCase = CaseHelper::snakeToCamel($name);
            $annotationString .= "
 * @property $annotationType \$$nameInCamelCase";

            if (count($properties) - 1 > $key) {
                $propertiesString .= ",";
                $castString .= ",";
            }
        }

        // remove last comma
        $relations = substr($relations, 0, -1);
        $tableName = CaseHelper::pascalToSnake($tableName);
        $template =
            "<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;$imports

/**$annotationString
 */
class $classname extends AbstractOrmModel
{
    protected static string \$_table_name = '$tableName';

    protected static array \$_properties = [$propertiesString
    ];

    protected \$casts = [$castString
    ];

    protected array \$relations = [$relations
    ];

    protected array \$timezones = [
    ];

    // It is very important! Do not remove these variables!
    protected static array \$_belongs_to = [];
    protected static array \$_has_one = [];
    protected static array \$_has_many = [];
}";

        $modelProjectPath = str_replace('/var/www', '', $modelAbsoluteFilepath);
        file_put_contents($modelAbsoluteFilepath, $template);
        Cli::write("Model created $modelProjectPath", 'black', 'green');


        ############## CREATE REPOSITORY ################
        $repositoryFilename = "{$classname}Repository.php";
        $repositoryAbsoluteFilepath = Helper_File::app_path('classes', 'repositories', $repositoryFilename);
        $repositoryTemplate = "<?php

namespace Repositories;

use Repositories\Orm\AbstractRepository;
use Models\\$classname;

class {$classname}Repository extends AbstractRepository
{
    public function __construct({$classname} \$model)
    {
        parent::__construct(\$model);
    }
}
";
        $repositoryProjectPath = str_replace('/var/www', '', $repositoryAbsoluteFilepath);
        if (file_exists($repositoryAbsoluteFilepath)) {
            Cli::write("Repository $repositoryProjectPath already exists", 'white', 'red');
        } else {
            file_put_contents($repositoryAbsoluteFilepath, $repositoryTemplate);
            Cli::write("Repository created $repositoryProjectPath", 'black', 'green');
        }

        $shouldCreateMigration = Cli::prompt('Do you want to create migration?', ['y', 'n']) === 'y';

        if (!$shouldCreateMigration) {
            return;
        }

        ############## CREATE MIGRATION ################
        $migrationName = "add_table_{$tableName}";
        $migrationClassname = preg_replace_callback('/(?<=_)[a-z]/', function (array $matches): string {
            return strtoupper($matches[0]);
        }, ucfirst($migrationName));

        $databaseProperties = "";

        foreach ($properties as $key => $property) {
            if ($property['name'] === 'id') {
                continue;
            }

            $propertyOptions = self::DATABASE_TYPES[$property['longType']];

            $databaseProperties .= "
                '{$property['name']}' => $propertyOptions";

            if (count($properties) - 1 > $key) {
                $databaseProperties .= ",";
            }
        }

        $migrationTemplate =
            "<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class $migrationClassname extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::create_table(
            '$tableName',
            [
                'id' => ['type' => 'int', 'constraint' => 3, 'unsigned' => true, 'auto_increment' => true],$databaseProperties
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci'
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_table('$tableName');
    }
}";
        $migrationFilename = Helpers_Time::migration_time_prefix() . '_' . $migrationName . '.php';
        $migrationAbsoluteFilepath = Helper_File::app_path('migrations', $migrationFilename);
        file_put_contents($migrationAbsoluteFilepath, $migrationTemplate);
        Cli::write("Migration created $migrationAbsoluteFilepath", 'black', 'green');
    }

    /**
     * Create seeder from template.
     * E.g. php oil refine forge:seed ClassName
     */
    public function seed(string $seederName): void
    {
        $classname = preg_replace_callback('/(?<=_)[a-z]/', function (array $matches): string {
            return strtoupper($matches[0]);
        }, ucfirst($seederName));
        $template =
            "<?php

namespace Fuel\Tasks\Seeders;

final class $classname extends Seeder
{
    use \Without_Foreign_Key_Checks;

    /**
     * Define columns used by seeder.
     * NOTE: can be for many tables.
     *
     * @return array format 'table' => [col1...coln]
     */
    protected function columnsStaging(): array
    {
        return [
            'tablename' => ['id', 'name']
        ];
    }

    /**
     * Define rows used by seeder.
     * NOTE: can be for many tables.
     *
     * @return array format 'table' => [row1[val1...valn]...rown[val1...valn]]
     */
    protected function rowsStaging(): array
    {
        return [
            'tablename' => [
                [1, 'Name'],
            ]
        ];
    }
}
";
        $seeder_filename = $seederName . '.php';
        $seeder_absolute_filepath = Helper_File::app_path('tasks/seeders', $seeder_filename);
        file_put_contents($seeder_absolute_filepath, $template);
        echo "Seeder created $seeder_absolute_filepath" . PHP_EOL;
    }
}
