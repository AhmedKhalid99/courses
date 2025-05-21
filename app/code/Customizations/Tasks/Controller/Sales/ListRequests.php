<?php

namespace Customizations\Tasks\Controller\Sales;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session as CustomerSession;

class ListRequests extends Action
{
    protected $resultPageFactory;
    protected $customerSession;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CustomerSession $customerSession
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession;
    }

    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            $this->messageManager->addErrorMessage(__('You must be logged in to view your refund requests.'));
            return $this->_redirect('customer/account/login');
        }

        $customerId = $this->customerSession->getCustomerId();

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getLayout()->getBlock('refund-list')->setCustomerId($customerId);

        return $resultPage;
    }
}
