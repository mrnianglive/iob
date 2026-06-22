import { Request, Response } from 'express';
import { AuthService } from '../services/auth.service';
import { logger } from '../config/logger';

export class AuthController {
  private authService: AuthService;

  constructor() {
    this.authService = new AuthService();
  }

  login = async (req: Request, res: Response) => {
    try {
      const { email, password } = req.body;
      const clientIp = req.ip || req.connection.remoteAddress || '';
      
      const result = await this.authService.login({ email, password }, clientIp);
      
      res.json(result);
    } catch (error: any) {
      logger.error('Login error:', error);
      res.status(401).json({
        success: false,
        error: error.message || 'Authentication failed',
      });
    }
  };

  verify2FA = async (req: Request, res: Response) => {
    try {
      const { token, code } = req.body;
      
      const result = await this.authService.verify2FA(token, code);
      
      res.json(result);
    } catch (error: any) {
      logger.error('2FA verification error:', error);
      res.status(401).json({
        success: false,
        error: error.message || '2FA verification failed',
      });
    }
  };

  refreshToken = async (req: Request, res: Response) => {
    try {
      const { refreshToken } = req.body;
      
      const result = await this.authService.refreshToken(refreshToken);
      
      res.json(result);
    } catch (error: any) {
      logger.error('Token refresh error:', error);
      res.status(401).json({
        success: false,
        error: error.message || 'Token refresh failed',
      });
    }
  };

  getProfile = async (req: Request, res: Response) => {
    try {
      if (!req.user) {
        return res.status(401).json({
          success: false,
          error: 'User not authenticated',
        });
      }

      res.json({
        success: true,
        user: {
          id: req.user.RefUser,
          name: req.user.Name,
          email: req.user.Email,
          country: req.user.country,
          partner: req.user.partner,
          permissions: req.user.permissions,
          cashRegisters: req.user.cashRegisters,
        },
      });
    } catch (error: any) {
      logger.error('Get profile error:', error);
      res.status(500).json({
        success: false,
        error: 'Failed to get profile',
      });
    }
  };

  logout = async (req: Request, res: Response) => {
    try {
      // Log the logout action
      if (req.user) {
        logger.info(`User ${req.user.RefUser} logged out`);
      }

      res.json({
        success: true,
        message: 'Logged out successfully',
      });
    } catch (error: any) {
      logger.error('Logout error:', error);
      res.status(500).json({
        success: false,
        error: 'Logout failed',
      });
    }
  };

  changePassword = async (req: Request, res: Response) => {
    try {
      const { oldPassword, newPassword } = req.body;
      
      if (!req.user) {
        return res.status(401).json({
          success: false,
          error: 'User not authenticated',
        });
      }

      const result = await this.authService.changePassword(
        req.user.RefUser,
        oldPassword,
        newPassword
      );
      
      res.json(result);
    } catch (error: any) {
      logger.error('Change password error:', error);
      res.status(400).json({
        success: false,
        error: error.message || 'Failed to change password',
      });
    }
  };

  setup2FA = async (req: Request, res: Response) => {
    try {
      if (!req.user) {
        return res.status(401).json({
          success: false,
          error: 'User not authenticated',
        });
      }

      const result = await this.authService.setup2FA(req.user.RefUser);
      
      res.json(result);
    } catch (error: any) {
      logger.error('Setup 2FA error:', error);
      res.status(500).json({
        success: false,
        error: error.message || 'Failed to setup 2FA',
      });
    }
  };

  disable2FA = async (req: Request, res: Response) => {
    try {
      if (!req.user) {
        return res.status(401).json({
          success: false,
          error: 'User not authenticated',
        });
      }

      const result = await this.authService.disable2FA(req.user.RefUser);
      
      res.json(result);
    } catch (error: any) {
      logger.error('Disable 2FA error:', error);
      res.status(500).json({
        success: false,
        error: error.message || 'Failed to disable 2FA',
      });
    }
  };

  forgotPassword = async (req: Request, res: Response) => {
    try {
      const { email } = req.body;
      
      // TODO: Implement password reset email logic
      logger.info(`Password reset requested for ${email}`);
      
      res.json({
        success: true,
        message: 'Password reset instructions sent to email',
      });
    } catch (error: any) {
      logger.error('Forgot password error:', error);
      res.status(500).json({
        success: false,
        error: 'Failed to process password reset',
      });
    }
  };

  resetPassword = async (req: Request, res: Response) => {
    try {
      const { token, newPassword } = req.body;
      
      // TODO: Implement password reset logic with token validation
      
      res.json({
        success: true,
        message: 'Password reset successfully',
      });
    } catch (error: any) {
      logger.error('Reset password error:', error);
      res.status(400).json({
        success: false,
        error: error.message || 'Failed to reset password',
      });
    }
  };
}