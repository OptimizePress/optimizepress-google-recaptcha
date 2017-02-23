window.opGoogleReCaptchaLastForm = null;
window.opGoogleReCaptcha = function() {
    var recaptchas = document.querySelectorAll('.op-g-recaptcha');
    for (var i = 0; i < recaptchas.length; i++) {
        var form = recaptchas[i].parentNode;
        grecaptcha.render(recaptchas[i].id, {
            sitekey: recaptchas[i].getAttribute('data-sitekey'),
            size: recaptchas[i].getAttribute('data-size'),
            callback: function(response){
                if (window.opGoogleReCaptchaLastForm){
                    window.opGoogleReCaptchaLastForm.querySelector('.g-recaptcha-response').value = response;
                    window.opGoogleReCaptchaLastForm.submit();
                }
            }
        });

        var submitButton = null;
        if (recaptchas[i].parentNode.querySelector(".default-button")){
            submitButton = recaptchas[i].parentNode.querySelector(".default-button");
        } else if (recaptchas[i].parentNode.querySelector(".css-button")){
            submitButton = recaptchas[i].parentNode.querySelector(".css-button");
        }
        submitButton.onclick = opGoogleReCaptchaValidate;
    }
};

window.opGoogleReCaptchaValidate = function () {
    window.opGoogleReCaptchaLastForm = this.form;
    event.preventDefault();
    grecaptcha.execute(window.opGoogleReCaptchaLastForm.id);
};