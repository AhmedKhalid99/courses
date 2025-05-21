<?php

namespace Customizations\Tasks\Controller\Outline;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Index extends Action
{
    protected $resultJsonFactory;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $outline = $this->getRequest()->getParam('outline');

        if (!$outline) {
            return $resultJson->setData(['error' => true, 'message' => 'No outline found']);
        }

        try {
             $html = '<div class="course-outline-popup">';
             switch ($outline) {
                 case 'Core Software Development':
                     $html .= $this->getCoreSoftwareDevelopmentHtml();
                     break;
                 case 'OOP':
                     $html .= $this->getOopHtml();
                     break;
                 case 'Data Structures':
                     $html .= $this->getDataStructuresHtml();
                     break;
                 case 'Databases':
                     $html .= $this->getDatabasesHtml();
                     break;
                 case 'Industry Bootcamp':
                     $html .= $this->getIndustryBootcampHtml();
                     break;
                 case 'Crack Interview':
                     $html .= $this->getCrackInterviewHtml();
                     break;
                 case 'Problem Solving':
                     $html .= $this->getProblemSolvingHtml();
                     break;
                 default:
                     $html .= '<p>Course outline not available.</p>';
             }
             $html .= '</div>';
 
            return $resultJson->setData(['error' => false, 'html' => $html]);

        } catch (\Exception $e) {
            return $resultJson->setData(['error' => true, 'message' => $e->getMessage()]);
        }
    }
    private function getCoreSoftwareDevelopmentHtml()
    {
        return <<<HTML
        <h2>Core Software Development - 25.5H</h2>
        <p><strong>PF</strong></p>
        <ul>
            <li>Technical: linear flow and dry run, packages library</li>
            <li>Variables: type, usage(input and output), memory allocation and scope</li>
            <li>Conditions: if-else, switch (1.5h)</li>
            <li>Loops: while, for loop (1h)</li>
            <li>Functions: void, recursive, time complexity (2h)</li>
            <li>Pointers: copy by reference and value (2h)</li>
            <li>Exercise: Play with problems and sorting</li>
        </ul>
        <p><strong>Total: 6.5h</strong></p>
        HTML;
    }

    private function getOopHtml()
    {
        return <<<HTML
        <h2>OOP - 5.5H</h2>
        <ul>
            <li>Overview of variable types</li>
            <li>Class vs Object (properties and methods)</li>
            <li>Constructor & Destructor (1.5h)</li>
            <li>Pillars of OOP: Abstraction, Encapsulation, Inheritance, Polymorphism</li>
            <li>Encapsulation: Public, Private, Protected (1h)</li>
            <li>Static methods memory</li>
            <li>Chess Project (2.5h)</li>
        </ul>
        <p><strong>Total: 5.5h</strong></p>
        HTML;
    }

    private function getDataStructuresHtml()
    {
        return <<<HTML
        <h2>Data Structures - 5.5H</h2>
        <ul>
            <li>Overview and importance (0.5h)</li>
            <li>Lists: linked, double-linked, etc. (1h)</li>
            <li>Queue (FIFO), Stack (LIFO) (1h)</li>
            <li>Trees and Graphs (2h)</li>
            <li>Hashmap (1h)</li>
        </ul>
        <p><strong>Total: 5.5h</strong></p>
        HTML;
    }

    private function getDatabasesHtml()
    {
        return <<<HTML
        <h2>Databases - 7H</h2>
        <ul>
            <li>Overview: file storage to tables, SQL (0.5h)</li>
            <li>CRUD operations (0.5h)</li>
            <li>Joins and Group By (1h)</li>
            <li>Stored Procedures, Transactions (1h)</li>
            <li>Indexes and Relationships (1h)</li>
            <li>Database Normalization (2h)</li>
            <li>Project Demonstrations and Exercises (1h)</li>
        </ul>
        <p><strong>Total: 7h</strong></p>
        HTML;
    }

    private function getIndustryBootcampHtml()
    {
        return <<<HTML
        <h2>Industry Bootcamp: Advanced Software Engineering</h2>
        <ul>
            <li>API Types, MVC, MVVM</li>
            <li>Design Patterns</li>
            <li>Node.js project with unit testing</li>
            <li>Laravel Backend with API case studies</li>
            <li>JWT, Cache, Event Observers</li>
            <li>AI Agent Integration</li>
        </ul>
        HTML;
    }

    private function getCrackInterviewHtml()
    {
        return <<<HTML
        <h2>Crack Interview and Mastering Programming - 12.5H</h2>
        <ul>
            <li>OOP Concepts (1h)</li>
            <li>Design Patterns, SOLID Principles (2h)</li>
            <li>Complex DB Queries (2h)</li>
            <li>Database Design & ACID (2h)</li>
            <li>Data Structures Questions (2h)</li>
        </ul>
        <p><strong>Total: 6h</strong></p>
        HTML;
    }

    private function getProblemSolvingHtml()
    {
        return <<<HTML
        <h2>Problem Solving - 3.5H</h2>
        <ul>
            <li>Array and String Manipulation</li>
            <li>Time Complexity Optimization (1h)</li>
            <li>Recursive Function Logic (1h)</li>
            <li>Practice Exercises (1.5h)</li>
        </ul>
        <p><strong>Total: 3.5h</strong></p>
        HTML;
    }
}
