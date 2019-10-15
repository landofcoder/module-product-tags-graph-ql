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
     * @param DataProvider\Tag $tagRepository
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
        $tagId = $this->getTagId($args);
        $tagData = $this->getTagData($tagId);

        return $tagData;
    }

    /**
     * @param array $args
     * @return int
     * @throws GraphQlInputException
     */
    private function getTagId(array $args): int
    {
        if (!isset($args['id'])) {
            throw new GraphQlInputException(__('"Tag id should be specified'));
        }

        return (int)$args['id'];
    }

    /**
     * @param int $tagId
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getTagData(int $tagId): array
    {
        try {
            $tagData = $this->tagDataProvider->getData($tagId);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return $tagData;
    }
}