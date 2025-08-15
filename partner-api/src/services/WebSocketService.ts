import { Server as SocketIOServer } from 'socket.io';
import { Server as HttpServer } from 'http';
import jwt from 'jsonwebtoken';
import { PrismaClient } from '@prisma/client';
import { logger } from '@/utils/logger';

interface SocketUser {
  userId: number;
  partnerId: number;
  email: string;
  role: string;
}

export class WebSocketService {
  private io: SocketIOServer;
  private prisma: PrismaClient;
  private connectedUsers: Map<string, SocketUser> = new Map();

  constructor(server: HttpServer) {
    this.prisma = new PrismaClient();
    this.io = new SocketIOServer(server, {
      cors: {
        origin: process.env.FRONTEND_URL || "http://localhost:3000",
        methods: ["GET", "POST"],
        credentials: true
      },
      path: '/partner-socket.io'
    });

    this.setupMiddleware();
    this.setupEventHandlers();
  }

  private setupMiddleware() {
    // Authentification des connexions WebSocket
    this.io.use(async (socket, next) => {
      try {
        const token = socket.handshake.auth.token || socket.handshake.headers.authorization?.replace('Bearer ', '');
        
        if (!token) {
          return next(new Error('Authentication token required'));
        }

        const payload = jwt.verify(token, process.env.PARTNER_JWT_SECRET!) as any;
        
        // Vérification que l'utilisateur existe et est actif
        const user = await this.prisma.partnerUser.findUnique({
          where: { RefUser: payload.userId },
          include: {
            partner: {
              select: { IsActive: true }
            }
          }
        });

        if (!user || !user.IsActive || !user.partner.IsActive) {
          return next(new Error('User or partner inactive'));
        }

        // Stockage des informations utilisateur dans le socket
        socket.data.user = {
          userId: payload.userId,
          partnerId: payload.partnerId,
          email: payload.email,
          role: payload.role
        };

        next();
      } catch (error) {
        logger.warn('WebSocket authentication failed', { error: error.message });
        next(new Error('Invalid authentication token'));
      }
    });
  }

  private setupEventHandlers() {
    this.io.on('connection', (socket) => {
      const user: SocketUser = socket.data.user;
      
      logger.info('WebSocket connection established', {
        userId: user.userId,
        partnerId: user.partnerId,
        socketId: socket.id
      });

      // Stockage de la connexion utilisateur
      this.connectedUsers.set(socket.id, user);

      // Rejoindre la room du partenaire pour les notifications ciblées
      socket.join(`partner_${user.partnerId}`);

      // Gestion des événements
      this.handleDashboardSubscription(socket);
      this.handleOperationsSubscription(socket);
      this.handleCaisseSubscription(socket);
      this.handleDisconnection(socket);

      // Envoi des données initiales
      this.sendInitialData(socket, user);
    });
  }

  private handleDashboardSubscription(socket: any) {
    socket.on('subscribe_dashboard', () => {
      const user: SocketUser = socket.data.user;
      socket.join(`dashboard_${user.partnerId}`);
      
      logger.debug('User subscribed to dashboard updates', {
        userId: user.userId,
        partnerId: user.partnerId
      });
    });

    socket.on('unsubscribe_dashboard', () => {
      const user: SocketUser = socket.data.user;
      socket.leave(`dashboard_${user.partnerId}`);
    });
  }

  private handleOperationsSubscription(socket: any) {
    socket.on('subscribe_operations', () => {
      const user: SocketUser = socket.data.user;
      socket.join(`operations_${user.partnerId}`);
      
      logger.debug('User subscribed to operations updates', {
        userId: user.userId,
        partnerId: user.partnerId
      });
    });

    socket.on('unsubscribe_operations', () => {
      const user: SocketUser = socket.data.user;
      socket.leave(`operations_${user.partnerId}`);
    });
  }

  private handleCaisseSubscription(socket: any) {
    socket.on('subscribe_caisse', (caisseId: number) => {
      const user: SocketUser = socket.data.user;
      socket.join(`caisse_${caisseId}_${user.partnerId}`);
      
      logger.debug('User subscribed to caisse updates', {
        userId: user.userId,
        partnerId: user.partnerId,
        caisseId
      });
    });

    socket.on('unsubscribe_caisse', (caisseId: number) => {
      const user: SocketUser = socket.data.user;
      socket.leave(`caisse_${caisseId}_${user.partnerId}`);
    });
  }

  private handleDisconnection(socket: any) {
    socket.on('disconnect', () => {
      const user = this.connectedUsers.get(socket.id);
      if (user) {
        logger.info('WebSocket connection closed', {
          userId: user.userId,
          partnerId: user.partnerId,
          socketId: socket.id
        });
        this.connectedUsers.delete(socket.id);
      }
    });
  }

  private async sendInitialData(socket: any, user: SocketUser) {
    try {
      // Envoi des statistiques initiales du dashboard
      const dashboardStats = await this.getDashboardStats(user.partnerId);
      socket.emit('dashboard_stats', dashboardStats);

      // Envoi du nombre d'opérations en attente
      const pendingOperations = await this.getPendingOperationsCount(user.partnerId);
      socket.emit('pending_operations_count', pendingOperations);

    } catch (error) {
      logger.error('Error sending initial WebSocket data', {
        userId: user.userId,
        error: error.message
      });
    }
  }

  // ===== MÉTHODES PUBLIQUES POUR ÉMETTRE DES ÉVÉNEMENTS =====

