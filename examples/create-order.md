# Example: Create Order

Creates a standard order with a license plate customization.

## What it does

Submits a new order via `POST /orders`. The order contains one item with a Euro license plate number. The API returns an order ID on success.

## Key classes

| Class | Purpose |
|-------|---------|
| `OrderCreationRequest` | Top-level request DTO |
| `OrderItem` | A single line item in the order |
| `LicensePlateItemCustomization` | Customization for a license plate item |
| `EuroLicensePlateNumberComponents` | City, middle, and end components of a Euro plate number |
| `Address` | Delivery and invoice address |

## Run

```bash
php examples/create-order.php
```

Replace the `DropshippingConfig` values and `productVariantId` with your actual credentials and product data before running.
