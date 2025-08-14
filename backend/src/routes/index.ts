import { Application } from 'express';
import authRoutes from './auth.routes';
import dashboardRoutes from './dashboard.routes';
import operationRoutes from './operation.routes';
import journalRoutes from './journal.routes';
import cashRegisterRoutes from './cashRegister.routes';
import userRoutes from './user.routes';
import analyticsRoutes from './analytics.routes';
import remittanceRoutes from './remittance.routes';

export function setupRoutes(app: Application) {
  // API prefix
  const apiPrefix = '/api';

  // Health check
  app.get(`${apiPrefix}/health`, (req, res) => {
    res.json({
      status: 'healthy',
      timestamp: new Date().toISOString(),
    });
  });

  // Auth routes (public)
  app.use(`${apiPrefix}/auth`, authRoutes);

  // Protected routes
  app.use(`${apiPrefix}/dashboard`, dashboardRoutes);
  app.use(`${apiPrefix}/operations`, operationRoutes);
  app.use(`${apiPrefix}/journal`, journalRoutes);
  app.use(`${apiPrefix}/cash-registers`, cashRegisterRoutes);
  app.use(`${apiPrefix}/users`, userRoutes);
  app.use(`${apiPrefix}/analytics`, analyticsRoutes);
  app.use(`${apiPrefix}/remittances`, remittanceRoutes);

  // Catch-all for undefined API routes
  app.all(`${apiPrefix}/*`, (req, res) => {
    res.status(404).json({
      success: false,
      error: 'API endpoint not found',
      path: req.path,
    });
  });
}