<?php

namespace Customizations\Tasks\Controller\Watch;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\Order\ItemFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;
    protected $orderItemFactory;
    protected $productRepository;
    protected $customerRepositoryInterface;
    protected $customerSession;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ItemFactory $orderItemFactory,
        ProductRepositoryInterface $productRepository,
        CustomerRepositoryInterface $customerRepositoryInterface,
        CustomerSession $customerSession
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->orderItemFactory = $orderItemFactory;
        $this->productRepository = $productRepository;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->customerSession = $customerSession;
    }

    public function execute()
    {

        // $_SERVER['REMOTE_ADDR']
        $itemId = $this->getRequest()->getParam('item_id');

        if (!$itemId) {
            $this->messageManager->addErrorMessage(__('Invalid request parameters.'));
            return $this->_redirect($this->_redirect->getRefererUrl());
        }


        if (!$this->customerSession->isLoggedIn()) {
            $this->messageManager->addErrorMessage(__('You must be logged in to view your refund requests.'));
            return $this->_redirect('customer/account/login');
        }

        $orderItem = $this->orderItemFactory->create()->load($itemId);

        if (!$orderItem->getId()) {
            $this->messageManager->addErrorMessage(__('Order item not found.'));
            return $this->_redirect($this->_redirect->getRefererUrl());
        }

        if ($orderItem->getRefundRequestId()) {
            $this->messageManager->addErrorMessage(__('You cannot watch this course as a refund request has been created for this item.'));
            return $this->_redirect($this->_redirect->getRefererUrl());
        }

        if ($orderItem->getOrder()->getCustomerId() != $this->customerSession->getCustomerId()) {
            $this->messageManager->addErrorMessage(__('You are not authorized for this site url.'));
            return $this->_redirect($this->_redirect->getRefererUrl());
        }

        $sku = $orderItem->getSku();
        $product = $this->productRepository->get($sku);
        $courseOutline = $product->getCustomAttribute('course_outline') ? $product->getCustomAttribute('course_outline')->getValue() : '';

        $customer = $this->customerRepositoryInterface->getById($this->customerSession->getCustomerId());
        $data = $customer->getCustomAttribute('customer_watch_courses');
        $watchedCourse = $data ? $data->getValue() : '[]';

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getLayout()->getBlock('course_outline')
        ->setData('course_outline', $courseOutline)
        ->setData('watched_course', $watchedCourse)
        ->setData('product_name', $product->getName().'test');

        return $resultPage;
    }
}
