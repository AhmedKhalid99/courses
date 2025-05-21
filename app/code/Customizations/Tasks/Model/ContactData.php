<?php
namespace Customizations\Tasks\Model;

use Magento\Framework\Model\AbstractModel;
use Customizations\Tasks\Model\ResourceModel\ContactData as ContactDataResource;

class ContactData extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(ContactDataResource::class);
    }
}
