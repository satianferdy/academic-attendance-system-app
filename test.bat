@echo off
echo Clearing cache...
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

echo Setting up test environment...
php artisan migrate:fresh --env=testing

echo Running tests...
php artisan test
