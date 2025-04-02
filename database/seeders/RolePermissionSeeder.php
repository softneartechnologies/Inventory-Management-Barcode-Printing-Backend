<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            'Dashboard' => ['View Dashboard'],
            'Product' => ['View Products', 'Add Products', 'Edit Products', 'Update stocks', 'Delete Products'],
            'Category' => ['View Categories','Add Categories', 'Edit Categories', 'Delete Categories'],
            'SubCategory' => ['Add SubCategories', 'Edit SubCategories', 'Delete SubCategories'],
            'Vendor' => ['Add Vendor', 'Edit Vendor', 'Delete Vendor'],
            'Manufacturer' => ['Add Manufacturer', 'Edit Manufacturer', 'Delete Manufacturer'],
            'Unit' => ['Add Unit', 'Edit Unit', 'Delete Unit'],
            'WeightUnits' => ['Add WeightUnits', 'Edit WeightUnits', 'Delete WeightUnits'],
            'MeasurementUnits' => ['Add MeasurementUnits', 'Edit MeasurementUnits', 'Delete MeasurementUnits'],
            'Employee' => ['Add Employees', 'Edit Employees', 'Remove Employees'],
            'Reports' => ['Inventory Adjustments', 'Inventory Summary Report', 'InventoryAlert'],
            'Orders' => ['Order', 'Update Stock', 'Delete'],
            'ProductIssuance' => ['Returnable', 'Goods Out', 'Employee Issuance'],
            'RolesPermission' => ['Add Roles', 'Edit Roles', 'Delete Roles'],
            'CurrencySettings' => ['Add Currency', 'Edit Currency', 'Delete Currency'],
            'BarcodeSettings' => ['Barcode Settings']
        ];

        foreach ($permissions as $module => $modulePermissions) {
            foreach ($modulePermissions as $permission) {
                Permission::firstOrCreate(['name' => $permission, 'module' => $module, 'guard_name' => 'api']);
            }
        }

        $admin = Role::firstOrCreate(['name' => 'Admin','guard_name' => 'api']);
        $user = Role::firstOrCreate(['name' => 'User', 'guard_name' => 'api']);

        $admin->givePermissionTo(Permission::where('guard_name', 'api')->get());
        $user->givePermissionTo(['View Dashboard', 'View Products', 'Add Products', 'View Categories']);

        
    }
}

