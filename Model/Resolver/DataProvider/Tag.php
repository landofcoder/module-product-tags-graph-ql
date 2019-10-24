<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Lof\ProductTagsGraphQl\Model\Resolver\DataProvider;

use Lof\ProductTags\Api\Data\TagInterface;
use Lof\ProductTags\Api\TagRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Lof Product Tags data provider
 */

class Tag
{
    /**
     * @var TagRepositoryInterface
     */
    private $tagRepository;

    /**
     * @param \Lof\ProductTags\Api\TagRepositoryInterface $tagRepository
     */
    public function __construct(
        \Lof\ProductTags\Api\TagRepositoryInterface $tagRepository
    ) {
        $this->tagRepository = $tagRepository;
    }

    /**
     * @param string $tagsId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getData(string $tagsId): array
    {
        $tag = $this->tagRepository->getById($tagsId);

        if (false === $tag->getStatus()) {
            throw new NoSuchEntityException();
        }

        $tagData = [
            TagInterface::TAG_ID => $tag->getTagId(),
            TagInterface::TAG_STATUS => $tag->getStatus(),
            TagInterface::TAG_TITLE => $tag->getTagTitle(),
            TagInterface::TAG_IDENTIFIER => $tag->getIdentifier(),
            TagInterface::TAG_DESCRIPTION => $tag->getTagDescription(),
        ];
        return $tagData;
    }
}