import { Server, Socket } from 'socket.io';
import jwt from 'jsonwebtoken';
import { logger } from '../config/logger';
import { prisma } from '../config/database';

interface SocketUser {
  userId: number;
  countryId: number;
  partnerId?: number;
  cashRegisterIds: number[];
}

const connectedUsers = new Map<string, SocketUser>();

export function setupSocketHandlers(io: Server) {
  // Authentication middleware
  io.use(async (socket, next) => {
    try {
      const token = socket.handshake.auth.token;
      
      if (!token) {
        return next(new Error('Authentication required'));
      }

      const payload = jwt.verify(token, process.env.JWT_SECRET || 'default-secret') as any;
      
      // Store user info
      const socketUser: SocketUser = {
        userId: payload.userId,
        countryId: payload.countryId,
        partnerId: payload.partnerId,
        cashRegisterIds: payload.cashRegisterIds || [],
      };

      socket.data.user = socketUser;
      
      next();
    } catch (error) {
      logger.error('Socket authentication error:', error);
      next(new Error('Authentication failed'));
    }
  });

  io.on('connection', (socket: Socket) => {
    const user = socket.data.user as SocketUser;
    logger.info(`User ${user.userId} connected via WebSocket`);
    
    // Store connected user
    connectedUsers.set(socket.id, user);

    // Join rooms based on user context
    socket.join(`country:${user.countryId}`);
    if (user.partnerId) {
      socket.join(`partner:${user.partnerId}`);
    }
    user.cashRegisterIds.forEach(crId => {
      socket.join(`cashRegister:${crId}`);
    });

    // Handle real-time events
    socket.on('subscribe:dashboard', () => {
      socket.join(`dashboard:${user.countryId}`);
      emitDashboardStats(socket, user);
    });

    socket.on('subscribe:operations', (cashRegisterId: number) => {
      if (user.cashRegisterIds.includes(cashRegisterId)) {
        socket.join(`operations:${cashRegisterId}`);
      }
    });

    socket.on('subscribe:balance', (cashRegisterId: number) => {
      if (user.cashRegisterIds.includes(cashRegisterId)) {
        socket.join(`balance:${cashRegisterId}`);
        emitCashRegisterBalance(socket, cashRegisterId);
      }
    });

    socket.on('operation:created', async (data) => {
      // Broadcast to relevant users
      io.to(`country:${user.countryId}`).emit('operation:new', {
        ...data,
        timestamp: new Date(),
      });

      // Update dashboard stats
      await updateDashboardStats(io, user.countryId);
    });

    socket.on('balance:updated', async (data) => {
      const { cashRegisterId, newBalance } = data;
      
      // Broadcast balance update
      io.to(`balance:${cashRegisterId}`).emit('balance:change', {
        cashRegisterId,
        balance: newBalance,
        timestamp: new Date(),
      });
    });

    socket.on('disconnect', () => {
      logger.info(`User ${user.userId} disconnected`);
      connectedUsers.delete(socket.id);
    });
  });

  // Periodic updates
  setInterval(() => {
    updateAllDashboards(io);
  }, 30000); // Every 30 seconds
}

async function emitDashboardStats(socket: Socket, user: SocketUser) {
  try {
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const stats = await prisma.operation.aggregate({
      where: {
        RefPays: user.countryId,
        datePayement: {
          gte: today,
        },
      },
      _sum: {
        MontantVersement: true,
      },
      _count: true,
    });

    const depositStats = await prisma.operation.aggregate({
      where: {
        RefPays: user.countryId,
        RefType: 1, // Deposit
        datePayement: {
          gte: today,
        },
      },
      _sum: {
        MontantVersement: true,
      },
      _count: true,
    });

    const withdrawalStats = await prisma.operation.aggregate({
      where: {
        RefPays: user.countryId,
        RefType: 2, // Withdrawal
        datePayement: {
          gte: today,
        },
      },
      _sum: {
        MontantVersement: true,
      },
      _count: true,
    });

    socket.emit('dashboard:stats', {
      totalOperations: stats._count,
      totalAmount: stats._sum.MontantVersement || 0,
      deposits: {
        count: depositStats._count,
        amount: depositStats._sum.MontantVersement || 0,
      },
      withdrawals: {
        count: withdrawalStats._count,
        amount: withdrawalStats._sum.MontantVersement || 0,
      },
      timestamp: new Date(),
    });
  } catch (error) {
    logger.error('Error emitting dashboard stats:', error);
  }
}

async function emitCashRegisterBalance(socket: Socket, cashRegisterId: number) {
  try {
    // Calculate current balance
    const deposits = await prisma.operation.aggregate({
      where: {
        RefCaisse: cashRegisterId,
        RefType: 1, // Deposits
        Validate: 2, // Validated
      },
      _sum: {
        MontantVersement: true,
      },
    });

    const withdrawals = await prisma.operation.aggregate({
      where: {
        RefCaisse: cashRegisterId,
        RefType: 2, // Withdrawals
        Validate: 2, // Validated
      },
      _sum: {
        MontantVersement: true,
      },
    });

    const balance = 
      parseFloat(deposits._sum.MontantVersement || '0') - 
      parseFloat(withdrawals._sum.MontantVersement || '0');

    socket.emit('balance:current', {
      cashRegisterId,
      balance,
      timestamp: new Date(),
    });
  } catch (error) {
    logger.error('Error emitting cash register balance:', error);
  }
}

async function updateDashboardStats(io: Server, countryId: number) {
  const room = `dashboard:${countryId}`;
  const sockets = await io.in(room).fetchSockets();
  
  for (const socket of sockets) {
    const user = connectedUsers.get(socket.id);
    if (user) {
      emitDashboardStats(socket as any, user);
    }
  }
}

async function updateAllDashboards(io: Server) {
  // Get all unique country IDs from connected users
  const countryIds = new Set<number>();
  connectedUsers.forEach(user => {
    countryIds.add(user.countryId);
  });

  // Update each country's dashboard
  for (const countryId of countryIds) {
    await updateDashboardStats(io, countryId);
  }
}

// Export functions for use in other modules
export function broadcastOperationUpdate(io: Server, operation: any) {
  io.to(`country:${operation.RefPays}`).emit('operation:updated', {
    operation,
    timestamp: new Date(),
  });
}

export function broadcastBalanceAlert(io: Server, cashRegisterId: number, alert: any) {
  io.to(`balance:${cashRegisterId}`).emit('balance:alert', {
    cashRegisterId,
    alert,
    timestamp: new Date(),
  });
}