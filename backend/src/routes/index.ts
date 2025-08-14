import { Application } from 'express';
import authRoutes from './auth.routes';
import dashboardRoutes from './dashboard.routes';
import operationRoutes from './operation.routes';
import journalRoutes from './journal.routes';
import cashRegisterRoutes from './cashRegister.routes';
import userRoutes from './user.routes';
import analyticsRoutes from './analytics.routes';
import remittanceRoutes from './remittance.routes';
import countryRoutes from './country.routes';
import partnerRoutes from './partner.routes';

export function setupRoutes(app: Application) {
  // API prefix with version
  const apiPrefix = '/api/v1';

  // Health check
  app.get('/api/health', (req, res) => {
    res.json({
      status: 'healthy',
      timestamp: new Date().toISOString(),
      version: 'v1.0.0',
    });
  });

  // Auth routes (public)
  app.use(`${apiPrefix}/auth`, authRoutes);

  // Protected routes
  app.use(`${apiPrefix}/dashboard`, dashboardRoutes);
  app.use(`${apiPrefix}/operations`, operationRoutes);
  app.use(`${apiPrefix}/journal`, journalRoutes);
  app.use(`${apiPrefix}/journals`, journalRoutes); // Alias for compatibility
  app.use(`${apiPrefix}/cash-registers`, cashRegisterRoutes);
  app.use(`${apiPrefix}/users`, userRoutes);
  app.use(`${apiPrefix}/analytics`, analyticsRoutes);
  app.use(`${apiPrefix}/remittances`, remittanceRoutes);
  app.use(`${apiPrefix}/countries`, countryRoutes);
  app.use(`${apiPrefix}/partners`, partnerRoutes);

  // Catch-all for undefined API routes
  app.all(`${apiPrefix}/*`, (req, res) => {
    res.status(404).json({
      success: false,
      error: 'API endpoint not found',
      path: req.path,
    });
  });
}