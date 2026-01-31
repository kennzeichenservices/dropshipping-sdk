<?php

declare(strict_types=1);

namespace Dropshipping\Enums;

enum WebhookEventType: string
{
    case Ping = 'PING';
    case DeliveryShipment = 'DELIVERY_SHIPMENT';
    case DeliveryReturn = 'DELIVERY_RETURN';
    case DeliveryCancellation = 'DELIVERY_CANCELLATION';
    case LicensePlateReservationApproval = 'LICENSE_PLATE_RESERVATION_APPROVAL';
    case LicensePlateReservationRejection = 'LICENSE_PLATE_RESERVATION_REJECTION';
    case LicensePlateReservationTimeout = 'LICENSE_PLATE_RESERVATION_TIMEOUT';
}
