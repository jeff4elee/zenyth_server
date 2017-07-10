$('#Submit').on('click', function() {
    alert('hi');
    $.ajax({
        method: 'POST',
        url: url,
        data: {
            password: $('#password').val(),
            password_confirmation: $('#password_confirmation').val(),
            _token: token
        },
        success: function(msg) {
            var html = null;
            if(msg['success'] == true) {
                html = "<br>";
                html += "<div class=\"success\">";
                html += msg['message'];
                html += "</div>";
            }
            else {
                html = "<br>";
                html += "<div class=\"error\">";
                html += msg['message'];
                html += "</div>";
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