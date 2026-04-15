# Example: GKS Configurations

Manages GKS (Großkundenschnittstelle) configurations for KBA interface access.

## What it does

Covers all four GKS configuration operations in a single script:

| Operation | Method | Endpoint |
|-----------|--------|----------|
| Create | `POST /gksConfigurations` | Returns the new configuration overview |
| Update | `PUT /gksConfigurations/{id}` | Returns nothing (204) |
| List all | `GET /gksConfigurations/overviews` | Returns all configuration overviews |
| Get single | `GET /gksConfigurations/overviews/{id}` | Returns one configuration overview |

A GKS configuration stores the KBA credentials (KOPA key, username, password, PEM certificate and private key) and the associated company details. The resulting UUID is passed as `gksConfigurationId` in vehicle deregistration requests.

## Key classes

| Class | Purpose |
|-------|---------|
| `GksConfigurationWriteRequest` | Request DTO for create and update operations |
| `GksConfigurationCompany` | Company name and address nested in the write request |
| `OverviewGksConfiguration` | Response DTO with `id` (UUID) and `name` |
| `GksConfigurationOverviewsResponse` | Response DTO wrapping the list of overviews |

## Run

```bash
php examples/gks-configurations.php
```

Replace the `DropshippingConfig` values and the certificate / private key paths with your actual credentials before running.
