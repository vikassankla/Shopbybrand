<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;

/**
 * @category Magestore
 * @package  Magestore_Shopbybrand
 * @module   Shopbybrand
 * @author   Magestore Developer
 */
class Image extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $_mediaDirectory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magestore\Shopbybrand\Model\ImageUploaderFactory
     */
    protected $_imageUploaderFactory;

    /**
     * Block constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem $filesystem,
        \Magestore\Shopbybrand\Model\ImageUploaderFactory $imageUploaderFactory
    ) {
        parent::__construct($context);

        $this->_mediaDirectory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->_imageUploaderFactory = $imageUploaderFactory;
    }

    /**
     * get media url of image.
     *
     * @param string $imagePath
     *
     * @return string
     */
    public function getMediaUrlImage($imagePath = '')
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $imagePath;
    }

    /**
     * @param \Magento\Framework\DataObject $model
     * @param                               $fileId
     * @param                               $relativePath
     * @param bool $makeResize
     *
     * @throws LocalizedException
     */
    public function mediaUploadImage(
        \Magento\Framework\DataObject $model,
        $fileId,
        $relativePath,
        $width = null,
        $height = null
    ) {
        $data = $this->_getRequest()->getFiles();
        if (!empty($data[$fileId]['name'])) {
            try {
                /** @var \Magento\MediaStorage\Model\File\Uploader $uploader */
                $uploader = $this->_imageUploaderFactory->create(['fileId' => $fileId]);
                $mediaAbsolutePath = $this->_mediaDirectory->getAbsolutePath($relativePath);
                $uploader->save($mediaAbsolutePath);

                /*
                 * resize to small image
                 */
                if ($width) {
                    $this->resizeImage(
                        $mediaAbsolutePath . $uploader->getUploadedFileName(),
                        $width,
                        $height
                    );
                    $imagePath = $this->_getResizeImageFileName($relativePath . $uploader->getUploadedFileName());
                } else {
                    $imagePath = $relativePath . $uploader->getUploadedFileName();
                }

                $model->setData($fileId, $imagePath);
            } catch (\Exception $e) {
                throw new LocalizedException(
                    __($e->getMessage())
                );
            }
        } else {
            if ($model->getData($fileId) && empty($model->getData($fileId . '/delete'))) {
                $model->setData($fileId, $model->getData($fileId . '/value'));
            } else {
                $model->setData($fileId, '');
            }
        }
    }

    /**
     * resize image.
     *
     * @param      $fileName
     * @param      $width
     * @param null $height
     */
    public function resizeImage($fileName, $width, $height = null)
    {
        /** @var \Magento\Framework\Image $image */
        $image = $this->_objectManager->create('Magento\Framework\Image', ['fileName' => $fileName]);
        $image->constrainOnly(true);
        $image->keepAspectRatio(true);
        $image->keepFrame(false);
        $image->resize($width, $height);
        $image->save($this->_getResizeImageFileName($fileName));
    }

    /**
     * @param $fileName
     *
     * @return string
     */
    protected function _getResizeImageFileName($fileName)
    {
        return dirname($fileName) . '/resize/' . basename($fileName);
    }
}
