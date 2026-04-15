# Example: Vehicle Deregistration

Submits a vehicle deregistration (Kfz-Abmeldung) and handles the resulting webhook with file download.

## What it does

1. Sends a deregistration request via `POST /vehicleDeregistrations/deregistrations`.
2. Sets up a webhook dispatcher that listens for `VEHICLE_DEREGISTRATION_XKFZ_EVENT` events.
3. For each file attached to the event, downloads the binary content via `GET /vehicleDeregistrations/files/content/{fileAccessKey}` and writes it to disk.

The API processes the deregistration asynchronously and sends one or more `VEHICLE_DEREGISTRATION_XKFZ_EVENT` webhooks as the status changes (e.g. `ACCEPTED` → `APPROVED_WITH_DOCUMENTS`).

## Key classes

| Class | Purpose |
|-------|---------|
| `VehicleDeregistrationRequest` | Top-level request DTO |
| `VehicleDeregistrationCustomization` | Vehicle data (VIN, registration date, plate security codes, etc.) |
| `VehicleDeregistrationVehicleHolder` | Vehicle holder address |
| `VehicleDeregistrationVehicleType` | Enum: `CAR`, `MOTORCYCLE`, `TRUCK`, `TRAILER`, `TRACTOR`, `LIGHT_MOTORCYCLE`, `OTHER` |
| `VehicleDeregistrationLicensePlateType` | Enum: `REGULAR`, `ELECTRIC`, `HISTORICAL`, `*_SEASON` variants |
| `VehicleDeregistrationXkfzEvent` | Webhook event with `status`, optional `files`, and optional `costBreakdown` |
| `VehicleDeregistrationXkfzEventFilePurposeType` | Enum: `CERTIFICATE`, `RECEIPT`, `APPLICATION`, `UNSPECIFIED` |

## Run

```bash
php examples/vehicle-deregistration.php
```

Replace the `DropshippingConfig` values, vehicle data, and `gksConfigurationId` with your actual credentials before running. The webhook portion requires a real incoming request from the API.
