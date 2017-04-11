<?php
/*
Plugin Name: OptimizePress Google ReCaptcha
Plugin URI: http://www.optimizepress.com
Description: Attaches invisible google ReCaptcha after submit button for optin form which needed to be checked in order to proceed
Version: 1.1.2
Author: OptimizePress
Author URI: http://www.optimizepress.com
*/

class OptimizePress_GoogleReCaptcha
{
    /**
     * @var OptimizePress_GoogleReCaptcha
     */
    protected static $instance;

    /**
     * @var string
     */
    public $pluginSlug = 'op-google-recaptcha';

    protected static $version = '1.1.2';

    protected $googleReCaptchaSiteKey = false;

    protected $googleReCaptchaSecret = false;

    protected $requestIsValid = -1;

    // protected $formNum = 1;

    /**
     * Registering actions and filters
     */
    protected function __construct()
    {
        add_action('init', array($this, 'loadPluginTextdomain'));
        add_action('admin_menu', array($this, 'registerAdminPage'), 111);

        add_action('init', array($this, 'googleReCaptchaScript'));
        add_action('op_after_optin_submit_button', array($this, 'renderGoogleReCaptcha'));

        // Adding to this actions to be able to stop the
        // form from submitting if captcha doesn't
        // pass the validation
        //
        // First one is for email data,
        // second for integrations
        add_action('op_pre_template_include', array($this, 'processOptinForm'), 1);
        add_action('template_redirect', array($this, 'processOptinFormIntegration'), 19);
    }

    /**
     * Checks Google ReCaptcha verification on server side before regular process opt-in form from OP
     */
    public function processOptinFormIntegration()
    {
        global $wp;
        if ($wp->request === 'process-optin-form') {
            if (isset($_POST['provider']) && $_POST['provider'] !== 'email') {
                if ($this->isInvisibleReCaptchaTokenValid() === false) {
                    wp_die("Invalid recaptcha token. Verification failed.");
                }
            }
        }
    }

    /**
     * Checks Google ReCaptcha verification on server side before regular process opt-in form from OP
     */
    public function processOptinForm()
    {
        if (isset($_POST['op_optin_form']) && $_POST['op_optin_form'] === 'Y') {
            if ($this->isInvisibleReCaptchaTokenValid() === false) {
                wp_die("Invalid recaptcha token. Verification failed.");
            }
        }
    }

    /**
     * Adds Google ReCaptcha element to opt-in forms
     */
    public function renderGoogleReCaptcha()
    {
        if ($this->googleReCaptchaSiteKey === false || $this->googleReCaptchaSecret === false){
            return;
        }

        $hide_logo = get_option('op_google_recaptcha_hide_logo');
        $hide_logo_string = '';

        if (isset($hide_logo) && !empty($hide_logo)) {
            $hide_logo_string = '<style>.grecaptcha-badge { display: none; }</style>';
        }

        // TODO: When element is cloned, uniqid is cloned as well, which breaks the captcha
        echo '
            <div class="op-g-recaptcha"
              id="op-g-recaptcha-' . uniqid() . '"
              data-sitekey="' . $this->googleReCaptchaSiteKey . '"
              data-badge="inline"
              data-size="invisible">
            </div>' . $hide_logo_string . '
        ';
    }

    /**
     * Enqueue Google ReCaptcha scripts
     */
    public function googleReCaptchaScript()
    {
        if ($this->googleReCaptchaSiteKey === false || $this->googleReCaptchaSecret === false){
            return;
        }

        wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js', array(), false, true);
        wp_enqueue_script('op-recaptcha', plugin_dir_url(__FILE__) . 'js/op_recaptcha.js', array('jquery'), false, true);
    }

    /**
     * Adds submenu page to OptimizePress page
     * @return  void
     */
    public function registerAdminPage()
    {
        add_submenu_page(OP_SN, __('Google ReCaptcha', $this->pluginSlug), __('Google ReCaptcha', $this->pluginSlug), 'edit_theme_options', OP_SN . '-google-recaptcha', array($this, 'displayAdminPage'));
    }

