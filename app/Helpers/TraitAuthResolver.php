<?php

namespace App\Helpers;

use App\Models\Auth\User;
use Illuminate\Support\Str;

trait TraitAuthResolver
{
    public function resolveAuthModelFullName(string $userType): string
    {
        $address = base_path() . "/app/Models/Auth";
        foreach (scandir($address) as $k => $v) {
            if (in_array($v, [".", ".."]) || !Str::contains($v, ".php")) {
                continue;
            }

            $class = Str::replace(".php", "", $v);
            $classFullname = "App\\Models\\Auth\\" . $class;
            if (!(($t1 = (new \ReflectionClass($classFullname))->getParentClass()) !== false && ($t2 = (new \ReflectionClass($classFullname))->getParentClass()->getName()) === User::class)) {
                continue;
            }

            if (Str::lower($class) === Str::lower($userType) || $userType === $classFullname) {
                return $classFullname;
            }
        }

        throw new \RuntimeException("", 404);
    }

    /**
     * @return string[]
     */
    public function authModelsFullName(): array
    {
        $names = [];
        $address = base_path() . "/app/Models/Auth";

        foreach (scandir($address) as $k => $v) {
            if (in_array($v, [".", ".."]) || !Str::contains($v, ".php")) {
                continue;
            }

            $class = str_replace(".php", "", $v);
            $classFullname = "App\\Models\\Auth\\" . $class;
            if (($t1 = (new \ReflectionClass($classFullname))->getParentClass()) !== false && ($t2 = (new \ReflectionClass($classFullname))->getParentClass()->getName()) === User::class) {
                $names[] = $classFullname;
            }
        }

        return $names;
    }

    /**
     * @return string[]
     */
    public function authModelsClassName(): array
    {
        $names = [];
        $address = base_path() . "/app/Models/Auth";

        foreach (scandir($address) as $k => $v) {
            if (in_array($v, [".", ".."]) || !Str::contains($v, ".php")) {
                continue;
            }

            $class = str_replace(".php", "", $v);
            $classFullname = "App\\Models\\Auth\\" . $class;
            if (($t1 = (new \ReflectionClass($classFullname))->getParentClass()) !== false && ($t2 = (new \ReflectionClass($classFullname))->getParentClass()->getName()) === User::class) {
                $names[] = $class;
            }
        }

        return $names;
    }
}
