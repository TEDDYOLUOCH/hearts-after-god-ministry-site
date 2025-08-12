/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./dashboard/**/*.{php,js,html}",
        "./backend/**/*.{php,js}",
        "./includes/**/*.php",
        "./src/**/*.css",
        "./errors/**/*.php",
        "./*.php"
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
}