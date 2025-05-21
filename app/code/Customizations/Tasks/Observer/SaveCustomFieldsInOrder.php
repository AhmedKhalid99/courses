<?php
namespace Customizations\Tasks\Observer;

class SaveCustomFieldsInOrder implements \Magento\Framework\Event\ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();
        // $order->setData('customer_dob', $quote->getCustomerDob());

        return $this;
    }
} 