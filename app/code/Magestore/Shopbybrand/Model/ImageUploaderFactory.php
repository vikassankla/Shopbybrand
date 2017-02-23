<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Model;

/**
 * class ImageUploaderFactory
 *
 * @category Magestore
 * @package  Magestore_Shopbybrand
 * @module   Pdfinvoiceplus
 * @author   Magestore Developer
 */
class ImageUploaderFactory
{
    /**
     * Object Manager instance.
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Instance name to create.
     *
     * @var string
     */
    protected $_instanceName = null;

    /**
     * Factory constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = 'Magento\MediaStorage\Model\File\Uploader'
    ) {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters.
     *
     * @param array $data
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create(array $data = [])
    {
        $uploader = $this->_objectManager->create($this->_instanceName, $data);

        if (!$uploader instanceof \Magento\MediaStorage\Model\File\Uploader) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The class uploader is invalid !')
            );
        }

        $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);

        /** @var \Magento\Framework\Image\Adapter\AdapterInterface $imageAdapter */
        $imageAdapter = $this->_objectManager->get('Magento\Framework\Image\AdapterFactory')->create();
        $uploader->addValidateCallback('shopbybrand_upload_image', $imageAdapter, 'validateUploadFile');
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(true);

        return $uploader;
    }
}
