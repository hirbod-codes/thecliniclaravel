<?php

namespace App\Http\Controllers;

use App\Http\Requests\BusinessDefault\UpdateRequest;
use App\Models\BusinessDefault as ModelsBusinessDefault;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use TheClinicDataStructures\DataStructures\Interfaces\Arrayable;

class BusinessDefault extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json($this->getBusinessDefaultsAttributes());
    }

    private function getBusinessDefaultsAttributes(): array
    {
        foreach ($attributes = ModelsBusinessDefault::query()->firstOrFail()->toArray() as $key => $value) {
            if (gettype($value) !== 'object') {
                continue;
            }

            if ($value instanceof \DateTime) {
                $attributes[$key] = $value->format('Y-m-d H:i:s');
            } elseif ($value instanceof Arrayable) {
                $attributes[$key] = $value->toArray();
            } elseif ($value instanceof \Stringable) {
                $attributes[$key] = $value->__toString();
            }
        }

        return $attributes;
    }

    public function update(UpdateRequest $request): JsonResponse
    {
        $validatedInput = $request->safe()->all();

        $columns = Schema::getColumnListing((new ModelsBusinessDefault)->getTable());
        $model = ModelsBusinessDefault::query()->firstOrFail();
        foreach ($columns as $column) {
            if (!isset($validatedInput[$column])) {
                continue;
            }

            $model->{$column} = $validatedInput[$column];
        }
        $model->saveOrFail();

        return response()->json($this->getBusinessDefaultsAttributes());
    }
}
