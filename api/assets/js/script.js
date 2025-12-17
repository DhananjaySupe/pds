function showLoader(target = 'body', loaderID = 'preloader', fill = false) {
    try {
        hideLoader(loaderID);
        const loaderHTML = `
            <div id="${loaderID}" class="preloader${fill ? ' fill' : ''}">
                <div class="loader">
                    <div class="loader-icon"></div>
                </div>
            </div>
        `;
        $(target).append(loaderHTML);
        $(`#${loaderID}`).fadeIn(300);
    } catch (error) {
        console.error('Error showing loader:', error);
    }
}
function hideLoader(loaderID = 'preloader') {
    try {
        const $loader = $(`#${loaderID}`);
        if ($loader.length) {
            $loader.fadeOut(300, function() {
                $(this).remove();
            });
        }
    } catch (error) {
        console.error('Error hiding loader:', error);
    }
}
window.addEventListener('load', function () {
	var forms = document.getElementsByClassName('needs-validation');
	var validation = Array.prototype.filter.call(forms, function (form) {
		form.addEventListener('submit', function (event) {
			if (form.checkValidity() === false) {
				event.preventDefault();
				event.stopPropagation();
				var errorElements = document.querySelectorAll("input:invalid, select:invalid");
				if(errorElements.length > 0 ){
					errorElements[0].focus();
				}
			} else {
				var submitButton = form.querySelector('button[type="submit"]');
				if (submitButton) {
					submitButton.setAttribute('disabled', true);
					var loadingText = submitButton.getAttribute('data-loading');
					if (loadingText) {
						submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>&nbsp; ' + loadingText;
					}
				}
			}
			form.classList.add('was-validated');
		}, false);
	});
}, false);