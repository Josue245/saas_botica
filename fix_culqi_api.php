<?php
$path = 'resources/views/billing/checkout.blade.php';
$c = file_get_contents($path);

// Reemplazar el bloque de script completo
$old = '{{-- Culqi.js --}}
<script src="https://checkout.culqi.com/js/v4"></script>
<script>
    // Configurar llave pública de Culqi
    Culqi.publicKey = \'{{ $culqiPublicKey }}\';
    Culqi.settings({
        title: \'Mi Botica\',
        currency: \'PEN\',
        amount: {{ (int)($plan->precio_mensual * 100) }},
        order: null,
    });

    // Formatear número de tarjeta
    document.getElementById(\'card-number\').addEventListener(\'input\', function(e) {
        let val = e.target.value.replace(/\D/g, \'\').substring(0, 16);
        e.target.value = val.match(/.{1,4}/g)?.join(\' \') ?? val;
    });

    // Formatear vencimiento
    document.getElementById(\'card-expiry\').addEventListener(\'input\', function(e) {
        let val = e.target.value.replace(/\D/g, \'\').substring(0, 4);
        if (val.length >= 2) val = val.substring(0,2) + \'/\' + val.substring(2);
        e.target.value = val;
    });

    document.getElementById(\'btn-pagar\').addEventListener(\'click\', function() {
        const number  = document.getElementById(\'card-number\').value.replace(/\s/g, \'\');
        const expiry  = document.getElementById(\'card-expiry\').value.split(\'/\');
        const cvv     = document.getElementById(\'card-cvv\').value;
        const name    = document.getElementById(\'card-name\').value;

        if (!number || !expiry[0] || !expiry[1] || !cvv || !name) {
            alert(\'Por favor completa todos los datos de la tarjeta.\');
            return;
        }

        const btn = document.getElementById(\'btn-pagar\');
        btn.disabled = true;
        btn.textContent = \'Procesando...\';

        Culqi.createToken({
            card_number:      number,
            cvv:              cvv,
            expiration_month: expiry[0].padStart(2, \'0\'),
            expiration_year:  \'20\' + expiry[1],
            email:            \'{{ $tenant->email }}\',
        }).then(function(token) {
            document.getElementById(\'culqi_token\').value = token.id;
            document.getElementById(\'culqi-form\').submit();
        }).catch(function(error) {
            btn.disabled = false;
            btn.textContent = \'Pagar S/. {{ number_format($plan->precio_mensual, 2) }}\';
            alert(\'Error: \' + (error.merchant_message || error.user_message || \'Error al procesar tarjeta\'));
        });
    });
</script>';

$new = '{{-- Culqi.js v4 con API correcta --}}
<script src="https://js.culqi.com/checkout-js"></script>
<script>
    // Formatear número de tarjeta
    document.getElementById("card-number").addEventListener("input", function(e) {
        let val = e.target.value.replace(/\D/g, "").substring(0, 16);
        e.target.value = val.match(/.{1,4}/g)?.join(" ") ?? val;
    });

    // Formatear vencimiento
    document.getElementById("card-expiry").addEventListener("input", function(e) {
        let val = e.target.value.replace(/\D/g, "").substring(0, 4);
        if (val.length >= 2) val = val.substring(0,2) + "/" + val.substring(2);
        e.target.value = val;
    });

    document.getElementById("btn-pagar").addEventListener("click", async function() {
        const number = document.getElementById("card-number").value.replace(/\s/g, "");
        const expiry = document.getElementById("card-expiry").value.split("/");
        const cvv    = document.getElementById("card-cvv").value;
        const name   = document.getElementById("card-name").value;

        if (!number || !expiry[0] || !expiry[1] || !cvv || !name) {
            alert("Por favor completa todos los datos de la tarjeta.");
            return;
        }

        const btn = document.getElementById("btn-pagar");
        btn.disabled = true;
        btn.innerHTML = "Procesando...";

        try {
            const culqi = new Culqi({ publicKey: "{{ $culqiPublicKey }}" });

            const token = await culqi.createToken({
                card_number:      number,
                cvv:              cvv,
                expiration_month: expiry[0].padStart(2, "0"),
                expiration_year:  "20" + expiry[1],
                email:            "{{ $tenant->email }}",
            });

            if (token.object === "error") {
                throw new Error(token.merchant_message || token.user_message || "Error al tokenizar");
            }

            document.getElementById("culqi_token").value = token.id;
            document.getElementById("culqi-form").submit();

        } catch (error) {
            btn.disabled = false;
            btn.innerHTML = "Pagar S/. {{ number_format($plan->precio_mensual, 2) }}";
            alert("Error: " + (error.message || "Error al procesar la tarjeta"));
        }
    });
</script>';

$c = str_replace($old, $new, $c);
file_put_contents($path, $c);
echo "Checkout: Culqi API corregida OK\n";
