# Example: Reserve License Plate

Submits a license plate reservation request.

## What it does

Creates a reservation via `POST /licensePlateReservations/reservations`. The API processes the request asynchronously and responds with a `LICENSE_PLATE_RESERVATION_APPROVAL` or `LICENSE_PLATE_RESERVATION_REJECTION` webhook event.

## Key classes

| Class | Purpose |
|-------|---------|
| `LicensePlateReservationRequest` | Top-level request DTO |
| `LicensePlateReservationCustomization` | Plate number, plate type, vehicle type, and registration office |
| `LicensePlateReservationVehicleHolder` | Vehicle holder address (and optional birth details) |
| `LicensePlateType` | Enum: `REGULAR`, `ELECTRIC`, `HISTORICAL`, `*_SEASON` variants |
| `VehicleType` | Enum: `CAR`, `MOTORCYCLE` |

## Run

```bash
php examples/reserve-license-plate.php
```

Replace the `DropshippingConfig` values and `registrationOfficeServiceId` with your actual credentials and office ID before running.
