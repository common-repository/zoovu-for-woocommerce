<?php

namespace Progressus\Zoovu\Helpers;

class Notice
{
    private static function fire($messages, $type, $isDismissible)
    {
        $toShow = [];

        if (! is_array($messages)) {
            $toShow[] = $messages;
        } else {
            $toShow = $messages;
        }

        add_action('admin_notices', function () use ($toShow, $type, $isDismissible) {
            foreach ($toShow as $message) {
                echo sprintf('
                    <div class="notice notice-%1$s%3$s">
                        <p>%2$s</p>
                    </div>
                ',
                    $type,
                    $message,
                    ($isDismissible ? ' is-dismissible' : '')
                );
            }
        });
    }

    public static function error($messages, $isDismissible = true)
    {
        self::fire($messages, 'error', $isDismissible);
    }

    public static function success($messages, $isDismissible = true)
    {
        self::fire($messages, 'success', $isDismissible);
    }
}
