<?php
namespace Customizations\Apis\Controller\Api;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Customizations\Apis\Model\WatchedCourses;
use Magento\Framework\App\RequestInterface;
use Magento\Customer\Model\Session; // ✅ Import Customer Session

class MarkedCourses extends Action
{
    private $jsonFactory;
    private $watchedCourses;
    private $request;
    private $customerSession;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        WatchedCourses $watchedCourses,
        RequestInterface $request,
        Session $customerSession // ✅ Inject session
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->watchedCourses = $watchedCourses;
        $this->request = $request;
        $this->customerSession = $customerSession; // ✅ Assign session
    }

    public function execute()
    {

        try {
            if (!$this->customerSession->isLoggedIn()) {
                throw new \Exception("Customer is not logged in.");
            }

            $customerId = $this->customerSession->getCustomerId(); 
            $subtopicTitle = $this->getRequest()->getParam('subtopicTitle');
            $courseName = $this->getRequest()->getParam('courseName');
            $image = $this->getRequest()->getParam('image');
            $response = $this->watchedCourses->markCourseAsWatched($subtopicTitle, $courseName, $image, $customerId);

            $result = $this->jsonFactory->create();
            return $result->setData(['success' => true, 'data' => $response]);
        } catch (\Exception $e) {
            return $result->setData(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
