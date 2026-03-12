# Build stage
FROM node:20 AS build
WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm install --frozen-lockfile
COPY . .
RUN npm run build

# Production stage
FROM node:20 AS prod
WORKDIR /app
COPY --from=build /app/package.json ./
COPY --from=build /app/build ./build
COPY --from=build /app/node_modules ./node_modules
EXPOSE 3000
CMD ["node", "build/index.js"]
