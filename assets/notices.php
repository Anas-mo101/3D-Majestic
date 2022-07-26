<?php


class Notice_Handler
{
    const NOTICES_HANDLER_KEY = 'admin_notices_handler';

    public static function init()
    {
        add_action('admin_notices', [__CLASS__, 'output_notices']);
    }

 
    public static function output_notices()
    {
        $notices = self::get_notices();
        if (empty($notices)) return;

        foreach ($notices as $type => $messages) {
            foreach ($messages as $message) {
                printf('<div class="notice notice-%1$s is-dismissible"> <p>%2$s</p> </div>', $type, $message);
            }
        }

        self::update_notices([]);
    }

    private static function get_notices()
    {
        $notices = get_option(self::NOTICES_HANDLER_KEY, []);
        return $notices;
    }


    private static function update_notices(array $notices)
    {
        update_option(self::NOTICES_HANDLER_KEY, $notices);
    }


    private static function add_notice($message, $type = 'success')
    {
        $notices = self::get_notices();
        $notices[$type][] = $message;
        self::update_notices($notices);
    }

    public static function add_success($message)
    {
        self::add_notice($message, 'success');
    }

    public static function add_error($message)
    {
        self::add_notice($message, 'error');
    }

    public static function add_warning($message)
    {
        self::add_notice($message, 'warning');
    }

    public static function add_info($message)
    {
        self::add_notice($message, 'info');
    }
}

Notice_Handler::init();
