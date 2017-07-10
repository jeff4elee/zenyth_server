$('#Submit').on('click', function() {
    $.ajax({
        method: 'POST',
        url: url,
        data: {
            password: $('#password').val(),
            password_confirmation: $('#password_confirmation').val(),
            _token: token
        },
        success: function(msg) {
            var html = "<li error>" + msg['message'] + "</li>";
            document.getElementById('body').innerHTML += html
        },
        error: function (xmlHttpRequest, textStatus, errorThrown) {
            alert(errorThrown.toString());
            alert('error');
            alert(textStatus)
        }
        });

});