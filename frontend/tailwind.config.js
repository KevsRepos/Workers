import colors from 'tailwindcss/colors';

/** @type {import('tailwindcss').Config} */
export default {
  theme: {
    extend: {
      colors: {
        ion: {
          input: {
            solidBg: colors.gray[50],
          },
        },
      },
    },
  },
};
