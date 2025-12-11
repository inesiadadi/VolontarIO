function setVoto(container, value) {
    if (value < 0 || value > 10) return;

    const filler = container.querySelector(".votoFiller");
    const numero = container.querySelector(".votoNumeroContainer");

    numero.textContent = value.toFixed(1);
    const percentuale = (value / 10) * 100;

    let colore;
    if(value < 2) colore = "#8B0000";   // rosso scuro
    else if(value < 3) colore = "#A80000"; 
    else if(value < 4) colore = "#BF2C00"; // arancio scuro
    else if(value < 5) colore = "#D64C00"; 
    else if(value < 6) colore = "#E68000"; // giallo scuro/ocra
    else if(value < 7) colore = "#C0A000"; 
    else if(value < 8) colore = "#2E8B57"; // verde scuro
    else if(value < 9) colore = "#3AAE6F"; 
    else colore = "#0D6B39"; // verde molto scuro

    filler.style.background = `conic-gradient(from -90deg at 50% 50%, ${colore} ${percentuale}%, #e0e0e0 0)`;
}

// Esempio
setVoto(document.querySelector(".valutazione"), 9.8);