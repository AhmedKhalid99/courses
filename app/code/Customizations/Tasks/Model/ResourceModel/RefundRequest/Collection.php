<?php
namespace Customizations\Tasks\Model\ResourceModel\RefundRequest;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Customizations\Tasks\Model\RefundRequest;
use Customizations\Tasks\Model\ResourceModel\RefundRequest as RefundRequestResource;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init(RefundRequest::class, RefundRequestResource::class);
    }
}
