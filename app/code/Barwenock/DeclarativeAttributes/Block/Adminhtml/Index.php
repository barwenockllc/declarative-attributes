<?php
/**
 * @author Barwenock
 * @copyright Copyright (c) Barwenock
 * @package Declarative Attributes Import for Magento 2
 */

declare(strict_types=1);

namespace Barwenock\DeclarativeAttributes\Block\Adminhtml;

class Index extends \Magento\Backend\Block\Template
{
    /**
     * Get controller url for form
     *
     * @return string
     */
    public function getFormAction()
    {
        return $this->getUrl('declarative_attributes/import/import');
    }
}
