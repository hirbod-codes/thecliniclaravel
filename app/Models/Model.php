<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Facades\Schema;

class Model extends EloquentModel
{
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
