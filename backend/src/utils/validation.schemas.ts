import { z } from 'zod';

// ========== AUTH SCHEMAS ==========

export const loginSchema = z.object({
  body: z.object({
    email: z.string().email('Invalid email format'),
    password: z.string().min(6, 'Password must be at least 6 characters'),
  }),
});

export const twoFactorSchema = z.object({
  body: z.object({
    token: z.string(),
    code: z.string().length(6, 'Code must be 6 digits'),
  }),
});

export const refreshTokenSchema = z.object({
  body: z.object({
    refreshToken: z.string(),
  }),
});

// ========== OPERATION SCHEMAS ==========

export const createOperationSchema = z.object({
  body: z.object({
    type: z.enum(['deposit', 'withdrawal', 'transfer', 'appro', 'sortie']),
    cashRegisterId: z.number().positive(),
    amount: z.number().positive('Amount must be positive'),
    clientName: z.string().min(2, 'Client name required'),
    accountNumber: z.string().min(1, 'Account number required'),
    depositorName: z.string().optional(),
    depositorPhone: z.string().optional(),
    productId: z.number().positive().optional(),
    notes: z.string().optional(),
    billBreakdown: z.object({
      bills10000: z.number().min(0).optional(),
      bills5000: z.number().min(0).optional(),
      bills2000: z.number().min(0).optional(),
      bills1000: z.number().min(0).optional(),
      bills500: z.number().min(0).optional(),
      bills250: z.number().min(0).optional(),
      bills200: z.number().min(0).optional(),
      bills100: z.number().min(0).optional(),
      coins50: z.number().min(0).optional(),
      coins25: z.number().min(0).optional(),
      coins10: z.number().min(0).optional(),
      coins5: z.number().min(0).optional(),
      coins1: z.number().min(0).optional(),
    }).optional(),
  }),
});

export const approveOperationSchema = z.object({
  params: z.object({
    id: z.string().regex(/^\d+$/).transform(Number),
  }),
  body: z.object({
    level: z.enum(['1', '2']),
    sourceAgencyId: z.number().positive().optional(),
  }),
});

export const validateOperationSchema = z.object({
  params: z.object({
    id: z.string().regex(/^\d+$/).transform(Number),
  }),
  body: z.object({
    sourceAgencyId: z.number().positive(),
  }),
});

// ========== DASHBOARD SCHEMAS ==========

export const dashboardFilterSchema = z.object({
  query: z.object({
    countryId: z.string().regex(/^\d+$/).transform(Number).optional(),
    agencyId: z.string().regex(/^\d+$/).transform(Number).optional(),
    cashRegisterId: z.string().regex(/^\d+$/).transform(Number).optional(),
    dateFrom: z.string().optional(),
    dateTo: z.string().optional(),
  }),
});

// ========== JOURNAL SCHEMAS ==========

export const journalFilterSchema = z.object({
  query: z.object({
    agencyId: z.string().regex(/^\d+$/).transform(Number).optional(),
    productId: z.string().regex(/^\d+$/).transform(Number).optional(),
    dateFrom: z.string(),
    dateTo: z.string(),
    status: z.enum(['all', 'pending', 'validated', 'cancelled']).optional(),
    page: z.string().regex(/^\d+$/).transform(Number).default('1'),
    limit: z.string().regex(/^\d+$/).transform(Number).default('20'),
  }),
});

// ========== CASH REGISTER SCHEMAS ==========

export const transferFundsSchema = z.object({
  body: z.object({
    sourceCashRegisterId: z.number().positive(),
    targetCashRegisterId: z.number().positive(),
    amount: z.number().positive('Amount must be positive'),
    notes: z.string().optional(),
  }),
});

// ========== USER MANAGEMENT SCHEMAS ==========

export const createUserSchema = z.object({
  body: z.object({
    login: z.string().min(3, 'Login must be at least 3 characters'),
    password: z.string().min(8, 'Password must be at least 8 characters')
      .regex(/[A-Z]/, 'Password must contain at least one uppercase letter')
      .regex(/[a-z]/, 'Password must contain at least one lowercase letter')
      .regex(/[0-9]/, 'Password must contain at least one number'),
    name: z.string().min(2, 'Name required'),
    email: z.string().email('Invalid email format'),
    countryId: z.number().positive(),
    partnerId: z.number().positive().optional(),
    permissions: z.array(z.string()).optional(),
    cashRegisterIds: z.array(z.number().positive()).optional(),
  }),
});

export const updateUserSchema = z.object({
  params: z.object({
    id: z.string().regex(/^\d+$/).transform(Number),
  }),
  body: z.object({
    name: z.string().min(2).optional(),
    email: z.string().email().optional(),
    isActive: z.boolean().optional(),
    permissions: z.array(z.string()).optional(),
    cashRegisterIds: z.array(z.number().positive()).optional(),
  }),
});

// ========== REMITTANCE SCHEMAS ==========

export const createRemittanceSchema = z.object({
  body: z.object({
    cashRegisterId: z.number().positive(),
    productId: z.number().positive(),
    type: z.enum(['transfer', 'international']),
    recipientPhone: z.string().min(10, 'Phone number required'),
    recipientName: z.string().min(2, 'Recipient name required'),
    amount: z.number().positive('Amount must be positive'),
    sourceAgencyId: z.number().positive().optional(),
    targetAgencyId: z.number().positive().optional(),
    exchangeRate: z.number().positive().optional(),
    fees: z.number().min(0).optional(),
  }),
});

// ========== ANALYTICS SCHEMAS ==========

export const analyticsFilterSchema = z.object({
  query: z.object({
    dateFrom: z.string(),
    dateTo: z.string(),
    groupBy: z.enum(['day', 'week', 'month']).optional(),
    agencyId: z.string().regex(/^\d+$/).transform(Number).optional(),
    productId: z.string().regex(/^\d+$/).transform(Number).optional(),
    cashRegisterId: z.string().regex(/^\d+$/).transform(Number).optional(),
  }),
});

// ========== VALIDATION MIDDLEWARE ==========

export const validate = (schema: z.ZodSchema) => {
  return (req: any, res: any, next: any) => {
    try {
      schema.parse({
        body: req.body,
        query: req.query,
        params: req.params,
      });
      next();
    } catch (error) {
      if (error instanceof z.ZodError) {
        return res.status(400).json({
          success: false,
          error: 'Validation failed',
          details: error.errors,
        });
      }
      next(error);
    }
  };
};