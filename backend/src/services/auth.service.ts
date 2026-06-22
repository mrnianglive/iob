import bcrypt from 'bcryptjs';
import jwt from 'jsonwebtoken';
import speakeasy from 'speakeasy';
import QRCode from 'qrcode';
import { prisma } from '../config/database';
import { logger } from '../config/logger';

interface LoginCredentials {
  email: string;
  password: string;
}

interface TokenPayload {
  userId: number;
  countryId: number;
  partnerId?: number;
  permissions: string[];
  cashRegisterIds: number[];
}

export class AuthService {
  private readonly jwtSecret = process.env.JWT_SECRET || 'default-secret';
  private readonly jwtRefreshSecret = process.env.JWT_REFRESH_SECRET || 'refresh-secret';
  private readonly jwtExpire = process.env.JWT_EXPIRE || '1h';
  private readonly jwtRefreshExpire = process.env.JWT_REFRESH_EXPIRE || '7d';

  /**
   * Authenticate user with email and password
   */
  async login(credentials: LoginCredentials) {
    const { email, password } = credentials;

    // Find user by email
    const user = await prisma.user.findFirst({
      where: { 
        Email: email,
        IsActive: true 
      },
      include: {
        country: true,
        partner: true,
        permissions: true,
        cashRegisters: {
          include: {
            cashRegister: true,
          },
        },
      },
    });

    if (!user) {
      throw new Error('Invalid credentials');
    }

    // Verify password
    const isValidPassword = await bcrypt.compare(password, user.Password);
    if (!isValidPassword) {
      throw new Error('Invalid credentials');
    }

    // Update last login
    await prisma.user.update({
      where: { RefUser: user.RefUser },
      data: { LastLogin: new Date() },
    });

    // Log connection
    await prisma.logConnection.create({
      data: {
        RefUsers: user.RefUser,
        IP: '', // Will be filled from request
        LogH: new Date(),
        LogoutH: new Date(),
      },
    });

    // Check if 2FA is enabled
    if (user.TwoFactorSecret) {
      // Generate temporary token for 2FA verification
      const tempToken = jwt.sign(
        { userId: user.RefUser, require2FA: true },
        this.jwtSecret,
        { expiresIn: '5m' }
      );

      return {
        success: true,
        require2FA: true,
        tempToken,
        user: {
          id: user.RefUser,
          name: user.Name,
          email: user.Email,
        },
      };
    }

    // Generate tokens
    const tokens = await this.generateTokens(user);

    return {
      success: true,
      require2FA: false,
      ...tokens,
      user: {
        id: user.RefUser,
        name: user.Name,
        email: user.Email,
        country: user.country,
        partner: user.partner,
        permissions: user.permissions.map(p => p.access),
        cashRegisters: user.cashRegisters.map(cr => ({
          id: cr.RefCaisse,
          name: cr.cashRegister.NameCaisse,
        })),
      },
    };
  }

  /**
   * Verify 2FA code
   */
  async verify2FA(tempToken: string, code: string) {
    // Verify temp token
    const payload = jwt.verify(tempToken, this.jwtSecret) as any;
    
    if (!payload.require2FA) {
      throw new Error('Invalid token');
    }

    // Get user with 2FA secret
    const user = await prisma.user.findUnique({
      where: { RefUser: payload.userId },
      include: {
        country: true,
        partner: true,
        permissions: true,
        cashRegisters: {
          include: {
            cashRegister: true,
          },
        },
      },
    });

    if (!user || !user.TwoFactorSecret) {
      throw new Error('2FA not configured');
    }

    // Verify TOTP code
    const verified = speakeasy.totp.verify({
      secret: user.TwoFactorSecret,
      encoding: 'base32',
      token: code,
      window: 2,
    });

    if (!verified) {
      throw new Error('Invalid 2FA code');
    }

    // Generate tokens
    const tokens = await this.generateTokens(user);

    return {
      success: true,
      ...tokens,
      user: {
        id: user.RefUser,
        name: user.Name,
        email: user.Email,
        country: user.country,
        partner: user.partner,
        permissions: user.permissions.map(p => p.access),
        cashRegisters: user.cashRegisters.map(cr => ({
          id: cr.RefCaisse,
          name: cr.cashRegister.NameCaisse,
        })),
      },
    };
  }

