<?php
namespace Customizations\Tasks\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ContactData extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('contact-us', 'Contact_data_id');
    }
}
