<?php

namespace Progressus\Zoovu\WC;

use Progressus\Zoovu\Autologin;
use Progressus\Zoovu\Options;

class Integration extends \WC_Integration
{
    const INTEGRATION_OPTION_KEY = 'woocommerce_progressus-zoovu-feeds_settings';
    const INTEGRATION_ID = 'zoovu-feeds';
    const INTEGRATION_METHOD_TITLE = 'Zoovu';

    public function __construct()
    {
        $this->id = self::INTEGRATION_ID;
        $this->method_title = __(self::INTEGRATION_METHOD_TITLE, \ZoovuFeeds::LOCALE_SLUG);
        $this->method_description = sprintf( __( 'Read Zoovu documentation %1$shere%2$s.', \ZoovuFeeds::LOCALE_SLUG ), '<a href="https://zoovu.zendesk.com/hc/en-us/articles/360026583633" target="_blank">', '</a>' );

        $this->init_form_fields();
        $this->init_settings();

        if( empty( $this->get_option( Options::OPTION_API_KEY_KEY ) ) ) {
            add_action( 'admin_notices', array( $this, 'welcome_notice' ) );
        }

        add_action('woocommerce_update_options_integration_' .  $this->id, [$this, 'process_admin_options']);
    }

    public function get_option_key()
    {
        return self::INTEGRATION_OPTION_KEY;
    }

    public function init_form_fields()
    {
        $this->form_fields = [
            'autologin' => [
                'type' => 'zoovu_autologin_button'
            ],
            Options::OPTION_API_KEY_KEY => [
                'title' => __('Connector token', \ZoovuFeeds::LOCALE_SLUG),
                'type' => 'password',
                'default' => '',
                'desc_tip' => false,
                'description' => sprintf( __( 'Connect your WooCommerce and Zoovu accounts. <a href="%1$s" target="_blank">How can I get my token?</a>', \ZoovuFeeds::LOCALE_SLUG ), 'https://zoovu.zendesk.com/hc/en-us/articles/360017200359' ),
            ],
            Options::OPTION_TRACK_ENABLED => [
                'label' => __('Enabled', \ZoovuFeeds::LOCALE_SLUG),
                'title' => __('Success tracking', \ZoovuFeeds::LOCALE_SLUG),
                'type' => 'checkbox',
                'default' => 'yes',
                'desc_tip' => false,
                'description' => __( 'Success tracking lets you see which category of products generates the most revenue for your business and drill down to the best performing SKU.<br/>Success tracking can be enabled and disabled at any time.', \ZoovuFeeds::LOCALE_SLUG ),
            ],
             Options::OPTION_LOGGING_KEY => [
                'label' => __('Enabled', \ZoovuFeeds::LOCALE_SLUG),
                'title' => __('Logging', \ZoovuFeeds::LOCALE_SLUG),
                'type' => 'checkbox',
                'default' => 'yes',
                'desc_tip' => false,
                'description' => __( 'Logs the requests sent to and responses received from the Zoovu API. Used for debugging purposes.<br/>To view the logs, go to WooCommerce > Status > Logs and select zoovu-for-woocommerce from the dropdown menu to the right.', \ZoovuFeeds::LOCALE_SLUG ),
            ]
        ];
    }

    public function generate_zoovu_autologin_button_html()
    {
        ob_start();
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
            </th>
            <td class="forminp">
                <a
                    href="<?php echo Autologin::getUrl() ?>"
                    style="
                        background-color: #1f3a54;
                        color: white;
                        padding: 6px 16px;
                        border-radius: 25px;
                        font-weight: bold;
                        text-decoration: none;
                        min-width: 200px;
                        display: inline-block;
                        text-align: center;
                    "
                    target="_blank"
                >
                    <?php _e('Go to Zoovu', \ZoovuFeeds::LOCALE_SLUG) ?>
                </a>
            </td>
        </tr>
        <?php

        return ob_get_clean();
    }

    public function sanitizeScript($value)
    {
        return $value;
    }

    public function welcome_notice() {
        ?>
        <div class="notice woocommerce-card  is-dismissible">
            <p><?php echo sprintf( __( '<strong>Welcome to Zoovu</strong><br/>This app lets you easily connect your WooCommerce product data with Zoovu. Once connected, you can create digital assistants to help your customers find the right products.<br/><br/>Start by connecting your WooCommerce and Zoovu accounts. <a href="%1$s">Go to Zoovu settings</a>, where you can insert your Zoovu connector token. Then, start exporting product data to Zoovu, or begin by building your conversation flow on Zoovu and connect your products later.', \ZoovuFeeds::LOCALE_SLUG ), admin_url( 'admin.php?page=wc-settings&tab=integration&section=zoovu-feeds' ) ); ?></p>
        </div>
    <?php
    }
}
