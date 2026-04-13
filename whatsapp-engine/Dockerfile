FROM node:20-alpine

WORKDIR /app

RUN apk add --no-cache ffmpeg python3 make g++ git

COPY package.json .
RUN npm install

COPY . .

EXPOSE 3000

CMD ["npm", "start"]
