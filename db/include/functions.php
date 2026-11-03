<?php 

function e($valore): string {
    return htmlspecialchars((string)($valore ?? ''), ENT_QUOTES, 'UTF-8');
}

function formatDateItalian($date) {
    // Mappa dei mesi in italiano
    $mesi = [
        1 => 'Gennaio', 2 => 'Febbraio', 3 => 'Marzo', 4 => 'Aprile',
        5 => 'Maggio', 6 => 'Giugno', 7 => 'Luglio', 8 => 'Agosto',
        9 => 'Settembre', 10 => 'Ottobre', 11 => 'Novembre', 12 => 'Dicembre'
    ];

    // Creazione oggetto DateTime dalla stringa del DB
    $d = new DateTime($date);
    
    $giorno = $d->format('j');                // Giorno senza zero iniziale (1-31)
    $mese   = $mesi[(int)$d->format('n')];    // Nome del mese dall'array (n = 1-12)
    $anno   = $d->format('Y');                // Anno a 4 cifre

    return "$giorno $mese $anno";
}

function url(string $path = '', bool $preserveState = false): string {
    $path = '/' . ltrim($path, '/');

    if ($preserveState) {
        $qs = $_SERVER['QUERY_STRING'] ?? '';
        if ($qs) {
            $path .= '?' . $qs;
        }
    }

    return BASE_PATH . $path;
}
/**
 * Salva l'immagine nella cartella rinominandola con l'ID utente.
 * Ritorna il nome del file se caricato, altrimenti null.
 */
function aggiungiNuovaImmagineProfilo($file, $idUtente) {
    $targetDir = __DIR__ . "/../../src/images/uploads/";
    
    if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
        $info = $file['size'] <= 2 * 1024 * 1024 ? getimagesize($file['tmp_name']) : false;
        
        if ($info !== false && $info[2] === IMAGETYPE_PNG) {
            $nuovoNome = $idUtente . ".png";
            $targetPath = $targetDir . $nuovoNome;

            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                return $nuovoNome;
            }
        }
    }
    return null;
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function csrf_verify(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        exit('Richiesta non valida. Ricarica la pagina e riprova.');
    }
}
?>
