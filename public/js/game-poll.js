function poll() {
    $.ajax({
        type: 'POST',
        url: url,
        dataType: 'JSON',
        data: $(`#${formId}`).serialize(),
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            document.getElementById("body").innerHTML = response.html; 
            console.log(successMsg); 

            // Temp solution maybe
            if (!document.getElementById(`${formId}`)) {
                location.replace(`/${url}`); 
            }
        },
        error: function () {
            console.log(`${errorMsg}`); 
        }
    });
}

$(document).ready(function () {
    setInterval(poll, 5000);
}); 