define(['jquery', 'TYPO3/CMS/Core/Ajax/AjaxRequest', 'TYPO3/CMS/Backend/Modal'], function ($, AjaxRequest, Modal) {

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

            // Init record preview
            const tds = document.querySelectorAll('#recordlist-tx_bwguild_domain_model_offer td.col-title')
            tds.forEach(td => {
                const span = document.createElement('a');
                span.setAttribute('href', '#')
                span.innerHTML = td.innerHTML
                span.addEventListener('click', e => {
                    e.preventDefault()

                    const uid = e.currentTarget.closest('tr').getAttribute('data-uid')
                    const table = e.currentTarget.closest('tr').getAttribute('data-table')

                    Modal.advanced({
                        type: Modal.types.ajax,
                        title: 'Preview',
                        content: TYPO3.settings.ajaxUrls.tx_bwguild_preview + '&table=' + table + '&uid=' + uid,
                        size: Modal.sizes.large,
                    })
                })

                const newTd = td.cloneNode(true)
                newTd.innerHTML = ''
                newTd.appendChild(span)

                td.parentNode.replaceChild(newTd, td);
            })
        },

    }

    return BackendOffer.init();
});
