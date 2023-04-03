define(['jquery', 'TYPO3/CMS/Backend/Icons', 'TYPO3/CMS/Backend/Notification', 'TYPO3/CMS/Core/Ajax/AjaxRequest', 'TYPO3/CMS/Core/Ajax/AjaxResponse'], function ($, Icons, Notification, AjaxRequest, AjaxResponse) {

    let BackendModifyListTable;
    return BackendModifyListTable = {

        init: (tableName) => {

            BackendModifyListTable.styleRows(tableName)

            $(document).on('click', '.t3js-record-public', (e) => {
                e.preventDefault();
                const $anchorElement = $(e.currentTarget);
                const $iconElement = $anchorElement.find('.t3js-icon');
                const $rowElement = $anchorElement.closest('tr[data-uid]');
                const params = $anchorElement.data('params');

                // add a spinner
                Icons.getIcon('spinner-circle-dark', Icons.sizes.small).then(icon => {
                    $iconElement.replaceWith(icon);
                });

                (new AjaxRequest(TYPO3.settings.ajaxUrls.record_process))
                    .withQueryArguments(params)
                    .get()
                    .then(() => {

                        const $iconElement = $anchorElement.find('.t3js-icon');

                        if ($anchorElement.data('public') === 'no') {
                            const nextState = 'yes';
                            const nextParams = params.replace('=1', '=0');
                            const iconName = 'actions-edit-hide';
                            $rowElement.removeClass('table-info');
                            $anchorElement.data('public', nextState).data('params', nextParams);
                            Icons.getIcon(iconName, Icons.sizes.small).then(icon => {
                                $iconElement.replaceWith(icon);
                            });
                        } else {
                            const nextState = 'no';
                            const nextParams = params.replace('=0', '=1');
                            const iconName = 'actions-edit-unhide';
                            $rowElement.addClass('table-info');
                            $anchorElement.data('public', nextState).data('params', nextParams);
                            Icons.getIcon(iconName, Icons.sizes.small).then(icon => {
                                $iconElement.replaceWith(icon);
                            });
                        }

                        const newTitle = $anchorElement.attr('data-toggle-title');
                        $anchorElement.attr('data-toggle-title', $anchorElement.attr('data-original-title'));
                        $anchorElement.attr('data-original-title', newTitle);
                        $anchorElement.tooltip('hide');

                    })
            })
        },

        styleRows: function (tableName) {
            $('tr.t3js-entity[data-table="' + tableName + '"]').each(function (i, e) {
                const isConfirmed = $(e).find('.t3js-record-public').attr('data-public');
                if (isConfirmed === 'no') $(e).addClass('table-info');
            });
        }
    }
})
