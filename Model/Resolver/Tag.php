<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\ProductTagsGraphQl\Model\Resolver;

use Lof\ProductTagsGraphQl\Model\Resolver\DataProvider\Tag as TagDataProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Tag implements ResolverInterface
{
    /**
     * @var TagDataProvider
     */
    private $tagDataProvider;

    /**
     * @param DataProvider\Tag $tagDataProvider
     */
    public function __construct(DataProvider\Tag $tagDataProvider)
    {
        $this->tagDataProvider = $tagDataProvider;
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
        $tagsId = $this->getTagId($args);
        $tagsData = $this->getTagData($tagsId);
        $resultData = [
            'items' => $tagsData,
        ];
        return $resultData;
    }

    /**
     * @param array $args
     * @return string[]
     * @throws GraphQlInputException
     */
    private function getTagId(array $args): array
    {
        if (!isset($args['id'])) {
            throw new GraphQlInputException(__('"Tag id should be specified'));
        }

        return (array)$args['id'];
    }

    /**
     * @param array $tagsId
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getTagData(array $tagsId): array
    {
        $tagsData = [];
        foreach ($tagsId as $tagId) {
            try {
                $tagsData[$tagId] = $this->tagDataProvider->getData($tagId);
            } catch (NoSuchEntityException $e) {
                $tagsData[$tagId] = new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
            }
        }
        return $tagsData;
    }
}