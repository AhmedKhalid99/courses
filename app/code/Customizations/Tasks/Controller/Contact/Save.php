<?php

namespace Customizations\Tasks\Controller\Contact;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Customizations\Tasks\Model\ContactDataFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Customer\Model\Session as CustomerSession;

class Save extends Action
{
    protected $contactDataFactory;
    protected $messageManager;
    protected $customerSession;

    public function __construct(
        Context $context,
        ContactDataFactory $contactDataFactory,
        ManagerInterface $messageManager,
        CustomerSession $customerSession
    ) {
        parent::__construct($context);
        $this->contactDataFactory = $contactDataFactory;
        $this->messageManager = $messageManager;
        $this->customerSession = $customerSession;
    }

    public function getLoggedInCustomer()
    {
        $customerId = $this->customerSession->getCustomerId() ? $this->customerSession->getCustomerId() : '';
        return $customerId;
    }


    public function execute()
    {
        $postData = $this->getRequest()->getPostValue();
        $customerId = $this->getLoggedInCustomer();
        if ($postData) {
            try {
                $contact = $this->contactDataFactory->create();
                $contact->setData('customer-id', $customerId); // Fix field names
                $contact->setData('customer-name', $postData['name']); // Fix field names
                $contact->setData('customer-email', $postData['email']);
                $contact->setData('phone-no', $postData['phone']);
                $contact->setData('message', $postData['message']); // Fix: Use setData instead of setMessage()
                $contact->save();
    
                $this->messageManager->addSuccessMessage(__('Your inquiry has been saved successfully.'));
    
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('An error occurred while saving your inquiry.'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Invalid form data.'));
        }
    
        // Redirect back to form
        return $this->_redirect('tasks/contact/index');
    }
    

}
