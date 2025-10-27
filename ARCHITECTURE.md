# Common Symfony Library - Architecture Documentation

## Overview

The Common Symfony Library (CSL) is a foundational Symfony 7.3 framework library designed to provide reusable components, patterns, and utilities for Symfony-based applications. It follows modern PHP practices with strict typing, dependency injection, and clean architecture principles.

## Project Structure

```
common-symfony-library/
├── bin/                          # Executable scripts
│   ├── console                   # Symfony console application
│   └── phpunit                   # PHPUnit test runner
├── config/                       # Configuration files
│   ├── bundles.php              # Bundle registration
│   ├── packages/                # Package-specific configurations
│   │   ├── cache.yaml           # Cache configuration
│   │   ├── doctrine.yaml        # Doctrine ORM configuration
│   │   ├── doctrine_migrations.yaml
│   │   ├── framework.yaml       # Symfony framework configuration
│   │   ├── redis_service.yaml.yaml
│   │   ├── routing.yaml         # Routing configuration
│   │   └── translation.yaml     # Translation configuration
│   ├── routes/                  # Route definitions
│   ├── services.yaml            # Service definitions
│   └── preload.php              # Preload configuration
├── migrations/                  # Database migrations
├── public/                      # Web-accessible files
│   └── index.php               # Application entry point
├── src/                         # Source code (CSL namespace)
│   ├── Controller/              # HTTP Controllers
│   │   ├── CslAbstractController.php
│   │   └── IndexController.php
│   ├── DTO/                     # Data Transfer Objects
│   ├── Entity/                  # Doctrine entities
│   │   └── User.php
│   ├── Events/                  # Event classes
│   ├── Exceptions/              # Custom exception classes
│   │   ├── CslAbstractException.php
│   │   ├── BadRequestException.php
│   │   ├── NotImplementedException.php
│   │   ├── ServiceUnavailableException.php
│   │   └── UnauthorizedException.php
│   ├── Kernel.php               # Application kernel
│   ├── Module/                  # Module components
│   └── Repository/              # Data access layer
│       ├── CslAbstractRepository.php
│       └── UsersRepository.php
├── tests/                       # Test suite
│   ├── bootstrap.php
│   └── Functional/
│       └── Repository/
│           └── UserRepositoryTest.php
└── var/                         # Variable data (cache, logs)
```

## Architecture Layers

### 1. Presentation Layer (Controllers)

**Location**: `src/Controller/`

The presentation layer handles HTTP requests and responses using Symfony's controller system.

#### Key Components:

- **`CslAbstractController`**: Base controller class extending Symfony's `AbstractController`
  - Provides common functionality for all controllers
  - Serves as the foundation for application-specific controllers

- **`IndexController`**: Example controller demonstrating:
  - Redis service integration
  - JSON response handling
  - Route definitions using PHP attributes

### 2. Domain Layer (Entities)

**Location**: `src/Entity/`

The domain layer contains business entities using Doctrine ORM.

#### Key Components:

- **`User`**: Example entity demonstrating:
  - Doctrine attribute-based mapping
  - Repository association
  - Fluent interface pattern for setters
  - Type-safe properties with PHP 8.2+ features

### 3. Data Access Layer (Repositories)

**Location**: `src/Repository/`

The data access layer abstracts database operations using the Repository pattern.

#### Key Components:

- **`CslAbstractRepository`**: Generic repository base class
  - Extends Doctrine's `EntityRepository`
  - Provides type-safe entity management
  - Uses PHP generics for type safety
  - Injects `EntityManagerInterface` for database operations

- **`UsersRepository`**: Concrete repository implementation
  - Extends `CslAbstractRepository` with `User` entity type
  - Demonstrates proper repository pattern implementation

### 4. Exception Handling Layer

**Location**: `src/Exceptions/`

Custom exception classes following HTTP status code conventions.

#### Key Components:

- **`CslAbstractException`**: Base exception class
  - Extends PHP's `Exception`
  - Provides `toArray()` method for API responses
  - Configurable message and code properties

