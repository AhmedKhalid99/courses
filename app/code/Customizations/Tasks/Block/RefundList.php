<?php

namespace Customizations\Tasks\Block;

use Magento\Framework\View\Element\Template;
use Customizations\Tasks\Model\RefundRequestFactory;

class RefundList extends Template
{
    protected $refundRequestFactory;

    public function __construct(
        Template\Context $context,
        RefundRequestFactory $refundRequestFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->refundRequestFactory = $refundRequestFactory;
    }

    public function getRefundRequests()
    {
        $customerId = $this->getCustomerId(); // Get customer ID passed from controller

        if (!$customerId) {
            return [];
        }

        $refundRequest = $this->refundRequestFactory->create();

        return $refundRequest->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->setOrder('created_at', 'DESC');
    }
}
