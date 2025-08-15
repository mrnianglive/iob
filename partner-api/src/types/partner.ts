export interface PartnerContext {
  partnerId: number;
  userId?: number;
  permissions: string[];
  apiKey?: string;
}

export interface PartnerLoginRequest {
  email: string;
  password: string;
  partner_code: string;
}

export interface PartnerLoginResponse {
  access_token: string;
  refresh_token: string;
  partner: {
    id: number;
    name: string;
    country: string;
    logo?: string;
    permissions: string[];
  };
  expires_in: number;
}

export interface PartnerStats {
  operations_count: number;
  total_volume: number;
  commissions: number;
  agencies_count: number;
  active_users: number;
  operations_trend?: number;
  volume_trend?: number;
  commission_trend?: number;
}

export interface OperationFilters {
  date_from?: string;
  date_to?: string;
  status?: string;
  agency_id?: number;
  product_id?: number;
  amount_min?: number;
  amount_max?: number;
  client_name?: string;
  reference?: string;
  page?: number;
  limit?: number;
  sort?: string;
  order?: 'asc' | 'desc';
}

export interface DateRange {
  start: Date;
  end: Date;
}

export interface ApiResponse<T> {
  success: boolean;
  data: T;
  message?: string;
  pagination?: {
    page: number;
    limit: number;
    total: number;
    pages: number;
  };
}

export interface PartnerPermission {
  VIEW_OPERATIONS: 'view_operations';
  VIEW_ANALYTICS: 'view_analytics';
  VIEW_AGENCIES: 'view_agencies';
  EXPORT_DATA: 'export_data';
  MANAGE_USERS: 'manage_users';
  API_ACCESS: 'api_access';
}

export const PARTNER_PERMISSIONS = {
  VIEW_OPERATIONS: 'view_operations',
  VIEW_ANALYTICS: 'view_analytics',
  VIEW_AGENCIES: 'view_agencies',
  EXPORT_DATA: 'export_data',
  MANAGE_USERS: 'manage_users',
  API_ACCESS: 'api_access',
} as const;

export type PartnerPermissionType = typeof PARTNER_PERMISSIONS[keyof typeof PARTNER_PERMISSIONS];

export interface PartnerConfig {
  partner_id: number;
  name: string;
  country: string;
  logo_url?: string;
  primary_color?: string;
  secondary_color?: string;
  api_key: string;
  api_secret: string;
  rate_limit: number;
  features: {
    analytics: boolean;
    export: boolean;
    webhooks: boolean;
    api_access: boolean;
  };
  notification_email?: string;
  webhook_urls: string[];
}

export interface WebhookPayload {
  event: string;
  data: any;
  timestamp: string;
  partner_id: number;
}

export interface PartnerWebhookConfig {
  url: string;
  events: string[];
  secret: string;
  is_active: boolean;
}

export interface ExportOptions {
  format: 'pdf' | 'excel' | 'csv';
  filters: OperationFilters;
  columns?: string[];
  template?: string;
}

// Request extensions
declare global {
  namespace Express {
    interface Request {
      partnerContext?: PartnerContext;
    }
  }
}