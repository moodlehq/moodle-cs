# Change log

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

The format of this change log follows the advice given at [Keep a CHANGELOG](http://keepachangelog.com).

## [Unreleased]
## [v3.3.12] - 2023-12-19
### Changed
- Moved `PHP_CodeSniffer` from `squizlabs` (being abandoned) to `PHPCSStandars` (new repository). Everybody is welcome to [contribute](https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/master/.github/CONTRIBUTING.md) or help [sponsoring](https://github.com/PHPCSStandards/PHP_CodeSniffer) the project!
- Update composer dependencies to current versions, notably `PHP_CodeSniffer` (3.8.0) and `PHPCompatibility` (306cd263).
- Applied various internal, coding and CI improvements:
    - Ruleset fixes.
    - PHP 8.3 compliance achieved.
    - PHP 8.3 GitHub workflows support.

## [v3.3.11] - 2023-11-29
### Changed
- Amended the installation instructions to be able to use some dev dependencies.
- Moved all links to "master" branches to their new "main" counterparts.

### Fixed
- Solved a problem with the `CommaAfterLast` Sniff that was causing some valid multi-line arrays to be identified as invalid, missing commas.

## [v3.3.10] - 2023-10-20
### Added
- Detected various duplicate array keys cases (code smell).
- Enforced, for Moodle 4.4dev and up, that all the test methods have a return type specified (normally `: void`).

## [v3.3.9] - 2023-09-26
### Added
- Defined (via `.gitattributes`) which files must not be part of distribution (generated .zip & .gz in GitHub, `--prefer-dist` in Composer, ...) packages. This includes docs, ci files, tests... Note that it's still possible to download the complete packages (using `git clone`, `--prefer-source` with Composer, ...).

### Fixed
- Removed the `PSR1.Classes.ClassDeclaration.MissingNamespace` sniff from the `moodle-extra` standard because it was reporting some (for Moodle) false positives.

## [v3.3.8] - 2023-09-22
### Added
- Detect PHPUnit data provider (`@dataProvider`) declarations using `()` after the function name.

### Fixed
- Fixed a problem with some non test files (`coverage.php`, ...) being managed as such.

## [v3.3.7] - 2023-09-22
### Added
- Require trailing commas in multi-line arrays.
- Verify that both `namespace` and `use` (class import) declarations don't use leading slashes (`\`).
- Verify various aspects/common mistakes related with PHPUnit data providers:
  - Private providers.
  - Missing providers.
  - Providers with incorrect names.
  - Providers with wrong return types.
  - Non-static providers.

### Fixed
- Fixed incorrect handling of abstract methods within PHPUnit sniffs.


## [v3.3.6] - 2023-09-15
### Added
- A new `moodle-extra` coding standard which moves towards a more PSR-12 compliant coding style.
- Enforce the use of the short array syntax (`[]`), warning about the long alternative (`array()`): `Generic.Arrays.DisallowLongArraySyntax`. This will be raised from `warning` to `error` in 1 year.

## [v3.3.5] - 2023-08-28
### Changed
- Update composer dependencies to current versions, notably PHPCompatibility (0a17f9ed).
- Enforce the use of `&&` and `||` logical operators, **now erroring** (after a grace period of 1 year) with `and` and `or` uses: `Squiz.Operators.ValidLogicalOperators`

## [v3.3.4] - 2023-05-28
### Changed
- Update composer dependencies to current versions, notably PHPCompatibility (70e4ca24).

### Added
- Various internal, code coverage related, improvements.
    - Add GHA PHP 8.2 support.
    - Upload and integrate the repository with [Codecov](https://about.codecov.io), for better tracking of changes.
    - Completely cover the MoodleUtil class, in charge of providing important
      information about Moodle (versions, branches, components...)

## [v3.3.3] - 2023-03-14
### Removed
- Revert the check for the only one-space rule before the assignment operator @ `Squiz.WhiteSpace.OperatorSpacing` as an interim solution while we revisit [MDLSITE-6594](https://tracker.moodle.org/browse/MDLSITE-6594).

## [v3.3.2] - 2023-02-13
### Added
- Check for one (and only one) space before assignment operator @ `Squiz.WhiteSpace.OperatorSpacing`

## [v3.3.1] - 2023-01-19
### Fixed
- Updated the outdated list of valid magic methods.

## [v3.3.0] - 2023-01-13
### Added
- Enforce the use of `&&` and `||` logical operators, warning about `and` and `or`: `Squiz.Operators.ValidLogicalOperators`

### Changed
- Many internal changes towards better self-testing and integration with other tools ([GH workflows](https://github.com/moodlehq/moodle-cs/actions), codechecker, core, phpunit...).
- Upgraded the [PHPCompatibility standard](https://github.com/PHPCompatibility/PHPCompatibility) from 3 years old version 9.3.5 (no releases since then) to current development version.

### Fixed
- Stop considering `class_alias` like a side effect.
- Add back the `Squiz.Arrays.ArrayBracketSpacing` sniff.

## v3.2.0 - 2022-02-28
This release is the first release of the new [moodlehq/moodle-cs](https://packagist.org/packages/moodlehq/moodle-plugin-ci) packages.

These rules, in an identical form, were previously available as a part of the [local_codechecker Moodle plugin](https://moodle.org/plugins/local_codechecker)  but are being moved to their own repository to make installation friendlier for developers.

All features are maintained and no new features have been introduced to either the rules, or the sniffs.

All the details about [previous releases] can be found in [local_codechecker](https://github.com/moodlehq/moodle-local_codechecker) own change log.

[Unreleased]: https://github.com/moodlehq/moodle-cs/compare/v3.3.12...main
[v3.3.12]: https://github.com/moodlehq/moodle-cs/compare/v3.3.11...v3.3.12
[v3.3.11]: https://github.com/moodlehq/moodle-cs/compare/v3.3.10...v3.3.11
[v3.3.10]: https://github.com/moodlehq/moodle-cs/compare/v3.3.9...v3.3.10
[v3.3.9]: https://github.com/moodlehq/moodle-cs/compare/v3.3.8...v3.3.9
[v3.3.8]: https://github.com/moodlehq/moodle-cs/compare/v3.3.7...v3.3.8
[v3.3.7]: https://github.com/moodlehq/moodle-cs/compare/v3.3.6...v3.3.7
[v3.3.6]: https://github.com/moodlehq/moodle-cs/compare/v3.3.5...v3.3.6
[v3.3.5]: https://github.com/moodlehq/moodle-cs/compare/v3.3.4...v3.3.5
[v3.3.4]: https://github.com/moodlehq/moodle-cs/compare/v3.3.3...v3.3.4
[v3.3.3]: https://github.com/moodlehq/moodle-cs/compare/v3.3.2...v3.3.3
[v3.3.2]: https://github.com/moodlehq/moodle-cs/compare/v3.3.1...v3.3.2
[v3.3.1]: https://github.com/moodlehq/moodle-cs/compare/v3.3.0...v3.3.1
[v3.3.0]: https://github.com/moodlehq/moodle-cs/compare/v3.2.0...v3.3.0
[Previous releases]: https://github.com/moodlehq/moodle-local_codechecker/blob/main/CHANGES.md#changes-in-version-400-20220825---welcome-moodle-cs
