/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./frontend/**/*.{php,html,js}",
    "./frontend/includes/**/*.php",
    "./frontend/pages/**/*.php"
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Open Sans', 'sans-serif'],
        display: ['Montserrat', 'sans-serif'],
      },
      colors: {
        primary: {
          purple: '#7C3AED',
          amber: '#F59E0B',
          blue: '#3B82F6',
        },
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
  ],
}
