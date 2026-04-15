# Example: Check License Plate Availability

Checks which license plate numbers are available at a registration office.

## What it does

Sends an availability check via `POST /licensePlateReservations/availabilityChecks`. The response contains a list of available `EuroLicensePlateNumberComponents` that can be used in a subsequent reservation request.

## Key classes

| Class | Purpose |
|-------|---------|
| `AvailabilityCheckRequest` | Request DTO with plate components, plate type, vehicle type, and registration office |
| `LicensePlateType` | Enum: `REGULAR`, `ELECTRIC`, `HISTORICAL`, `*_SEASON` variants |
| `VehicleType` | Enum: `CAR`, `MOTORCYCLE` |

## Run

```bash
php examples/check-license-plate-availability.php
```

Replace the `DropshippingConfig` values and `registrationOfficeServiceId` with your actual credentials and office ID before running.
