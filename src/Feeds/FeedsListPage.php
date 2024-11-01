<?php

namespace Progressus\Zoovu\Feeds;

use Progressus\Zoovu\Helpers\Notice;
use Progressus\Zoovu\Helpers\Page;

class FeedsListPage implements Page
{
    /**
     * FeedsListPage constructor.
     */
    public function __construct()
    {
        if ($this->isDeleteRequest()) {
            try {
                $feed = Feed::find($_REQUEST['feed']);

                if ($feed->delete()) {
                    do_action('zoovu-feed-deleted', $feed);
                    wp_redirect(self::getUrl());
                }
            } catch (\Exception $e) {
                Notice::error($e->getMessage());
            }
        }

        if ($this->isCreatedAction()) {
            Notice::success( __('Feed saved successfully. It will be sent to Zoovu now and then as given in "Next export".', \ZoovuFeeds::LOCALE_SLUG) );
        }

        if ($this->isEditedAction()) {
            Notice::success( __('Feed edited successfully.', \ZoovuFeeds::LOCALE_SLUG) );
        }
    }

    /**
     * Check weather its created request
     *
     * @return bool
     */
    private function isDeleteRequest()
    {
        return isset($_REQUEST['action']) && $_REQUEST['action'] === 'delete' && $_REQUEST['feed'];
    }

    /**
     * Check weather its edited request
     *
     * @return bool
     */
    private function isEditedAction()
    {
        return isset($_REQUEST['action']) && $_REQUEST['action'] === 'edited';
    }

    /**
     * Check weather its delete request
     *
     * @return bool
     */
    private function isCreatedAction()
    {
        return isset($_REQUEST['action']) && $_REQUEST['action'] === 'created';
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
            __('Zoovu product feeds', \ZoovuFeeds::LOCALE_SLUG),
            __('Zoovu', \ZoovuFeeds::LOCALE_SLUG),
            'manage_options',
            'zoovo-feeds',
            [$this, 'pageContent'],
            6
        );
    }

    /**
     * FeedsListPage content
     */
    public function pageContent()
    {
        // Register Autologin Functionality
        \Progressus\Zoovu\Autologin::register();

        $table = new FeedsListTable();
        $table->prepare_items();

        ?>

        <div class="wrap">
            <h1 class="wp-heading-inline"><?php echo get_admin_page_title() ?></h1>
            <a href="<?php echo admin_url('admin.php?page=zoovo-feeds-create') ?>" class="page-title-action"><?php _e('Create feed', \ZoovuFeeds::LOCALE_SLUG); ?></a>
            <p>
                <?php echo sprintf( __('<a href="%1$s">Go to Zoovu settings</a>'), admin_url( 'admin.php?page=wc-settings&tab=integration&section=zoovu-feeds' ) ); 
                ?>
            </p>

            <?php $table->display() ?>
        </div>

        <?php
    }

    public static function getUrl()
    {
        $params = [
            'page' => 'zoovo-feeds',
        ];

        return add_query_arg($params, admin_url('admin.php'));
    }
}