  /**
   * Notifie tous les utilisateurs d'un partenaire d'une nouvelle opération
   */
  public notifyNewOperation(partnerId: number, operation: any) {
    this.io.to(`partner_${partnerId}`).emit('new_operation', {
      id: operation.id,
      type: operation.type,
      amount: operation.amount,
      status: operation.status,
      createdAt: operation.createdAt
    });

    this.io.to(`operations_${partnerId}`).emit('operations_updated');
    
    logger.debug('New operation notification sent', { partnerId, operationId: operation.id });
  }

  /**
   * Notifie les changements de statut d'opération
   */
  public notifyOperationStatusChange(partnerId: number, operationId: number, newStatus: string) {
    this.io.to(`partner_${partnerId}`).emit('operation_status_changed', {
      operationId,
      newStatus,
      timestamp: new Date()
    });

    this.io.to(`operations_${partnerId}`).emit('operations_updated');
    
    logger.debug('Operation status change notification sent', { partnerId, operationId, newStatus });
  }

  /**
   * Notifie les mises à jour de solde de caisse
   */
  public notifyCaisseBalanceUpdate(partnerId: number, caisseId: number, newBalance: number) {
    this.io.to(`caisse_${caisseId}_${partnerId}`).emit('caisse_balance_updated', {
      caisseId,
      newBalance,
      timestamp: new Date()
    });

    this.io.to(`dashboard_${partnerId}`).emit('dashboard_stats_updated');
    
    logger.debug('Caisse balance update notification sent', { partnerId, caisseId, newBalance });
  }

  /**
   * Notifie les nouvelles remittances
   */
  public notifyNewRemittance(partnerId: number, remittance: any) {
    this.io.to(`partner_${partnerId}`).emit('new_remittance', {
      id: remittance.id,
      sourceAgency: remittance.sourceAgency,
      destinationAgency: remittance.destinationAgency,
      amount: remittance.amount,
      status: remittance.status
    });
    
    logger.debug('New remittance notification sent', { partnerId, remittanceId: remittance.id });
  }

  /**
   * Notifie les mises à jour des statistiques du dashboard
   */
  public async notifyDashboardStatsUpdate(partnerId: number) {
    try {
      const stats = await this.getDashboardStats(partnerId);
      this.io.to(`dashboard_${partnerId}`).emit('dashboard_stats_updated', stats);
      
      logger.debug('Dashboard stats update notification sent', { partnerId });
    } catch (error) {
      logger.error('Error sending dashboard stats update', { partnerId, error: error.message });
    }
  }

  /**
   * Envoie une notification système à tous les utilisateurs d'un partenaire
   */
  public sendSystemNotification(partnerId: number, notification: {
    type: 'info' | 'warning' | 'error' | 'success';
    title: string;
    message: string;
    persistent?: boolean;
  }) {
    this.io.to(`partner_${partnerId}`).emit('system_notification', {
      ...notification,
      timestamp: new Date(),
      id: Date.now().toString()
    });
    
    logger.info('System notification sent', { partnerId, type: notification.type, title: notification.title });
  }

  /**
   * Récupère le nombre d'utilisateurs connectés pour un partenaire
   */
  public getConnectedUsersCount(partnerId: number): number {
    let count = 0;
    for (const user of this.connectedUsers.values()) {
      if (user.partnerId === partnerId) {
        count++;
      }
    }
    return count;
  }

  /**
   * Ferme proprement le service WebSocket
   */
  public close() {
    this.io.close();
    this.prisma.$disconnect();
    logger.info('WebSocket service closed');
  }

  // ===== MÉTHODES PRIVÉES =====

  private async getDashboardStats(partnerId: number) {
    // Implémentation simplifiée - à adapter selon le schéma Prisma réel
    try {
      const today = new Date();
      today.setHours(0, 0, 0, 0);

      // Note: Adapter les noms de tables selon le schéma Prisma réel
      const [todayOperations, totalBalance] = await Promise.all([
        this.prisma.$queryRaw`
          SELECT COUNT(*) as count, COALESCE(SUM(Amount), 0) as total
          FROM TbleOperations 
          WHERE RefBanque = ${partnerId} 
          AND DATE(CreatedAt) = DATE(${today})
        `,
        this.prisma.$queryRaw`
          SELECT COALESCE(SUM(Balance), 0) as total
          FROM TbleCaisse c
          INNER JOIN TbleAgency a ON c.RefAgency = a.RefAgency
          WHERE a.RefBanque = ${partnerId}
        `
      ]);

      return {
        todayOperationsCount: Number((todayOperations as any)[0]?.count || 0),
        todayVolume: Number((todayOperations as any)[0]?.total || 0),
        totalBalance: Number((totalBalance as any)[0]?.total || 0),
        lastUpdate: new Date()
      };
    } catch (error) {
      logger.error('Error fetching dashboard stats', { partnerId, error: error.message });
      return {
        todayOperationsCount: 0,
        todayVolume: 0,
        totalBalance: 0,
        lastUpdate: new Date()
      };
    }
  }

  private async getPendingOperationsCount(partnerId: number): Promise<number> {
    try {
      const result = await this.prisma.$queryRaw`
        SELECT COUNT(*) as count
        FROM TbleOperations 
        WHERE RefBanque = ${partnerId} 
        AND Status = 'pending'
      `;
      
      return Number((result as any)[0]?.count || 0);
    } catch (error) {
      logger.error('Error fetching pending operations count', { partnerId, error: error.message });
      return 0;
    }
  }
}
