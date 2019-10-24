<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\ProductTagsGraphQl\Model\Resolver;

use Lof\ProductTagsGraphQl\Model\Resolver\DataProvider\Product as ProductDataProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Product implements ResolverInterface
{

    /**
     * @var ProductDataProvider
     */
    private $productDataProvider;

    /**
     * @param DataProvider\Product $productDataProvider
     */
    public function __construct(
        DataProvider\Product $productDataProvider
    ) {
        $this->productDataProvider = $productDataProvider;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $tagCodes = $this->getProductIdentifiers($args);
        $productData = $this->getProductData($tagCodes);

        return $productData;
    }

    /**
     * Get Product identifiers
     *
     * @param array $args
     * @return string[]
     * @throws GraphQlInputException
     */
    private function getProductIdentifiers(array $args): array
    {
        if (!isset($args['identifiers']) || !is_array($args['identifiers']) || count($args['identifiers']) === 0) {
            throw new GraphQlInputException(__('"identifiers" of Product Tag should be specified'));
        }

        return $args['identifiers'];
    }

    /**
     * Get Product data
     *
     * @param array $tagCodes
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getProductData(array $tagCodes): array
    {
        $productsData = [];
        foreach ($tagCodes as $tagCode) {
            try {
                $productsData[$tagCode] = $this->productDataProvider->getData($tagCode);
            } catch (NoSuchEntityException $e) {
                $productsData[$tagCode] = new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
            }
        }
        return $productsData;
    }
}