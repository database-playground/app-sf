import pluginJs from "@eslint/js";
import globals from "globals";
import tseslint from "typescript-eslint";

export default [
  { files: ["assets/**/*.{js,mjs,cjs,ts}"] },
  {
    ignores: [
      "assets/vendor/**/*",
      "vendor/**/*",
      "public/bundles/**/*",
      "var/**/*",
    ],
  },
  { languageOptions: { globals: globals.browser } },
  pluginJs.configs.recommended,
  ...tseslint.configs.recommended,
];
