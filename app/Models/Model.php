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
    public function toArrayWithoutRelations(): array
    {
        $columns = Schema::getColumnListing($this->getTable());

        $visibleAttributes = array_diff($columns, $this->hidden);

        $instanceAttributes = $this->attributesToArray();

        $attributes = [];
        foreach ($visibleAttributes as $visibleAttribute) {
            $attributes[$visibleAttribute] = isset($instanceAttributes[$visibleAttribute]) ? $instanceAttributes[$visibleAttribute] : null;
        }

        return $attributes;
    }
}
