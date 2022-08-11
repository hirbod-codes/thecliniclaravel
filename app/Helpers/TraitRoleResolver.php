<?php

namespace App\Helpers;

use App\Models\Auth\User;

trait TraitRoleResolver
{
    public function resolveRuleModelFullName(string $roleName): string
    {
        $address = base_path() . '/app/Models/roles';
        foreach (scandir($address) as $k => $v) {
            if (in_array($v, ['.', '..'])) {
                continue;
            }

            $class = str_replace('.php', '', $v);
            $classFullname = 'App\\Models\\roles\\' . $class;
            if (!($t=class_exists($classFullname, false) && ($t1=(new \ReflectionClass($classFullname))->getParentClass()) !== false && ($t2=(new \ReflectionClass($classFullname))->getParentClass()->getName()) === User::class)) {
                continue;
            }

            if (strtolower(str_replace('Role.php', '', $v)) === strtolower($roleName)) {
                return $classFullname;
            }
        }

        throw new \RuntimeException('', 500);
    }
}
