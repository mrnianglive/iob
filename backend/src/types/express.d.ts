import { User } from '@prisma/client';

export interface TenantContext {
  countryId: number;
  partnerId?: number;
  permissions: string[];
  cashRegisterIds: number[];
}

export interface AuthenticatedUser extends Omit<User, 'Password'> {
  country?: any;
  partner?: any;
  permissions?: any[];
  cashRegisters?: any[];
}

declare global {
  namespace Express {
    interface Request {
      user?: AuthenticatedUser;
      tenantContext?: TenantContext;
    }
  }
}

export {};