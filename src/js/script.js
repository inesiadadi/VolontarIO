document.addEventListener('DOMContentLoaded', function () { 
    if (document.getElementById('citta-input')) {
        initCittaAutocomplete();
    }
});

document.addEventListener("DOMContentLoaded", function() {
    const stars = document.querySelectorAll('.starsColor[data-percentuale]');
    stars.forEach(star => {
        // Legge il dato dall'HTML e lo applica come variabile CSS
        star.style.setProperty('--percentuale', star.dataset.percentuale);
    });
});

function initCittaAutocomplete() {
    const input = document.getElementById('citta-input');
    const lista = document.getElementById('suggerimenti-citta');
    const hiddenInput = document.getElementById('idCitta-input');
    const statusBox = document.getElementById('status-citta');

    let timeout = null;
    let indexCorrente = -1;

    function chiudiLista() {
        lista.style.display = 'none';
        lista.innerHTML = '';
        indexCorrente = -1;
        input.setAttribute('aria-expanded', 'false');
        input.removeAttribute('aria-activedescendant');
    }

    input.addEventListener('input', function () {
        clearTimeout(timeout);
        const termine = this.value.trim();

        indexCorrente = -1;
        input.removeAttribute('aria-activedescendant');

        if (termine.length < 2) {
            chiudiLista();
            return;
        }

        timeout = setTimeout(() => {
            fetch(`/volontario/db/include/comuni.php?q=${encodeURIComponent(termine)}`)
                .then(res => res.json())
                .then(comuni => {
                    lista.innerHTML = '';

                    if (comuni.length > 0) {
                        lista.style.display = 'block';
                        input.setAttribute('aria-expanded', 'true');
                        statusBox.textContent = `${comuni.length} comuni trovati. Usa freccia su/giù.`;

                        comuni.forEach((comune, idx) => {
                            const li = document.createElement('li');
                            li.id = `citta-option-${idx}`;
                            li.textContent = comune.nome;
                            li.setAttribute('role', 'option');
                            li.setAttribute('aria-selected', 'false');

                            li.addEventListener('click', function() {
                                selezionaComune(comune);
                            });

                            lista.appendChild(li);
                        });
                    } else {
                        chiudiLista();
                        statusBox.textContent = "Nessun comune trovato.";
                    }
                })
                .catch(err => console.error(err));
        }, 300);
    });

    input.addEventListener('keydown', function(e) {
        const items = lista.querySelectorAll('li');
        const isListOpen = lista.style.display === 'block' && items.length > 0;

        if (!isListOpen) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            indexCorrente++;
            if (indexCorrente >= items.length) indexCorrente = 0;
            aggiornaEvidenziato(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            indexCorrente--;
            if (indexCorrente < 0) indexCorrente = items.length - 1;
            aggiornaEvidenziato(items);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (indexCorrente > -1 && items[indexCorrente]) {
                items[indexCorrente].click();
            }
        } else if (e.key === 'Escape') {
            chiudiLista();
            statusBox.textContent = "Lista chiusa.";
        }
    });

    function aggiornaEvidenziato(items) {
        Array.from(items).forEach(item => {
            item.classList.remove('active');
            item.setAttribute('aria-selected', 'false');
        });

        const attivo = items[indexCorrente];
        if (!attivo) return;

        attivo.classList.add('active');
        attivo.setAttribute('aria-selected', 'true');

        // scroll se fuori vista
        attivo.scrollIntoView({ block: 'nearest', behavior: 'auto' });

        // aggiorna input con il testo completo della città
        input.value = attivo.textContent;

        // aggiorna aria-activedescendant per screen reader
        input.setAttribute('aria-activedescendant', attivo.id);
    }

    function selezionaComune(comune) {
        input.value = comune.nome;
        hiddenInput.value = comune.idCitta;
        statusBox.textContent = `Hai selezionato ${comune.nome}`;
        chiudiLista();
        input.focus();
    }

    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !lista.contains(e.target)) {
            chiudiLista();
        }
    });
}

// VALIDAZIONE DEL FORM DI REGISTER O FORM DI CONTATTO O FORM DI LOGIN

