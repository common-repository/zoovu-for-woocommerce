<?php

namespace Progressus\Zoovu;

use BMC\View;

class TrackScript
{
    public static function register()
    {
        if (! Options::getTrackScript() ) {
            return;
        }

        if ( Options::isTrackingEnabled() ) {
            // Add script to thank you page as high as possible
            add_action('woocommerce_thankyou', [new self, 'add'], 1);
        }
    }

    public function add($order_id)
    {
        $order = wc_get_order($order_id);
        $products = $this->prepareProducts($order);

        if (! $order->get_id() || $products->isEmpty()) {
            return;
        }

        echo stripslashes(Options::getTrackScript());

        echo sprintf('
            <script>
                Zoovu.Tracking.trackPurchase({
                    transactionId: "%s",
                    currency: "%s",
                    products: %s
                })
            
            </script>
        ',
            $order->get_id(),
            $order->get_currency(),
            $products
        );
    }

    /**
     * @param $order
     * @return \Tightenco\Collect\Support\Collection
     */
    private function prepareProducts($order)
    {
        return collect($order->get_items())->map(function (\WC_Order_Item_Product $item) {
            if (! $item->get_product()->get_sku()) {
                return;
            }


            return [
                'sku' => $item->get_product()->get_sku(),
                'name' => $item->get_name(),
                'pricePerUnit' => $item->get_product()->get_price(),
                'quantity' => $item->get_quantity()
            ];
        })->values();
    }
}
