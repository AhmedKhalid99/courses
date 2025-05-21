<?php
namespace Customizations\Apis\Model;

use Customizations\Apis\Api\WatchedCoursesInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;

class WatchedCourses implements WatchedCoursesInterface
{
    private $customerRepository;
    private $customerId = 8; // Hardcoded customer ID

    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function getWatchedCourses($customerId = null)
    {
        try {
            $customerId = $customerId ?? $this->customerId;
            $customer = $this->customerRepository->getById($customerId);
            $watchedCourses = $customer->getCustomAttribute('customer_watch_courses');
    
            if (!$watchedCourses || !$watchedCourses->getValue()) {
                return [];
            }
    
            $watchedList = json_decode($watchedCourses->getValue(), true);
    
            return is_array($watchedList) ? $watchedList : [];
        } catch (\Exception $e) {
            return [];
        }
    }
     
    public function markCourseAsWatched($subtopicTitle, $courseName, $image, $customerId = null)
    {
        try {
            $customerId = $customerId ?? $this->customerId; 
            $customer = $this->customerRepository->getById($customerId);
            $watchedCourses = $customer->getCustomAttribute('customer_watch_courses');
    
            // Retrieve existing watched courses, ensure proper decoding
            $watchedList = $watchedCourses && $watchedCourses->getValue() 
                ? json_decode($watchedCourses->getValue(), true) 
                : [];
    
            if (!is_array($watchedList)) {
                $watchedList = []; // Fallback to an empty array if decoding fails
            }
    
            // Ensure watched courses retain previous data before modifying
            if (!isset($watchedList[$courseName]) || !is_array($watchedList[$courseName])) {
                $watchedList[$courseName] = []; // Maintain previous data
            }
    
            // Check if the subtopic already exists
            foreach ($watchedList[$courseName] as $entry) {
                if (isset($entry['name']) && $entry['name'] === $subtopicTitle) {
                    return "Subtopic already marked as watched.";
                }
            }
    
            // Append new subtopic with timestamp
            $watchedList[$courseName][] = [
                'name' => $subtopicTitle,
                'time' => date('Y-m-d H:i:s'),
                'image' => $image
            ];
    
            // Save updated watched courses back to the customer attribute
            $customer->setCustomAttribute('customer_watch_courses', json_encode($watchedList));
            $this->customerRepository->save($customer);
    
            return "Subtopic marked as watched.";
        } catch (\Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }
}

