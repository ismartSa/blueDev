# Installation Guide

## Roles and Admin Setup

This application includes automated tools to set up user roles and create an admin user.

### Available Roles

- **admin**: Full system access
- **instructor**: Can create and manage courses
- **student**: Can enroll in courses and take quizzes

### Installation Methods

#### Method 1: Using Artisan Command (Recommended)

```bash
# Install roles and create admin user
php artisan install:roles-admin

# Force reinstall (if admin user already exists)
php artisan install:roles-admin --force
```

#### Method 2: Using Database Seeder

```bash
# Run the specific seeder
php artisan db:seed --class=RoleAndAdminSeeder

# Or run all seeders (includes roles and admin)
php artisan db:seed
```

### Default Admin Credentials

After installation, you can login with:

- **Email**: `superadmin@superadmin.com`
- **Password**: `superadmin`
- **Role**: `admin`

### Usage Notes

- The installation is safe to run multiple times
- Use `--force` flag to recreate the admin user if needed
- All roles are created automatically if they don't exist
- The admin user email is verified by default

### Security Recommendation

⚠️ **Important**: Change the default admin password after first login for security purposes.

## Application Access

Once installed, access your application at:
- Local: `http://localhost:8000`
- Login: `http://localhost:8000/login`
- Register: `http://localhost:8000/register`