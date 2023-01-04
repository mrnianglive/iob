$(document).ready(function() {
  $("#tfa_code").submit(function(event) {
    event.preventDefault();

      var code = $("#tfa_code").val();

    $.ajax({
      url: '/config/check-code.php',
      type: 'POST',
      data: { code: code },
      success: function(response) {
        if (response.codeExists) {
          window.location.replace("/");
        } else {
          
            $_SESSION['message']['type'] = 'warning';
            $_SESSION['message']['text'] = 'Le code est incorrect,Try again  !';
            $_SESSION['message']['number'] = 2;
            window.location.replace("/connexion/doubleauth");

        }
      },
      error: function(error) {
        console.log(error);
      }
    });
  });
});
