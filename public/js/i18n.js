/** UI texts for scripts: the server embeds the "js.*" dictionary entries as JSON in the page. */
let messages = null;

function load() {
    if (messages === null) {
        try {
            messages = JSON.parse(document.getElementById('i18n-data')?.textContent ?? '{}');
        } catch (error) {
            messages = {};
        }
    }
    return messages;
}

/** Returns the text for a key with ":name" placeholders replaced; an unknown key is returned as is. */
export function t(key, params = {}) {
    let text = load()[key] ?? key;
    for (const [name, value] of Object.entries(params)) {
        text = text.replaceAll(`:${name}`, String(value));
    }
    return text;
}
