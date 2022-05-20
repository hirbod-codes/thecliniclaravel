<?php

namespace Database\Traits;

use App\Models\Role;
use App\Models\roles\AdminRole;
use Illuminate\Support\Facades\DB;

trait DataBaseHelpers
{
    use ResolveUserModel;

    public function updateRoleNameTriggers(string $relatedRole): void
    {
        $relatedRole = $this->resolveRuleType($relatedRole);

        $roles = [];
        /** @var Role $role */
        foreach (Role::query()->where('role', '=', $relatedRole)->get()->all() as $role) {
            $roles[] = $role->name;
        }

        $modelFullname = $this->resolveRuleModelFullName($relatedRole);

        $table = (new $modelFullname)->getTable();
        $fk = (new $modelFullname)->getKeyName();
        $fkUserRole = (new AdminRole)->getUserRoleNameFKColumnName();

        if ($this->triggerExists('before_' . $table . '_insert')) {
            DB::statement('DROP TRIGGER before_' . $table . '_insert');
        }

        if ($this->triggerExists('before_' . $table . '_update')) {
            DB::statement('DROP TRIGGER before_' . $table . '_update');
        }

        $condition = '';
        for ($i = 0; $i < count($roles); $i++) {
            $condition .= 'NEW.' . $fkUserRole . ' <> \'' . $roles[$i] . '\' ';
            if ($i !== count($roles) - 1) {
                $condition .= '&& ';
            }
        }

        if ($condition === '') {
            return;
        }

        DB::statement(
            'CREATE TRIGGER before_' . $table . '_insert BEFORE INSERT ON ' . $table . '
                            FOR EACH ROW
                            BEGIN
                                IF ' . $condition . 'THEN
                                signal sqlstate \'45000\'
                                SET MESSAGE_TEXT = "Mysql insert trigger";
                                END IF;

                                INSERT INTO users_guard (id) VALUES (NEW.' . $fk . ');
                            END;'
        );

        DB::statement(
            'CREATE TRIGGER before_' . $table . '_update BEFORE UPDATE ON ' . $table . '
                        FOR EACH ROW
                        BEGIN
                            IF ' . $condition . 'THEN
                            signal sqlstate \'45000\'
                            SET MESSAGE_TEXT = "Mysql update trigger";
                            END IF;
                        END;'
        );
    }

    public function triggerExists(string $triggerName): bool
    {
        $triggerNames = array_unique(array_map(function (\stdClass $obj) {
            return $obj->Trigger;
        }, DB::select("SHOW TRIGGERS FROM " . env('DB_DATABASE', '') . ";")));

        return in_array($triggerName, $triggerNames);
    }
}
