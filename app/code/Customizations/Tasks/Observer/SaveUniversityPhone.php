<?php

namespace Customizations\Tasks\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\CustomerFactory;

class SaveUniversityPhone implements ObserverInterface
{
    protected $request;
    protected $customerFactory;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        CustomerFactory $customerFactory
    ) {
        $this->request = $request;
        $this->customerFactory = $customerFactory;
    }

    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $email = $customer->getEmail();
        $university = $this->request->getParam('university');
        $mobile = $this->request->getParam('mobile');
        $data = $university."$$".$mobile;
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId(1); // 1 for default website
        $customer->loadByEmail($email);


        if ($university) {
            // $customAttributes = $customer->getCustomAttributes() ?? [];
            // $customer->setCustomAttribute('university', $university);
            // $this->customerRepository->save($customer);
            $customer->setData('university', $data);
            $customer->getResource()->saveAttribute($customer, 'university');
        }
    }
}