- **Specific Exception Classes**:
  - `BadRequestException` (400)
  - `UnauthorizedException` (401)
  - `ServiceUnavailableException` (503)
  - `NotImplementedException` (501)

## Technology Stack

### Core Framework
- **Symfony 7.3**: Modern PHP framework
- **PHP 8.2+**: Latest PHP features with strict typing
- **Doctrine ORM 3.5**: Object-relational mapping
- **Doctrine Migrations**: Database schema management

### Development Tools
- **PHPUnit 12.3**: Testing framework
- **PHPStan 2.1**: Static analysis
- **PHP CS Fixer 3.87**: Code formatting
- **Symfony Browser Kit**: Functional testing

### External Services
- **Redis Service**: Caching and session management via `uzunov-labs/redis-service`

## Configuration Management

### Bundle Configuration (`config/bundles.php`)
```php
return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    RedisService\Symfony\RedisServiceBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ['all' => true],
];
```

### Service Configuration (`config/services.yaml`)
- Auto-wiring enabled for dependency injection
- Auto-configuration for commands and event subscribers
- PSR-4 autoloading for `CSL\` namespace

### Doctrine Configuration
- Attribute-based entity mapping
- PostgreSQL platform support
- Environment-specific configurations (dev/test/prod)
- Caching strategies for production

## Design Patterns

### 1. Repository Pattern
- Abstracts data access logic
- Provides type-safe entity operations
- Enables easy testing with mock repositories

### 2. Abstract Base Classes
- `CslAbstractController`: Common controller functionality
- `CslAbstractRepository`: Generic repository operations
- `CslAbstractException`: Standardized exception handling

### 3. Dependency Injection
- Constructor injection for services
- Auto-wiring configuration
- Interface-based abstractions

### 4. Fluent Interface
- Entity setters return `$this` for method chaining
- Improves code readability and maintainability

## Code Quality Standards

### Static Analysis
- **PHPStan Level 2**: Comprehensive static analysis
- Type declarations for all methods and properties
- Generic type support for repositories

### Code Formatting
- **PHP CS Fixer**: Consistent code style
- PSR-12 compliance
- Custom rules for project-specific standards

### Testing Strategy
- **PHPUnit**: Unit and functional testing
- Repository testing with database integration
- Browser kit for HTTP testing

## Development Workflow

### Available Commands
```bash
# Database operations
composer db-migrate:next          # Run next migration
composer db-migrate:generate      # Generate new migration

# Code quality
composer phpstan                  # Run static analysis
composer cs-fix                   # Fix code style
composer code-fix                 # Run both cs-fix and phpstan

# Development server
symfony serve                     # Start development server
```

### Environment Setup
1. Clone repository
2. Copy `.env.example` to `.env`
3. Run `composer install`
4. Execute `composer db-migrate:next`
5. Start server with `symfony serve`

## Extension Points

### Adding New Entities
1. Create entity in `src/Entity/`
2. Create corresponding repository in `src/Repository/`
3. Generate migration with `composer db-migrate:generate`
4. Run migration with `composer db-migrate:next`

### Adding New Controllers
1. Extend `CslAbstractController`
2. Define routes using PHP attributes
3. Inject required services via constructor
4. Return appropriate response types

### Adding New Exceptions
1. Extend `CslAbstractException`
2. Set appropriate HTTP status code
3. Define meaningful error message
4. Use in controllers for consistent error handling

## Security Considerations

- Input validation through Symfony's validation component
- SQL injection prevention via Doctrine ORM
- XSS protection through proper output escaping
- CSRF protection for state-changing operations
- Environment-based configuration for sensitive data

## Performance Optimizations

- Doctrine query optimization
- Redis caching integration
- Production-specific cache configurations
- Lazy loading for entity relationships
- Proxy class generation for development

## Future Enhancements

- API documentation with OpenAPI/Swagger
- Event-driven architecture with Symfony EventDispatcher
- Command pattern for complex operations
- CQRS implementation for read/write separation
- Microservice communication patterns
