<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ========================================
        // DEFINE PERMISSIONS
        // ========================================
        $permissions = [
            // User Management
            'view_users',
            'create_user',
            'edit_user',
            'delete_user',

            // Customer Management
            'view_customers',
            'create_customer',
            'edit_customer',
            'delete_customer',
            'suspend_customer',
            'activate_customer',

            // Package Management
            'view_packages',
            'create_package',
            'edit_package',
            'delete_package',

            // Invoice Management
            'view_invoices',
            'create_invoice',
            'edit_invoice',
            'delete_invoice',
            'mark_invoice_paid',

            // Router Management
            'view_routers',
            'create_router',
            'edit_router',
            'delete_router',
            'reboot_router',
            'access_router',

            // OLT Management
            'view_olts',
            'create_olt',
            'edit_olt',
            'delete_olt',
            'access_olt',

            // Ticket Management
            'view_all_tickets',
            'view_assigned_tickets',
            'create_ticket',
            'edit_ticket',
            'delete_ticket',
            'assign_ticket',
            'close_ticket',

            // Reports
            'view_financial_reports',
            'view_customer_reports',
            'view_network_reports',
            'view_operational_reports',

            // Settings
            'view_settings',
            'edit_settings',
            'view_audit_log',
            'manage_backup',
        ];

        // Create permissions
        DB::transaction(function () use ($permissions) {
            foreach ($permissions as $permission) {
                Permission::create(['name' => $permission]);
            }
        });

        // ========================================
        // DEFINE ROLES & ASSIGN PERMISSIONS
        // ========================================

        // 1. SUPER ADMIN - Full Access
        $superAdmin = Role::create(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // 2. ADMIN - Operational Full Access
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            'view_users', 'create_user', 'edit_user',
            'view_customers', 'create_customer', 'edit_customer', 'delete_customer', 'suspend_customer', 'activate_customer',
            'view_packages', 'create_package', 'edit_package', 'delete_package',
            'view_invoices', 'create_invoice', 'edit_invoice', 'mark_invoice_paid',
            'view_routers', 'create_router', 'edit_router', 'delete_router', 'reboot_router', 'access_router',
            'view_olts', 'create_olt', 'edit_olt', 'delete_olt', 'access_olt',
            'view_all_tickets', 'create_ticket', 'edit_ticket', 'assign_ticket', 'close_ticket',
            'view_financial_reports', 'view_customer_reports', 'view_network_reports', 'view_operational_reports',
        ]);

        // 3. CS (Customer Service)
        $cs = Role::create(['name' => 'cs']);
        $cs->givePermissionTo([
            'view_customers', 'create_customer', 'edit_customer', 'suspend_customer', 'activate_customer',
            'view_invoices', 'create_invoice', 'edit_invoice', 'mark_invoice_paid',
            'view_all_tickets', 'create_ticket', 'edit_ticket', 'assign_ticket',
            'view_packages',
            'view_customer_reports',
        ]);

        // 4. TEKNISI
        $teknisi = Role::create(['name' => 'teknisi']);
        $teknisi->givePermissionTo([
            'view_customers',
            'view_routers', 'reboot_router',
            'view_olts',
            'view_assigned_tickets', 'edit_ticket', 'close_ticket',
        ]);

        // 5. CUSTOMER - Self Service (no permissions needed)
        Role::create(['name' => 'customer']);
    }
}
