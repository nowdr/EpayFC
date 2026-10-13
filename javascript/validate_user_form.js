let rules;

$(async function() {
    const success = await getRules();

    if (success) {
        initialize();
    }
})

async function getRules() {
    try {
        rules = await $.getJSON("user/get_rules_service");
        return true;
    } catch (error) {
        console.error("Erreur lors de la récupération des règles métiers :", error);
        return false;
    }
}

function initialize() {
    const $fullName = $("#full_name");
    const $email = $("#email");
    const $pseudo = $("#pseudo");
    const $password = $("#password");
    const $passwordConfirm = $("#password_confirm");
    const $iban = $("#iban");
    const $fullNameError = $("#full_name_error");
    const $emailError = $("#email_error");
    const $pseudoError = $("#pseudo_error");
    const $passwordError = $("#password_error");    
    const $passwordConfirmError = $("#password_confirm_error");
    const $ibanError = $("#iban_error");

    if ($fullName.length > 0) {
        $fullName.on("blur", () => {
            let isLengthValid = validateLength($fullName, rules.fullName.min, null);

            if (!isLengthValid) {
                setInvalid($fullName, $fullNameError, `Name must contain at least ${rules.fullName.min} characters.`);
            } else {
                fetch("user/is_full_name_unique_service", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        full_name: $fullName.val()
                    })
                }).then(res => res.json())
                .then(data => {
                    if (!data.isUnique) {
                        setInvalid($fullName, $fullNameError, `This name is already taken.`);
                    } else {
                        setValid($fullName, $fullNameError);
                    }
                });
            }
        })
    }

    if ($email.length > 0) {
        const emailRegex = new RegExp(rules.email.regex);
        $email.on("blur", () => {
            let isFormatValid = validateRegex($email, emailRegex);

            if (!isFormatValid) {
                setInvalid($email, $emailError, `Invalid email address format. Please use following format : \"local-part@domain.tld\".`);
            } else {
                fetch("user/is_email_unique_service", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        email: $email.val()
                    })
                }).then(res => res.json())
                .then(data => {
                    if (!data.isUnique) {
                        setInvalid($email, $emailError, `This email is already taken.`);
                    } else {
                        setValid($email, $emailError);
                    }
                });
            }
        });
    }

    if ($pseudo.length > 0) {
        $pseudo.on("blur", () => {
            let isLengthValid = validateLength($pseudo, rules.pseudo.min, rules.pseudo.max);

            if (!isLengthValid) {
                setInvalid($pseudo, $pseudoError, `Pseudo must be between ${rules.pseudo.min} and ${rules.pseudo.max} characters long.`);
            } else {
                fetch("user/is_pseudo_unique_service", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        pseudo: $pseudo.val()
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (!data.isUnique) {
                        setInvalid($pseudo, $pseudoError, `This pseudo is already taken.`);
                    } else {
                        setValid($pseudo, $pseudoError);
                    }
                });
            }
        });
    }
    
    if ($password.length > 0) {
        const passwordRegex = new RegExp(rules.password.regex);
        $password.on("blur", () => {
            let isFormatValid = validateRegex($password, passwordRegex);

            if (!isEmpty($passwordConfirm)) {
                $passwordConfirm.trigger("blur");
            }

            if (!isFormatValid) {
                setInvalid($password, $passwordError, `Password must contain at least 8 characters, one lowercase letter, one uppercase letter, one number and one special character.`);
            } else {
                setValid($password, $passwordError);
            }
        });
    }       

    if ($passwordConfirm.length > 0) {
        $passwordConfirm.on("blur", () => {
            let isMatchValid = validateMatch($passwordConfirm, $password);

            if (!isMatchValid) {
                setInvalid($passwordConfirm, $passwordConfirmError,`You have to enter twice the same password.`);
            } else {
                setValid($passwordConfirm, $passwordConfirmError);
            }
        });
    }

    if ($iban.length > 0) {
        const ibanRegex = new RegExp(rules.iban.regex);
        $iban.on("blur", () => {
            if (isEmpty($iban)) {
                resetField($iban, $ibanError);
            } else {
                let isFormatValid = validateRegex($iban, ibanRegex);

                if (!isFormatValid) {
                    setInvalid($iban, $ibanError, `Invalid IBAN format. Please use following format : \"BE99 9999 9999 9999 9999\".`);
                } else {
                    setValid($iban, $ibanError);
                }
            }
        });
    }
}

function isEmpty(field) {
    return field.val() === "";
}

function validateLength(field, minLength, maxLength) {
    const fieldLength = field.val().length;
    
    if (maxLength == null) {
        return fieldLength >= minLength;
    }
    return fieldLength >= minLength && fieldLength <= maxLength;
}

function validateRegex(field, regex) {
    return regex.test(field.val());
}

function validateMatch(field, matchField) {
    return field.val() === matchField.val();
}

function resetField(field, errorField) {
    field.removeClass("is-valid is-invalid");
    errorField.text("");
    checkFormValidity();
}

function setInvalid(field, errorField, errorMessage) {
    field.removeClass("is-valid").addClass("is-invalid");
    errorField.text(errorMessage);
    checkFormValidity();
}

function setValid(field, errorField) {
    field.removeClass("is-invalid").addClass("is-valid");
    errorField.text("");
    checkFormValidity();
}

function checkFormValidity() {
    $("button").prop("disabled", $("form").find(".is-invalid").length > 0);
}