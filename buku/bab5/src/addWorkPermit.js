$(document).ready(function () {
    const initials = JSON.parse($('data').attr('initials'));
    const relocationRequestInputId = '#relocation-request';
    const detailInputId = '#detail';
    const addButtonId = '#add-button';
    const detailOptionsClass = '.detail-options';
    const choosenListId = '#choosen-list';
    const detailsValueId = '#details';
    const submitButtonId = '#submit-button';

    let detailNameMapping = {};
    let currentRelocationRequest;
    let currentDetail;
    let taken;
    let currentChoosenDetail = [];

    const init = () => {
        taken = _.reduce(initials.relocationRequestDetails, (result, current) => {
            result[current.id] = false;

            return result;
        }, {});

        _.forEach(initials.relocationRequestDetails, (current) => {
           detailNameMapping[current.id] =  current.activity + ' ' + current.original_work_unit_code + ' ' + current.original_work_unit_name + ' ' + current.ip_address + ' ' + current.provider.provider_name;
        });
    }

    const getRequestRelocationValue = () => {
        currentRelocationRequest = $(relocationRequestInputId).val();
    }

    const getDetailValue = () => {
        currentDetail = $(detailInputId).val();
    }

    const renderDetailOptions = () => {
        $(detailOptionsClass)
            .find('option')
            .remove()
            .end()
            .append('<option value=""> --Pilih Detail Request Relokasi </option>');

        if (!_.isEmpty(currentRelocationRequest)) {
            _.forEach(initials.detailByRelocationRequest[currentRelocationRequest], (current) => {
                $(detailOptionsClass).append($('<option>', {
                    value: current.id,
                    disabled: taken[current.id],
                    text: detailNameMapping[current.id]
                }));
            });
        }
        updateSubmitButton();
    }

    const renderAddButton = () => {
        if (!_.isEmpty(currentDetail)) {
            $(addButtonId).attr('disabled', false);
        } else {
            $(addButtonId).attr('disabled', true);
        }
        updateSubmitButton();
    }

    const addDetail = () => {
        currentChoosenDetail.push(currentDetail);
        taken[currentDetail] = true;
        $(detailInputId).val("");
        getDetailValue();
        renderAddButton();
        renderDetailOptions();
    }

    const renderChoosenList = () => {
        $(choosenListId)
            .find('li')
            .remove()
            .end();

        _.forEach(currentChoosenDetail, (current) => {
            $(choosenListId).append($('<li>', {
                id: current,
                text: detailNameMapping[current]
            }));
        });
        updateDetailsValue();
        updateSubmitButton();
    }

    const updateDetailsValue = () => {
        $(detailsValueId).val(JSON.stringify(currentChoosenDetail));
    }

    const updateSubmitButton = () => {
        if (currentChoosenDetail.length > 0) {
            $(submitButtonId).attr('disabled', false);
        } else {
            $(submitButtonId).attr('disabled', true);
        }
    }


    init();

    $(relocationRequestInputId).change(() => {
        getRequestRelocationValue();
        renderDetailOptions();
    });

    $(detailInputId).change(() => {
        getDetailValue();
        renderAddButton();
    });

    $(addButtonId).click(() => {
        addDetail();
        renderChoosenList();
        return false;
    });
});