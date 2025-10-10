import { defineConfig } from "vite";
import symfonyPlugin from "vite-plugin-symfony";
import tailwindcss from "@tailwindcss/vite"
import path from "path"
import react from '@vitejs/plugin-react';

function resolve(fileName) {
    return path.resolve(__dirname, fileName)
}


export default defineConfig({
    plugins: [
        react(),
        tailwindcss(),
        symfonyPlugin(),
    ],
    resolve: {
        alias: {
            "@": resolve("assets/js"),
            "!": resolve("assets/images"),
            "#": resolve("assets/css"),
        },
    },
    build: {
        rollupOptions: {
            input: {
                app: resolve("assets/js/app.js"),
                appjsx: resolve("assets/js/app.jsx")
            },
        }
    },
});
