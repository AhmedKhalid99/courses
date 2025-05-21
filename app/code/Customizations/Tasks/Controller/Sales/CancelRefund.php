<?php
namespace Customizations\Tasks\Controller\Sales;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Customer\Model\Session;
use Customizations\Tasks\Model\RefundRequestFactory;
use Magento\Sales\Model\Order\ItemFactory as OrderItemFactory;
use Magento\Sales\Model\ResourceModel\Order\Item as OrderItemResource;
use Magento\Customer\Api\CustomerRepositoryInterface;

class CancelRefund extends Action
{
    protected $resultRedirectFactory;
    protected $refundRequestFactory;
    protected $orderItemFactory;
    protected $orderItemResource;
    protected $customerRepository;
    protected $customerSession;

    public function __construct(
        Context $context,
        RedirectFactory $resultRedirectFactory,
        RefundRequestFactory $refundRequestFactory,
        OrderItemFactory $orderItemFactory,
        OrderItemResource $orderItemResource,
        CustomerRepositoryInterface $customerRepository,
        Session $customerSession
    ) {
        parent::__construct($context);
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->refundRequestFactory = $refundRequestFactory;
        $this->orderItemFactory = $orderItemFactory;
        $this->orderItemResource = $orderItemResource;
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
    }

    public function execute()
    {

        //handle a case where the customer has ordered a same product but different option but now we need to merge it into a single item
        if (!$this->customerSession->isLoggedIn()) {
            $this->messageManager->addErrorMessage(__('You must be logged in.'));
            return $this->resultRedirectFactory->create()->setPath('customer/account/login');
        }

        $refundId = $this->getRequest()->getParam('refund_id');
        if (!$refundId) {
            $this->messageManager->addErrorMessage(__('Invalid refund request.'));
            return $this->resultRedirectFactory->create()->setPath('tasks/sales/refundlist');
        }

        try {
            $refund = $this->refundRequestFactory->create()->load($refundId);
            if (!$refund->getId() || $refund->getStatus() !== 'Pending') {
                $this->messageManager->addErrorMessage(__('Refund request cannot be canceled.'));
                return $this->resultRedirectFactory->create()->setPath('tasks/sales/refundlist');
            }

            // Mark refund as cancelled
            $refund->setStatus('Cancelled');
            $refund->save();

            // Revert Order Item Association
            $orderItem = $this->orderItemFactory->create()->load($refund->getOrderItemId());
            if ($orderItem->getId()) {
                $orderItem->setData('refund_request_id', null);
                $this->orderItemResource->save($orderItem);
            }

            // Restore Customer Courses Data
            $customer = $this->customerRepository->getById($refund->getCustomerId());
            $existingCourses = $customer->getCustomAttribute('customer_courses');
            $courseData = $existingCourses ? json_decode($existingCourses->getValue(), true) : [];

            if ($courseData && $orderItem->getId()) {
                $backendName = $orderItem->getId().'#'.$orderItem->getProduct()->getData('backend_name');
                if ($backendName) {
                    foreach ($courseData as $category => $topics) {
                        if ($category === $backendName . '$$refund') {
                            $courseData[str_replace('$$refund', '', $category)] = $topics;
                            unset($courseData[$category]);
                            break;
                        }
                    }
                }
            }

            $customer->setCustomAttribute('customer_courses', json_encode($courseData));
            $this->customerRepository->save($customer);

            $this->messageManager->addSuccessMessage(__('Refund request canceled successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error: ' . $e->getMessage()));
        }

        return $this->resultRedirectFactory->create()->setPath('tasks/sales/ListRequests');
    }
}
