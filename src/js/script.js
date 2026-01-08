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

document.addEventListener('DOMContentLoaded', initReviewFilters);
document.addEventListener('DOMContentLoaded', initRequestFilters);

function initReviewFilters() {
    const voteSelect = document.getElementById('voteFilter');
    const catSelect  = document.getElementById('categoryFilter');

    if (voteSelect) voteSelect.addEventListener('change', applyReviewFilters);
    if (catSelect)  catSelect.addEventListener('change', applyReviewFilters);

    applyReviewFilters();
}

function initRequestFilters() {
    const statSelect = document.getElementById('stateFilter');
    const catSelect = document.getElementById('categoryFilter');

    if(statSelect) statSelect.addEventListener('change', applyRequestFilters);
    if(catSelect) catSelect.addEventListener('change', applyRequestFilters);
}

function getReviewFilterState() {
    const voteValue =
        document.getElementById('voteFilter')?.value ?? '0-10';

    const [minVote, maxVote] = voteValue.split('-').map(Number);

    const category = document.getElementById('categoryFilter')?.value?.toLowerCase() ?? 'all';

    return { minVote, maxVote, category };
}

function getRequestFilterState() {
    const status = document.getElementById('stateFilter')?.value?.toLowerCase() ?? 'all';
    const category = document.getElementById('categoryFilter')?.value?.toLowerCase() ?? 'all';

    return { status, category };
}

function getReviewCards() {
    return document.querySelectorAll('.reviewCard');
}

function getRequestCards() {
    return document.querySelectorAll('.requestCard');
}

function getReviewGrid() {
    return (
        document.getElementById('grigliaRecensioni') ?? document.querySelector('.reviewsGrid')
    );
}

function getRequestGrid() {
    return (
        document.getElementById('grigliaRichieste') ?? document.querySelector('.requestGrid')
    );
}

function getEmptyState() {
    return document.getElementById('filterEmptyState');
}

function matchesReviewVote(card, { minVote, maxVote }) {
    const rating = card.querySelector('[itemprop="ratingValue"]');
    if (!rating) return false;

    const vote = Number(rating.textContent.trim());
    return vote >= minVote && vote <= maxVote;
}

function matchesReviewCategory(card, { category }) {
    if (category === 'all') return true;

    const cat = card.querySelector('.reviewCategory');
    if (!cat) return false;

    return cat.textContent.trim().toLowerCase() === category;
}

function matchesRequestStatus(card, { status }) {
    if (status === 'all') return true;

    const cardStatus = card.dataset.status;
    if (!cardStatus) return false;

    return cardStatus.toLowerCase() === status;
}

function matchesRequestCategory(card, { category }) {
    if (category === 'all') return true;

    const cat = card.querySelector('.requestCategory');
    if (!cat) return false;

    return cat.textContent.trim().toLowerCase() === category;
}

function applyReviewFilters() {
    const state = getReviewFilterState();
    const cards = getReviewCards();

    let visibleCount = 0;

    cards.forEach(card => {
        const isVisible =
            matchesReviewVote(card, state) &&
            matchesReviewCategory(card, state);

        card.style.display = isVisible ? '' : 'none';
        if (isVisible) visibleCount++;
    });

    updateReviewEmptyState(visibleCount);
}

function applyRequestFilters() {
    const state = getRequestFilterState();
    const cards = getRequestCards();

    let visibleCount = 0;

    cards.forEach(card => {
        const isVisible =
            matchesRequestStatus(card, state) &&
            matchesRequestCategory(card, state);

        card.style.display = isVisible ? '' : 'none';
        if (isVisible) visibleCount++;
    });

    updateRequestEmptyState(visibleCount);
}

function updateReviewEmptyState(visibleCount) {
    const grid  = getReviewGrid();
    const empty = getEmptyState();

    if (!grid || !empty) return;

    if (visibleCount === 0) {
        grid.style.display  = 'none';
        empty.style.display = 'flex';
    } else {
        grid.style.display  = 'grid';
        empty.style.display = 'none';
    }
}

function updateRequestEmptyState(visibleCount) {
    const grid  = getRequestGrid();
    const empty = getEmptyState();

    if (!grid || !empty) return;

    if (visibleCount === 0) {
        grid.style.display  = 'none';
        empty.style.display = 'flex';
    } else {
        grid.style.display  = 'grid';
        empty.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', () => {

    //GESTIONE MENU HAMBURGER
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const menuWrapper = document.getElementById('menuWrapper');

    if (hamburgerBtn && menuWrapper) {
        hamburgerBtn.addEventListener('click', () => {
            menuWrapper.classList.toggle('active');

            hamburgerBtn.classList.toggle('open');
        });
    }

    //GESTIONE DARK MODE
    const themeBtn = document.getElementById('themeBtn');
    const body = document.body;
    const savedTheme = localStorage.getItem('theme');
    
    if (savedTheme === 'dark') {
        body.classList.add('dark-mode');
    }

    if (themeBtn) {
        themeBtn.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            if (body.classList.contains('dark-mode')) {
                localStorage.setItem('theme', 'dark');
            } else {
                localStorage.setItem('theme', 'light');
            }
        });
    }
});