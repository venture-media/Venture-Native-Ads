<?php
/**
 * Plugin Name:       Venture Native Ads
 * Plugin URI:        https://github.com/venture-media/Venture-Native-Ads
 * Description:       Client plugin to display rotating native ads from Venture Native Ad Management host.
 * Version:           0.9.0
 * Author:            Leon de Klerk
 * Author URI:        https://github.com/Leon2332
 * License:           MIT
 * License URI:       https://github.com/venture-media/Venture-Native-Ads/blob/main/LICENSE
 * Text Domain:       venture-native-ads
 */

if (!defined('ABSPATH')) exit;

class Venture_Native_Ads {

    private static $instance = null;
    public $version = '0.9.0';

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init();
    }

    private function init() {
        register_activation_hook(__FILE__, [$this, 'activate']);

        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);

        add_shortcode('venture-native-ads', [$this, 'ads_shortcode']);
    }

    public function activate() {
        if (!get_option('venture_host_url')) update_option('venture_host_url', '');
        if (!get_option('venture_secret_key')) update_option('venture_secret_key', '');
        if (!get_option('venture_ad_duration')) update_option('venture_ad_duration', 15);
    }

    public function add_admin_menu() {
        add_menu_page(
            'Venture Native Ads',
            'Venture Native Ads',
            'manage_options',
            'venture-native-ads-settings',
            [$this, 'settings_page'],
            'dashicons-megaphone',
            31
        );
    }

    public function register_settings() {
        register_setting('venture_native_ads_settings', 'venture_host_url');
        register_setting('venture_native_ads_settings', 'venture_secret_key');
        register_setting('venture_native_ads_settings', 'venture_ad_duration');
    }

    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>Venture Native Ads – Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('venture_native_ads_settings');
                do_settings_sections('venture-native-ads');
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">Host Site URL</th>
                        <td>
                            <input type="url" name="venture_host_url" value="<?php echo esc_attr(get_option('venture_host_url')); ?>" class="regular-text" placeholder="https://your-main-site.com" />
                            <p class="description">Full URL of the site running Venture Native Ad Management (host plugin).</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Secret Key</th>
                        <td>
                            <input type="password" name="venture_secret_key" value="<?php echo esc_attr(get_option('venture_secret_key')); ?>" class="regular-text" />
                            <p class="description">Copy from the host plugin → Settings page.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Ad Duration (seconds)</th>
                        <td>
                            <input type="number" name="venture_ad_duration" value="<?php echo esc_attr(get_option('venture_ad_duration', 15)); ?>" min="5" max="120" style="width:120px;" />
                            <p class="description">How long each ad is shown before fading to the next.</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function enqueue_assets() {
        wp_enqueue_style('venture-native-ads-css', plugin_dir_url(__FILE__) . 'css/frontend.css', [], $this->version);
        wp_enqueue_script('venture-native-ads-js', plugin_dir_url(__FILE__) . 'js/frontend.js', ['jquery'], $this->version, true);

        wp_localize_script('venture-native-ads-js', 'ventureAdsConfig', [
            'adDuration' => (int) get_option('venture_ad_duration', 15)
        ]);
    }

    public function ads_shortcode($atts) {
        $atts = shortcode_atts(['id' => ''], $atts);
        $campaign_id = sanitize_text_field($atts['id']);

        if (empty($campaign_id)) {
            return '<p style="color:red;">Venture Native Ads: Campaign ID is required in shortcode.</p>';
        }

        $host = get_option('venture_host_url');
        $key  = get_option('venture_secret_key');

        if (empty($host) || empty($key)) {
            return '<p style="color:orange;">Venture Native Ads: Please configure Host URL and Secret Key in plugin settings.</p>';
        }

        ob_start();
        ?>
        <div class="venture-native-ads-wrapper"
             data-campaign-id="<?php echo esc_attr($campaign_id); ?>"
             data-host-url="<?php echo esc_attr($host); ?>"
             data-secret-key="<?php echo esc_attr($key); ?>">
            <!-- JS injects the rotating ad here -->
        </div>
        <?php
        return ob_get_clean();
    }
}

Venture_Native_Ads::get_instance();
