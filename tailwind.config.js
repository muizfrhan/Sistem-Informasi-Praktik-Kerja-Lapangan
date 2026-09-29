import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/**
 * ============================================================================
 *  SIPKL DESIGN SYSTEM — palet dari template patch
 *  (public/template/form-login.html & public/template/dashboard.html)
 *
 *  Sumber kebenaran warna adalah blok `tailwind.config` di kedua template
 *  tersebut. Nilai di bawah disalin apa adanya — JANGAN diganti ke palet
 *  lain (mis. DESIGN.md) tanpa diminta.
 * ============================================================================
 */

/** Palet Material 3 — identik dengan template patch. */
const palette = {
    // ---- Primary ----
    primary: "#004ac6",
    "primary-container": "#2563eb",
    "on-primary": "#ffffff",
    "on-primary-container": "#eeefff",
    "primary-fixed": "#dbe1ff",
    "primary-fixed-dim": "#b4c5ff",
    "on-primary-fixed": "#00174b",
    "on-primary-fixed-variant": "#003ea8",
    "inverse-primary": "#b4c5ff",
    "surface-tint": "#0053db",

    // ---- Secondary ----
    secondary: "#565e74",
    "on-secondary": "#ffffff",
    "secondary-container": "#dae2fd",
    "on-secondary-container": "#5c647a",
    "secondary-fixed": "#dae2fd",
    "secondary-fixed-dim": "#bec6e0",
    "on-secondary-fixed": "#131b2e",
    "on-secondary-fixed-variant": "#3f465c",

    // ---- Tertiary (sukses) ----
    tertiary: "#006329",
    "on-tertiary": "#ffffff",
    "tertiary-container": "#007f36",
    "on-tertiary-container": "#c7ffca",
    "tertiary-fixed": "#7ffc97",
    "tertiary-fixed-dim": "#62df7d",
    "on-tertiary-fixed": "#002109",
    "on-tertiary-fixed-variant": "#005320",

    // ---- Error ----
    error: "#ba1a1a",
    "on-error": "#ffffff",
    "error-container": "#ffdad6",
    "on-error-container": "#93000a",

    // ---- Permukaan ----
    background: "#f8f9ff",
    surface: "#f8f9ff",
    "surface-bright": "#f8f9ff",
    "surface-dim": "#cbdbf5",
    "surface-variant": "#d3e4fe",
    "surface-container-lowest": "#ffffff",
    "surface-container-low": "#eff4ff",
    "surface-container": "#e5eeff",
    "surface-container-high": "#dce9ff",
    "surface-container-highest": "#d3e4fe",
    "on-surface": "#0b1c30",
    "on-surface-variant": "#434655",
    "on-background": "#0b1c30",
    "inverse-surface": "#213145",
    "inverse-on-surface": "#eaf1ff",

    // ---- Outline ----
    outline: "#737686",
    "outline-variant": "#c3c6d7",
};

/**
 * Alias semantik.
 *
 * Template patch hanya mendefinisikan token M3 di atas, tetapi view memakai
 * beberapa nama yang lebih deskriptif. Semua alias di sini diarahkan ke
 * warna yang SUDAH ADA di template, sehingga tampilan tetap konsisten.
 */
const aliases = {
    // Muted caption / ikon tidak aktif -> outline
    neutral: "#737686",
    // Garis kartu & input -> outline-variant
    "border-subtle": "#c3c6d7",
    "border-default": "#c3c6d7",
    // Primary lebih gelap saat hover/active
    "primary-hover": "#003ea8",
    "primary-active": "#003ea8",
    // Primary very soft fill -> primary-fixed
    "primary-tint": "#dbe1ff",
    // Alias error
    danger: "#ba1a1a",
    "on-danger": "#ffffff",
    // "Menunggu / perlu perhatian" pada template memakai secondary-container
    warning: "#565e74",
    "warning-container": "#dae2fd",
    "on-warning-container": "#5c647a",
    // Canvas = background
    canvas: "#f8f9ff",
};

