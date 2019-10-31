<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\ProductTagsGraphQl\Model\Resolver;

use Lof\ProductTags\Api\Data\TagInterface;
use Lof\ProductTags\Api\TagRepositoryInterface;
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
     * @var TagRepositoryInterface
     */
    private $tagRepository;

    /**
     * @var TagDataProvider
     */
    private $tagDataProvider;

    /**
     * @param DataProvider\Tag $tagDataProvider
     */
    public function __construct(DataProvider\Tag $tagDataProvider,
    \Lof\ProductTags\Api\TagRepositoryInterface $tagRepository)
    {
        $this->tagDataProvider = $tagDataProvider;
        $this->tagRepository = $tagRepository;
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
        $tags = $this->getTags($args);
        $tagsData = $this->getTagsData($tags);
        $resultData = [
            'items' => $tagsData,
        ];
        return $resultData;
    }

    /**
     * @param array $args
     * @return Lof\ProductTags\Model\ResourceModel\Tag\Collection $tagList
     * @throws GraphQlInputException
     */
    private function getTags(array $args)
    {
        if (isset($args['identifiers'])||isset($args['tag_id'])||isset($args['tag_title'])||isset($args['status'])) {
            //throw new GraphQlInputException(__('"identifiers" of Tag should be specified'));
            $taglist = $this->tagRepository->getListTag($args);
        }
        else{
            throw new GraphQlInputException(__('"identifiers", "tag_id", "tag_title" or "status" of Tag should be specified'));
        }
        //$taglist = $this->tagRepository->getListTag($args);
        
        return $taglist;
    }

    /**
     * @param Lof\ProductTags\Model\ResourceModel\Tag\Collection $tagList
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getTagsData($tagList): array
    {
        $tagsData = [];
        if($tagList->getSize()){
            foreach ($tagList as $tagItem) {
                $tagId = $tagItem->getId();
                try {
                    $tagsData[$tagId] = $this->tagDataProvider->getData($tagItem);
                } catch (NoSuchEntityException $e) {
                    $tagsData[$tagId] = new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
                }
            }
        }
        return $tagsData;
    }
}