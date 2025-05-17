# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

API.Shelfling is a Laravel-based REST API for managing libraries of different types (books, movies) with role-based user access.

## Key Concepts

- **Libraries**: Collection entities with types (book, movie) 
- **Users**: Accounts with authentication via Laravel Sanctum
- **Roles**: Users can have various roles in libraries (owner, editor, viewer)
- **Authorization**: Role-based permissions using Laravel Policies

## Commands

### Environment Setup

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# Create database and migrate
php artisan migrate

# Seed database with initial data
php artisan db:seed
```

### Development

```bash
# Start development environment (server, queue, logs, vite)
composer run dev

# Or start just the API server
php artisan serve

# Run queue worker
php artisan queue:listen --tries=1

# Watch for log messages
php artisan pail --timeout=0

# Start frontend asset building
npm run dev
```

### Testing

```bash
# Run tests
composer run test
# Or directly with artisan
php artisan test

# Run a specific test
php artisan test --filter=TestName

# Run tests with coverage
php artisan test --coverage
```

### Code Quality

```bash
# Run Laravel Pint (code style fixer)
./vendor/bin/pint

# Check code style
./vendor/bin/pint --test
```

## Architecture

### Models and Relationships

- `User`: Authenticated users who can access libraries
- `Library`: Collections with type (book/movie)
- `LibraryUser`: Pivot model for user-library relationships with roles

### Controllers

- `AuthController`: Handles user registration, login, token management
- `LibraryController`: Manages library CRUD operations and user associations

### Policies

- `LibraryPolicy`: Controls access to libraries based on user roles

### API Routes

- Authentication: `/register`, `/login`, `/logout`
- Libraries: CRUD operations at `/libraries`
- Library Users: Add users to libraries at `/libraries/{library}/users`

All authenticated routes use Sanctum token authentication middleware.

## Testing Approach

Tests are written using Pest PHP, a testing framework built on PHPUnit with a more fluent syntax. Tests are organized into Feature and Unit directories.