<?php
namespace Customizations\Tasks\Controller\Sales;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Customizations\Tasks\Model\RefundRequestFactory;
use Magento\Sales\Model\Order\ItemFactory as OrderItemFactory;
use Magento\Sales\Model\ResourceModel\Order\Item as OrderItemResource;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;

class SubmitRefund extends Action
{
    protected $customerSession;
    protected $refundRequestFactory;
    protected $orderItemFactory;
    protected $orderItemResource;
    protected $resultJsonFactory;
    protected $customerRepositoryInterface;


    public function __construct(
        Context $context,
        Session $customerSession,
        RefundRequestFactory $refundRequestFactory,
        OrderItemFactory $orderItemFactory,
        OrderItemResource $orderItemResource,
        JsonFactory $resultJsonFactory,
        CustomerRepositoryInterface $customerRepositoryInterface
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->refundRequestFactory = $refundRequestFactory;
        $this->orderItemFactory = $orderItemFactory;
        $this->orderItemResource = $orderItemResource;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerRepositoryInterface = $customerRepositoryInterface;

    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        if (!$this->customerSession->isLoggedIn()) {
            return $result->setData(['success' => false, 'message' => 'User not logged in']);
        }

        $data = $this->getRequest()->getParams();
        $customerId = $this->customerSession->getCustomer()->getId();

        if (empty($data['order_item_id']) || empty($data['reason'])) {
            return $result->setData(['success' => false, 'message' => 'Invalid data']);
        }

        try {

            $existingRefund = $this->refundRequestFactory->create()
            ->getCollection()
            ->addFieldToFilter('order_item_id', $data['order_item_id'])
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('status', 'Pending')
            ->getFirstItem();

            if ($existingRefund->getId()) {
                return $result->setData(['success' => false, 'message' => 'Refund request already exists for this item']);
            }
            
            $refund = $this->refundRequestFactory->create();
            $refund->setData([
                'customer_id' => $customerId,
                'order_item_id' => $data['order_item_id'],
                'reason' => $data['reason'],
                'status' => 'Pending'
            ]);
            $refund->save();

            $orderItem = $this->orderItemFactory->create()->load($data['order_item_id']);
            if ($orderItem->getId()) {
                $orderItem->setData('refund_request_id', $refund->getId());
                $this->orderItemResource->save($orderItem);
            }

            $customer = $this->customerRepositoryInterface->getById($customerId);
            $existingCourses = $customer->getCustomAttribute('customer_courses');
            $courseData = $existingCourses ? json_decode($existingCourses->getValue(), true) : [];
            if ($courseData && $orderItem->getId()) {
                $backendName = $orderItem->getId().'#'.$orderItem->getProduct()->getData('backend_name');

                foreach ($courseData as $category => $topics) {
                    if ($category === $backendName) {
                        $courseData[$category . '$$refund'] = $topics;
                        unset($courseData[$category]); 
                        break;
                    }
                }  
            }
            $customer->setCustomAttribute('customer_courses', json_encode($courseData));
            $this->customerRepositoryInterface->save($customer);
       
            return $result->setData(['success' => true, 'message' => 'Refund request submitted']);
        } catch (\Exception $e) {
            return $result->setData(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}