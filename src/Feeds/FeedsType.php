<?php

namespace Progressus\Zoovu\Feeds;

use Progressus\Zoovu\Helpers\PostType;

class FeedsType extends PostType
{
    /**
     * The post type
     *
     * @var string
     */
    protected $post_type = 'zoovu_feeds';

    /**
     * The default attributes
     *
     * @var array
     */
    protected $attributes = [
        'has_archive'           => false,
        'hierarchical'          => false,
        'public'                => false,
        'show_ui'               => false,
        'show_in_menu'          => false,
        'publicly_queryable'    => false,
        'rewrite'               => false,
    ];
}
