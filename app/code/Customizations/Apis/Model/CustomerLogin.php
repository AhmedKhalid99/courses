<?php
namespace Customizations\Apis\Model;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Integration\Model\CredentialsValidator;
use Magento\Integration\Model\Oauth\Token as Token;
use Magento\Integration\Model\Oauth\TokenFactory as TokenModelFactory;
use Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory as TokenCollectionFactory;
use Magento\Integration\Model\Oauth\Token\RequestThrottler;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Customizations\Apis\Api\CustomerLoginInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\Webapi\Exception;

class CustomerLogin implements CustomerLoginInterface
{
 
    protected $tokenModelFactory;
    protected $accountManagement;
    protected $validatorHelper;
    protected $tokenModelCollectionFactory;
    protected $jsonEncoder;

    public function __construct(
        TokenModelFactory $tokenModelFactory,
        AccountManagementInterface $accountManagement,
        TokenCollectionFactory $tokenModelCollectionFactory,
        CredentialsValidator $validatorHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder
    ) {
        $this->tokenModelFactory = $tokenModelFactory;
        $this->accountManagement = $accountManagement;
        $this->tokenModelCollectionFactory = $tokenModelCollectionFactory;
        $this->validatorHelper = $validatorHelper;
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * Customer login implementation.
     *
     * @param string $email
     * @param string $password
     * @param string $machineIp
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function login($email, $password, $machineIp)
    {
        try {  
            $this->validatorHelper->validate($email, $password);
            $customerDataObject = $this->accountManagement->authenticate($email, $password);
            $customerCourses = $customerDataObject->getCustomAttributes()['customer_courses'] ?? null;
            $coursesDetails=[];

            if ($customerCourses && $customerCourses->getValue()) {
                $decodedCourses = json_decode($customerCourses->getValue(), true);
                $coursesDetails = $this->getCoursesDetail($decodedCourses);
            }
            $token = '';
            // $token = $this->tokenModelFactory->create()->createCustomerToken($customerDataObject->getId())->getToken();

            $response = [
                'status' => 200,
                'customer-details' => [
                    'user_token' => $token,  
                    'customer_id' => $customerDataObject->getId(),
                    'customer_name' => $customerDataObject->getFirstname()
                ],
                'courses-details' => $coursesDetails
            ];
            
            return $this->jsonEncoder->encode($response);
        } catch (\Exception $e) {
            throw new LocalizedException(__('Customer login failed: %1', $e->getMessage()));
        }
    }
    public function getCoursesDetail($decodedCourses)
    {
        foreach ($decodedCourses as $courseName => $courseParts) {
            if (strpos($courseName, 'child-') === 0) {
                $parentCourse = str_replace('child-', '', $courseName);
                foreach ($courseParts as $childCourse => $childTopics) {
                    if (!isset($decodedCourses[$parentCourse])) {
                        $decodedCourses[$parentCourse] = [];
                    }
                    $existingIndex = array_search($childCourse, $decodedCourses[$parentCourse]);
                    if ($existingIndex !== false) {
                        if (!is_array($decodedCourses[$parentCourse][$existingIndex])) {
                            $decodedCourses[$parentCourse][$existingIndex] = [$decodedCourses[$parentCourse][$existingIndex]];
                        }
                        $decodedCourses[$parentCourse][$existingIndex] = array_unique(
                            array_merge($decodedCourses[$parentCourse][$existingIndex], $childTopics)
                        );
                    } else {
                        $decodedCourses[$parentCourse][$childCourse] = $childTopics;
                    }
                }
            }
        }

        foreach ($decodedCourses as $courseName => $courseParts) {
            if (strpos($courseName, 'child-') === 0) {
                continue;
            }
            $coursesDetails[] = [
                'course_name' => $courseName,
                'course_part' => $courseParts
            ];
        }
        return $coursesDetails;
    }
}
