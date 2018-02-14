var showDetailForm = false;
var detailCounter = 0;
var relocationRequestDetails = [];
var currentRelocationDetailInput = {};
var relocationRequest = {};

var formId = {
    referenceNumber: '#relocation_request_reference_number',
    date: '#relocation_request_date_letter',
    regionalOffice: '#regional_office',
    activity: '#activity',    
    originalWorkUnitCode: '#original_work_unit_code',
    origWorkUnitName: '#original_work_unit_name',
    originalLocation: '#original_location',
    destinationWorkUnitCode: '#destination_work_unit_code',
    destinationWorkUnitName: '#destination_work_unit_name',
    destinationLocation: '#destination_location',
    ipAddress: '#ip_address',
    provider: '#provider',
    reason: '#reason_of_relocation_request'
}

var relocationRequestInput = ['referenceNumber', 'date', 'regionalOffice'];
var detailRelocationRequestInput = ['activity', 'originalWorkUnitCode', 'origWorkUnitName', 'originalLocation', 'destinationWorkUnitCode', 'destinationWorkUnitName', 'destinationLocation', 'ipAddress', 'provider', 'reason'];
var requestInputRequiredFields = ['referenceNumber', 'date', 'regionalOffice'];
var detailRequestInputRequiredFields = ['activity', 'originalWorkUnitCode', 'origWorkUnitName', 'originalLocation', 'destinationWorkUnitCode', 'destinationWorkUnitName', 'destinationLocation', 'ipAddress', 'provider'];
var nonRepotitionDetailRequestInputRequiredFields = ['activity', 'originalWorkUnitCode', 'origWorkUnitName', 'originalLocation', 'ipAddress', 'provider'];

var isDirty = {};

updateIsDirty = function() {
    _.forEach(relocationRequestInput, function(current) {
        isDirty[current] = relocationRequest[current] !== '' ? true : isDirty[current];
    });
    _.forEach(detailRelocationRequestInput, function(current) {
        isDirty[current] = currentRelocationDetailInput[current] !== '' ? true : isDirty[current];
    });   
}

isValidRelocationRequest = function() {
    var ret = true;
    _.forEach(requestInputRequiredFields, function(current) {     
        if (relocationRequest[current] === '') {
            ret = false;
            if (isDirty[current]) {
                $(formId[current] + '-error').show(10);
            }           
        } else {
            $(formId[current] + '-error').hide(10);
        }       
    });

    return ret;
}

isValidDetailRelocationRequest = function() {
    var ret = true;
    var requiredInput;
    if (currentRelocationDetailInput.activity === "Reposisi" || currentRelocationDetailInput.activity === "Instalasi") {
        requiredInput = nonRepotitionDetailRequestInputRequiredFields;        
    } else {
        requiredInput = detailRequestInputRequiredFields;
    }
    _.forEach(requiredInput, function(current) {        
        if (isDirty[current] && currentRelocationDetailInput[current] === '') {
            ret = false;
            $(formId[current] + '-error').show(10);
        } else if(currentRelocationDetailInput[current] === '') {
            ret = false;
        } else {
            $(formId[current] + '-error').hide(10);
        }        
    });

    return ret;
}

validateInput = function() {
    if (!isValidRelocationRequest()) {
        closeDetailForm();
        $('#add-detail-button').attr('disabled', true);
        $('#submit-detail-button').attr('disabled', true);
    } else if (!showDetailForm) {
        $('#add-detail-button').attr('disabled', false);
        $('#submit-detail-button').attr('disabled', false);
    }

    if (!isValidDetailRelocationRequest()) {        
        $('#submit-detail-button').attr('disabled', true);
    } else {        
        $('#submit-detail-button').attr('disabled', false);
    }
}

getRelocationRequestInput = function () {
    relocationRequestInput.forEach(function(currentInput) {
        relocationRequest[currentInput] = $(formId[currentInput]).val();
    });
};

setRelocationRequestInput = function () {
    relocationRequestInput.forEach(function(currentInput) {
        $(formId[currentInput]).val(relocationRequest[currentInput] ? relocationRequest[currentInput] : '');
    });
};

getCurrentRelocationDetail = function () {
    detailRelocationRequestInput.forEach(function(currentInput) {
        currentRelocationDetailInput[currentInput] = $(formId[currentInput]).val();
    });
};

setCurrentRelocationDetail = function () {
    $('#detail_reference_number').val(relocationRequest.referenceNumber ? relocationRequest.referenceNumber : '');
    detailRelocationRequestInput.forEach(function(currentInput) {
        $(formId[currentInput]).val(currentRelocationDetailInput[currentInput] ? currentRelocationDetailInput[currentInput] : '');
    });
};

