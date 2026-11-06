<?php

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

// NOTE: The product catalog needs dropshipping API 2.6.0. Set DROPSHIPPING_API_VERSION=2.6.0 or
// pass apiVersion to DropshippingConfig, and make sure your client is enabled for that version.
// Below 2.6.0 the API answers 403 Forbidden, as it does for every endpoint on a version the
// client is not entitled to.

$catalog = $client->products->getCatalog();

echo 'Catalog entries: ' . count($catalog->products) . "\n\n";

// Costs are decimal strings, not floats: these are billed cent amounts, and a float cannot hold
// them exactly. Convert them where you need arithmetic, not before.
//
// Printed with separators rather than padded columns on purpose: product names carry umlauts,
// and printf pads by bytes, so a %-32s column drifts by one position per multibyte character.
foreach ($catalog->products as $product) {
    echo implode(' | ', [
        $product->productVariantId,
        $product->productType,
        $product->productVariantName,
        'first ' . $product->costItem1 . ' EUR',
        'shipping ' . $product->shippingCost . ' EUR',
    ]) . "\n";
}

echo "\n";

// productVariantId is what an order item references, so the catalog is where it comes from.
$plate = $catalog->findByVariantId(2);

if ($plate === null) {
    echo "Variant 2 is not in the catalog.\n";
} else {
    // The four costs are a quantity staircase: the price per item drops with each item. A pair of
    // plates therefore costs costForItem(1) + costForItem(2), not twice the first.
    echo "A pair of \"{$plate->productVariantName}\" costs "
        . "{$plate->costForItem(1)} + {$plate->costForItem(2)} EUR net "
        . "plus {$plate->shippingCost} EUR shipping\n";

    if ($plate->isLicensePlate()) {
        echo "Plate numbers may be up to {$plate->licensePlateNumberLength} characters long\n";
    }
}

echo "\n";

// productType is a plain string, not the ProductType enum: the catalog publishes types that enum
// does not model, EMISSION_STICKER among them. The comparison is exact and case sensitive.
foreach (['LICENSE_PLATE', 'EMISSION_STICKER'] as $type) {
    echo $type . ': ' . count($catalog->ofType($type)) . " variants\n";
}

// The listing is not paginated and changes rarely, so cache it on your side rather than fetching
// it per order. It is the same content as the product catalog CSV export of the dropshipping self
// service.
