function showDuckLockDialog(duckElement) {
    let duckId = duckElement.getAttribute("data-duck-id");
    let duckName = duckElement.querySelector("h1").innerText;
    Swal.fire({
        title: "Ente sperren",
        text: `Bist du sicher, dass du ${duckName} sperren möchtest? Es können dann keine weiteren Beiträge in seinem Namen verfasst werden.`,
        icon: "warning",
        showConfirmButton: false,
        showDenyButton: true,
        showCancelButton: true,
        denyButtonText: "Sperren"
    }).then((result) => {
        if(result.isDenied) {
            api("lockDuck", {"duckId": duckId}).then(() => {
                document.getElementById("lock-duck-button").style.display = "none";
                document.getElementById("unlock-duck-button").style.display = "";
            });
        }
    });
}

function showDuckUnlockDialog(duckElement) {
    let duckId = duckElement.getAttribute("data-duck-id");
    let duckName = duckElement.querySelector("h1").innerText;
    Swal.fire({
        title: "Ente entsperren",
        text: `Bist du sicher, dass du ${duckName} entsperren möchtest? Dadurch können wieder Beiträge in seinem Namen verfasst werden.`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Entperren"
    }).then((result) => {
        if(result.isConfirmed) {
            api("unlockDuck", {"duckId": duckId}).then(() => {
                document.getElementById("lock-duck-button").style.display = "";
                document.getElementById("unlock-duck-button").style.display = "none";
            });
        }
    });
}

function showBiographyEditDialog(duckElement) {
    let duckId = duckElement.getAttribute("data-duck-id");
    let duckName = duckElement.querySelector("h1").innerText;
    let duckBio = duckElement.querySelector("p").innerText;
    duckBio = duckBio === "keine Biografie gesetzt" ? "" : duckBio;
    Swal.fire({
        title: "Biografie ändern",
        text: `Bitte leg die Biografie von ${duckName} fest.`,
        inputPlaceholder: "Halo ich bin ...",
        input: "text",
        inputValue: duckBio,
        showCancelButton: true,
        confirmButtonText: "Ändern",
        inputAttributes: { "maxlength": BIOGRAPHY_MAX_LENGTH }
    }).then((result) => {
        if(result.isConfirmed) {
            api("editBiography", {"duckId": duckId, "biography": result.value}).then(() => {
                duckElement.querySelector("p").innerText = result.value === "" ? "keine Biografie gesetzt" : result.value;
            });
        }
    });
}

function showNotificationDialog(duckElement) {
    let duckId = duckElement.getAttribute("data-duck-id");
    let duckName = duckElement.querySelector("h1").innerText;
    Swal.fire({
        title: "ntfy.sh Benachrichtigung",
        html: `Du kannst Benachrichtigungen für ${duckName} empfangen, indem du diese ntfy.sh URL abonnierst: <a href="ntfy://ntfy.myfdweb.de/fsinfo-duck-${duckId}">ntfy://ntfy.myfdweb.de/fsinfo-duck-${duckId}</a>`
    })
}