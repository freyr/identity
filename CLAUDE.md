# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Freyr Identity is a PHP library providing a thin adapter layer around the Ramsey UUID interface. It implements a simple `Id` class that encapsulates UUID v7 functionality for use as domain identifiers.

## Architecture

### Core Component
- **Id class** (`src/Id.php`): Immutable value object wrapping `UuidInterface` from ramsey/uuid
  - Uses UUID v7 by default for time-ordered, sortable identifiers
  - Supports binary and string representations
  - Implements equality comparison via `sameAs()`
  - Constructor is private; use static factory methods: `new()`, `fromBinary()`, `fromString()`

### Key Design Patterns
- **Value Object**: Id instances are immutable with final methods preventing inheritance changes
- **Factory Pattern**: Static factory methods for different instantiation scenarios
- **Adapter Pattern**: Wraps ramsey/uuid's UuidInterface to provide domain-specific API

## Development Commands

### Testing
```bash
# Run all tests
composer test
# Or via PHPUnit directly
vendor/bin/phpunit

# Run tests with Docker
make test
# Or
docker-compose run --rm php vendor/bin/phpunit --testdox
```

### Code Quality
```bash
# Run ECS (Easy Coding Standard) - auto-fixes code style issues
composer ecs
# Or
vendor/bin/ecs check --fix

# Run PHPStan static analysis (max level with strict rules)
composer phpstan
# Or
vendor/bin/phpstan --memory-limit=-1
```

### Docker Environment
```bash
# Open PHP shell in Docker
make shell
# Or
docker compose run --rm php sh
```

## Technical Requirements
- **PHP**: ^8.4 (strict typing enabled)
- **UUID**: ramsey/uuid ^4.7
- **Namespace**: `Freyr\Identity\`

## Quality Standards
- **PHPStan**: Level max with bleeding-edge features and all strict rules enabled
- **ECS**: PSR-12 + Array, Clean Code, and Doctrine Annotations sets
- **PHPUnit**: Strict configuration with failure on warnings/risky tests
- **Cache**: All tools use `cache/` directory (phpstan, ecs, phpunit)
