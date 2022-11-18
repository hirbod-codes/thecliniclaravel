<?php

namespace App\Models;

use App\Models\Auth\User;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

/**
 * @property int $id
 * @property \DateTime $created_at
 * @property \DateTime $updated_at
 */
class Model extends EloquentModel
{
    /**
     * @var array<string, string> ['relationship_name' => 'foreign_key_name', ...]
     */
    protected array $foreignKeys = [];

    protected $casts = [
        'craeted_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getForeignKeys(): array
    {
        return $this->foreignKeys;
    }

    /**
     * @return string[]
     */
    public function getAllForeignKeys(): array
    {
        $fkColumns = [];

        $foreignKeyConstraints = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableForeignKeys($this->getTable(), config('database.connections.mysql.database'));

        array_map(function (ForeignKeyConstraint $foreignKeyConstraint) use (&$fkColumns) {
            return array_push($fkColumns, ...$foreignKeyConstraint->getLocalColumns());
        }, $foreignKeyConstraints);

        return $fkColumns;
    }

    public function getAllModelsFullname(): array
    {
        return $this->getModelsFullnameInDirectory(__DIR__);
    }

    public function getModelsFullnameInDirectory(string $dir): array
    {
        $modelFullnames = [];

        $namespace = $this->resolveNamespace($dir);

        foreach (scandir($dir) as $fileOrFolder) {
            if (in_array($fileOrFolder, ['.', '..'])) {
                continue;
            }

            if (is_dir($dir . '/' . $fileOrFolder)) {
                array_push($modelFullnames, ...$this->{__FUNCTION__}($dir . '/' . $fileOrFolder));
            }

            $modelFullname = $namespace . class_basename(str_replace(['.php', '/'], ['', '\\'], $fileOrFolder));

            if (self::class === $modelFullname) {
                continue;
            }

            if (
                class_exists($modelFullname) &&
                ($reflectionParent = (new \ReflectionClass($modelFullname))->getParentClass()) !== false &&
                in_array($reflectionParent->getName(), [EloquentModel::class, User::class])
            ) {
                $modelFullnames[] = $modelFullname;
            }
        }

        return $modelFullnames;
    }

    private function resolveNamespace(string $dir): string
    {
        $previous = '';
        $start = false;
        $fragments = [];
        foreach (explode('\\', str_replace('/', '\\', $dir)) as $item) {
            if ($item === 'Models' && $previous === 'app') {
                $start = true;
                continue;
            }
            $previous = $item;

            if (!$start) {
                continue;
            }

            $fragments[] = $item;
        }

        return 'App\\Models\\' . (!empty($fragments) ? implode('\\', $fragments) . '\\' : '');
    }

    protected function createdAt(): Attribute
    {
        return Attribute::make(get: function ($value) {
            if (gettype($value) === "string") {
                return new \DateTime($value);
            } elseif ($value instanceof Carbon) {
                return new \DateTime($value->toDateTimeString());
            }
        });
    }

    protected function updatedAt(): Attribute
    {
        return Attribute::make(get: function ($value) {
            if (gettype($value) === "string") {
                return new \DateTime($value);
            } elseif ($value instanceof Carbon) {
                return new \DateTime($value->toDateTimeString());
            }
        });
    }

    /**
     * The purpose of this method is to get current values of all the visible attributes in a model instance even if they hsve null value.
     *
     * @return array<string, mixed>
     */
    public function toArrayWithoutRelations(array $excludedColumns = [], bool $excludeForeignKeys = false): array
    {
        $columns = Schema::getColumnListing($this->getTable());

        $fkColumns = $this->getForeignKeys();

        $attributes = [];
        foreach ($columns as $column) {
            if (in_array($column, array_merge($this->hidden, $excludeForeignKeys ? array_values($fkColumns) : [], $excludedColumns))) {
                continue;
            }

            $attributes[$column] = $this->getAttributeValue($column);
        }

        $visibleAttributes = array_diff($columns, $this->hidden, $excludeForeignKeys ? array_values($fkColumns) : [], $excludedColumns);

        return $attributes;
    }
}
