const valutazione = document.querySelector(".valutazione");

function setVoto(container, value) {
    if(value < 1 || value > 10) return;

    container.querySelector(".votoFiller").style.transform = "rotate(" + value / 20 + "turn)";
    container.querySelector(".votoNumeroContainer").textContent = value;

    let colore;
    if(value < 2) colore = "#C22E2A";
    else if(value < 3) colore = "#D8462F";
    else if(value < 4) colore = "#E55B22";
    else if(value < 5) colore = "#F0721F";
    else if(value < 6) colore = "#F5A623";
    else if(value < 7) colore = "#F2C438";
    else if(value < 8) colore = "#88C552";
    else if(value < 9) colore = "#4FBD6C";
    else colore = "#39A853";

    container.querySelector(".votoFiller").style.background = colore;
}

setVoto(valutazione, 10.0);