inputChange = function () {      
    getRelocationRequestInput();
    getCurrentRelocationDetail();
    hideFieldByActivityType();    
    renderInput();
    updateIsDirty();
    validateInput(); 
    render();
};

addRelocationRequestDetail = function () {
    currentRelocationDetailInput.id = detailCounter;
    relocationRequestDetails.push(currentRelocationDetailInput);
    detailCounter++;
    currentRelocationDetailInput = {};
    renderInput();
};

hideFieldByActivityType = function () {
    if (currentRelocationDetailInput.activity === "Reposisi" || currentRelocationDetailInput.activity === "Instalasi") {
        currentRelocationDetailInput.destinationWorkUnitCode = '';
        currentRelocationDetailInput.destinationWorkUnitName = '';
        currentRelocationDetailInput.destinationLocation = '';

        $('#destination_work_unit_code').val('');
        $('#destination_work_unit_name').val('');
        $('#destination_location').val('');

        $('#destination_work_unit_code').attr('disabled', true);
        $('#destination_work_unit_code').attr('placeholder', '');
        $('#destination_work_unit_name').attr('disabled', true);
        $('#destination_work_unit_name').attr('placeholder', '');
        $('#destination_location').attr('disabled', true);
        $('#destination_location').attr('placeholder', '');                
    } else {
        $('#destination_work_unit_code').attr('disabled', false);
        $('#destination_work_unit_code').attr('placeholder', 'Contoh: 56212');
        $('#destination_work_unit_name').attr('disabled', false);
        $('#destination_work_unit_name').attr('placeholder', 'Contoh: KC Bogor Pajajaran');
        $('#destination_location').attr('disabled', false);
        $('#destination_location').attr('placeholder', 'Contoh: Bogor');        
    }
}

renderInput = function() {       
    setRelocationRequestInput();
    setCurrentRelocationDetail();
    if (isValidRelocationRequest()) {
        // Enable request relocation submit button
        $('#submit-relocation-request-button').attr('disabled', false);
        // Enable add detail button
        $('#add-detail-button').attr('disabled', false);
    }
};

renderRelocationDetailList = function() {
    var relocationDetailList = $('#request-relocation-table');    
    var tr = '<tr><th>Nomor Surat</th><th>Detail</th><th>Delete</th></tr>';
    relocationRequestDetails.forEach(function (currentRelocationDetail) {
        tr += '<td>'+currentRelocationDetail.originalWorkUnitCode+'</td>';
        tr = '<tr>' + tr + '</tr>';        
    });
    relocationDetailList.html(tr);
};

render = function () {
    renderRelocationDetailList();    
    if (showDetailForm) {        
        // Disable request relocation submit button
        $('#submit-relocation-request-button').attr('disabled', true);
        // Disable add detail button
        $('#add-detail-button').attr('disabled', true);
        // Show detail form
        $('#request-relocation-detail-form').show(500);
    } else {        
        if (isValidRelocationRequest()) {
            // Enable request relocation submit button
            $('#submit-relocation-request-button').attr('disabled', false);
            // Enable add detail button
            $('#add-detail-button').attr('disabled', false);
        }
        // Hide detail form
        $('#request-relocation-detail-form').hide(500);
    }    
    appendData();
};

closeDetailForm = function() {
    _.forEach(detailRelocationRequestInput, function(current) {
        currentRelocationDetailInput[current] = '';
        isDirty[current] = false;
        
    }); 
    showDetailForm = false;
    renderInput();
    render();
}

init = function() {
   _.forEach(relocationRequestInput, function(current) {
        relocationRequest[current] = '';
        isDirty[current] = false;
    });

    _.forEach(detailRelocationRequestInput, function(current) {
        currentRelocationDetailInput[current] = '';        
        isDirty[current] = false;
    });          
}

appendData = function() {
    var requestRelocationData = JSON.stringify(relocationRequest);
    var detailsData = JSON.stringify(relocationRequestDetails);

    $('#relocation_request_data').val(requestRelocationData);
    $('#details_data').val(detailsData);
}

$(document).ready(function () {
    init();    
    render();

    // When add detail button clicked
    $('#add-detail-button').click(function () {
        showDetailForm = true;
        render();
        return false;
    });

    // When cancel detail form button clicked
    $('#cancel-detail-button').click(function () {
        closeDetailForm();
        return false;
    });

    $('#submit-detail-button').click(function () {        
        return false;
    })

    $('#submit-detail-button').click(function () {
        if (isValidDetailRelocationRequest()) {
            showDetailForm = false;
            addRelocationRequestDetail();

            render();
        }
        return false;
    });

    $('.form-control').keyup(function () {
        inputChange();
    });

    $('.form-control').change(function () {
        inputChange();
    });
});