<?php
namespace Customizations\Tasks\Observer;

use \Magento\Customer\Api\CustomerRepositoryInterface;
use \Magento\Catalog\Model\ProductRepository;

class SaveProductDetails implements \Magento\Framework\Event\ObserverInterface
{
    protected $customerRepositoryInterface;
    protected $productRepository;

    public function __construct(
        CustomerRepositoryInterface $customerRepositoryInterface,
        ProductRepository $productRepository
    ) {
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->productRepository = $productRepository;
    }

    // public function execute(\Magento\Framework\Event\Observer $observer) {
    //     $order = $observer->getEvent()->getOrder();
    //     $customerRepo = $this->customerRepositoryInterface->getById($order->getCustomerId());
    //     $existingCourses = $customerRepo->getCustomAttribute('customer_courses');
        
    //     if ($existingCourses) {
    //         $productOptionsArray = json_decode($existingCourses->getValue(), true);
    //     } else {
    //         $productOptionsArray = [];
    //     }
    
    //     foreach ($order->getAllItems() as $item) {
    //         $productType = $item->getProductType();
    //         $productOptions = $item->getProductOptions();
    
    //         if ($productType == 'virtual' && isset($productOptions['info_buyRequest']['super_attribute'])) {
    //             continue;
    //         }
    
    //         if ($productType == 'configurable') {
    //             if (isset($productOptions['attributes_info'])) {
    //                 $backendName = $item->getProduct()->getData('backend_name');
    //                 if ($backendName && strpos($backendName, '||') !== false) {
                        
    //                     list($courseName, $extraAttribute) = explode('||', $backendName);
    //                     $courseName = trim($courseName);
    //                     $extraAttribute = trim($extraAttribute);
                
    //                     if (!isset($productOptionsArray['child-'.$courseName])) {
    //                         $productOptionsArray['child-'.$courseName] = [];
    //                     }
    //                     if (!isset($productOptionsArray['child-'.$courseName][$extraAttribute])) {
    //                         $productOptionsArray['child-'.$courseName][$extraAttribute] = [];
    //                     }
                
    //                     $selectedAttributes = [];
    //                     foreach ($productOptions['attributes_info'] as $attribute) {
    //                         if (strtolower($attribute['value']) === 'yes') {
    //                             $selectedAttributes[] = $attribute['label'];
    //                         }
    //                     }
                
    //                     $productOptionsArray['child-'.$courseName][$extraAttribute] = array_unique(array_merge(
    //                         $productOptionsArray['child-'.$courseName][$extraAttribute], 
    //                         $selectedAttributes
    //                     ));
    //                 }
    //                 else {
    //                     $productName = $item->getProduct()->getName();
    //                     if (!isset($productOptionsArray[$productName])) {
    //                         $productOptionsArray[$productName] = [];
    //                     }
    //                     $selectedAttributes = [];
    //                     foreach ($productOptions['attributes_info'] as $attribute) {
    //                         if (strtolower($attribute['value']) === 'yes') {
    //                             $selectedAttributes[] = $attribute['label'];
    //                         }
    //                     }
    //                     $productOptionsArray[$productName] = array_unique(array_merge(
    //                         $productOptionsArray[$productName], 
    //                         $selectedAttributes
    //                     ));
    //                 }
    //             }
    //         } else {
    //             $backendName = $item->getProduct()->getData('backend_name');
    //             if ($backendName && strpos($backendName, '||') !== false) {
    //                 list($courseName, $extraAttribute) = explode('||', $backendName);
    //                 $courseName = trim($courseName);
    //                 $extraAttribute = trim($extraAttribute);
            
    //                 if (!isset($productOptionsArray[$courseName])) {
    //                     $productOptionsArray[$courseName] = [];
    //                 }
    //                 if (!in_array($extraAttribute, $productOptionsArray[$courseName])) {
    //                     $productOptionsArray[$courseName][] = $extraAttribute;
    //                 }
    //             }
    //         }
    //     }
    
    //     $jsonData = json_encode($productOptionsArray);
    //     $customerRepo->setCustomAttribute('customer_courses', $jsonData);
    //     $this->customerRepositoryInterface->save($customerRepo);
    //     return $this;
    // }


    public function execute(\Magento\Framework\Event\Observer $observer) {
    
        //may be we need to add the content outline here save as as seperate entity with each product for react case
        $order = $observer->getEvent()->getOrder();
    
        $customer = $this->customerRepositoryInterface->getById($order->getCustomerId());
        $existingCourses = $customer->getCustomAttribute('customer_courses');
        $courseData = $existingCourses ? json_decode($existingCourses->getValue(), true) : [];

        foreach ($order->getAllItems() as $item) {
            $productType = $item->getProductType();
            $productOptions = $item->getProductOptions();
            if ($productType == 'virtual' && isset($productOptions['info_buyRequest']['super_attribute'])) {
                continue;
            }
            $courseName = $item->getProduct()->getData('backend_name') ?: $item->getProduct()->getName();
            $courseName = $item->getId().'#'.$courseName;

            if (!isset($courseData[$courseName])) {
                $courseData[$courseName] = [];
            }

            if ($productType == 'configurable') {

                if (isset($productOptions['attributes_info'])) {
                    foreach ($productOptions['attributes_info'] as $attribute) {
                        if (strtolower($attribute['value']) === 'yes' || strtolower($attribute['value']) === 'Yes') {
                            $courseData[$courseName][] = $attribute['label'];
                        }
                    }
                }
            }
            else{
                $backendName = $item->getProduct()->getData('backend_name');
                $backendName = $item->getId().'#'.$backendName;
                if ($backendName && strpos($backendName, '||') !== false) {
                    list($courseName, $extraAttribute) = explode('||', $backendName);
                    $courseName = trim($courseName);
                    $extraAttribute = trim($extraAttribute);
                
                    if (!isset($courseData[$backendName])) {
                        $courseData[$backendName] = [];
                    }
                    if (!in_array($extraAttribute, $courseData[$backendName])) {
                        $courseData[$backendName][] = $extraAttribute;
                    }
                }

            }
        }

        $customer->setCustomAttribute('customer_courses', json_encode($courseData));
        $this->customerRepositoryInterface->save($customer);

        return $this;
    }
} 