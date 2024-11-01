<?php

namespace Progressus\Zoovu\Feeds;

use Progressus\Zoovu\Helpers\Forms\FormGenerator;
use Progressus\Zoovu\Helpers\Notice;
use Progressus\Zoovu\Helpers\Page;

class FeedsCreatePage implements Page
{
    private $form;

    public function __construct()
    {
        $feed = false;

        // Generate form
        $this->form = (new FormGenerator())
            ->addCheckbox(
                'enabled',
                __('Create activated', \ZoovuFeeds::LOCALE_SLUG)
            )
            ->addText(
                'name',
                __('Feed name', \ZoovuFeeds::LOCALE_SLUG),
                null,
                null,
                'required'
            )
            ->addSelect(
                'schedule_type',
                __('Frequency', \ZoovuFeeds::LOCALE_SLUG),
                [
                    Feed::TYPE_HOURLY => __('Hourly', \ZoovuFeeds::LOCALE_SLUG),
                    Feed::TYPE_DAILY => __('Daily', \ZoovuFeeds::LOCALE_SLUG)
                ]
            )
            ->addTimePicker(
                'daily_time',
                __('Select time', \ZoovuFeeds::LOCALE_SLUG),
                null,
                null,
                'required_if:schedule_type,' . Feed::TYPE_DAILY,
                [
                    'schedule_type' => [
                        'condition' => '===',
                        'value' => Feed::TYPE_DAILY
                    ]
                ]
            )
            ->addAutocomplete(
                'individual_products',
                __('Individual products', \ZoovuFeeds::LOCALE_SLUG),
                [],
                admin_url('admin-ajax.php?action=pzfFindProducts'),
                true,
                null,
                null
            )
            ->addAutocomplete(
                'individual_categories',
                __('Individual categories', \ZoovuFeeds::LOCALE_SLUG),
                [],
                admin_url('admin-ajax.php?action=pzfFindCategories'),
                true
            )
            ->addAutocomplete(
                'exclude_category_products',
                __('Exclude products', \ZoovuFeeds::LOCALE_SLUG),
                [],
                admin_url('admin-ajax.php?action=pzfFindProducts'),
                true
            );

        if ($this->isEditing()) {
            $this->form
                ->addHidden(FormGenerator::ACTION_SLUG, FormGenerator::ACTION_EDIT);
        } else {
            $this->form
                ->addHidden(FormGenerator::ACTION_SLUG, FormGenerator::ACTION_CREATE);
        }

        if ($this->form->wasSubmitted() && $this->form->valid()) {
            // If updating
            if ($this->isEditing()) {

                $oldFeed = Feed::find($_REQUEST['feed_id']);

                if( Feed::exists($this->form->getData(), $_REQUEST['feed_id']) ) {

                    $feed = $oldFeed;

                    Notice::error('Please provide a name that’s not currently in use.');

                } else {
                    $feed = $oldFeed->update($this->form->getData());

                    Notice::success('Successfully updated feed!');

                    do_action('zoovu-feed-updated', $feed, $oldFeed);

                    wp_redirect( admin_url( 'admin.php?page=zoovo-feeds&action=edited' ) );
                }
                

            // If creating
            } else {
                if( Feed::exists($this->form->getData()) ) {

                    Notice::error('Please provide a name that’s not currently in use.');

                } else {
                    $feed = Feed::create($this->form->getData());

                    Notice::success('Successfully created feed!');

                    // Then create scheduled feed
                    do_action('zoovu-feed-created', $feed);

                    wp_redirect( admin_url( 'admin.php?page=zoovo-feeds&action=created' ) );
                }
            }

        // Otherwise if not submited but editing get feed
        } elseif ($this->isEditing()) {
            $feed = Feed::find($_REQUEST['feed_id']);
        }
        
        if ($feed) {
            $this->form
                ->setData([
                    'enabled' => $feed->isEnabled() ? $feed->isEnabled() : 'no',
                    'name' => $feed->getTitle(),
                    'schedule_type' => $feed->getScheduleType(),
                    'daily_time' => $feed->getScheduledTime(),
                    'individual_products' => $feed->getIndividualProducts(),
                    'individual_categories' => $feed->getIndividualCategories(),
                    'exclude_category_products' => $feed->getExcludedProducts()
                ]);
        }

        // Add AJAX endpoints
        add_action('wp_ajax_pzfFindProducts', [$this, 'findProducts']);
        add_action('wp_ajax_pzfFindCategories', [$this, 'findCategories']);
    }

    public function isEditing()
    {
        return isset($_REQUEST['feed_id']) && $_REQUEST['feed_id'];
    }

    /**
     * AJAX find categories
     */
    public function findCategories()
    {
        $term = false;
        if( isset( $_REQUEST['q'] ) ) {
            $term = sanitize_text_field($_REQUEST['q']);    
        }

        $params = [
            'taxonomy' => 'product_cat'
        ];

        // Add search term filter
        if ($term) {
            $params['search'] = $term;
        }

        $terms = \collect(get_terms($params));

        $terms = $terms->map(function ($item) {
            return [
                'id' => $item->term_id,
                'text' => $item->name
            ];
        });

        wp_send_json([
            'results' => $terms->toArray()
        ]);

        wp_die();
    }

    /**
     * AJAX find products
     */
    public function findProducts()
    {
        $term = false;
        if( isset( $_REQUEST['q'] ) ) {
            $term = sanitize_text_field($_REQUEST['q']);    
        }
        
        $term_ids = false;
        if( isset( $_REQUEST['term_ids'] ) ) {
            $term_ids = sanitize_text_field($_REQUEST['term_ids']);
        }

        $params = [
            'post_type' => 'product',
            'posts_per_page' => 10
        ];

        // Add taxonomy filter
        if ($term_ids) {
            $params['tax_query'] = [
                [
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => [$term_ids],
                ]
            ];
        }

        // Add search term filter
        if ($term) {
            $params['s'] = $term;
        }

        $posts = \collect(get_posts($params));

        $posts = $posts->map(function ($item) {
            return [
                'id' => $item->ID,
                'text' => $item->post_title
            ];
        });

        wp_send_json([
            'results' => $posts->toArray()
        ]);

        wp_die();
    }

    /**
     * Add menu item under Woocommerce
     */
    public static function add()
    {
        add_action('admin_menu', [new self, 'addMenuItem'], 100);
    }

    /**
     * Menu Item registration function
     *
     * @action admin_menu
     */
    public function addMenuItem()
    {
        add_submenu_page(
                'woocommerce',
            __('Create Feed', \ZoovuFeeds::LOCALE_SLUG),
            null,
            'manage_options',
            'zoovo-feeds-create',
            [$this, 'pageContent']
        );
    }

    /**
     * FeedsListPage content
     */
    public function pageContent()
    {
        ?>

        <div class="wrap woocommerce">
            <h1 class="wp-heading-inline">
                <?php if ($this->isEditing()) : ?>
                    <?php echo __('Edit product feed', \ZoovuFeeds::LOCALE_SLUG) ?>
                <?php else : ?>
                    <?php echo __('Create product feed', \ZoovuFeeds::LOCALE_SLUG) ?>
                <?php endif ?>
            </h1>

            <?php $this->form->render() ?>
        </div>

        <?php
    }

    /**
     * Get url of create page
     *
     * @param false $feedID
     * @return string
     */
    public static function getUrl($feedID = false)
    {
        $params = [
            'page' => 'zoovo-feeds-create',
        ];

        if ($feedID) {
            $params['feed_id'] = $feedID;
        }

        return add_query_arg($params, admin_url('admin.php'));
    }
}