function initValidations() {
    
    const form = document.getElementById('signForm') || document.getElementById('contattoForm');
    var regole = [];

    if(form.id == "signForm") {
        regole = [
            ['username', /^[a-zA-Z0-9_]{4,20}$/, "4-20 caratteri (lettere, numeri, underscore)", "Username non valido"],
            ['email', /^[a-zA-Z0-9._%+-]+@[a-zAèZ0-9.-]+\.[a-zA-Z]{2,}$/, "Inserisci un'email valida, es: aala@ajmai.me", "Email non valida"],
            ['nome', /^[A-Za-zÀ-ÿ\s']{2,30}$/, "Solo lettere, minimo 2 caratteri, es: Mario", "Nome non valido"],
            ['cognome', /^[A-Za-zÀ-ÿ\s']{2,30}$/, "Solo lettere, minimo 2 caratteri, es: Rossi", "Cognome non valido"],
            ['password', /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/, "Minimo 8 caratteri con almeno una maiuscola e un numero", "Password debole"],
            ['dataNascita', /^\d{4}-\d{2}-\d{2}$/, "Inserisci la tua data di nascita, almeno 18 anni, es: 01/09/2007", "Data non valida"]
        ];
    } else if (form.id == "contattoForm") {
        regole = [
            ['nome_cognome', /^[a-zA-Zà-ùÀ-Ù\s']{2,}$/, "Solo lettere, minimo 2 caratteri, es: Mario Rossi", "Nome e cognome non validi"],
            ['email', /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/, "Inserisci un'email valida, es: aala@ajmai.me", "Email non valida"],
            ['tel', /^(\+?[0-9\s\-]{8,20})?$/, "Inserisci un numero di telefono valido, es: +39 333 111 2222", "Formato non valido"],
            ['messaggio', /\S+\s+\S+/, "Inserisci la tua richiesta, almeno 2 parole, es: \"Come creare un account?\"", "Richiesta non valida"]
        ];
    }

    function validaCampo(id, regex, testoAiuto, testoErrore) {
        const input = document.getElementById(id);
        const span = document.getElementById('aiuto-' + id);
    
        if (input.value === "") {
            span.textContent = testoAiuto;
            span.className = "aiuto";
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
        regole.forEach(campo => validaCampo(campo[0], campo[1], campo[2], campo[3]));
        if (document.querySelectorAll('.aiuto.errore').length > 0) {
            e.preventDefault();
        }
    });
}

/* Helpers per le funzioni relative alle recensioni e alle richieste */
document.addEventListener('DOMContentLoaded', () => {
  initReviewFiltering();
  initRequestsFiltering();
});

function initReviewFiltering() {
  console.log("initReviewFiltering avviato");

  const sidebar = document.querySelector('.filters-sidebar');
  const reviewsGrid = getReviewGrid();
  const cards = document.querySelectorAll('.reviewCard');

  if (!sidebar || !reviewsGrid || cards.length === 0) return;

  if (sidebar.dataset.reviewFilteringInit === '1') return;
  sidebar.dataset.reviewFilteringInit = '1';

  // Event delegation: UN SOLO listener
  sidebar.addEventListener('change', (e) => {
    const t = e.target;
    if (!(t instanceof HTMLInputElement)) return;
    if (t.type !== 'checkbox') return;
    if (t.name !== 'vote[]' && t.name !== 'category[]') return;

    applyReviewFiltering();
  });

  applyReviewFiltering();
}

function applyReviewFiltering() {
  const selectedVoteRanges = getCheckedValue('vote[]');                // array
  const selectedCategories = getCheckedValue('category[]').map(normalizeToken);

  const cards = document.querySelectorAll('.reviewCard');
  let visible = 0;

  for (const card of cards) {
    const vote = readReviewVote(card);
    const category = readReviewCategory(card); // token normalizzato

    const passVote =
      (selectedVoteRanges.length === 0) ||
      selectedVoteRanges.some(r => voteInRange(vote, r));

    const passCategory =
      (selectedCategories.length === 0) ||
      selectedCategories.includes(category);

    const show = passVote && passCategory;
    card.style.display = show ? '' : 'none';
    if (show) visible++;
  }

    updateEmptyState(grid, empty, visible);
}

function readReviewVote(card) {
  const el = card.querySelector('[itemprop="ratingValue"]');
  if (!el) return NaN;

  const n = Number(String(el.textContent).trim());
  return Number.isFinite(n) ? n : NaN;
}

function readReviewCategory(card) {
  const catEl = card.querySelector('.reviewCategory');
  if (!catEl) return '';

  const clone = catEl.cloneNode(true);

  clone.querySelectorAll('.srOnly, .sr-only').forEach(n => n.remove());

  return normalizeToken(clone.textContent);
}

function getReviewGrid() {
  return document.getElementById('grigliaRecensioni') || document.querySelector(".reviewsGrid");
}

function getReviewEmptyState() {
  return document.getElementById('filterEmptyState');
}

function getCheckedValue(name) {
    console.log("Valore nome individuato:", name);
    return Array.from(document.querySelectorAll(`input[name="${name}"]:checked`))
        .map(el => String(el.value).trim());
}

function voteInRange(vote, rangeStr) {
  if (!Number.isFinite(vote)) return false;

  const s = String(rangeStr).trim();
  if (s.includes('-')) {
    const [min, max] = s.split('-').map(Number);
    return Number.isFinite(min) && Number.isFinite(max) && vote >= min && vote <= max;
  }
  const x = Number(s);
  return Number.isFinite(x) && vote === x;
}

function normalizeToken(str) {
  return String(str)
    .trim()
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/['"]/g, '')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/-+/g, '-')
    .replace(/^-|-$/g, '');
}

function updateEmptyState(grid, empty, visibleCount) {
  if (!grid || !empty) return;

  const isEmpty = visibleCount === 0;

  empty.hidden = !isEmpty;

  // forza display per vincere eventuali regole CSS che mettono display:none
  empty.style.display = isEmpty ? 'flex' : '';

  // la griglia resta nel flow, tanto le card sono già hidden
  grid.hidden = false;
}


document.addEventListener('DOMContentLoaded', initRequestFilters);
function initRequestFilters() {
    const statSelect = document.getElementById('stateFilter');
    const catSelect = document.getElementById('categoryFilter');

    if(statSelect) statSelect.addEventListener('change', applyRequestFilters);
    if(catSelect) catSelect.addEventListener('change', applyRequestFilters);
}

function getRequestFilterState() {
    const status = document.getElementById('stateFilter')?.value?.toLowerCase() ?? 'all';
    const category = document.getElementById('categoryFilter')?.value?.toLowerCase() ?? 'all';

    return { status, category };
}

function getRequestCards() {
    return document.querySelectorAll('.requestCard');
}

function getRequestGrid() {
    return (
        document.getElementById('grigliaRichieste') ?? document.querySelector('.requestGrid')
    );
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

    // MENU HAMBURGER
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const menuWrapper = document.getElementById('menuWrapper');

    if (hamburgerBtn && menuWrapper) {
        hamburgerBtn.addEventListener('click', () => {
            const isOpen = menuWrapper.classList.toggle('active');
            hamburgerBtn.classList.toggle('open');
            hamburgerBtn.setAttribute('aria-expanded', isOpen);

            if (isOpen) {
                const primoLink = menuWrapper.querySelector('a, button');
                if (primoLink) setTimeout(() => { primoLink.focus(); }, 100);
            } else {
                hamburgerBtn.focus();
            }
        });

        menuWrapper.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                menuWrapper.classList.remove('active');
                hamburgerBtn.classList.remove('open');
                hamburgerBtn.setAttribute('aria-expanded', 'false');
                hamburgerBtn.focus();
            }
        });
    }

    //MENU UTENTE
    const userBtn = document.getElementById('userBtn');
    const dropdownContent = document.querySelector('.dropdown-content');

    if (userBtn && dropdownContent) {
        userBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isAperto = dropdownContent.classList.toggle('show');
            userBtn.setAttribute('aria-expanded', isAperto);

            if (isAperto) {
                const primoLinkUser = dropdownContent.querySelector('a');
                if (primoLinkUser) setTimeout(() => { primoLinkUser.focus(); }, 100);
            }
        });
        document.addEventListener('click', (e) => {
            if (!userBtn.contains(e.target) && !dropdownContent.contains(e.target)) {
                dropdownContent.classList.remove('show');
                userBtn.setAttribute('aria-expanded', 'false');
            }
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && dropdownContent.classList.contains('show')) {
                dropdownContent.classList.remove('show');
                userBtn.setAttribute('aria-expanded', 'false');
                userBtn.focus();
            }
        });
    }

    //DARK MODE
    const themeBtn = document.getElementById('themeBtn');
    const body = document.body;
    const savedTheme = localStorage.getItem('theme');
    
    if (savedTheme === 'dark') {
        body.classList.add('dark-mode');
    }

    if (themeBtn) {
        themeBtn.addEventListener('click', () => {
            const isDark = body.classList.toggle('dark-mode');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        });
    }

    // VALIDAZIONE FORM
    if (document.getElementById('signForm') || document.getElementById('contattoForm')) {
        if (typeof initValidations === 'function') {
            initValidations();
        }
    } 
});
