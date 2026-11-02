# Internal README: Project Setup & Testing

## Project Requirements

- PHP 8.2 or higher (CI runs the tests on 8.2, 8.3, 8.4 and 8.5)
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

## Releasing

Releases run from the **Release QueueToken PHP** pipeline (`pipelines/release.yml`). Do not
change `version` in `composer.json` by hand: it holds the last released version, and the
pipeline calculates the next one from it.

Before running it:

- Write `docs/release-notes/<version>.md` and add its row to `docs/release-notes/README.md`
  (see that README for the standard), and merge that to `master`. The pipeline fails if
  either is missing. A prerelease (`<version>-rc.<timestamp>`) uses the notes of `<version>`.

Then:

1. Run the pipeline with **ReleaseType** `major`, `minor`, `patch` or `prerelease`. It tests
   on the minimum supported PHP version, sets the new version in `composer.json`, pushes
   `release/v<version>` to `github.com/queueit/QueueToken.V1.PHP` and commits the version
   bump back to `master`.
2. Pushing the branch opens the release PR on GitHub. Its description is the release notes
   file.
3. Review and merge the PR. The merge runs the **Final GitHub Release** workflow, which
   tags the merged commit `v<version>` and creates the GitHub release with the PR
   description as its text.
4. Packagist publishes the tag through its GitHub hook within a few minutes:
   https://packagist.org/packages/queueit/queuetoken-v1-php

---
This file is for internal use and documents the setup and test process for developers working on this project.