  /**
   * Setup 2FA for user
   */
  async setup2FA(userId: number) {
    const user = await prisma.user.findUnique({
      where: { RefUser: userId },
    });

    if (!user) {
      throw new Error('User not found');
    }

    // Generate secret
    const secret = speakeasy.generateSecret({
      name: `IOB Banking (${user.Email})`,
      issuer: process.env.TWO_FACTOR_APP_NAME || 'IOB Banking',
    });

    // Save secret to user
    await prisma.user.update({
      where: { RefUser: userId },
      data: { TwoFactorSecret: secret.base32 },
    });

    // Generate QR code
    const qrCodeUrl = await QRCode.toDataURL(secret.otpauth_url!);

    return {
      success: true,
      secret: secret.base32,
      qrCode: qrCodeUrl,
    };
  }

  /**
   * Disable 2FA for user
   */
  async disable2FA(userId: number) {
    await prisma.user.update({
      where: { RefUser: userId },
      data: { TwoFactorSecret: null },
    });

    return { success: true };
  }

  /**
   * Refresh access token
   */
  async refreshToken(refreshToken: string) {
    try {
      // Verify refresh token
      const payload = jwt.verify(refreshToken, this.jwtRefreshSecret) as any;

      // Get updated user data
      const user = await prisma.user.findUnique({
        where: { RefUser: payload.userId },
        include: {
          permissions: true,
          cashRegisters: true,
        },
      });

      if (!user || !user.IsActive) {
        throw new Error('User not found or inactive');
      }

      // Generate new access token
      const tokenPayload: TokenPayload = {
        userId: user.RefUser,
        countryId: user.RefPays,
        partnerId: user.RefBanque || undefined,
        permissions: user.permissions.map(p => String(p.access)),
        cashRegisterIds: user.cashRegisters.map(cr => cr.RefCaisse),
      };

      const accessToken = jwt.sign(tokenPayload, this.jwtSecret, {
        expiresIn: this.jwtExpire,
      });

      return {
        success: true,
        accessToken,
      };
    } catch (error) {
      logger.error('Refresh token error:', error);
      throw new Error('Invalid refresh token');
    }
  }

  /**
   * Generate access and refresh tokens
   */
  private async generateTokens(user: any) {
    const tokenPayload: TokenPayload = {
      userId: user.RefUser,
      countryId: user.RefPays,
      partnerId: user.RefBanque || undefined,
      permissions: user.permissions.map((p: any) => String(p.access)),
      cashRegisterIds: user.cashRegisters.map((cr: any) => cr.RefCaisse),
    };

    const accessToken = jwt.sign(tokenPayload, this.jwtSecret, {
      expiresIn: this.jwtExpire,
    });

    const refreshToken = jwt.sign(
      { userId: user.RefUser },
      this.jwtRefreshSecret,
      { expiresIn: this.jwtRefreshExpire }
    );

    return {
      accessToken,
      refreshToken,
      expiresIn: this.jwtExpire,
    };
  }

  /**
   * Change user password
   */
  async changePassword(userId: number, oldPassword: string, newPassword: string) {
    const user = await prisma.user.findUnique({
      where: { RefUser: userId },
    });

    if (!user) {
      throw new Error('User not found');
    }

    // Verify old password
    const isValidPassword = await bcrypt.compare(oldPassword, user.Password);
    if (!isValidPassword) {
      throw new Error('Invalid current password');
    }

    // Hash new password
    const hashedPassword = await bcrypt.hash(newPassword, 10);

    // Update password
    await prisma.user.update({
      where: { RefUser: userId },
      data: { Password: hashedPassword },
    });

    logger.info(`Password changed for user ${userId}`);

    return { success: true };
  }

  /**
   * Reset user password (admin function)
   */
  async resetPassword(userId: number, newPassword: string) {
    const hashedPassword = await bcrypt.hash(newPassword, 10);

    await prisma.user.update({
      where: { RefUser: userId },
      data: { Password: hashedPassword },
    });

    logger.info(`Password reset for user ${userId}`);

    return { success: true };
  }
}