# Internal README: Project Setup & Testing

## Project Requirements

- PHP 7.4 or higher
- Composer

## Setup Instructions

1. **Clone or copy the repository** to your local machine.
2. **Install dependencies**:

  ```shell
  composer install
  ```

3. **Run the test suite**:

  ```shell
  composer test
  ```

- This will execute all tests in the `tests/` directory using PHPUnit and display readable output in TestDox format.

## Project Structure

- **Source code:** `src/`
- **Tests:** `tests/`
- **Configuration:**
  - Composer: `composer.json`
  - PHPUnit: `phpunit.xml`

## Adding and Running Tests

- Place new test files in the appropriate subfolder under `tests/`.
- Run all tests with:

 ```shell
 composer test
 ```

## Additional Notes

- If you need to run PHPUnit directly, use:

 ```shell
 vendor\bin\phpunit tests --testdox
 ```

- For code coverage, you can use:

 ```shell
 vendor\bin\phpunit --coverage-html coverage
 ```

 (Requires Xdebug or PCOV extension)

---
This file is for internal use and documents the setup and test process for developers working on this project.
