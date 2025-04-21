function api(action, params = {}) {
    const formData = new FormData();
    formData.append('csrf_token', CSRF_TOKEN);
    formData.append("action", action);
    for(let key in params) {
        formData.append(key, params[key]);
    }
    return new Promise((resolve, reject) => {
        fetch("/api.php", {
            method: "POST",
            body: formData
        }).then((result) => {
            if(result.status === 403) {
                showDuckLoginDialog("Deine Sitzung ist abgelaufen. Bitte melde dich erneut mit deiner Ente an. Halte dafür den NFC Chip der Ente an dein Gerät.",
                    () => { api(action, params).then((result) => resolve(result)); });
            } else if(result.status === 200) {
                resolve(result.text());
            } else {
                reject(result.status);
            }
        })
    })
}