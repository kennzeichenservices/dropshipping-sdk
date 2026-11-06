# Example: Product Catalog

Lists every orderable product with the net costs that currently apply.

> Needs dropshipping API 2.6.0. Set `DROPSHIPPING_API_VERSION=2.6.0` or pass
> `apiVersion: '2.6.0'` to `DropshippingConfig`, and make sure your client is enabled for that
> version. Below 2.6.0 the API answers `403 Forbidden`.

## What it does

Fetches the catalog via `GET /products`, prints every entry, then resolves one variant to show how
the four costs and the shipping cost fit together. The content is the same as the product catalog
CSV export of the dropshipping self service.

This is where `productVariantId` for an order item comes from, so the catalog is worth fetching
once rather than hard-coding IDs.

## Key classes

| Class | Purpose |
|-------|---------|
| `ProductsResponse` | The listing, with `findByVariantId()` and `ofType()` |
| `Product` | One catalog entry, with `costForItem(1..4)` and `isLicensePlate()` |

## Costs are strings

`costItem1` through `costItem4` and `shippingCost` are decimal strings with a period as the
separator, and the SDK keeps them that way. These are billed cent amounts, and a float cannot hold
them exactly. Convert them where you need arithmetic, not before.

The four costs are a quantity staircase: the price per item drops with each item, so a pair of
plates costs `costForItem(1) + costForItem(2)`, not twice the first. `costForItem()` only resolves
the position and throws a `DropshippingException` outside 1 to 4.

## `productType` is a string, not an enum

The catalog publishes product types the SDK's `ProductType` enum does not model, `EMISSION_STICKER`
among them, and the spec declares the field as a string. `Product::$productType` therefore stays a
plain string and `ofType()` compares exactly, case included.

`licensePlateNumberLength` and `utschSku` are null for products they do not apply to: a plate has a
number length, an emission sticker does not, and `utschSku` is only set when UTSCH supplies the
product. `isLicensePlate()` reads the former.

## Run

```bash
php examples/product-catalog.php
```

Replace the `DropshippingConfig` values in `_bootstrap.php` with your actual credentials before
running. The listing is not paginated and changes rarely, so cache it on your side rather than
fetching it per order.
