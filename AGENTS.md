# AGENTS.md - Codebase Guidelines for MLC APP /iob

## Project Overview
This is a PHP MVC application for a financial/money transfer system (iob). It uses a custom MVC framework with:
- `Library/` - Core framework classes (Entities, Controllers, Managers, Forms)
- `Applications/` - Module-specific controllers and views
- `Web/` - Public assets (CSS, JS, images)
- `composer.json` - Dependencies (robthree/twofactorauth)

## Build/Lint/Test Commands

### Dependencies
```bash
composer install
```

### Database Setup
```bash
# Import the SQL schema (located in project root)
mysql -u root -p iob < cp1146011p43_iob\ \(10\).sql
```

### Testing
There is **no automated test framework** currently set up. When adding tests:
```bash
# Install PHPUnit (if needed)
composer require --dev phpunit/phpunit

# Run all tests
./vendor/bin/phpunit

# Run single test file
./vendor/bin/phpunit tests/Path/To/TestFile.php

# Run single test method
./vendor/bin/phpunit --filter testMethodName
```

### Code Quality (if tools are added)
```bash
# PHP linting
php -l Library/
php -l Applications/

# Composer validation
composer validate
```

## Code Style Guidelines

### PHP

#### Naming Conventions
- **Classes**: PascalCase (e.g., `BackController`, `UserManager`)
- **Methods**: camelCase (e.g., `execute()`, `setModule()`)
- **Variables**: camelCase with French names where appropriate (e.g., `$donnees`, `$erreurs`, `$valeur`)
- **Constants**: UPPER_SNAKE_CASE
- **Files**: Match class name with `.class.php` suffix (e.g., `BackController.class.php`)
- **Namespaces**: `Library\` for core classes, `Library\Models\` for data managers

#### Architecture Patterns
- **Entities**: Abstract `Entity` class in `Library/Entity.class.php`
  - Implement `ArrayAccess` interface
  - Use `hydrate()` method to populate from arrays
  - Use `set*()` and `get*()` methods for properties
- **Managers**: Follow pattern `XxxManager` and `XxxManagerPDO`
  - Place PDO implementations in `Library/Models/`
- **Controllers**: Extend `BackController` abstract class
  - Use `execute*()` methods for actions
  - Use `$this->page->setContentFile()` to render views
- **Views**: PHP files in `Applications/App/Modules/{Module}/Views/`

#### Indentation & Formatting
- Use **tabs** for indentation (match existing codebase)
- Opening braces on same line as class/function declaration
- No spaces inside parentheses: `function foo($bar)` not `function foo( $bar )`
- Spaces around operators: `$a + $b` not `$a+$b`
- Chained method calls: one per line with `.` at end

#### Error Handling
- Throw exceptions for invalid inputs: `\InvalidArgumentException`
- Throw `\RuntimeException` for undefined actions
- Throw `\Exception` for forbidden operations
- Always validate input types and non-empty strings
- PDO errors: set `\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION`

#### Database
- Use prepared statements with PDO
- Use UTF-8 charset: `charset=utf8`
- Database name: `iob`, user: `root`, password: empty (for dev)
- File: `DBFactory.class.php` contains connection logic

#### Imports
- Use fully qualified class names with `\` prefix (e.g., `\PDO`, `\RuntimeException`)
- Namespace declared at top: `namespace Library;`
- No `use` statements; use fully qualified names

### JavaScript

#### File Location
- Place in `Web/scripts/` directory
- Named descriptively: `{feature}.js` (e.g., `billetage.js`, `loginpage.js`)

#### Patterns
- Wrap in `$(function(){ ... });` for document ready
- Use `$` prefix for jQuery objects: `var $element = $('#element');`
- Event handlers: `$element.on('change', function(){ ... });`
- Avoid duplicate code; extract common logic into functions

#### Style
- Use `var` (existing codebase pattern) or `const`/`let` for new code
- camelCase for variables and functions
- Semicolons at end of statements

### CSS

#### File Location
- `Web/css/` for main stylesheets
- `Web/bordereau/` for bordereau-specific styles
- `Web/scss/` for SCSS source files

#### Patterns
- Use existing Bootstrap-based classes
- Custom styles in `style.css`, `styles.css`
- Responsive styles in `responsive.scss`

### Templates (PHP Views)

#### Location
- `Applications/App/Templates/` for layout files
- `Applications/App/Modules/{Module}/Views/` for module views

#### Patterns
- Use `<?= $variable ?>` for output
- Use `<?php foreach ($items as $item): ?>` syntax for readability
- Keep presentation logic minimal in views
- Use `number_format()` for currency display
- Use `date()` for date formatting

## Common Tasks

### Adding a New Module
1. Create controller in `Applications/App/Modules/{Module}/{Module}Controller.class.php`
2. Create views directory: `Applications/App/Modules/{Module}/Views/`
3. Add routes in `Applications/App/Config/routes.xml`
4. Create Manager if needed in `Library/Models/{Module}Manager.class.php`

### Database Changes
1. Export current schema: `mysqldump -u root -p iob > schema.sql`
2. Apply changes to SQL file
3. Document changes in `note.txt`

## Security Notes
- Database credentials are hardcoded (for legacy reasons - consider environment variables)
- Two-factor authentication via `robthree/twofactorauth`
- Session-based authentication with timeout (`inactivity.js`)
- QR code generation via `Web/phpqrcode/`

## Framework Reference
- Base classes: `Application`, `ApplicationComponent`, `BackController`, `Entity`, `Page`
- Form handling: `Form`, `FormBuilder`, `Field`, validators
- HTTP handling: `HTTPRequest`, `HTTPResponse`
- Routing: `Router`, `Route` with XML config
