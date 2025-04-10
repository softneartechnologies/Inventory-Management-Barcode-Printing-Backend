<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use DB;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        DB::table('role_permission')->truncate();
        DB::table('roles')->truncate();
        DB::table('permissions')->truncate();

        $permissions = [
            'Dashboard' => ['View Dashboard'],
            'Product' => ['View Products', 'Add Products', 'Edit Products', 'Update Stocks', 'Delete Products'],
            'Category' => ['View Categories', 'Add Categories', 'Edit Categories', 'Delete Categories'],
            'SubCategory' => ['Add SubCategories', 'Edit SubCategories', 'Delete SubCategories'],
            'Vendor' => ['Add Vendor', 'Edit Vendor', 'Delete Vendor'],
            'Manufacturer' => ['Add Manufacturer', 'Edit Manufacturer', 'Delete Manufacturer'],
            'Unit' => ['Add Unit', 'Edit Unit', 'Delete Unit'],
            'WeightUnits' => ['Add WeightUnits', 'Edit WeightUnits', 'Delete WeightUnits'],
            'MeasurementUnits' => ['Add MeasurementUnits', 'Edit MeasurementUnits', 'Delete MeasurementUnits'],
            'Employee' => ['Add Employees', 'Edit Employees', 'Remove Employees'],
            'Reports' => ['Inventory Adjustments', 'Inventory Summary Report', 'Inventory Alert'],
            'Orders' => ['Order', 'Update Stock', 'Delete'],
            'ProductAssign' => ['Returnable Only Scan','Returnable Issue to', 'Goods Out Only Scan', 'Goods Out Issue to', 'Employee Issuance'],
            'RolesPermission' => ['Add Roles', 'Edit Roles', 'Delete Roles'],
            'CurrencySettings' => ['Add Currency', 'Edit Currency', 'Delete Currency'],
            'BarcodeSettings' => ['Barcode Settings']
        ];

        // Permissions Create Karna
        foreach ($permissions as $module => $modulePermissions) {
            foreach ($modulePermissions as $permission) {
                Permission::create(['name' => $permission, 'module' => $module]);
            }
        }

        // Roles Create Karna
        $admin = Role::create(['name' => 'Admin']);
        $user = Role::create(['name' => 'User']);

        // Admin ko sabhi permissions dena
        $admin->permissions()->attach(Permission::pluck('id'));

        // User ko sirf kuch permissions dena
        $userPermissions = Permission::whereIn('name', [
            'View Dashboard', 'View Products', 'Add Products', 'View Categories'
        ])->pluck('id');

        $user->permissions()->attach($userPermissions);
    }
}
