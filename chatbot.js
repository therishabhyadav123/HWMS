
(function () {
    const init = () => {
        const chatbot = document.getElementById("chatbot");
        const toggle = document.getElementById("chatbot-toggle");
        const closeBtn = document.getElementById("chat-close");
        const chatBody = document.getElementById("chatBody");
        const userInput = document.getElementById("userInput");
        const sendBtn = document.querySelector("#chatbot .chat-input button");

        if (!chatbot || !toggle || !chatBody || !userInput || !closeBtn) {
            console.error("Chatbot elements not found");
            return;
        }

        const history = [];

        function addMessage(text, role) {
            const cls = role === "user" ? "user-msg" : "bot-msg";
            const el = document.createElement("div");
            el.className = cls;
            el.textContent = text;
            chatBody.appendChild(el);
            chatBody.scrollTop = chatBody.scrollHeight;
            return el;
        }

        function getChunkSize(length) {
            if (length > 1200) return 6;
            if (length > 600) return 4;
            if (length > 280) return 3;
            return 2;
        }

        function typeMessage(el, text) {
            const chunkSize = getChunkSize(text.length);
            const delay = 16;
            let index = 0;

            return new Promise((resolve) => {
                const tick = () => {
                    if (index >= text.length) {
                        resolve();
                        return;
                    }
                    const next = text.slice(index, index + chunkSize);
                    el.textContent += next;
                    index += chunkSize;
                    chatBody.scrollTop = chatBody.scrollHeight;
                    setTimeout(tick, delay);
                };
                tick();
            });
        }

        function addTyping() {
            const el = document.createElement("div");
            el.className = "bot-msg typing";
            el.textContent = "Typing...";
            chatBody.appendChild(el);
            chatBody.scrollTop = chatBody.scrollHeight;
            return el;
        }

        function localReply(message) {
            let reply = "Sorry, I didn't understand. Please ask about hospital waste management.";

            if (message.includes("types"))
                reply = "Hospital waste types: General, Infectious, Sharps, Chemical, Pharmaceutical.";
            else if (message.includes("color"))
                reply = "Color code: Yellow - Infectious, Red - Plastic, White - Sharps, Blue - Glassware.";
            else if (message.includes("sharps"))
                reply = "Sharps waste includes needles and blades. Use white puncture-proof containers.";
            else if (message.includes("safety"))
                reply = "Always wear gloves, masks, and PPE while handling hospital waste.";
            else if (message.includes("hello") || message.includes("hi"))
                reply = "Hello! How can I help you?";

            return reply;
        }

        async function sendToOllama(message) {
            const res = await fetch("ollama_chat.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "message=" + encodeURIComponent(message)
            });
            let data = null;
            try {
                data = await res.json();
            } catch (_) {
                data = null;
            }
            if (!res.ok) {
                throw new Error((data && data.error) || "AI request failed");
            }
            return (data && data.response) ? data.response : "No reply";
        }

        async function handleSend() {
            const msg = userInput.value.trim();
            if (!msg) return;

            addMessage(msg, "user");
            history.push({ role: "user", text: msg });
            userInput.value = "";
            userInput.focus();

            const typing = addTyping();
            if (sendBtn) sendBtn.disabled = true;

            try {
                const reply = await sendToOllama(msg);
                typing.remove();
                const botEl = addMessage("", "bot");
                await typeMessage(botEl, reply);
                history.push({ role: "model", text: reply });
            } catch (err) {
                typing.remove();
                const reply = localReply(msg.toLowerCase());
                const botEl = addMessage("", "bot");
                await typeMessage(botEl, reply);
                history.push({ role: "model", text: reply });
            } finally {
                if (sendBtn) sendBtn.disabled = false;
            }
        }

        toggle.addEventListener("click", function () {
            chatbot.style.display = "flex";
            toggle.style.display = "none";
        });

        closeBtn.addEventListener("click", function () {
            chatbot.style.display = "none";
            toggle.style.display = "block";
        });

        window.sendMessage = handleSend;

        userInput.addEventListener("keydown", function (e) {
            if (e.key === "Enter") {
                e.preventDefault();
                handleSend();
            }
        });
    };

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }
})();
