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
     * @param array $args
     * @return string[]
     * @throws GraphQlInputException
     */
    private function getTagIdentifier(array $args): array
    {
        if (!isset($args['identifiers'])||!isset($args['tag_id'])||!isset($args['tag_title'])||!isset($args['status'])) {
            throw new GraphQlInputException(__('"identifiers", "tag_id", "tag_title" or "status" of Tag should be specified'));
        }
        //return (array)$args['identifiers'];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
		$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection = $resource->getConnection();
		$tableName = $resource->getTableName('lof_producttags_tag');
        $sql = "Select lof_producttags_tag.tag_id FROM lof_producttags_tag INNER JOIN lof_producttags_store ON lof_producttags_tag.tag_id = lof_producttags_store.tag_id WHERE lof_producttags_tag.identifier LIKE '%" . $args['identifiers'] . "%' AND lof_producttags_tag.tag_id LIKE '%" . $args['tag_id'] . "%' AND lof_producttags_tag.status = true AND lof_producttags_tag.tag_title LIKE '%" . $args['tag_title'] . "%';";
        $result = $connection->fetchCol($sql);
        return $result;
    }

    /**
     * @param array $tagsIdentifier
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getTagData(array $tagsIdentifier): array
    {
        $tagsData = []; 
        //$tagsIdentifier = ['test-2', 'test-3', 'test-4', 'test-5'];
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