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
            if(msg['success'] == true) {
                var html = "<ul error>";
                html += "<li>" + msg['message'] + "</li>";
                html += "</ul>";
            }
            else {
                var html = "<ul success>";
                html += "<li>" + msg['message'] + "</li>";
                html += "</ul>";
            }
            document.getElementById('body').innerHTML += html
        },
        error: function (xmlHttpRequest, textStatus, errorThrown) {
            alert(errorThrown.toString());
            alert('error');
            alert(textStatus)
        }
        });

});