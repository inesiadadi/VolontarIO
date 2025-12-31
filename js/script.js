document.addEventListener('DOMContentLoaded', function() {
    // 1. Recupero Elementi
    const voteSelect = document.getElementById('voteFilter');
    const catSelect = document.getElementById('categoryFilter'); // Nuovo filtro
    
    // Fallback sicuro se l'ID manca
    let reviewGrid = document.getElementById('grigliaRecensioni');
    if (!reviewGrid) reviewGrid = document.querySelector('.reviewsGrid');

    const msgContainer = document.getElementById('filterEmptyState');
    const cards = document.querySelectorAll('.reviewCard');

    // Funzione unica di filtraggio
    function filterReviews() {
        // A. Leggi e prepara il Voto
        const rangeValue = voteSelect ? voteSelect.value : "0-10";
        const parts = rangeValue.split('-');
        const minVote = parseInt(parts[0]);
        const maxVote = parseInt(parts[1]);

        // B. Leggi e prepara la Categoria (trasformata in minuscolo per confronto sicuro)
        const selectedCat = catSelect ? catSelect.value.toLowerCase() : "all";

        let visibleCount = 0;

        // C. Ciclo su tutte le card
        cards.forEach(card => {
            // --- CHECK 1: VOTO ---
            const ratingSpan = card.querySelector('[itemprop="ratingValue"]');
            let matchVote = false;
            
            if (ratingSpan) {
                const vote = parseInt(ratingSpan.textContent.trim());
                if (vote >= minVote && vote <= maxVote) {
                    matchVote = true;
                }
            }

            // --- CHECK 2: CATEGORIA ---
            // Cerchiamo il div .reviewCategory dentro la card
            const catDiv = card.querySelector('.reviewCategory');
            let matchCat = false;

            // Se il filtro è "all", passa sempre. Altrimenti controlliamo il testo.
            if (selectedCat === 'all') {
                matchCat = true;
            } else if (catDiv) {
                // Prendiamo il testo (es: "Sanità "), togliamo spazi (.trim) e rendiamo minuscolo (.toLowerCase)
                const cardCatText = catDiv.textContent.trim().toLowerCase();
                
                // Confrontiamo: "sanità" === "sanità"
                if (cardCatText === selectedCat) {
                    matchCat = true;
                }
            }

            // --- DECISIONE FINALE (AND Logic) ---
            // Deve passare ENTRAMBI i controlli
            if (matchVote && matchCat) {
                card.style.display = ''; 
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // D. Mostra/Nascondi Griglia o Messaggio Vuoto
        if (visibleCount === 0) {
            if (reviewGrid) reviewGrid.style.display = 'none';
            if (msgContainer) msgContainer.style.display = 'flex';
        } else {
            if (reviewGrid) reviewGrid.style.display = 'grid';
            if (msgContainer) msgContainer.style.display = 'none';
        }
    }

    // 2. Attiva gli ascoltatori sugli eventi
    if (voteSelect) voteSelect.addEventListener('change', filterReviews);
    if (catSelect) catSelect.addEventListener('change', filterReviews);
});