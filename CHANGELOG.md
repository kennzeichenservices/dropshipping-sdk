# Changelog

All notable changes to this project will be documented in this file.

## [2.3.20] — 2026-07-29

### Features

- Add beta vehicle registration feature

### Miscellaneous

- Bump version to 2.3.20

## [2.3.11] — 2026-07-22

### Miscellaneous

- Release v2.3.11
- Bump version to 2.3.11 and update VehicleDeregistrationRequest tests

## [2.3.10] — 2026-07-22

### Miscellaneous

- Release v2.3.10
- Bump version to 2.3.10 in composer.json

## [2.3.9] — 2026-04-29

### Miscellaneous

- Release v2.3.9

## [2.3.8] — 2026-04-23

### Features

- Enhance VehicleDeregistrationXkfzEvent with derived status and messages support
- Simplify webhook handling by utilizing DS for pipeline and dispatcher setup

## [2.3.7] — 2026-04-15

### Features

- Add examples for webhook processing, license plate reservation, and order creation

## [2.3.5] — 2026-04-14

### Features

- Add expiration time to certificate file in webhook event test
- Update vehicle deregistration API to include expiration time and improve documentation

## [2.3.2] — 2026-04-13

### Features

- Add vehicle deregistration file download functionality and update related event structures

## [2.3.1] — 2026-04-13

### Features

- Add Dropshipping Webhooks API documentation for versions 3.0.0 and 3.1.0
- Expand SDK features to include GKS configurations and vehicle deregistration endpoints

## [2.3.0] — 2026-04-12

### Features

- Add vehicle deregistration functionality and related DTOs

## [1.2.11] — 2026-03-17

### Features

- Add support for explicit and environment-based API versioning in DropshippingConfig

## [1.2.10] — 2026-02-28

### Features

- Add nullable string and integer validation methods; enhance validation in DTOs

## [1.2.6] — 2026-02-03

### Bug Fixes

- Format parameters in AvailabilityCheckRequestTest for better readability
- Update registrationOfficeServiceId in AvailabilityCheckRequest and LicensePlateReservationRequest tests

## [1.2.5] — 2026-02-03

### Bug Fixes

- Remove integration tests for PHP 8.2 from release workflow

## [1.2.4] — 2026-02-03

### Features

- Add built-in request/response debug logging functionality

## [1.2.3] — 2026-02-03

### Bug Fixes

- Remove integration tests from workflow
- Specify target SHA for release creation in workflow

### Features

- Add integration tests for PHP 8.2 and enhance getBaseUrl method to strip schemes and trailing slashes

### Miscellaneous

- Re-trigger release workflow for v1.2.3
- Trigger release workflow for v1.2.3

## [1.2.1] — 2026-02-03

### Features

- Enhance webhook event DTOs with detailed documentation and constructors

## [1.1.0] — 2026-01-31

### Features

- Add unit tests for Orders, Products, Shipments, Enums, Http, Security, Serialization, Support, and Webhook components

## [1.0.0] — 2026-01-31

### Features

- Add initial project files including README, .gitignore, and GitHub Actions workflow
