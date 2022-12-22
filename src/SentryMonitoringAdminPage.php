<?php

namespace ClarkU_Sentry_Monitoring;

use Sentry;
use Exception;
use function Sentry\captureException;

class SentryMonitoringAdminPage
{
    protected const CONSTANT_NOT_SET_MESSAGE = 'Please set the constant "<b>WP_SENTRY_PHP_DSN</b>" in wp-config.php';

    private static $instance = null;
    protected bool $dsnSet = false;

    private function __construct()
    {
        $this->addActions();

        if ($this->areWeReady()) {
            // Start Sentry monitoring
            \Sentry\init(['dsn' => WP_SENTRY_PHP_DSN]);
        }
    }

    public static function boot(): self
    {
        if (!self::$instance) {
            self::$instance = new SentryMonitoringAdminPage();
        }

        return self::$instance;
    }

    public function addActions(): void
    {
        register_activation_hook(SENTRY_MONITORING_ROOT_FILE, [$this, 'adminNoticeActivationHook']);

        add_action('wp_loaded', [$this, 'loadScript']);
        add_action('admin_notices', [$this, 'adminNotice']);
        add_action('network_admin_menu', [$this, 'addMenuPage']);
        add_action('wp_ajax_nopriv_send_test_error_to_sentry', [$this, 'generateTestException']);
        add_action('wp_ajax_send_test_error_to_sentry', [$this, 'generateTestException']);

    }

    public function loadScript()
    {
        $file = plugin_dir_path(__FILE__) . 'js/sentry-monitoring.js';
        $cacheBuster = filemtime($file);
        wp_register_script(
            'sentry-monitoring',
            plugins_url('/js/sentry-monitoring.js',
                __FILE__), array(), $cacheBuster, true
        );
        wp_enqueue_script('sentry-monitoring');
    }

    public function adminNoticeActivationHook(): void
    {
        set_transient('clark-sentry-admin-notice', true, 5);
    }

    public function adminNotice()
    {
        /* Check transient, if available display notice */
        if (get_transient('clark-sentry-admin-notice')) {
            ?>
            <div class="updated notice is-dismissible">
                <p>Clark Uninversity Sentry Monitoring plugin</p>
                <p>
                    <?php
                    echo $this->areWeReady()
                        ? 'Ready to go'
                        : self::CONSTANT_NOT_SET_MESSAGE
                    ?>
                </p>
            </div>
            <?php
            /* Delete transient, only display this notice once. */
            delete_transient('clark-sentry-admin-notice');
        }
    }

    public function addMenuPage(): void
    {
        add_menu_page(
            __('Sentry Error Monitoringr', 'uri'),
            'Sentry Error Monitoring',
            'switch_themes',
            'sentry-error-monitoring',
            [$this, 'showTestPage'],
            'dashicons-admin-tools',
            90
        );
    }

    public function showTestPage()
    {
        if (!$this->areWeReady()) {
            echo '<div class="update-nag notice notice-warning inline">';
            echo self::CONSTANT_NOT_SET_MESSAGE;
            echo '</div>';
        }
        echo '<div id="test_generator" style="max-width: 90%; margin: 3rem;">';
        echo '<span style="font-weight: bolder">Generate a test error:</span> ';
        echo '<button id="sentry_test_error">Test</button>';
        echo '</div>';
    }

    public function generateTestException()
    {
        try {
            $this->functionBogus();
        } catch (\Throwable $exception) {
            captureException($exception);

            wp_send_json(['message' => 'A test error was sent to Sentry ' . date("F j, Y, g:i a"). ' UTC']);
        }

        die();
    }

    protected function areWeReady(): bool
    {
        return defined('WP_SENTRY_PHP_DSN');
    }
}