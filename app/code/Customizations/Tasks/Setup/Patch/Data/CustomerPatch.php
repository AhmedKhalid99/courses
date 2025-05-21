<?php

namespace Customizations\Tasks\Setup\Patch\Data;

use Magento\Customer\Model\ResourceModel\Attribute;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class CustomerPatch implements DataPatchInterface
{
    /**
     * @var EavSetup
     */
    private $eavSetup;

    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * Constructor.
     *
     * @param ModuleDataSetupInterface $setup
     * @param EavSetup $eavSetup
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        EavSetup $eavSetup
    ) {
        $this->setup = $setup;
        $this->eavSetup = $eavSetup;
    }

    /**
     * Apply the patch.
     *
     * @return void
     */
    public function apply()
    {
        // Add the custom customer attribute
        $this->eavSetup->addAttribute(
            \Magento\Customer\Model\Customer::ENTITY, // Entity type
            'university', // Attribute code
            [
                'type' => 'varchar',
                'label' => 'University',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'default' => '',
                'system' => false,
                'sort_order' => 100,
                'visible_on_front' => true,
                'unique' => false,
            ]
        );

        // Assign attribute to customer forms
        $attribute = $this->setup->getConnection()
            ->select()
            ->from($this->setup->getTable('eav_attribute'))
            ->where('attribute_code = ?', 'university')
            ->where('entity_type_id = ?', 1)
            ->query()
            ->fetch();

        if ($attribute) {
            $this->setup->getConnection()->insertOnDuplicate(
                $this->setup->getTable('customer_form_attribute'),
                [
                    ['form_code' => 'adminhtml_customer', 'attribute_id' => $attribute['attribute_id']],
                    ['form_code' => 'customer_account_create', 'attribute_id' => $attribute['attribute_id']],
                    ['form_code' => 'customer_account_edit', 'attribute_id' => $attribute['attribute_id']],
                ]
            );
        }
    }

    /**
     * GetDependencies.
     *
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * GetAliases.
     *
     * @return array
     */
    public function getAliases()
    {
        return [];
    }
}
