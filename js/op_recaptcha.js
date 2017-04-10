;(function ($) {

    // captchaValidatedDefered is deferred object that is resolved when
    // the captcha is solved. Once that happens we resolve
    // deferred from OptimizePress validation. And this
    // deferred is added to the list of
    // other validation deferreds
    var captchaValidatedDefered = $.Deferred();
    function opGoogleReCaptchaValidation(deferred) {
        captchaValidatedDefered.done(function(value) {
            deferred.resolve(value);
        });
    }

    window.opGoogleReCaptchaLastForm = null;

    window.opGoogleReCaptcha = function() {
        var recaptchas;
        var i = 0;

        // We're not using the captcha with callback function,
        // because other plugins / themes could load it
        // separately, which means that the callback
        // is never actually executed - and that
        // in turn blocks all forms.
        if (typeof window.recaptcha === 'undefined') {
            setTimeout(opGoogleReCaptcha, 500);
            return false;
        }

        recaptchas = document.querySelectorAll('.op-g-recaptcha');

        for (i = 0; i < recaptchas.length; i++) {
            grecaptcha.render(recaptchas[i].id, {
                sitekey: recaptchas[i].getAttribute('data-sitekey'),
                size: recaptchas[i].getAttribute('data-size'),
                callback: function(response){
                    if (window.opGoogleReCaptchaLastForm){
                        window.opGoogleReCaptchaLastForm.querySelector('.g-recaptcha-response').value = response;
                        captchaValidatedDefered.resolve(true);
                    }
                }
            });
        }

        // Add check on the form submit
        if (recaptchas.length > 0) {
            var optinForms = document.querySelectorAll('.op-optin-validation');
            for (i = 0; i < optinForms.length; i++) {
                optinForms[i].addEventListener('submit', opGoogleReCaptchaValidate);
            }
        } else {
            captchaValidatedDefered.resolve(true);
        }
    }

    window.opGoogleReCaptchaValidate = function (e) {
        e.preventDefault();
        window.opGoogleReCaptchaLastForm = e.target;
        // grecaptcha.execute(window.opGoogleReCaptchaLastForm.id);
        grecaptcha.execute(e.target.id);
    }

    // Initialize ReCaptcha
    window.opGoogleReCaptcha();

    // We add this check to the list of default OptimizePress deferreds
    // that have to be resolved before the form can be submitted
    OptimizePress._validationDeferreds = OptimizePress._validationDeferreds || [];
    OptimizePress._validationDeferreds.push(opGoogleReCaptchaValidation);

}(opjq));