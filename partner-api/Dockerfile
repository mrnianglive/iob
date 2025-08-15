# Dockerfile multi-stage pour l'API Partner IOB
FROM node:18-alpine AS builder

# Installer les dépendances système nécessaires
RUN apk add --no-cache python3 make g++

WORKDIR /app

# Copier les fichiers de dépendances
COPY package*.json ./
COPY tsconfig.json ./

# Installer toutes les dépendances (y compris dev)
RUN npm ci

# Copier le code source
COPY . .

# Générer le client Prisma
RUN npx prisma generate

# Compiler TypeScript
RUN npm run build

# Stage de production
FROM node:18-alpine AS production

# Créer un utilisateur non-root
RUN addgroup -g 1001 -S nodejs
RUN adduser -S iob -u 1001

# Installer les dépendances système pour la production
RUN apk add --no-cache \
    curl \
    dumb-init

WORKDIR /app

# Copier les fichiers de dépendances
COPY package*.json ./

# Installer uniquement les dépendances de production
RUN npm ci --only=production && npm cache clean --force

# Copier les fichiers compilés depuis le stage builder
COPY --from=builder /app/dist ./dist
COPY --from=builder /app/node_modules/.prisma ./node_modules/.prisma
COPY --from=builder /app/prisma ./prisma

# Créer les dossiers nécessaires
RUN mkdir -p logs uploads && \
    chown -R iob:nodejs /app

# Changer vers l'utilisateur non-root
USER iob

# Exposer le port
EXPOSE 3001

# Variables d'environnement par défaut
ENV NODE_ENV=production
ENV PORT=3001

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=30s --retries=3 \
    CMD curl -f http://localhost:3001/health || exit 1

# Utiliser dumb-init pour la gestion des signaux
ENTRYPOINT ["dumb-init", "--"]

# Commande de démarrage
CMD ["node", "dist/index.js"]