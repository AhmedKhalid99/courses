<?php
namespace Customizations\Tasks\Model;

use Magento\Framework\Model\AbstractModel;
use Customizations\Tasks\Model\ResourceModel\RefundRequest as RefundRequestResource;

class RefundRequest extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(RefundRequestResource::class);
    }
}
