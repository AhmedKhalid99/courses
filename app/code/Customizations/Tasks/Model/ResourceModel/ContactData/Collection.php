<?php
namespace Customizations\Tasks\Model\ResourceModel\ContactData;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Customizations\Tasks\Model\ContactData;
use Customizations\Tasks\Model\ResourceModel\ContactData as ContactDataResource;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'Contact_data_id';

    protected function _construct()
    {
        $this->_init(ContactData::class, ContactDataResource::class);
    }
}
