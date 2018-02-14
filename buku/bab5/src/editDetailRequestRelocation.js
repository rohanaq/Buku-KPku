$(document).ready(() => {
    const fields = ['activity', 'destinationWorkUnitCode', 'destinationWorkUnitName', 'destinationLocation'];
    const inputId = {
        activity: '#activity',
        destinationWorkUnitCode: '#destination_work_unit_code',
        destinationWorkUnitName: '#destination_work_unit_name',
        destinationLocation: '#destination_location'
    };

    let currentInput = {};
    let initInput = {};

    const getInitInput = () => {
        _.forEach(fields, (field) => {
            initInput[field] = $(inputId[field]).val();
        });
    };

    const getInput = () => {
      _.forEach(fields, (field) => {
         currentInput[field] = $(inputId[field]).val();
      });
    };

    const onInputChange = () => {
        getInput();
        updateDestinationStatus();
        getInput();
    };

    const updateDestinationStatus = () => {
        console.log($(inputId['activity']));
        if ($(inputId['activity']).val() === 'Relokasi') {
            $(inputId['destinationWorkUnitCode']).val(initInput['destinationWorkUnitCode']);
            $(inputId['destinationWorkUnitName']).val(initInput['destinationWorkUnitName']);
            $(inputId['destinationLocation']).val(initInput['destinationLocation']);

            $(inputId['destinationWorkUnitCode']).attr('disabled', false);
            $(inputId['destinationWorkUnitName']).attr('disabled', false);
            $(inputId['destinationLocation']).attr('disabled', false);

            $(".destination-label").css('color', 'black');
        } else {
            $(inputId['destinationWorkUnitCode']).val('');
            $(inputId['destinationWorkUnitName']).val('');
            $(inputId['destinationLocation']).val('');

            $(inputId['destinationWorkUnitCode']).attr('disabled', true);
            $(inputId['destinationWorkUnitName']).attr('disabled', true);
            $(inputId['destinationLocation']).attr('disabled', true);

            $(".destination-label").css('color', 'gray');
        }
    };

    getInitInput();
    onInputChange();

    $(inputId['activity']).change(() => {
       onInputChange();
    });
});