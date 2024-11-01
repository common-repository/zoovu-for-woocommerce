<?php

namespace Progressus\Zoovu\Feeds;

class FeedsListTable extends \WP_List_Table
{
    public function get_columns()
    {
        return [
            // 'id' => __('ID', \ZoovuFeeds::LOCALE_SLUG),
            'title' => __('Feed name', \ZoovuFeeds::LOCALE_SLUG),
            'status' => __('Feed status', \ZoovuFeeds::LOCALE_SLUG),
            'assistant_info' => __('Used in assistant', \ZoovuFeeds::LOCALE_SLUG),
            'products_count' => __('Product count', \ZoovuFeeds::LOCALE_SLUG),
            'last_update' => __('Export status', \ZoovuFeeds::LOCALE_SLUG),
            'next_export' => __('Next export', \ZoovuFeeds::LOCALE_SLUG),
        ];
    }

    protected function column_title( $item ) {
        $page = wp_unslash( $_REQUEST['page'] ); // WPCS: Input var ok.

        $actions['edit'] = sprintf(
            '<a href="%1$s">%2$s</a>',
            esc_url( FeedsCreatePage::getUrl( $item->id ) ),
            _x( 'Edit', 'List table row action', 'wp-list-table-example' )
        );

        // Build delete row action.
        $delete_query_args = array(
            'page'   => $page,
            'action' => 'delete',
            'feed'  => $item->id,
        );

        $actions['delete'] = sprintf(
            '<a href="%1$s">%2$s</a>',
            esc_url( wp_nonce_url( add_query_arg( $delete_query_args, 'admin.php' ), 'deletemovie_' . $item->id ) ),
            _x( 'Delete', 'List table row action', 'wp-list-table-example' )
        );

        // Return the title contents.
        return sprintf( '%1$s %2$s',
            $item->title,
            $this->row_actions( $actions )
        );
    }

    public function has_items()
    {
        return $this->items->isNotEmpty();
    }

    public function no_items()
    {
        _e('Create a product feed for your Zoovu account', \ZoovuFeeds::LOCALE_SLUG);
    }

    public function column_default( $item, $column_name ) {
        switch( $column_name ) {
            case 'assistant_info':
                return isset($item->$column_name) && $item->$column_name ? __('Used', \ZoovuFeeds::LOCALE_SLUG) : __('Not Used', \ZoovuFeeds::LOCALE_SLUG);
            // case 'status':
            //     return isset($item->$column_name) && $item->$column_name ? __('Active', \ZoovuFeeds::LOCALE_SLUG) : __('Inactive', \ZoovuFeeds::LOCALE_SLUG);
            default:
                return isset($item->$column_name) && $item->$column_name ? $item->$column_name : '-';
        }
    }

    public function prepare_items()
    {
        $this->_column_headers = [
            $this->get_columns(),
            [],
            []
        ];

        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 0) : 0;

        $feeds_list = Feed::paged($paged);
        $this->items = $feeds_list->items;

        $this->set_pagination_args([
            'total_items' => $feeds_list->total,
            'per_page' => 10
        ]);
    }
}
