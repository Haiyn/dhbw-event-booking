/*
    Only enable the register button if username, email and password are filled
 */
$(document).ready(function() {
    $('#js-register-username, #js-register-email, #js-register-password').bind('keyup', function()
    {
        if(requiredFieldsFilled())
        {
            $('#js-register-submit').removeAttr('disabled');
        }
    });
});

/*
    Checks if the required fields are filled
    Returns true when all are filled, returns false if not
 */
function requiredFieldsFilled()
{
    let filled = false;

    // Check if all fields are filled
    if(document.getElementById("js-register-username").value !== ""
        && document.getElementById("js-register-email").value !== ""
        && document.getElementById("js-register-password").value !== "")
    {
        filled = true;
    }

    return filled;
}