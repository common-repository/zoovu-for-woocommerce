<?php

namespace Progressus\Zoovu\Feeds;

use Carbon\Carbon;
use Progressus\Zoovu\Api\Client;

class FeedEvents
{
    public static function register()
    {
        add_action('zoovu-feed-updated', [new self, 'checkIfWasChanges'], 10, 3);
        add_action('zoovu-feed-updated', [new self, 'checkIfWasNameChange'], 9, 3);
        add_action('zoovu-feed-created', [new self, 'registerEvent']);
        add_action('zoovu-feed-deleted', [new self, 'removeEvent']);
        add_action('zoovu-feed-do-task', [new self, 'doExport']);
    }

    /**
     * Let API know that we changed feed name
     *
     * @param Feed $feed
     * @param Feed $oldFeed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function checkIfWasNameChange(Feed $feed, Feed $oldFeed)
    {
        if ($feed->getTitle() !== $oldFeed->getTitle() && $feed->isEnabled()) {
            try {
                $client = new Client();

                $client->updateFileName(
                    $oldFeed->getGeneratedFileName(),
                    $feed->getGeneratedFileName()
                );
            } catch (\Exception $e) {
                error_log('There was an error during the feed name update: ' . $e->getMessage());
            }
        }
    }


    /**
     * @param Feed $feed
     * @param Feed $oldFeed
     */
    public function checkIfWasChanges(Feed $feed, Feed $oldFeed)
    {
        // If feed is disabled make sure no event exists
        if (! $feed->isEnabled()) {
            $this
                ->removeEvent($feed);

            return;
        }

        // Run feed immediately after change
        $this->doExport($feed->id);
        
        // If feed type is not the same as old feed type
        if (
            ($feed->getScheduleType() !== $oldFeed->getScheduleType()) ||
            (! $oldFeed->isEnabled())
        ) {

            $this
                ->removeEvent($feed)
                ->registerEvent($feed);

            return;
        }

        // If feed type is hourly
        if ($feed->getScheduleType() === Feed::TYPE_HOURLY) {
            return;
        }

        // Otherwise remove event and register new
        $this
            ->removeEvent($feed)
            ->registerEvent($feed);
    }

    /**
     * @param Feed $feed
     * @return $this
     */
    public function removeEvent(Feed $feed)
    {
        wp_clear_scheduled_hook('zoovu-feed-do-task', ['feed' => $feed->id]);

        return $this;
    }

    /**
     * @param Feed $feed
     */
    public function registerEvent(Feed $feed)
    {
        if (! $feed->isEnabled()) {
            return;
        }

        wp_schedule_event(
            $feed->getNextExportDate()->getTimestamp(),
            $feed->getScheduleType(),
            'zoovu-feed-do-task',
            ['feed' => $feed->id]
        );

        // Run feed immediately after change
        $this->doExport($feed->id);
    }

    /**
     * @param int $feedId
     */
    public function doExport($feedId)
    {
        if (! $feedId) {
            error_log('No feed id was passed to exporter! Task was temrinated!');

            return;
        }

        $feed = Feed::find($feedId);

        try {
            $client = new Client();

            $client
                ->uploadFeed($feed);

            $results = $client
                ->feedUsage($feed);

            $feed->setMetaData('last_update', __('Success', \ZoovuFeeds::LOCALE_SLUG ) );

            $feed->setMetaData('assistant_info', $results->isUsed);

        } catch (\Exception $e) {
            error_log('There was an error during the feed upload: ' . $e->getMessage());

            $feed->setMetaData('last_update', sprintf('Error (%s)', Carbon::now()));
        }
    }
}