    /**
     * Site cloner page logic
     * @return void
     */
    public function displayAdminPage()
    {
        /*
         * Lets go ahead and create a new blog
         */
        if (isset($_POST['google_recaptcha'])) {
            check_admin_referer('op-google-recaptcha', '_wpnonce_op-google-recaptcha');

            // Save site key
            if (isset($_POST['op_google_recaptcha_sitekey']) && !empty($_POST['op_google_recaptcha_sitekey'])) {
                update_option('op_google_recaptcha_sitekey', sanitize_text_field($_POST['op_google_recaptcha_sitekey']));
            } else {
                delete_option('op_google_recaptcha_sitekey');
            }

            // Save secret key
            if (isset($_POST['op_google_recaptcha_secret']) && !empty($_POST['op_google_recaptcha_secret'])) {
                update_option('op_google_recaptcha_secret', sanitize_text_field($_POST['op_google_recaptcha_secret']));
            } else {
                delete_option('op_google_recaptcha_secret');
            }

            // Save Hide ReCaptcha Logo option
            if (isset($_POST['op_google_recaptcha_hide_logo'])) {
                update_option('op_google_recaptcha_hide_logo', true);
            } else {
                delete_option('op_google_recaptcha_hide_logo');
            }

            /*
             * Setting success message
             */
            $messages = array(
                'Fields saved',
            );
        }

        require_once plugin_dir_path(__FILE__) . 'views/admin.php';
    }

    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function loadPluginTextdomain()
    {
        $this->googleReCaptchaSiteKey = get_option('op_google_recaptcha_sitekey');
        $this->googleReCaptchaSecret = get_option('op_google_recaptcha_secret');

        $domain = $this->pluginSlug;
        $locale = apply_filters('plugin_locale', get_locale(), $domain);

        load_textdomain($domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo');
        load_plugin_textdomain($domain, FALSE, plugin_dir_path(__FILE__) . '/lang/');
    }


    /**
     * Checks if page is frontend LiveEditor
     *
     * @return bool
     */
    protected function checkIfLEPage()
    {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
        $checkIfLEPage = get_post_meta( url_to_postid( $protocol.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] ), '_optimizepress_pagebuilder', true );

        $pageBuilder = false;
        if ( isset($_GET['page']) ) {
            $pageBuilder = ($_GET['page'] == 'optimizepress-page-builder' ) ? true : false;
        }
        $liveEditorAjaxInsert = false;
        if ( isset($_REQUEST['action']) ) {
            $liveEditorAjaxInsert = ($_REQUEST['action'] == 'optimizepress-live-editor-parse' ) ? true : false;
        }

        if ( ($checkIfLEPage == 'Y') && !$pageBuilder || $liveEditorAjaxInsert ){
            return true;
        }

        return false;
    }

    /**
     * Checks Google ReCaptcha return code
     *
     * @return bool
     */
    public function isInvisibleReCaptchaTokenValid()
    {

        $this->googleReCaptchaSiteKey = get_option('op_google_recaptcha_sitekey');
        $this->googleReCaptchaSecret = get_option('op_google_recaptcha_secret');
        $this->requestIsValid = true;

        if (! function_exists( "op_get_client_ip_env" )) {
            return false;
        }

        if (empty( $_POST['g-recaptcha-response'] )) {
            return false;
        }

        $response = wp_remote_retrieve_body(
            wp_remote_get(
                add_query_arg(
                    array(
                        'secret'   => $this->googleReCaptchaSecret,
                        'response' => $_POST['g-recaptcha-response'],
                        'remoteip' => op_get_client_ip_env()
                    ), 'https://www.google.com/recaptcha/api/siteverify')
            )
        );

        if (empty( $response )) {
            $this->requestIsValid = false;
        }

        $json = json_decode( $response );
        if (gettype( $json ) !== 'object' || empty( $json->success )) {
            $this->requestIsValid = false;
        }

        return $this->requestIsValid;
    }

    /**response
     * Singleton
     * @return OptimizePress_GoogleReCaptcha
     */
    public static function getInstance()
    {
        if (null == self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }
}

add_action('plugins_loaded', array('OptimizePress_GoogleReCaptcha', 'getInstance'));
