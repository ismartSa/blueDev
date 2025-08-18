# Installation Guide

## Setting Up User Roles, Permissions, and Admin User

This application requires specific user roles and permissions to function properly. You can set up everything using the following methods:

### Method 1: Install Permissions and Roles (Recommended)

```bash
php artisan install:permissions
```

To force reinstall (update existing permissions and roles):
```bash
php artisan install:permissions --force
```

### Method 2: Install Roles and Admin User

```bash
php artisan install:roles-admin
```

To force reinstall (recreate existing roles and admin user):
```bash
php artisan install:roles-admin --force
```

### Method 3: Using Database Seeder

```bash
php artisan db:seed --class=RoleAndAdminSeeder
```

## Complete Setup (Recommended)

For a complete setup, run both commands:
```bash
php artisan install:permissions
php artisan install:roles-admin
```

## Default Admin Credentials

After running the roles and admin setup, you can log in with:
- **Email:** `superadmin@superadmin.com`
- **Password:** `superadmin`

## Available Roles and Permissions

The system will create the following roles with their respective permissions:

### Superadmin
- Full system access including user, course, role, and permission management
- Database management capabilities

### Admin
- User management (create, read, update, delete)
- Role and permission viewing
- Database management

### Operator
- User creation and viewing
- Role and permission viewing

### Instructor
- Course management (create, update, read)
- Quiz management and reporting

### Student
- Course and quiz access (read-only)

## Usage Notes

- The `install:permissions` command sets up all permissions and assigns them to roles
- The `install:roles-admin` command creates roles and the admin user
- Use the `--force` flag to update existing permissions/roles
- The admin user will be created or updated each time you run the roles command
- Make sure to change the default admin password after first login

## Security Recommendation

⚠️ **Important:** Change the default admin password immediately after your first login for security purposes.

## Application Access

Once installed, access your application at:
- Local: `http://localhost:8000`
- Login: `http://localhost:8000/login`
- Register: `http://localhost:8000/register`