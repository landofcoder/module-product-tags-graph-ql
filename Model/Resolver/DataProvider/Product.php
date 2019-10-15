<?php


namespace Lof\ProductTagsGraphQl\Model\Resolver\DataProvider;

class Product
{

    private $tag;

    /**
     * @param \Lof\ProductTags\Api\Data\TagInterface $tag
     */
    public function __construct(
        \Lof\ProductTags\Api\Data\TagInterface $tag
    ) {
        $this->tag = $tag;
    }

    public function getProduct()
    {
        return 'proviced data';
    }
}