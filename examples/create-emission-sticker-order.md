# Example: Create Emission Sticker Order

Creates an emission sticker order with a vehicle document file upload.

## What it does

Submits a multipart order via `POST /orders/emissionStickerOrders`. The request includes the license plate number, emission key number, and one or more file paths (e.g. a Fahrzeugschein scan). Files are uploaded as multipart form data.

## Key classes

| Class | Purpose |
|-------|---------|
| `EmissionStickerOrderRequest` | Request DTO including file paths for multipart upload |
| `EuroLicensePlateNumberComponents` | City, middle, and end components of a Euro plate number |
| `Address` | Delivery and invoice address |

## Run

```bash
php examples/create-emission-sticker-order.php
```

Replace the `DropshippingConfig` values and `filePaths` with your actual credentials and document paths before running.