/** Skala tipografi — identik dengan template patch. */
const typeScale = {
    "label-sm": ["11px", { lineHeight: "14px", letterSpacing: "0.04em", fontWeight: "700" }],
    "label-md": ["12px", { lineHeight: "16px", letterSpacing: "0.02em", fontWeight: "600" }],
    "body-sm": ["13px", { lineHeight: "18px", letterSpacing: "0.005em", fontWeight: "400" }],
    "body-md": ["14px", { lineHeight: "22px", letterSpacing: "0em", fontWeight: "400" }],
    "body-lg": ["16px", { lineHeight: "26px", letterSpacing: "0em", fontWeight: "400" }],
    "title-sm": ["14px", { lineHeight: "20px", letterSpacing: "0em", fontWeight: "600" }],
    "title-md": ["16px", { lineHeight: "24px", letterSpacing: "-0.005em", fontWeight: "600" }],
    "headline-sm": ["20px", { lineHeight: "28px", letterSpacing: "-0.01em", fontWeight: "600" }],
    "headline-md": ["24px", { lineHeight: "32px", letterSpacing: "-0.015em", fontWeight: "600" }],
    "headline-lg": ["30px", { lineHeight: "38px", letterSpacing: "-0.02em", fontWeight: "700" }],
    "headline-lg-mobile": ["24px", { lineHeight: "32px", letterSpacing: "-0.015em", fontWeight: "700" }],
    display: ["36px", { lineHeight: "44px", letterSpacing: "-0.025em", fontWeight: "700" }],
    "display-mobile": ["28px", { lineHeight: "36px", letterSpacing: "-0.02em", fontWeight: "700" }],
};

const jakarta = ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans];

/** Alias font agar `font-<token>` mengikuti skala di atas. */
const typeFamilies = {
    jakarta,
    "label-sm": jakarta,
    "label-md": jakarta,
    "body-sm": jakarta,
    "body-md": jakarta,
    "body-lg": jakarta,
    "title-sm": jakarta,
    "title-md": jakarta,
    "headline-sm": jakarta,
    "headline-md": jakarta,
    "headline-lg": jakarta,
    "headline-lg-mobile": jakarta,
    "display-mobile": jakarta,
    display: jakarta,
};

/** @type {import('tailwindcss').Config} */
export default {
    // Tidak ada mode gelap: aplikasi selalu tampil terang (putih).
    // Variant `dark:` sengaja tidak dipakai di view mana pun.
    content: [
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],

    theme: {
        extend: {
            colors: { ...palette, ...aliases },

            fontSize: typeScale,
            fontFamily: {
                sans: jakarta,
                ...typeFamilies,
            },

            // Shapes — sama dengan template patch.
            borderRadius: {
                sm: "0.25rem",
                DEFAULT: "0.25rem",
                md: "0.5rem",
                lg: "0.5rem",
                xl: "0.75rem",
                "2xl": "0.75rem",
                "3xl": "0.75rem",
                full: "9999px",
            },

            // Elevation — lembut & terdispersi, gaya template (shadow-sm).
            boxShadow: {
                "level-1": "0 1px 2px 0 rgba(0, 0, 0, 0.04)",
                "level-2": "0 1px 8px 0 rgba(0, 0, 0, 0.06)",
                "level-3": "0 4px 16px -2px rgba(0, 0, 0, 0.10)",
                "level-4": "0 12px 32px -8px rgba(0, 0, 0, 0.18)",
            },

            spacing: {
                "space-xs": "0.25rem",
                "space-sm": "0.5rem",
                "space-md": "1rem",
                "space-lg": "1.5rem",
                "space-xl": "2rem",
                gutter: "1.5rem",
                "gutter-mobile": "1rem",
                margin: "2rem",
                "margin-mobile": "1rem",
                "app-bar": "68px",
                "sidebar": "280px",
            },

            maxWidth: {
                canvas: "1600px",
            },
        },
    },

    plugins: [forms],
};
