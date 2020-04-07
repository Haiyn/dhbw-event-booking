; <?php die() ?>
[Application]
URL             = "localhost:8080"

[Database]
DB_TYPE         = "pgsql"
DB_HOST         = "pg-database"
DB_PORT         = "5432"
DB_NAME         = "event-booking"
DB_USER         = "dev"
DB_PASS         = "dev"

[Email]
; Enable sending of mails
; If false, critical information will be shown in the browser
EMAIL_ENABLED           = "true"

; Enable PHPMailer Mailing
; EMAIL_ENABLED must be true
PHPMAILER_ENABLED       = "true"

EMAIL_FROM_ADDRESS      = "noreply@dhbw-event-booking.com"
EMAIL_FROM_NAME         = "DHBW Event Booking"

; Use SMTP
EMAIL_IS_SMTP           = "true"
EMAIL_SMTP_HOST         = "smtp.gmail.com"

; Authorization for SMTP settings
; EMAIL_IS_SMTP must be true
EMAIL_IS_AUTH           = "true"
EMAIL_SMTP_USERNAME     = ""
EMAIL_SMTP_PASSWORD     = ""

; Encrypt SMTP
; EMAIL_IS_SMTP must be true
EMAIL_IS_ENCRYPTED      = "true"



[Security]
AUTH_SALT       = "0~y802M]fWH>J]=C7>OlniyMU]>yxCt#-j(r@K37D)B{18yh9 x#@+6Y[@U4Tc,{"

