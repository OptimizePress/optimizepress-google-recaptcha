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
        var recaptchas = document.querySelectorAll('.op-g-recaptcha');
        var i = 0;

        for (i = 0; i < recaptchas.length; i++) {
            // var form = recaptchas[i].parentNode;
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
            // if (typeof recaptchas[i] !== 'undefined') {
                // recaptchas[i].parentNode.addEventListener('submit', opGoogleReCaptchaValidate);
            // }
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
        grecaptcha.execute(window.opGoogleReCaptchaLastForm.id);
        grecaptcha.execute(e.target.id);
    }

    // We add this check to the list of default OptimizePress deferreds
    // that have to be resolved before the form can be submitted
    OptimizePress._validationDeferreds = OptimizePress._validationDeferreds || [];
    OptimizePress._validationDeferreds.push(opGoogleReCaptchaValidation);

}(opjq));