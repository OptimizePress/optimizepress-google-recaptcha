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
    <p><?php _e('Sign-up for invisible <a href="https://www.google.com/recaptcha/" target="_blank">Google ReCaptcha</a> and enter site key & secret in order to apply it to all opt-in forms.', 'op-google-recaptcha'); ?>
    <p><?php _e('Make sure you clean up <a href="' . admin_url('admin.php?page=optimizepress-support#cache') . '">OptimizePress Cache</a> after setting up Google ReCaptcha keys.', 'op-google-recaptcha'); ?>
    <form method="post">
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
    </table>
    <?php submit_button(__('Save', 'op-google-recaptcha'), 'primary', 'google_recaptcha'); ?>
    </form>
</div>
