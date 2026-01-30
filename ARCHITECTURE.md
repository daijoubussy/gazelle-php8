# Gazelle Modern PHP 8.5 Architecture

This document describes the modern architecture patterns used in this codebase. All legacy Gazelle code should be migrated to follow these patterns.

## Architecture Overview

The application follows **Clean Architecture** principles with a **Domain-Driven Design** approach:

```
┌─────────────────────────────────────────────────────────────┐
│                      HTTP Layer                              │
│  Controllers, Middleware, Request/Response                   │
├─────────────────────────────────────────────────────────────┤
│                   Application Layer                          │
│  Commands, Queries, Handlers, DTOs, Services                │
├─────────────────────────────────────────────────────────────┤
│                     Domain Layer                             │
│  Entities, Value Objects, Aggregates, Repository Interfaces │
├─────────────────────────────────────────────────────────────┤
│                  Infrastructure Layer                        │
│  Database, Cache, External Services, Repository Impls       │
└─────────────────────────────────────────────────────────────┘
```

## Directory Structure

```
src/
├── Core/                    # Framework core
│   ├── Bootstrap.php       # Application entry point
│   ├── Config/             # Configuration management
│   ├── Container/          # Dependency injection
│   ├── Http/               # HTTP abstractions
│   └── Routing/            # Router
│
├── Domain/                  # Business logic (no framework deps)
│   ├── Common/             # Base classes
│   │   ├── Entity.php
│   │   ├── AggregateRoot.php
│   │   ├── ValueObject.php
│   │   ├── EntityId.php
│   │   ├── DomainEvent.php
│   │   └── Repository.php
│   │
│   ├── User/               # User aggregate
│   │   ├── User.php
│   │   ├── UserId.php
│   │   ├── UserRepository.php
│   │   ├── ValueObjects/
│   │   └── Events/
│   │
│   └── Torrent/            # Torrent aggregate
│       ├── Torrent.php
│       ├── TorrentId.php
│       ├── TorrentRepository.php
│       ├── ValueObjects/
│       └── Events/
│
├── Application/             # Use cases and services
│   ├── Common/
│   │   ├── Command.php
│   │   ├── CommandHandler.php
│   │   ├── Query.php
│   │   ├── QueryHandler.php
│   │   └── Bus/
│   │
│   └── User/
│       ├── Commands/       # Write operations
│       ├── Queries/        # Read operations
│       ├── DTOs/           # Data transfer objects
│       └── Services/       # Application services
│
├── Infrastructure/          # External integrations
│   ├── Persistence/        # Database implementations
│   ├── Cache/              # Caching
│   ├── Events/             # Event dispatcher
│   └── Providers/          # Service providers
│
└── Http/                    # Web layer
    ├── Controller.php
    ├── Controllers/
    └── Middleware/
```

## Key Principles

### 1. Strict Types Everywhere

```php
declare(strict_types=1);
```

### 2. Immutable Value Objects

```php
final readonly class Email extends ValueObject
{
    private function __construct(private string $value) {}

    public static function fromString(string $email): self
    {
        // Validation in factory method
        return new self($email);
    }
}
```

### 3. Rich Domain Entities

```php
final class User extends AggregateRoot
{
    public function changePassword(Password $newPassword): void
    {
        $this->password = $newPassword;
        $this->recordEvent(new UserPasswordChanged($this->id));
    }
}
```

### 4. Repository Pattern

```php
// Interface in Domain layer
interface UserRepository
{
    public function findById(UserId $id): ?User;
    public function save(User $user): void;
}

// Implementation in Infrastructure layer
final readonly class UserRepositoryImpl implements UserRepository
{
    public function __construct(private DatabaseConnection $db) {}
}
```

### 5. CQRS (Command/Query Separation)

```php
// Command (write)
final readonly class RegisterUser implements Command
{
    public function __construct(
        public string $username,
        public string $email,
        public string $password
    ) {}
}

// Query (read)
final readonly class GetUserById implements Query
{
    public function __construct(public int $userId) {}
}
```

### 6. Dependency Injection

```php
final readonly class RegisterUserHandler implements CommandHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private EventDispatcher $eventDispatcher
    ) {}
}
```

## PHP 8.5 Features Used

- `readonly` classes and properties
- Constructor property promotion
- `match` expressions
- Enums with methods
- Named arguments
- Union types
- `never` return type
- Attributes (for future expansion)

## Migration Guide

### Migrating Legacy Classes

1. **Identify the domain concept** (User, Torrent, Forum, etc.)
2. **Extract value objects** from primitives (Email, Username, InfoHash)
3. **Create a proper Entity** with behavior methods
4. **Define a Repository interface** in the Domain layer
5. **Implement the Repository** in Infrastructure
6. **Create Commands/Queries** for operations
7. **Implement Handlers** with proper DI

### Example: Migrating Users Class

Before (legacy):
```php
class Users {
    public static function verify_password($user_id, $password) {
        global $DB;
        $DB->query("SELECT Password FROM users WHERE ID = $user_id");
        // ...
    }
}
```

After (modern):
```php
// Domain/User/User.php
final class User extends AggregateRoot
{
    public function verifyPassword(string $plaintext): bool
    {
        return $this->password->verify($plaintext);
    }
}

// Application/User/Commands/AuthenticateUserHandler.php
final readonly class AuthenticateUserHandler implements CommandHandler
{
    public function __construct(private UserRepository $userRepository) {}

    public function handle(Command $command): AuthResultDTO
    {
        $user = $this->userRepository->findByUsername($command->username);
        if (!$user?->verifyPassword($command->password)) {
            return AuthResultDTO::failed('Invalid credentials');
        }
        // ...
    }
}
```

## Testing

All new code should have unit tests:

```php
final class UserTest extends TestCase
{
    public function testCanCreateUser(): void
    {
        $user = User::create(
            UserId::fromInt(1),
            Username::fromString('testuser'),
            Email::fromString('test@example.com'),
            Password::fromPlaintext('SecurePass123')
        );

        $this->assertSame('testuser', (string) $user->username());
    }
}
```

## Configuration

Configuration uses environment variables with sensible defaults:

```php
// config/database.php
return [
    'host' => env('DB_HOST', 'localhost'),
    'database' => env('DB_DATABASE', 'gazelle'),
];
```

## Error Handling

- Domain exceptions extend `\DomainException`
- Application exceptions for business rule violations
- HTTP layer catches and converts to proper responses

```php
if ($user === null) {
    throw UserNotFoundException::withId($userId);
}
```
