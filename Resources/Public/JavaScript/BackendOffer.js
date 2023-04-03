define(['jquery'], function ($) {

    const BackendOffer = {

        init: () => {
            BackendOffer.bindListener();
        },

        bindListener: () => {

            // Init accordions
            const links = document.querySelectorAll('.module-body .card-container a')
            links.forEach(link => link.addEventListener('click', (e) => {
                e.preventDefault()
                const id = e.currentTarget.getAttribute('href').substring(1)

                // link active state
                links.forEach(link => link.classList.remove('active'))
                e.currentTarget.classList.add('active')

                // table view
                $('.module-body .tab-pane').hide();
                $('.module-body .tab-pane#' + id).show();
            }))
        },

    }

    return BackendOffer.init();
});
