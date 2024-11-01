<?php

namespace Progressus\Zoovu\Helpers;

class PostType {
    /**
     * The post type name
     *
     * @var string
     */
    protected $post_type = '';

    /**
     * The post type attributes
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * The taxonomies to be registered
     *
     * @var array
     */
    protected $taxonomies = [];

    /**
     * Registers a post type
     *
     * @return \WP_Post_Type
     * @throws \Exception
     */
    public function register() : \WP_Post_Type
    {
        if (empty((string) $this->post_type)) {
            throw new \Exception('Either remove the register method or add the post type name!');
        }

        if (!empty((array) $this->taxonomies)) {
            \collect($this->taxonomies)->each(function(string $taxonomy) {
                $tax = new $taxonomy;
                register_taxonomy( (string) $tax->name, (string) $this->post_type, (array) $tax->attributes );
            });
        }

        return register_post_type((string) $this->post_type, (array) $this->attributes);
    }
}
