<?php
/**
 * @author Barwenock
 * @copyright Copyright (c) Barwenock
 * @package Declarative Attributes Import for Magento 2
 */

declare(strict_types=1);

namespace Barwenock\DeclarativeAttributes\Controller\Adminhtml\Index;

class Index extends \Magento\Backend\App\Action
{
    /**
     * Execute controller action and returns page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->prepend(__('Declarative Attributes Import'));

        return $resultPage;
    }
}
