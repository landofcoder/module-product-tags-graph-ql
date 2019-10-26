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
        $tagsIdentifier = $this->getTagIdentifier($args);
        $tagsData = $this->getTagData($tagsIdentifier);
        $resultData = [
            'items' => $tagsData,
        ];
        return $resultData;
    }

    /**
     * @param string $args
     * @return string[]
     * @throws GraphQlInputException
     */
    private function getTagIdentifier(string $args): array
    {
        if (!isset($args['identifiers']) || !is_array($args['identifiers']) || count($args['identifiers']) === 0) {
            throw new GraphQlInputException(__('"identifiers" of Tag should be specified'));
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
		$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection = $resource->getConnection();
		$tableName = $resource->getTableName('lof_producttags_tag');
        $select = $connection->select()->from(
            $tableName,
            'identifier'
            )
        ->where(
            'identifier'
            )
        ->like($args['identifiers']);
        return $select;
    }

    /**
     * @param array $tagsIdentifier
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getTagData(array $tagsIdentifier): array
    {
        $tagsData = [];

        foreach ($tagsIdentifier as $tagIdentifier) {
            try {
                $tagsData[$tagIdentifier] = $this->tagDataProvider->getData($tagIdentifier);
            } catch (NoSuchEntityException $e) {
                $tagsData[$tagIdentifier] = new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
            }
        }
        return $tagsData;
    }
}