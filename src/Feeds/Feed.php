<?php

namespace Progressus\Zoovu\Feeds;

use Carbon\Carbon;
use Ramsey\Collection\Collection;
use Progressus\Zoovu\Helpers\Time;

class Feed
{
    const TYPE_DAILY = 'daily';
    const TYPE_HOURLY = 'hourly';

    public $id;
    public $title;

    public function __construct($post)
    {
        if (! $post) {
            throw new \Exception("No post was set for Feed model!");
        }

        $this->post = get_post($post);

        $this->data = get_post_meta($this->post->ID, 'zoovu_feeds_params', true);

        $this->setParams();
    }

    /**
     * 
     * Values used for table display
     * 
     **/
    private function setParams()
    {
        $this->id = $this->post->ID;
        $this->title = $this->post->post_title;
        $this->next_export = Time::convertUTCLocal( $this->getNextExportDate() );
        $this->products_count = $this->getProductsCount();
        $this->last_update = $this->getLastUpdate();
        
        $this->status = $this->isEnabled() ? __('Active', \ZoovuFeeds::LOCALE_SLUG) : __('Inactive', \ZoovuFeeds::LOCALE_SLUG);
        $this->status .= ' (' . ucfirst( $this->getScheduleType() ) . ')';

        $this->assistant_info = $this->getAssitantInfoCount();
    }

    public function getLastUpdate()
    {
        return $this->getMetaData()->get('last_update', '-');
    }

    public function getScheduleType()
    {
        return $this->getMetaData()->get('schedule_type');
    }

    public function getProductsCount()
    {
        return $this->getMetaData()->get('products_count', 0);
    }

    public function getAssitantInfoCount()
    {
        return $this->getMetaData()->get('assistant_info');
    }

    public function getTitle()
    {
        return $this->post->post_title;
    }

    public function isEnabled()
    {
        return $this->getMetaData()->get('enabled', false);
    }

    public function getScheduledTime()
    {
        return $this->getMetaData()->get('daily_time', false);
    }

    public function getIndividualProducts()
    {
        $products = \collect($this->getMetaData()->get('individual_products', []))->map(function ($productID) {
            $product = get_post($productID);
            return [
                'id' => $product->ID,
                'text' => $product->post_title
            ];
        });

        return $products;
    }

    public function getIndividualCategories()
    {
        $terms = \collect($this->getMetaData()->get('individual_categories', []))->map(function ($termID) {
            $term = get_term($termID);
            return [
                'id' => $term->term_id,
                'text' => $term->name
            ];
        });

        return $terms;
    }

    public function getExcludedProducts()
    {
        $products = \collect($this->getMetaData()->get('exclude_category_products', []))->map(function ($productID) {
            $product = get_post($productID);
            return [
                'id' => $product->ID,
                'text' => $product->post_title
            ];
        });

        return $products;
    }

    /**
     * @return \WC_Product[]
     */
    public function getProductsList()
    {
        $all_products_list = \collect($this->getMetaData()->get('individual_products', []))->map(function ($productID) {
            return wc_get_product($productID);
        });

        $category_products = \collect(wc_get_products([
            'post_type' => 'product',
            'posts_per_page' => -1,
            'post__not_in' => $this->getMetaData()->get('exclude_category_products', []),
            'tax_query' => [
                [
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => $this->getMetaData()->get('individual_categories', []),
                ]
            ]
        ]));

        return $all_products_list->merge($category_products)->unique()->toArray();
    }

    /**
     * @return Collection
     */
    public function getMetaData()
    {
        return $this->data;
    }

    public function setMetaData($key, $value)
    {
        $meta = $this->getMetaData();

        $meta = $meta->put($key, $value);

        update_post_meta($this->id, 'zoovu_feeds_params', $meta);

        $this->data = $meta;
    }

    public function getNextExportDate()
    {
        $timezone = wp_timezone_string();

        if ($this->getScheduleType() === Feed::TYPE_DAILY) {
            $scheduledTime = $this->getScheduledTime();

            if (! $scheduledTime) {
                return null;
            }

            list($hour, $minute) = explode(':', $scheduledTime);

            return Carbon::now()->setTime($hour, $minute)->isPast() ?
                Carbon::tomorrow()->setTime($hour, $minute) :
                Carbon::now()->setTime($hour, $minute);
        }

        return Carbon::now()->addHour()->setMinutes( 0 )->setSeconds( 0 );
    }

    public function getCSVContents()
    {
        $data[] = [
            'price',
            'name',            
            'picture',
            'offerurl',
            'sku',
        ];

        $products = $this->getProductsList();

        foreach ($products as $product) {
            $data[] = [
                $product->get_price(),
                $product->get_title(),
                wp_get_attachment_image_url($product->get_image_id(), 'full'),
                $product->get_permalink(),
                $product->get_sku()
            ];
        }

        ob_start();

        $temp = fopen('php://output', 'w+');

        foreach ($data as $line) {
            fputcsv($temp, $line);
        }

        return ob_get_clean();
    }

    public function getGeneratedFileName()
    {
        $is_idable = false;

        if ($is_idable) {
            return sprintf(
                'feed-data-%s.csv',
                $this->id
            );
        }

        return sprintf(
            '%s.csv',
            sanitize_title($this->getTitle())
        );
    }

    /**
     * Find Feed by ID
     *
     * @param $feedId
     * @return Feed
     * @throws \Exception
     */
    public static function find($feedId)
    {
        return new self(get_post($feedId));
    }

    /**
     * Delete current feed
     *
     * @return array|false|\WP_Post|null
     */
    public function delete()
    {
        return wp_delete_post($this->id);
    }

    /**
     * Create new feed
     *
     * @param $data
     */
    public static function exists($data, $feed_id = 0)
    {
        // Get existing Zoovu feeds with new name, that are not the feed itself   
        $posts = get_posts([
            'post_type'  => 'zoovu_feeds',
            'title' => $data['name'],
            'exclude' => array( $feed_id )
        ]);

        if ( empty($posts) ) {
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * Create new feed
     *
     * @param $data
     */
    public static function create($data)
    {
        $postID = wp_insert_post([
            'post_type' => 'zoovu_feeds',
            'post_title' => $data['name'],
            'post_status' => 'publish'
        ], false);

        add_post_meta($postID, 'zoovu_feeds_params', \collect($data));

        return (new self($postID))
            ->updateProductCount();
    }

    public function update($data)
    {
        $default_attrs = [
            'daily_time' => null,
            'individual_products' => null,
            'individual_categories' => null,
            'exclude_category_products' => null
        ];

        wp_update_post([
            'ID' => $this->post->ID,
            'post_title' => $data['name']
        ]);

        update_post_meta($this->post->ID, 'zoovu_feeds_params', \collect(array_merge($default_attrs, $data)));

        return (new self($this->post->ID))
            ->updateProductCount();
    }

    private function updateProductCount()
    {
        $this->setMetaData('products_count', count($this->getProductsList()));

        return $this;
    }

    /**
     * Get list of feeds paginated
     *
     * @param int $number_of_page
     * @return object
     */
    public static function paged($number_of_page = 1)
    {
        $query = new \WP_Query([
            'post_type' => 'zoovu_feeds',
            'posts_per_page' => 10,
            'paged' => $number_of_page
        ]);

        $feeds = \collect($query->posts)->map(function ($item) {
            return new self($item);
        });

        return (object) [
            'items' => $feeds,
            'total' => $query->found_posts
        ];
    }
}
