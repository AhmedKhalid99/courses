<?php

namespace Customizations\Tasks\Setup\Patch\Data;


use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class Test implements DataPatchInterface
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
     * AddCustomProductAttribute constructor.
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
        // Add the custom product attribute
        $this->eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY, // Entity type
            'custom_attribute1', // Attribute code
            [
                'type' => 'text',
                'label' => 'Custom Attribute1',
                'input' => 'textarea',
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'default' => '',
                'global' => Attribute::SCOPE_STORE,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'wysiwyg_enabled' => true,
            ]
        );
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
