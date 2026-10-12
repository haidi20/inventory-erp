import Echo from "laravel-echo";
import Pusher from "pusher-js";

declare global {
    interface Window {
        Echo: Echo<"reverb"> | null;
        Pusher: typeof Pusher;
    }
}

function initEcho(): Echo<"reverb"> | null {
    const key = import.meta.env.VITE_REVERB_APP_KEY;

    if (!key) {
        return null;
    }

    window.Pusher = Pusher;

    return new Echo({
        broadcaster: "reverb",
        key,
        wsHost: import.meta.env.VITE_REVERB_HOST ?? "localhost",
        wsPort: Number(import.meta.env.VITE_REVERB_PORT ?? 8080),
        wssPort: Number(import.meta.env.VITE_REVERB_PORT ?? 8080),
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? "http") === "https",
        enabledTransports: ["ws", "wss"],
    });
}

window.Echo = initEcho();

export {};
