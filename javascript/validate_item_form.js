let rules;

$(async function() {
    const success = await getRules();

    if (success) {
        initialize();
    }
})

async function getRules() {
    try {
        rules = await $.getJSON("items/get_rules_service");
        return true;
    } catch (error) {
        console.error("Erreur lors de la récupération des règles métiers :", error);
        return false;
    }
}

function initialize() {
    const $title = $("#title");
    const $description = $("#description");
    const $durationDays = $("#duration_days");
    const $startingBid = $("#starting_bid");
    const $instantPurchase = $("#instant_purchase");
    const $salePrice = $("#sale_price");
    const $titleError = $("#title_error");
    const $descriptionError = $("#description_error");   
    const $durationDaysError = $("#duration_days_error");
    const $startingBidError = $("#starting_bid_error");
    const $instantPurchaseError = $("#instant_purchase_error");
    const $salePriceError = $("#sale_price_error");

    const $itemId = $("form").data("item-id");

    if ($title.length > 0) {
        $title.on("blur", () => {
            let isLengthValid = validateLength($title, rules.title.min, rules.title.max);
            
            if (!isLengthValid) {
                setInvalid($title, $titleError, `Title must be between ${rules.title.min} and ${rules.title.max} characters.`);
            } else {
                fetch("items/is_title_unique_service", {
                    method: "POST",
                    headers: {
                        "Content-type": "application/json"
                    },
                    body: JSON.stringify({
                        title: $title.val(),
                        itemId: $itemId
                    })
                }).then(res => res.json())
                .then(data => {
                    if (!data.isUnique) {
                        setInvalid($title, $titleError, `An item with this title already exists.`);
                    } else {
                        setValid($title, $titleError);
                    }
                });
            }
        });
    }

    if ($description.length > 0) {    
        $description.on("blur", () => {
            if (isEmpty($description)) {
                resetField($description, $descriptionError);
            } else {
                let isLengthValid = validateLength($description, rules.description.min, null);
                
                if (!isLengthValid) {
                    setInvalid($description, $descriptionError, `Description must be at least ${rules.description.min} characters.`);
                } else {
                    setValid($description, $descriptionError);
                }
            }
        });
    }

    if ($durationDays.length > 0) {
        $durationDays.on("blur", () => {
            let isDurationValid = isBetween($durationDays, rules.durationDays.min, rules.durationDays.max, true);            
            
            if (!isDurationValid) {
                setInvalid($durationDays, $durationDaysError, `Sale Duration must be a whole number between ${rules.durationDays.min} and ${rules.durationDays.max} days.`);
            } else {
                setValid($durationDays, $durationDaysError);
            }
        });
    }

    if ($startingBid.length > 0) {
        const priceRegex = new RegExp(rules.price.regex);
        $startingBid.on("blur", () => {
            if (isEmpty($startingBid)) {
                resetField($startingBid, $startingBidError);

                if (!isEmpty($instantPurchase)) {
                    $instantPurchase.trigger("blur");
                }

                if (!isEmpty($salePrice)) {
                    $salePrice.trigger("blur");
                }
            } else {
                let isFormatValid = validateRegex($startingBid, priceRegex);
                let isSellingModeValid = validateSellingMode($startingBid, $salePrice);

                if (!isFormatValid) {
                    setInvalid($startingBid, $startingBidError, `Starting bid must be a positive number (up to 8 digits) with no more than two decimal places.`);
                } else if (!isSellingModeValid) {
                    setInvalid($startingBid, $startingBidError, `Cannot list as both an auction and a direct sale simultaneously.`);
                    setInvalid($salePrice, $salePriceError, `Cannot list as both an auction and a direct sale simultaneously.`);
                } else {
                    setValid($startingBid, $startingBidError);
                    
                    if (isEmpty($salePrice)) {
                        resetField($salePrice, $salePriceError);
                    }
                    
                    if (!isEmpty($instantPurchase)) {
                        $instantPurchase.trigger("blur");
                    }
                }
            }
        });
    }

    if ($instantPurchase.length > 0) {
        const priceRegex = new RegExp(rules.price.regex);
        $instantPurchase.on("blur", () => {
            if (isEmpty($instantPurchase)) {
                resetField($instantPurchase, $instantPurchaseError);

                if (!isEmpty($startingBid)) {
                    $startingBid.trigger("blur");
                }

                if (!isEmpty($salePrice)) {
                    $salePrice.trigger("blur");
                }
            } else {
                let isFormatValid = validateRegex($instantPurchase, priceRegex);
                let isSellingModeValid = validateSellingMode($instantPurchase, $salePrice);
                let hasStartingBid = !isEmpty($startingBid);
                let isAuctionValid = validateAuction($startingBid, $instantPurchase);

                if (!isFormatValid) {
                    setInvalid($instantPurchase, $instantPurchaseError, `Instant purchase must be a positive number (up to 8 digits) with no more than two decimal places.`);
                } else if (!isSellingModeValid) {
                    setInvalid($instantPurchase, $instantPurchaseError, `Cannot offer instant purchase on direct sale items.`);
                    setInvalid($salePrice, $salePriceError, `Cannot offer instant purchase on direct sale items.`);
                } else if (!hasStartingBid) {
                    setInvalid($instantPurchase, $instantPurchaseError, `A starting bid is required to offer an instant purchase.`);
                } else if (!isAuctionValid) {
                    setInvalid($instantPurchase, $instantPurchaseError, `Instant purchase must be higher than starting bid.`);
                } else {
                    setValid($instantPurchase, $instantPurchaseError);

                    if (isEmpty($salePrice)) {
                        resetField($salePrice, $salePriceError);
                    }
                }
            }
        });
    }

    if ($salePrice.length > 0) {
        const priceRegex = new RegExp(rules.price.regex);
        $salePrice.on("blur", () => {
            if (isEmpty($salePrice)) {
                resetField($salePrice, $salePriceError);

                if (!isEmpty($startingBid)) {
                    $startingBid.trigger("blur");
                }

                if (!isEmpty($instantPurchase)) {
                    $instantPurchase.trigger("blur");
                }
            } else {
                let isFormatValid = validateRegex($salePrice, priceRegex);
                let isStartingBidSellingModeValid = validateSellingMode($startingBid, $salePrice);
                let isInstantPurchaseSellingModeValid = validateSellingMode($instantPurchase, $salePrice);

                if (!isFormatValid) {
                    setInvalid($salePrice, $salePriceError, `Sale price must be a positive number (up to 8 digits) with no more than two decimal places.`);
                } else if (!isStartingBidSellingModeValid) {
                    setInvalid($salePrice, $salePriceError, `Cannot list as both an auction and a direct sale simultaneously.`);
                    setInvalid($startingBid, $startingBidError, `Cannot list as both an auction and a direct sale simultaneously.`);
                } else if (!isInstantPurchaseSellingModeValid) {
                    setInvalid($salePrice, $salePriceError, `Cannot list an instant purchase and a direct sale simultaneously.`);
                    setInvalid($instantPurchase, $instantPurchaseError, `Cannot list an instant purchase and a direct sale simultaneously.`);
                } else {
                    setValid($salePrice, $salePriceError);

                    if (isEmpty($startingBid)) {
                        resetField($startingBid, $startingBidError);
                    }

                    if (isEmpty($instantPurchase)) {
                        resetField($instantPurchase, $instantPurchaseError);
                    }
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

function isBetween(field, minValue, maxValue, isInteger) {
    const fieldValue = Number(field.val());
    if (isInteger && !Number.isInteger(fieldValue)) {
        return false;
    }
    return fieldValue >= minValue && fieldValue <= maxValue;
}

function validateAuction(firstField, secondField) {
    if (isEmpty(firstField) || isEmpty(secondField)) {
        return true;
    }
    return Number(firstField.val()) < Number(secondField.val());
}

function validateSellingMode(firstField, secondField) {
    return isEmpty(firstField) || isEmpty(secondField);
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