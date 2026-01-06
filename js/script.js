document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('signForm')) {
        initValidations();
    }  
});



// VALIDAZIONE DEL FORM DI REGISTER
function initValidations() {
    const form = document.getElementById('signForm');

    const regole = [
        ['username', /^[a-zA-Z0-9_]{4,20}$/, "4-20 caratteri (lettere, numeri, underscore)", "Username non valido"],
        ['email', /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/, "Inserisci un'email valida, es: aala@ajmai.me", "Email non valida"],
        ['nome', /^[A-Za-zÀ-ÿ\s']{2,30}$/, "Solo lettere, minimo 2 caratteri, es: Mario", "Nome non valido"],
        ['cognome', /^[A-Za-zÀ-ÿ\s']{2,30}$/, "Solo lettere, minimo 2 caratteri, es: Rossi", "Cognome non valido"],
        ['password', /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/, "Minimo 8 caratteri con almeno una maiuscola e un numero", "Password debole"],
        ['dataNascita', /^\d{4}-\d{2}-\d{2}$/, "Inserisci la tua data di nascita", "Data non valida"]
    ];


    function validaCampo(id, regex, testoAiuto, testoErrore) {
        const input = document.getElementById(id);
        const span = document.getElementById('aiuto-' + id);
    
        if (input.value === "") {
            span.textContent = testoAiuto;
            span.className="aiuto";
            input.classList.remove("input-error");
        } else if (!regex.test(input.value)) {
            span.textContent = testoErrore;
            span.className = "aiuto error";
            input.classList.add("input-error");
        } else {
            // Stato Valido
            span.textContent = "";
            span.className = ""; 
            input.classList.remove("input-error");
        }
    }

    regole.forEach(([id, regex, aiuto, errore]) => {
        const input = document.getElementById(id);
        if (input) {
            validaCampo(id, regex, aiuto, errore);
            input.addEventListener('input', () => validaCampo(id, regex, aiuto, errore));
        }
    });

    const p2 = document.getElementById('pass_again');
    const spanP2 = document.getElementById('aiuto-pass_again');
    if (p2 && spanP2) {
        p2.addEventListener('input', function() {
            const p1 = document.getElementById('password');
            if (this.value === "") {
                spanP2.textContent = "Ripeti la password scelta";
                spanP2.className = "aiuto";
            } else if (this.value !== p1.value) {
                p2.classList.add("input-error");
                spanP2.textContent = "Le password non coincidono";
                spanP2.className = "aiuto error";
            } else {
                p2.classList.remove("input-error");
                spanP2.className = "";
                spanP2.textContent = "";

            }
        });
    }

    form.addEventListener('submit', function(e) {
        regole.forEach(campo => validaCampo(campo[0], campo[1], campo[2], campo[3], campo[4], campo[5]));
        if (document.querySelectorAll('.aiuto.errore').length > 0) {
            e.preventDefault();
        }
    });
}

