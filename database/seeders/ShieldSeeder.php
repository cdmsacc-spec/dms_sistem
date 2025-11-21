<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $rolesWithPermissions = '[{"name":"super_admin","guard_name":"web","permissions":["ViewAny:Dokumen","View:Dokumen","Create:Dokumen","Update:Dokumen","Delete:Dokumen","Restore:Dokumen","ForceDelete:Dokumen","ForceDeleteAny:Dokumen","RestoreAny:Dokumen","Replicate:Dokumen","Reorder:Dokumen","ViewAny:JenisDokumen","View:JenisDokumen","Create:JenisDokumen","Update:JenisDokumen","Delete:JenisDokumen","Restore:JenisDokumen","ForceDelete:JenisDokumen","ForceDeleteAny:JenisDokumen","RestoreAny:JenisDokumen","Replicate:JenisDokumen","Reorder:JenisDokumen","ViewAny:JenisKapal","View:JenisKapal","Create:JenisKapal","Update:JenisKapal","Delete:JenisKapal","Restore:JenisKapal","ForceDelete:JenisKapal","ForceDeleteAny:JenisKapal","RestoreAny:JenisKapal","Replicate:JenisKapal","Reorder:JenisKapal","ViewAny:Kapal","View:Kapal","Create:Kapal","Update:Kapal","Delete:Kapal","Restore:Kapal","ForceDelete:Kapal","ForceDeleteAny:Kapal","RestoreAny:Kapal","Replicate:Kapal","Reorder:Kapal","ViewAny:Perusahaan","View:Perusahaan","Create:Perusahaan","Update:Perusahaan","Delete:Perusahaan","Restore:Perusahaan","ForceDelete:Perusahaan","ForceDeleteAny:Perusahaan","RestoreAny:Perusahaan","Replicate:Perusahaan","Reorder:Perusahaan","ViewAny:WilayahOperasional","View:WilayahOperasional","Create:WilayahOperasional","Update:WilayahOperasional","Delete:WilayahOperasional","Restore:WilayahOperasional","ForceDelete:WilayahOperasional","ForceDeleteAny:WilayahOperasional","RestoreAny:WilayahOperasional","Replicate:WilayahOperasional","Reorder:WilayahOperasional","ViewAny:Role","View:Role","Create:Role","Update:Role","Delete:Role","Restore:Role","ForceDelete:Role","ForceDeleteAny:Role","RestoreAny:Role","Replicate:Role","Reorder:Role","View:Dashboard","View:DokumenAnalytic","View:DokumentNearExpired"]}]';
        $directPermissions = '[]';

        static::makeRolesWithPermissions($rolesWithPermissions);
        static::makeDirectPermissions($directPermissions);

        $this->command->info('Shield Seeding Completed.');
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (! blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            /** @var Model $roleModel */
            $roleModel = Utils::getRoleModel();
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($rolePlusPermissions as $rolePlusPermission) {
                $role = $roleModel::firstOrCreate([
                    'name' => $rolePlusPermission['name'],
                    'guard_name' => $rolePlusPermission['guard_name'],
                ]);

                if (! blank($rolePlusPermission['permissions'])) {
                    $permissionModels = collect($rolePlusPermission['permissions'])
                        ->map(fn ($permission) => $permissionModel::firstOrCreate([
                            'name' => $permission,
                            'guard_name' => $rolePlusPermission['guard_name'],
                        ]))
                        ->all();

                    $role->syncPermissions($permissionModels);
                }
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (! blank($permissions = json_decode($directPermissions, true))) {
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($permissions as $permission) {
                if ($permissionModel::whereName($permission)->doesntExist()) {
                    $permissionModel::create([
                        'name' => $permission['name'],
                        'guard_name' => $permission['guard_name'],
                    ]);
                }
            }
        }
    }
}
