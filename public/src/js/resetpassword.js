$(document).ready(function() {
    $('#Submit').click(function() {
        alert();
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
                    var html = "<div>" + msg['message']
                    "</div>";
                    document.documentElement.innerHTML = html;
                } else {
                    alert(msg['message']);
                }
            },
            error: function (xmlHttpRequest, textStatus, errorThrown) {
                alert(textStatus)
            }
        });

    });
});
