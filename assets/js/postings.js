let offset = 0;
let limit = 20;
let loadingPosts = false;
let finishedLoading = false;

function loadPostings(duck = null) {
    if (!finishedLoading && !loadingPosts) {
        loadingPosts = true;
        let params = {"offset": offset, "limit": limit};
        if(duck != null) {
            params["duck"] = duck;
        }
        api("postings", params).then((html) => {
            if (html !== "") {
                const hash = window.location.hash;
                const needToScroll = hash && !document.querySelector(hash);
                document.getElementById("postings-list").insertAdjacentHTML('beforeend', html);
                if (needToScroll) {
                    setTimeout(() => {
                        const target = document.querySelector(hash);
                        if (target) {
                            // When adding many entries, the last ones are not fully rendered.
                            // This leads to incorrect scrolling when the browser renders them on the fly.
                            // Therefore, we scroll approx. to the element and as soon as it's on the screen, we scroll direct.
                            new Promise((resolve) => {
                                const observer = new IntersectionObserver((entries) => {
                                    if (entries[0].isIntersecting) {
                                        observer.disconnect();
                                        resolve();
                                    }
                                }, { threshold: 1.0 });
                                observer.observe(target);
                                target.scrollIntoView({ behavior: "smooth", block: "start", inline: "nearest" });
                            }).then(() => {
                                target.scrollIntoView({ behavior: "smooth", block: "start", inline: "nearest" });
                            });
                        } else {
                            loadPostings(DUCK);
                        }
                    }, 50);
                }
                offset += limit;
            } else {
                finishedLoading = true;
                document.getElementById("postings-loader").style.display = "none";
            }
            loadingPosts = false;
        });
    }
}

function showPostingEditDialog(postingElement) {
    let postingId = postingElement.id.substring(8);
    let content = postingElement.querySelector('.posting-content').innerText;
    let author = postingElement.querySelector('.posting-author').innerText;
    Swal.fire({
        title: "Posting bearbeiten",
        html: `
            <input id="swal-input-content" class="swal2-input" placeholder="Nachricht" maxlength=${POSTING_MAX_LENGTH} value="${content}">
            <input id="swal-input-author" class="swal2-input" placeholder="Autor" maxlength=${AUTHOR_MAX_LENGTH} value="${author}">
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: "Speichern",
        cancelButtonText: "Abbrechen",
        preConfirm: () => {
            return [
                document.getElementById("swal-input-content").value,
                document.getElementById("swal-input-author").value
            ];
        }
    }).then(result => {
        if (result.isConfirmed) {
            let newContent = result.value[0];
            let newAuthor = result.value[1];
            api("editPosting", {"postingId": postingId, "content": newContent, "author": newAuthor}).then(() => {
                postingElement.querySelector('.posting-content').innerText = newContent;
                postingElement.querySelector('.posting-author').innerText = newAuthor;
            });
        }
    });
}

function showPostingDeleteDialog(postingElement) {
    let postingId = postingElement.id.substring(8);
    let author = postingElement.querySelector('.posting-author').innerText;
    Swal.fire({
        title: "Posting löschen",
        text: `Bist du sicher, dass du den Post von ${author} löschen möchtest?`,
        showConfirmButton: false,
        showDenyButton: true,
        showCancelButton: true,
        denyButtonText: "Löschen",
        cancelButtonText: "Abbrechen",
    }).then((result) => {
        if (result.isDenied) {
            api("deletePosting", {"postingId": postingId}).then(() => {
                postingElement.remove();
            });
        }
    });
}

function createPosting(formElement) {
    let content = formElement.querySelector("[name=content]").value;
    let author = formElement.querySelector("[name=author]").value;
    formElement.querySelector("button[type=submit]").disabled = true;
    api("createPosting", {"duckId": DUCK, "content": content, "author": author})
        .then((result) => result.text()).then((html) => {
        formElement.querySelector("[name=content]").value = "";
        formElement.querySelector("[name=author]").value = "";
        formElement.querySelector("button[type=submit]").disabled = false;
        document.getElementById("postings-list").insertAdjacentHTML("afterbegin", html);
    });
}

function showAttachment(element) {
    let img = element.querySelector("img");
    let overlay = document.getElementById("attachment-viewer");
    overlay.querySelector("img").src = img.src;
    overlay.style.display = "block";
    document.body.classList.add("no-scroll");
}

document.addEventListener("DOMContentLoaded", function () {
    loadPostings(DUCK);
});

window.addEventListener("scroll", () => {
    const scrollPosition = window.scrollY + window.innerHeight;
    const documentHeight = document.documentElement.scrollHeight;

    if (documentHeight - scrollPosition <= 500) {
        loadPostings(DUCK);
    }
});