import { defineConfig } from "vite";
import tailwindcss from "@tailwindcss/vite";
import { createRequire } from 'module';

const require = createRequire(import.meta.url);

export default defineConfig({
  plugins: [tailwindcss()],
});
