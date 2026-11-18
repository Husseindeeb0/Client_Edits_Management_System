/// <reference types="vite/client" />

interface ImportMetaEnv {
  /** Laravel API origin, e.g. `http://localhost:8000` (set in .env). */
  readonly VITE_API_URL: string;
}

interface ImportMeta {
  readonly env: ImportMetaEnv;
}
