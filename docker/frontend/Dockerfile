FROM node:22-alpine

WORKDIR /app/frontend

COPY frontend/package.json ./
RUN npm install --no-audit --no-fund

COPY frontend ./

EXPOSE 5173

CMD ["npm", "run", "dev"]
