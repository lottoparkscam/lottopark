<?php
    $token = Security::htmlentities($post_data['token']);
    
    $shopper_result_url = Security::htmlentities($post_data['shopper_result_url']);
    $brands = Security::htmlentities($post_data['brands']);
    $script_url = Security::htmlentities($post_data['script_url']);

    $item_number = $token;
    $item_description = _("Transaction ") . $token;
    $total_formatted = Security::htmlentities($post_data['total_formatted']);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Truevo CC - <?= $token; ?></title>
        <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600&display=swap" rel="stylesheet">
        <style>
            html {
                font-size: 10px;
            }

            body {
                font-family: "Source Sans Pro", Arial, Helvetica, sans-serif;
                background-color: white;
                font-size: 1.7rem;
            }

            h1 {
                font-size: 3.2rem;
                text-align: left;
                color: #303030;
            }

            .container {
                max-width: 30em;
                margin: 0px auto 4rem;
                color: #737373;
            }

            .description-content {
                background-color: #f6f6f5;
                border-radius: 10px;
                padding: 1rem 2rem;
            }

            .description-head {
                text-align: center;
                margin: 3rem 0px;
            }
            
            .row {
                padding: 8px 0px;
            }
            
            .column-left {
                float: left;
                width: 33%;
            }
            
            .column-right {
                width: auto;
                text-align: right;
            }
            
            .description-foot {
                padding: 8px 0px;
            }
            
            .back-div {
                text-align: left !important;
                margin-top: 12px;
            }
            
            a {
                color: blue;
            }

            a:link {
                text-decoration: underline;
            }

            a:visited {
                text-decoration: underline;
            }

            a:hover {
                text-decoration: underline;
            }

            a:active {
                text-decoration: underline;
            }

            .wpwl-button-pay {
                background-color: #67bd2e;
                border-radius: 5px;
                border: none;
            }

            .wpwl-button-pay:hover {
                background-color: #5caa29;
                border:none;
            }

            .wpwl-button-pay:focus,
            .wpwl-button-pay:active:focus {
                outline: none;
            }

            select.wpwl-control,
            input.wpwl-control,
            input, .wpwl-control-cardNumber,
            input, .wpwl-control-cvv {
                border-radius: 5px;
                font-family: Arial;
                font-size: 15px !important;
                line-height: 15px;
                box-shadow: 3px 3px 3px -2px #ccc;
                -webkit-box-shadow: 3px 3px 3px -2px #ccc;
                -moz-box-shadow: 3px 3px 3px -2px #ccc;
            }

            .wpwl-group {
                min-height: 35px;
            }

            .wpwl-label {
                color: #737373;
            }

            .wpwl-sup-wrapper-state,
            .wpwl-sup-wrapper-street2 {
                display: none;
            }

            @media only screen and (max-width: 576px) {
                html {
                    font-size: 8px;
                    width: 95%;
                    margin:auto;
                }
            }

            @media only screen and (max-width: 480px) {
                .column-left, .column-right {
                    float:none;
                    width: 100%;
                    text-align: left;
                    font-size: 16px;
                }

                .wpwl-label {
                    margin: 4px 0;
                    font-size: 16px;
                }

                .wpwl-label-brand, .wpwl-wrapper-brand {
                    padding-right: 13px;
                }

                .wpwl-button-pay {
                    font-size: 16px;
                }

                select.wpwl-control,
                input.wpwl-control,
                input, .wpwl-control-cardNumber,
                input, .wpwl-control-cvv {
                    margin-bottom: 4px;
                }
            }
        </style>
    </head>
    <body>
        <section class="container">
            <div class="description-head">
                <h1><?= _("Your order summary"); ?></h1>
            </div>
            <div class="description-content">
                <div class="row">
                    <div class="column-left">
                        <?= _("Item number") ?>:
                    </div>
                    <div class="column-right">
                        <b><?= $item_number; ?></b>
                    </div>
                </div>
                <div class="row">
                    <div class="column-left">
                        <?= _("Item description"); ?>:
                    </div>
                    <div class="column-right">
                        <b><?= $item_description; ?></b>
                    </div>
                </div>
                <div class="description-foot">
                    <div class="column-left">
                        <?= _("Total"); ?>:
                    </div>
                    <div class="column-right">
                        <b><?= $total_formatted; ?></b>
                    </div>
                </div>
            </div>
        </section>
        
        <form action="<?= $shopper_result_url; ?>" 
              class="paymentWidgets" 
              data-brands="<?= $brands; ?>"></form>
        
        <script>
            var wpwlOptions = {
                style:"plain",
                iframeStyles: {
                    'card-number-placeholder': {
                        'color': '#737373',
                        'font-size': '14px',
                        'font-family': 'Arial'
                    },
                        'cvv-placeholder': {
                        'color': '#737373',
                        'font-size': '14px',
                        'font-family': 'Arial'
                    }
                },               
                billingAddress: {
                    country: "<?php echo $user_data['country'] ?>",
                    city: "<?php echo $user_data['city'] ?>",
                    postcode: "<?php echo $user_data['zip'] ?>",
                    street1: "<?php echo $user_data['address_1'] ?>",
                },
                mandatoryBillingFields:{
                    country: true,
                    city: true,
                    state: false,
                    postcode: true,
                    street1: true,
                    street2: false
                },
                onReady: () => {
                    const fieldsSet = [
                        {
                            inputName: 'card.holder',
                            inputWrapperClass: 'wpwl-wrapper-cardHolder',
                            errorClass: 'wpwl-hint-cardHolderError'
                        },
                        {
                            inputName: 'billing.street1',
                            inputWrapperClass: 'wpwl-sup-wrapper-street1',
                            errorClass: 'wpwl-hint-billingStreetError'
                        }
                    ];

                    addListeners(fieldsSet);
                }
            }

            function addListeners(fieldsSet)
            {
                fieldsSet.forEach(fieldSet => {
                    const {inputName, inputWrapperClass, errorClass} = fieldSet;
                    const inputs = document.getElementsByName(inputName);

                    if (inputs.length <= 0) {
                        return;
                    }

                    inputs[0].addEventListener('input', event => {validateField(event, errorClass, inputWrapperClass)});
                    inputs[0].addEventListener('change', event => {validateField(event, errorClass, inputWrapperClass)});
                    inputs[0].addEventListener('blur', event => {validateField(event, errorClass, inputWrapperClass)});
                });
            }

            function validateField(event, errorClass, inputWrapperClass)
            {
                const inputWrapper = document.getElementsByClassName(inputWrapperClass)[0];
                const payButton = document.getElementsByClassName('wpwl-button-pay')[0];
                const errorDiv = document.getElementsByClassName(errorClass)[0];
                const message = 'This field contains invalid character';

                // check if its any non 8859 character
                if (/[^\u0000-\u00ff]/g.test(event.target.value)) {
                    // add red border to input
                    event.target.classList.add('wpwl-has-error');

                    // disable main pay button
                    payButton.disabled = true;
                    payButton.classList.add('wpwl-button-error');

                    // add message
                    if (!errorDiv) {
                        const newErrorDiv = document.createElement('div');
                        newErrorDiv.classList.add('wpwl-hint');
                        newErrorDiv.classList.add(errorClass);
                        newErrorDiv.innerHTML = message;
                        inputWrapper.appendChild(newErrorDiv);
                    } else {
                        errorDiv.innerHTML = message;
                    }
                }
            }
        </script>
        <script src="<?= $script_url; ?>"></script>
    </body>
</html>


