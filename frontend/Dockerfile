# Build stage
FROM node:20-alpine AS build
WORKDIR /app
COPY package.json package-lock.json* pnpm-lock.yaml* yarn.lock* ./
RUN npm install --frozen-lockfile || yarn install || pnpm install --frozen-lockfile
COPY . .
RUN npm run build || yarn build || pnpm build

# Production stage
FROM node:20-alpine AS prod
WORKDIR /app
COPY --from=build /app/build ./build
COPY --from=build /app/package.json ./
RUN npm install --omit=dev || yarn install --production || pnpm install --prod

EXPOSE 3000
CMD ["node", "build"]
