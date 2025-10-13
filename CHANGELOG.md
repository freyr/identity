# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 0.2.1 - 2025-10-13

### Added
- `IdCollection` class for managing collections of Id objects with immutable operations

## 0.2.0 - 2025-10-02
- Doctrine custom type example (`examples/DoctrineIdType.php`) demonstrating binary UUID v7 storage
- CLAUDE.md with project documentation and development commands
- Support for inheritance: `Id` class can now be extended for custom typed identifiers

### Changed
- Changed constructor visibility from `private` to `protected` to enable inheritance
- Changed internal `$id` property visibility to `protected` for subclass access
- Removed `readonly` modifier from internal `$id` property to enable Doctrine hydration
- Made factory methods (`new()`, `fromBinary()`, `fromString()`) final to ensure consistent behavior
- Added `@phpstan-consistent-constructor` annotation to enforce constructor signature consistency in subclasses

### Fixed
- Fixed `toBinary()` method to return binary bytes instead of string representation

## [0.1.1] - 2025-06-09

### Fixed
- Add missing namespace

## [0.1.0] - 2025-06-09

### Added
- Initial release of Freyr Identity library
- `Id` class as UUID v7 wrapper for entity identifiers
- Factory methods: `new()`, `fromBinary()`, `fromString()`
- Conversion methods: `__toString()`, `toBinary()`
- Equality comparison via `sameAs()`
