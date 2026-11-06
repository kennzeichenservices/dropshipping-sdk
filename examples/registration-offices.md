# Example: Registration Offices

Lists every registration office (Zulassungsstelle) with the reservation fee it charges.

> Needs dropshipping API 2.6.0. Set `DROPSHIPPING_API_VERSION=2.6.0` or pass
> `apiVersion: '2.6.0'` to `DropshippingConfig`, and make sure your client is enabled for that
> version. Below 2.6.0 the API answers `403 Forbidden`.

## What it does

Fetches the listing via `GET /registrationOffices`, prints every office, then looks one up by
region code and by ID. The content is the same as the registration office CSV export of the
dropshipping self service.

## Key classes

| Class | Purpose |
|-------|---------|
| `RegistrationOfficesResponse` | The listing, with `findById()` and `findByRegionCode()` |
| `RegistrationOffice` | One office, with `chargesReservationFee()` |

## A region code is not unique

Several offices can issue the same code, so `findByRegionCode()` returns a list rather than hiding
the rest behind the first match. Codes carry umlauts (`BÖ`, `LÖ`, `MÜ`), and the comparison is
exact, so pass them as the listing publishes them.

## The reservation fee

`reservationPrice` is what the office charges in advance for a license plate reservation, as a
decimal string with a period as the separator. It stays a string for the same reason the catalog's
costs do: it is a billed cent amount.

`chargesReservationFee()` compares it numerically, so `"0"`, `"0.00"` and `"0.0000"` all count as
free. A value that cannot be read as a number counts as a fee, which is the safer of the two
mistakes: an office that silently looks free is the one that costs money.

## About the `id`

The spec does not say whether this `id` is the same identifier as the `registrationOfficeServiceId`
that [check-license-plate-availability.php](check-license-plate-availability.php) and
[reserve-license-plate.php](reserve-license-plate.php) take. The names differ and so do the formats
(`int64` here against `int32` there), which reads like an office and a service of that office are
two things. Confirm with the API before passing one for the other.

## Run

```bash
php examples/registration-offices.php
```

Replace the `DropshippingConfig` values in `_bootstrap.php` with your actual credentials before
running. The listing is not paginated and changes rarely, so cache it on your side rather than
fetching it per order.
