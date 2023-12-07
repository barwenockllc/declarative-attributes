<?php
/**
 * @author Barwenock
 * @copyright Copyright (c) Barwenock
 * @package Declarative Attributes Import for Magento 2
 */

declare(strict_types=1);

namespace Barwenock\DeclarativeAttributes\Controller\Adminhtml\Import;

class Import extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected \Magento\Framework\Filesystem $fileSystem;

    /**
     * @var \Barwenock\DeclarativeAttributes\Model\Attributes\AttributesManagement
     */
    protected \Barwenock\DeclarativeAttributes\Model\Attributes\AttributesManagement $attributesManagement;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Barwenock\DeclarativeAttributes\Model\Attributes\AttributesManagement $attributesManagement
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Filesystem $filesystem,
        \Barwenock\DeclarativeAttributes\Model\Attributes\AttributesManagement $attributesManagement
    ) {
        parent::__construct($context);
        $this->fileSystem = $filesystem;
        $this->attributesManagement = $attributesManagement;
    }

    /**
     * Import execution
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $uploadedFile = $this->getRequest()->getFiles('file_upload');

        if ($uploadedFile && isset($uploadedFile['name']) && $uploadedFile['name']) {
            try {
                $targetDir = $this->fileSystem->getDirectoryWrite(
                    \Magento\Framework\App\Filesystem\DirectoryList::PUB
                )->getAbsolutePath('import');

                $uploader = new \Magento\Framework\File\Uploader($uploadedFile);
                $uploader->setAllowedExtensions(['csv']);
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);

                $uploader->save($targetDir, $uploadedFile['name']);

                $this->attributesManagement->attributesProcess();
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage('Error uploading file: ' . $exception->getMessage());
            }
        } else {
            $this->messageManager->addErrorMessage('Please select a file to upload.');
        }

        $this->messageManager->addSuccessMessage('Import was successfully.');
        return $resultRedirect->setPath('declarative_attributes/index/index');
    }
}
