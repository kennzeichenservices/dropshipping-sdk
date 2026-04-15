# Example: Create Reshipped Order

Creates a new shipment for a previously returned delivery.

## What it does

Submits a reshipped order via `POST /orders/reshippedOrders`. Use this when a delivery has been returned (signalled by a `DELIVERY_RETURN` webhook event) and the customer wants the item sent again to a new address.

The `returnedDeliveryId` is the delivery ID from the `DELIVERY_RETURN` webhook event (`$event->delivery->id`).

## Key classes

| Class | Purpose |
|-------|---------|
| `ReshippedOrderRequest` | Request DTO referencing the original returned delivery |
| `Address` | New delivery and invoice address |

## Run

```bash
php examples/create-reshipped-order.php
```

Replace the `DropshippingConfig` values and `returnedDeliveryId` with your actual credentials and delivery ID before running.
