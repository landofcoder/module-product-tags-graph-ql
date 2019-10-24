<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\ProductTagsGraphQl\Model\Resolver\DataProvider;

use Lof\ProductTags\Api\Data\TagProductLinkInterface;
use Lof\ProductTags\Model\Data\TagProductLink;
use Lof\ProductTags\Api\ProductsManagementInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Lof Product Tags data provider
 */

class Product
{
    /**
     * @var ProductsManagementInterface
     */
    private $productsManagement;

    /**
     * @var \Lof\ProductTags\Api\Data\TagProductLinkInterfaceFactory
     */
    protected $productLinkFactory;

    /**
     * @param \Lof\ProductTags\Api\ProductsManagementInterface $productsManagement
     * @param \Lof\ProductTags\Model\TagFactory $tagModelFactory
     * @param \Lof\ProductTags\Api\Data\TagProductLinkInterfaceFactory $productLinkFactory
     */
    public function __construct(
        \Lof\ProductTags\Api\ProductsManagementInterface $productsManagement,
        \Lof\ProductTags\Model\TagFactory $tagModelFactory,
        \Lof\ProductTags\Api\Data\TagProductLinkInterfaceFactory $productLinkFactory
    ) {
        $this->productsManagement = $productsManagement;
        $this->_tagModelFactory = $tagModelFactory;
        $this->productLinkFactory = $productLinkFactory;
    }

    /**
     * Get product data
     *
     * @param string $tagCode
     * @return array
     * @throws NoSuchEntityException
     */
    public function getData(string $tagCode): array
    {
        $tagModel = $this->_tagModelFactory->create();
        $tagModel->loadByIdentifier($tagCode);
        if (!$tagModel->getId()) {
            return [];
        }
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $products */
        $products = $tagModel->getProductCollection();
        /** @var \Lof\ProductTags\Api\Data\TagProductLinkInterface[] $productsData */
        $productsData = [];
        if($products){
            /** @var \Magento\Catalog\Model\Product $product */
            foreach ($products->getItems() as $product) {
                /** @var \Lof\ProductTags\Api\Data\TagProductLinkInterface $productData */
                $productData = $this->productLinkFactory->create();
                $productData->setSku($product->getSku())
                    ->setPosition($product->getData('tag_index_position'))
                    ->setTagId($tagModel->getId());
                $productData = [
                    TagProductLinkInterface::KEY_SKU => $product->getSku(),
                    TagProductLinkInterface::KEY_POSITION => $product->getPosition(),
                    TagProductLinkInterface::KEY_TAG_ID => $product->getTagId(),   
                ];
                // $productData->setSku($product->getSku())
                //     ->setPosition($product->getData('tag_index_position'))
                //     ->setTagId($tagModel->getId());
                $productsData[] = $productData;
                return $productsData;
            }
        }
        // $product = $this->productsManagement->getProducts($tagCode);

        // $productData = [
        //     TagProductLinkInterface::KEY_SKU => $product->getSku(),
        //     TagProductLinkInterface::KEY_POSITION => $product->getPosition(),
        //     TagProductLinkInterface::KEY_TAG_ID => $product->getTagId(),
        // ];
        // return $productData;
    }
}