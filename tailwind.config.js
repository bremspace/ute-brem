/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#f0f4ff',
          100: '#e0eaff',
          200: '#c7d7fe',
          300: '#a4bcfd',
          400: '#7c9afb',
          500: '#5c73f8',
          600: '#4f55eb',
          700: '#4040d3',
          800: '#3535ab',
          900: '#2f3187',
        }
      }
    },
  },
  plugins: [],
}
