<?php

namespace Progressus\Zoovu;

class PageSettings
{
    private $post_types = ['page'];

    public static function register()
    {
        $instance = new self();

        add_action('add_meta_boxes', [$instance, 'metaBox']);
        add_action('save_post', [$instance, 'saveFields']);
        add_action('wp_body_open', [$instance, 'outputEmbedCode']);
    }

    public function metaBox()
    {
        add_meta_box(
            'zoovu-page-settings',
            __('Zoovu Page Settings', \ZoovuFeeds::LOCALE_SLUG),
            [$this, 'outputFields'],
            $this->getPostTypes(),
            'side'
        );
    }

    /**
     * Output post fields
     */
    public function outputFields()
    {
        printf(
            '<div class="row">
                <div class="label">%s</div>
                <div class="fields">
                    <textarea rows="5" name="%s">%s</textarea>
                </div>
            </div>',
            __('Embed Code', \ZoovuFeeds::LOCALE_SLUG),
            '_zoovu_embed_code',
            $this->getCurrentPostEmbedCode()
        );
    }

    /**
     * Save embed code field in post
     */
    public function saveFields()
    {
        global $post;

        if (isset($_POST["_zoovu_embed_code"])) {
            update_post_meta($post->ID, '_zoovu_embed_code', $_POST["_zoovu_embed_code"]);
        }
    }

    /**
     * Output embed code
     */
    public function outputEmbedCode()
    {
        echo $this->getCurrentPostEmbedCode();
    }

    /**
     * Current post embed code
     *
     * @return mixed
     */
    public function getCurrentPostEmbedCode()
    {
        global $post;

        return get_post_meta($post->ID, '_zoovu_embed_code', true);
    }

    /**
     * Get possible post types for meta box
     *
     * @return array
     */
    private function getPostTypes()
    {
        return apply_filters('zoovuforwoo_embed_script_in_post_types', $this->post_types);
    }
}
