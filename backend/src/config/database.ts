import { PrismaClient } from '@prisma/client';
import { logger } from './logger';

// Extend PrismaClient with logging in development
const prisma = new PrismaClient({
  log: process.env.NODE_ENV === 'development' 
    ? ['query', 'info', 'warn', 'error']
    : ['error'],
  errorFormat: 'pretty',
});

// Handle connection events
prisma.$on('query' as never, (e: any) => {
  if (process.env.NODE_ENV === 'development') {
    logger.debug('Query: ' + e.query);
    logger.debug('Duration: ' + e.duration + 'ms');
  }
});

// Middleware for automatic multi-tenant filtering
prisma.$use(async (params, next) => {
  // Add tenant filtering logic here if needed
  // This can be enhanced based on the context
  
  const result = await next(params);
  return result;
});

// Test database connection
export async function connectDatabase() {
  try {
    await prisma.$connect();
    logger.info('✅ Database connected successfully');
    
    // Test query to verify connection
    const userCount = await prisma.user.count();
    logger.info(`Database has ${userCount} users`);
    
    return true;
  } catch (error) {
    logger.error('❌ Database connection failed:', error);
    throw error;
  }
}

// Graceful shutdown
export async function disconnectDatabase() {
  await prisma.$disconnect();
  logger.info('Database disconnected');
}

export { prisma };