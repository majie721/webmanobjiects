<?php

namespace Tests\ExampleClass;

class Order extends \Majie\Converter\Converter
{
    /** @var ?string purchaseOrderId */
    public ?string $purchaseOrderId = null;

    /** @var ?string customerOrderId */
    public ?string $customerOrderId = null;

    /** @var ?string customerEmailId */
    public ?string $customerEmailId = null;

    /** @var ?string buyerId */
    public ?string $buyerId = null;

    /** @var ?string orderType */
    public ?string $orderType = null;

    /** @var ?string originalCustomerOrderID */
    public ?string $originalCustomerOrderID = null;

    /** @var int orderDate */
    public int $orderDate;

    public ShippingInfo $shippingInfo;

    public ?OrderLines $orderLines = null;

}