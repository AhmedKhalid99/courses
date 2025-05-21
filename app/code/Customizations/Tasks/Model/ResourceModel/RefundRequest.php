<?php
namespace Customizations\Tasks\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class RefundRequest extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('refund_requests', 'id');
    }
}