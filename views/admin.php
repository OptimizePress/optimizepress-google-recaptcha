<div class="wrap">
    <h2 id="add-new-site"><?php echo esc_html(get_admin_page_title()); ?></h2>
    <?php

    if (!empty($errors)) {
        foreach ($errors as $msg) {
            echo '<div id="message" class="error"><p>' . $msg . '</p></div>';
        }
    }

    if (!empty($messages)) {
        foreach ($messages as $msg) {
            echo '<div id="message" class="updated"><p>' . $msg . '</p></div>';
        }
    }

    ?>
    <p><?php _e('Sign-up for <b>invisible <a href="https://www.google.com/recaptcha/" target="_blank">Google ReCaptcha</a></b> and enter site key & secret in order to apply it to all opt-in forms.', 'op-google-recaptcha'); ?>
    <form method="post" id="op_google_recaptcha_admin_form">
    <?php wp_nonce_field('op-google-recaptcha', '_wpnonce_op-google-recaptcha'); ?>
    <table class="form-table">
        <tr class="form-required">
            <th scope="row">
                <?php _e('Google ReCaptcha Sitekey', 'op-google-recaptcha'); ?>
            </th>
            <td>
                <input name="op_google_recaptcha_sitekey" type="text" size="100" value="<?php echo get_option("op_google_recaptcha_sitekey"); ?>">
            </td>
        </tr>
        <tr class="form-required">
            <th scope="row">
                <?php _e('Google ReCaptcha Secret', 'op-google-recaptcha'); ?>
            </th>
            <td>
                <input name="op_google_recaptcha_secret" type="text" size="100" value="<?php echo get_option("op_google_recaptcha_secret"); ?>">
            </td>
        </tr>
        <tr class="form">
            <th scope="row">
                <?php _e('Hide ReCaptcha Logo ', 'op-google-recaptcha'); ?>
            </th>
            <td>
                <?php
                    $recaptcha_logo = get_option("op_google_recaptcha_hide_logo");
                    $recaptcha_logo_checked = '';
                    if (isset($recaptcha_logo) && !empty($recaptcha_logo)) {
                        $recaptcha_logo_checked = ' checked="checked"';
                    }
                ?>
                <input name="op_google_recaptcha_hide_logo" type="checkbox" <?php echo $recaptcha_logo_checked; ?>>
                <p>
                    <em>Make sure you test your site before you hide the logo, because error messages related to your Google ReCaptcha API key are shown in Google ReCaptcha logo container.</em>
                </p>
            </td>
        </tr>
    </table>
    <?php submit_button(__('Save', 'op-google-recaptcha'), 'primary', 'google_recaptcha'); ?>
    <script>
        ;(function ($) {
            $('#op_google_recaptcha_admin_form').on('submit', function () {
                op_show_loading();
                OptimizePress.ajax.clearElementsCache().then(function(response) {
                    $(this).submit();
                });
            });
        }(opjq));
    </script>
    </form>
</div>
