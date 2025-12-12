# Test Strategy & Documentation - Surat Siega Project

## 📋 Overview

Project ini mengimplementasikan testing strategy yang komprehensif dengan TestSprite MCP untuk menguji kedua aspek:
- **Frontend Tests**: JavaScript/Vitest untuk utility functions dan komponen
- **Backend Tests**: PHPUnit untuk Laravel aplikasi

---

## 🧪 Frontend Testing (Vitest)

### Setup & Konfigurasi

**Konfigurasi File:**
- `vitest.config.js` - Konfigurasi Vitest
- `testsprite_tests/tmp/frontend_config.json` - TestSprite frontend configuration

**Installed Packages:**
- `vitest` - Test runner
- `@vitejs/plugin-vue` - Vue 3 support
- `jsdom` - DOM environment
- `@testing-library/dom` - DOM testing utilities

### Menjalankan Frontend Tests

```bash
# Run tests once
npm run test

# Run tests in watch mode
npm run test:watch

# Run tests with UI
npm run test:ui

# Generate HTML report
npm run test:report
```

### Test Files Structure

```
resources/js/
└── __tests__/
    ├── date.test.js          # Date utilities
    ├── math.test.js          # Math utilities
    └── validation.test.js    # String validation utilities
```

### Test Coverage

| File | Tests | Status |
|------|-------|--------|
| `math.test.js` | 3 tests | ✅ Passed |
| `validation.test.js` | 7 tests | ✅ Passed |
| `date.test.js` | 5 tests | ✅ Passed |
| **Total** | **15 tests** | **✅ Passed** |

### Contoh Frontend Test

```javascript
import { describe, it, expect } from 'vitest';

describe('Math Utilities', () => {
  it('should add two numbers correctly', () => {
    expect(add(2, 3)).toBe(5);
  });
});
```

---

## 🔧 Backend Testing (PHPUnit)

### Setup & Konfigurasi

**Konfigurasi File:**
- `phpunit.xml` - PHPUnit configuration
- `testsprite_tests/tmp/config.json` - TestSprite backend configuration

### Menjalankan Backend Tests

```bash
# Run all tests
npm run test:backend
# atau
php vendor/bin/phpunit

# Run specific test file
php vendor/bin/phpunit tests/Feature/ExampleTest.php

# Run with coverage
php vendor/bin/phpunit --coverage-html coverage/
```

### Test Files Structure

```
tests/
├── Feature/
│   └── ExampleTest.php       # Feature tests
├── Unit/
│   └── ExampleTest.php       # Unit tests
└── TestCase.php              # Base test class
```

### Test Coverage

| Suite | Tests | Status |
|-------|-------|--------|
| Unit Tests | 1 test | ✅ Passed |
| Feature Tests | 1 test | ✅ Passed |
| **Total** | **2 tests** | **✅ Passed** |

### Contoh Backend Test

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }
}
```

---

## 🚀 Running Complete Test Suite

### Option 1: Sequential Testing
```bash
# Run frontend then backend
npm run test:all
```

### Option 2: Manual Testing
```bash
# Terminal 1: Frontend tests
npm run test:watch

# Terminal 2: Backend tests
php vendor/bin/phpunit --watch
```

### Option 3: Using TestSprite MCP

TestSprite configurations tersedia di:
- **Backend:** `testsprite_tests/tmp/config.json`
- **Frontend:** `testsprite_tests/tmp/frontend_config.json`

---

## 📊 Test Results Summary

### Latest Run
**Date:** December 9, 2025  
**Total Tests:** 17  
**Passed:** 17 ✅  
**Failed:** 0  
**Skipped:** 0  

```
Frontend:  15 passed
Backend:    2 passed
─────────────────────
Total:     17 passed
```

---

## 🛠️ Adding New Tests

### Frontend Test
1. Buat file di `resources/js/__tests__/feature.test.js`
2. Import Vitest functions:
   ```javascript
   import { describe, it, expect } from 'vitest';
   ```
3. Tulis test case:
   ```javascript
   describe('Feature Name', () => {
     it('should do something', () => {
       expect(result).toBe(expected);
     });
   });
   ```

### Backend Test
1. Buat file di `tests/Feature/` atau `tests/Unit/`
2. Extend `Tests\TestCase`
3. Tulis test case:
   ```php
   public function test_something(): void
   {
       $this->assertTrue(true);
   }
   ```

---

## 📁 Test Configuration Files

### Backend Config (`testsprite_tests/tmp/config.json`)
```json
{
  "status": "init",
  "scope": "codebase",
  "type": "backend",
  "executionArgs": {
    "command": "php",
    "args": ["vendor\\bin\\phpunit", "--colors=always"],
    "projectPath": "c:\\laragon\\www\\surat_siega"
  }
}
```

### Frontend Config (`testsprite_tests/tmp/frontend_config.json`)
```json
{
  "status": "init",
  "scope": "codebase",
  "type": "frontend",
  "executionArgs": {
    "command": "npm",
    "args": ["run", "test:ui"],
    "projectPath": "c:\\laragon\\www\\surat_siega"
  }
}
```

---

## 🎯 Testing Best Practices

1. **Naming Convention**: Gunakan deskriptif nama test (`test_*.js` atau `*Test.php`)
2. **AAA Pattern**: Arrange-Act-Assert
3. **DRY**: Gunakan `beforeEach()` atau `setUp()` untuk shared setup
4. **Focus**: Gunakan `describe.only` atau `it.only` untuk debug single test
5. **Isolation**: Setiap test harus independent

---

## 🔍 Troubleshooting

### Frontend Tests Gagal
```bash
# Clear cache dan reinstall
rm -r node_modules
npm install
npm run test
```

### Backend Tests Gagal
```bash
# Ensure environment variables
export APP_ENV=testing
php vendor/bin/phpunit
```

### Permission Issues
```bash
chmod +x vendor/bin/phpunit
```

---

## 📚 Resources

- [Vitest Documentation](https://vitest.dev/)
- [PHPUnit Documentation](https://phpunit.de/)
- [TestSprite Documentation](https://testsprite.com/)
- [Testing Library](https://testing-library.com/)

---

## 📝 Notes

- Tests menggunakan SQLite in-memory database untuk isolasi
- Mail driver diset ke `array` untuk testing
- Queue driver diset ke `sync` untuk immediate processing
- Session driver diset ke `array` untuk stateless testing

---

**Last Updated:** December 9, 2025
