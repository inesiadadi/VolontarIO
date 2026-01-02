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