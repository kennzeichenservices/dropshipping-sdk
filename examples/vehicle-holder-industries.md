# Example: Vehicle Holder Industries

Lists the industries (Wirtschaftszweige) a legal person can be registered under as the vehicle
holder of a vehicle registration.

> Needs dropshipping API 2.7.0. Set `DROPSHIPPING_API_VERSION=2.7.0` or pass
> `apiVersion: '2.7.0'` to `DropshippingConfig`, and make sure your client is enabled for that
> version. Below 2.7.0 the API answers `403 Forbidden`.

## What it does

Fetches the listing via `GET /vehicleHolderIndustries`, prints it grouped by section, then looks a
code up and checks whether it is accepted. The content comes from the Berufsschlüssel of the
ErfassRL Kfz, a KBA reference table.

## Key classes

| Class | Purpose |
|-------|---------|
| `VehicleHolderIndustriesResponse` | The listing, with `findByCode()`, `hasCode()`, `codes()` and `groupedBySection()` |
| `VehicleHolderIndustry` | One industry, with `sectionLetter()` |

## A code is not unique

The spec says a code may belong to several industry names, so `findByCode()` returns a list rather
than hiding the rest behind the first match. `codes()` deduplicates, which is why it can report
fewer codes than there are entries.

The comparison is exact, so pass a code in upper case, as the listing publishes it.

## What the code is for

It goes into `industryCode` of `DS::registrationLegalPersonVehicleHolder(...)`, the vehicle holder
of a registration ordered for a company. See
[vehicle-registration.php](vehicle-registration.php).

The SDK checks the shape of the code (three characters of `A-Z` and `0-9`) when the holder is
built, but only the API knows which codes exist, and it rejects an unknown one *after* the customer
has identified themselves and signed. `hasCode()` is therefore worth calling before the order goes
out.

## Run

```bash
php examples/vehicle-holder-industries.php
```

Replace the `DropshippingConfig` values in `_bootstrap.php` with your actual credentials before
running. The listing is not paginated and changes rarely, so cache it on your side rather than
fetching it per order.
