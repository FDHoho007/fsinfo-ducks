function showKeyDialog() {
    Swal.fire({
        title: "Berechtigungsschlüssel verwenden",
        text: "Bitte gib deinen Berechtigungsschlüssel ein.",
        inputPlaceholder: "Berechtigungsschlüssel",
        input: "text",
        showCancelButton: true,
        confirmButtonText: "Verwenden"
    }).then((result) => {
        if(result.isConfirmed) {
            api("useKey", {"key": result.value}).then((result) => {
                if(result !== "false") {
                    window.location.reload();
                } else {
                    Swal.fire({
                        title: "Ungültiger Schlüssel",
                        text: "Der eingegebene Berechtigungsschlüssel ist ungültig",
                        icon: "error"
                    });
                }
            });
        }
    });
}

function showLogoutDialog() {
    Swal.fire({
        title: "Abmelden bestätigen",
        text: "Bist du sicher, dass du dich abmelden möchtest? Du wirst alle verwendeten Berechtigungsschlüssel erneut eingeben müssen.",
        showConfirmButton: false,
        showDenyButton: true,
        showCancelButton: true,
        denyButtonText: "Abmelden",
        cancelButtonText: "Abbrechen",
    }).then((result) => {
        if (result.isDenied) {
            api("logout").then(() => {
                window.location.reload();
            });
        }
    });
}

function showDuckLoginDialog(loginPrompt = "Bitte halte den NFC Chip der Ente an dein Gerät.", afterLoginAction = () => window.location.reload()) {
    if('NDEFReader' in window) {
        const reader = new NDEFReader();
        let abortController = new AbortController();
        reader.addEventListener("reading", (e) => {
            Swal.close();
            let key = null;
            let records = e["message"]["records"];
            for(let record of records) {
                const encoding = record["encoding"];
                const decoder = encoding ? new TextDecoder(encoding) : new TextDecoder();
                const data = decoder.decode(record["data"]);
                if(record["recordType"] === "url") {
                    if(data.startsWith("fsinfo-duck-key://")) {
                        key = data.substring(18);
                    }
                    if(data.startsWith("https://ducks.fs-info.de/") && data.includes("?pwd=")) {
                        key = data.substring(data.indexOf("?pwd=") + 5);
                    }
                    if(key != null) {
                        api("useKey", {"key": key}).then((result) => {
                            if (result !== "false") {
                                afterLoginAction();
                            } else {
                                Swal.fire({
                                    title: "Ungültiger Schlüssel",
                                    text: "Der NFC Chip dieser Ente wurde entweder noch nicht aktualisiert oder ist temporär gesperrt worden. Eine Anmeldung ist derzeit nicht möglich.",
                                    icon: "error"
                                });
                            }
                        });
                        break;
                    }
                }
            }
            if(key == null) {
                Swal.fire({
                    title: "Ungültiger NFC Chip",
                    text: "Bei diesem NFC Chip handelt es sich nicht um einen Entenchip.",
                    icon: "error"
                });
            }
        });
        reader.scan({signal: abortController.signal});
        Swal.fire({
            title: "Mit Ente anmelden",
            text: loginPrompt,
            showConfirmButton: false,
            showCancelButton: true,
            cancelButtonText: "Abbrechen",
        }).then(() => {
            abortController.abort();
        });
    } else {
        Swal.fire({
            title: "Browser nicht unterstützt",
            text: "Die Anmeldung mit Ente ist derzeit nur per NFC möglich, was von deinem Browser nicht unterstützt wird."
        });
    }
}

document.addEventListener("DOMContentLoaded", function() {
    const menu = document.getElementById("user-options");

    document.getElementById("user-menu-toggle").addEventListener("click", function(event) {
        event.stopPropagation();
        menu.classList.toggle("show-menu");
    });

    document.addEventListener("click", function() {
        menu.classList.remove("show-menu");
    });

    document.getElementById("menu-toggle").addEventListener("click", function() {
        document.getElementsByTagName("nav")[0].classList.toggle("active");
    });
});