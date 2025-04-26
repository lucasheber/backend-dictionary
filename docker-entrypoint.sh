#!/bin/sh

# Database creation (only if not created)
echo "PostgreSQL started, ensuring the database exists..."

# Check if database exists, if not, create it
PGPASSWORD="dictionary_pass" psql -h postgres -U dictionary_user -c "SELECT 1 FROM pg_database WHERE datname = 'dictionary';" | grep -q 1 || \
  PGPASSWORD="dictionary_pass" psql -h postgres -U dictionary_user -c "CREATE DATABASE dictionary;"

# Run migrations
echo "Running Laravel migrations..."
php artisan migrate

# Run the application (start Apache)
exec "$@"