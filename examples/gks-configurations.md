# Example: GKS Configurations

Manages GKS (Großkundenschnittstelle) configurations for KBA interface access.

## What it does

Covers all GKS configuration operations in a single script:

| Operation | Method | Endpoint |
|-----------|--------|----------|
| Create | `POST /gksConfigurations` | Returns the new configuration overview |
| Update | `PUT /gksConfigurations/{id}` | Returns nothing (204) |
| List all | `GET /gksConfigurations/overviews` | Returns all configuration overviews |
| Get single | `GET /gksConfigurations/overviews/{id}` | Returns one configuration overview |
| Enabled versions | `GET /gksClientVersions/enabled` | Returns the GKS client versions a configuration may reference |

A GKS configuration stores the KBA credentials (KOPA key, username, password, PEM certificate and private key), the associated company details and the GKS client version it runs on. The resulting UUID is passed as `gksConfigurationId` in vehicle deregistration requests.

`gksClientVersionNumber` is required by dropshipping API 2.4.0. It defaults to `2.0` — the version the API assumed before the field existed — so existing calls keep working; pass another value from `getEnabledClientVersions()` to move a configuration.

## Key classes

| Class | Purpose |
|-------|---------|
| `GksConfigurationWriteRequest` | Request DTO for create and update operations |
| `GksConfigurationCompany` | Company name and address nested in the write request |
| `OverviewGksConfiguration` | Response DTO with `id` (UUID), `name` and `gksClientVersionNumber` |
| `GksConfigurationOverviewsResponse` | Response DTO wrapping the list of overviews |
| `EnabledGksClientVersionsResponse` | Response DTO wrapping the enabled versions, with `versionNumbers()` |
| `GksClientVersion` | Response DTO with a single `versionNumber` |

## Run

```bash
php examples/gks-configurations.php
```

Replace the `DropshippingConfig` values and the certificate / private key paths with your actual credentials before running.
