import 'cross-fetch/polyfill';
import { createHmac } from 'crypto';

// Types et interfaces
export interface PartnerConfig {
  apiKey: string;
  apiSecret: string;
  baseUrl?: string;
  timeout?: number;
}

export interface OperationFilters {
  date_from?: string;
  date_to?: string;
  status?: 'pending' | 'approved' | 'rejected' | 'cancelled';
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

export interface Operation {
  RefOperation: number;
  Reference: string;
  ClientName: string;
  ClientPhone: string;
  Amount: number;
  Commission: number;
  Status: string;
  Insert_Time: string;
  cashRegister?: {
    agency?: {
      NameAgence: string;
      country?: {
        NamePays: string;
      };
    };
  };
  product?: {
    NameProduit: string;
  };
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

export interface ExportOptions {
  format: 'pdf' | 'excel' | 'csv';
  filters?: OperationFilters;
  columns?: string[];
  template?: string;
}

export interface WebhookConfig {
  url: string;
  events: string[];
  is_active?: boolean;
}

export class IOBPartnerSDKError extends Error {
  constructor(
    message: string,
    public statusCode?: number,
    public response?: any
  ) {
    super(message);
    this.name = 'IOBPartnerSDKError';
  }
}

/**
 * SDK pour l'intégration avec l'API IOB Partner
 * 
 * @example
 * ```typescript
 * const sdk = new IOBPartnerSDK({
 *   apiKey: 'your-api-key',
 *   apiSecret: 'your-api-secret',
 *   baseUrl: 'https://api.iob.com/partner-api'
 * });
 * 
 * // Récupérer les statistiques
 * const stats = await sdk.getStats();
 * 
 * // Récupérer les opérations
 * const operations = await sdk.getOperations({
 *   date_from: '2024-01-01',
 *   limit: 50
 * });
 * ```
 */
export class IOBPartnerSDK {
  private config: Required<PartnerConfig>;

  constructor(config: PartnerConfig) {
    this.config = {
      baseUrl: 'https://api.iob.com/partner-api',
      timeout: 30000,
      ...config
    };

    if (!this.config.apiKey || !this.config.apiSecret) {
      throw new IOBPartnerSDKError('API key and secret are required');
    }
  }

  // ===== MÉTHODES PUBLIQUES =====

  /**
   * Récupérer les statistiques du partenaire
   */
  async getStats(dateRange?: { date_from?: string; date_to?: string }): Promise<PartnerStats> {
    const queryParams = new URLSearchParams();
    if (dateRange?.date_from) queryParams.append('date_from', dateRange.date_from);
    if (dateRange?.date_to) queryParams.append('date_to', dateRange.date_to);

    const response = await this.makeRequest<PartnerStats>(
      'GET',
      `/dashboard/stats${queryParams.toString() ? '?' + queryParams.toString() : ''}`
    );

    return response.data;
  }

  /**
   * Récupérer les opérations du partenaire
   */
  async getOperations(filters?: OperationFilters): Promise<{
    operations: Operation[];
    pagination?: ApiResponse<Operation[]>['pagination'];
  }> {
    const queryParams = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
          queryParams.append(key, value.toString());
        }
      });
    }

    const response = await this.makeRequest<Operation[]>(
      'GET',
      `/operations${queryParams.toString() ? '?' + queryParams.toString() : ''}`
    );

    return {
      operations: response.data,
      pagination: response.pagination
    };
  }

  /**
   * Récupérer une opération spécifique
   */
  async getOperation(operationId: number): Promise<Operation> {
    const response = await this.makeRequest<Operation>('GET', `/operations/${operationId}`);
    return response.data;
  }

  /**
   * Récupérer les agences du partenaire
   */
  async getAgencies(): Promise<any[]> {
    const response = await this.makeRequest<any[]>('GET', '/agencies');
    return response.data;
  }

  /**
   * Récupérer les produits du partenaire
   */
  async getProducts(): Promise<any[]> {
    const response = await this.makeRequest<any[]>('GET', '/products');
    return response.data;
  }

  /**
   * Exporter les opérations
   */
  async exportOperations(options: ExportOptions): Promise<Blob> {
    const response = await this.makeRequest<ArrayBuffer>(
      'POST',
      '/operations/export',
      options,
      { responseType: 'blob' }
    );

    return new Blob([response.data], {
      type: this.getContentTypeForFormat(options.format)
    });
  }

  /**
   * Récupérer les analytics avancés
   */
  async getAnalytics(dateRange: { date_from: string; date_to: string }): Promise<any> {
    const queryParams = new URLSearchParams({
      date_from: dateRange.date_from,
      date_to: dateRange.date_to
    });

    const response = await this.makeRequest<any>(
      'GET',
      `/analytics/operations?${queryParams.toString()}`
    );

    return response.data;
  }

  /**
   * Récupérer les analytics de commissions
   */
  async getCommissionsAnalytics(dateRange: { date_from: string; date_to: string }): Promise<any> {
    const queryParams = new URLSearchParams({
      date_from: dateRange.date_from,
      date_to: dateRange.date_to
    });

    const response = await this.makeRequest<any>(
      'GET',
      `/analytics/commissions?${queryParams.toString()}`
    );

    return response.data;
  }

  /**
   * Récupérer les analytics de volumes
   */
  async getVolumesAnalytics(dateRange: { date_from: string; date_to: string }): Promise<any> {
    const queryParams = new URLSearchParams({
      date_from: dateRange.date_from,
      date_to: dateRange.date_to
    });

    const response = await this.makeRequest<any>(
      'GET',
      `/analytics/volumes?${queryParams.toString()}`
    );

    return response.data;
  }

  /**
   * Créer un webhook
   */
  async createWebhook(config: WebhookConfig): Promise<any> {
    const response = await this.makeRequest<any>('POST', '/webhooks', config);
    return response.data;
  }

  /**
   * Récupérer la liste des webhooks
   */
  async getWebhooks(): Promise<any[]> {
    const response = await this.makeRequest<any[]>('GET', '/webhooks');
    return response.data;
  }

  /**
   * Mettre à jour un webhook
   */
  async updateWebhook(webhookId: number, config: Partial<WebhookConfig>): Promise<any> {
    const response = await this.makeRequest<any>('PUT', `/webhooks/${webhookId}`, config);
    return response.data;
  }

  /**
   * Supprimer un webhook
   */
  async deleteWebhook(webhookId: number): Promise<void> {
    await this.makeRequest<any>('DELETE', `/webhooks/${webhookId}`);
  }

  /**
   * Tester un webhook
   */
  async testWebhook(webhookId: number): Promise<any> {
    const response = await this.makeRequest<any>('POST', `/webhooks/${webhookId}/test`);
    return response.data;
  }

  // ===== MÉTHODES UTILITAIRES =====

  /**
   * Valider la signature d'un webhook
   */
  validateWebhookSignature(payload: string, signature: string, secret: string): boolean {
    const expectedSignature = createHmac('sha256', secret)
      .update(payload)
      .digest('hex');

    return signature === expectedSignature;
  }

  /**
   * Générer une signature HMAC pour l'authentification
   */
  generateSignature(data: any, timestamp: number): string {
    const payload = JSON.stringify(data) + timestamp;
    return createHmac('sha256', this.config.apiSecret)
      .update(payload)
      .digest('hex');
  }

  // ===== MÉTHODES PRIVÉES =====

  private async makeRequest<T>(
    method: string,
    endpoint: string,
    data?: any,
    options?: { responseType?: 'json' | 'blob' }
  ): Promise<ApiResponse<T>> {
    const url = `${this.config.baseUrl}${endpoint}`;
    const timestamp = Date.now();
    const signature = this.generateSignature(data || {}, timestamp);

    const headers: Record<string, string> = {
      'X-API-Key': this.config.apiKey,
      'X-Signature': signature,
      'X-Timestamp': timestamp.toString(),
      'User-Agent': 'IOB-Partner-SDK/1.0.0',
    };

    if (method !== 'GET' && data) {
      headers['Content-Type'] = 'application/json';
    }

    const requestOptions: RequestInit = {
      method,
      headers,
      body: data && method !== 'GET' ? JSON.stringify(data) : undefined,
    };

    // Ajouter un timeout
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), this.config.timeout);
    requestOptions.signal = controller.signal;

    try {
      const response = await fetch(url, requestOptions);
      clearTimeout(timeoutId);

      if (!response.ok) {
        let errorData;
        try {
          errorData = await response.json();
        } catch {
          errorData = { error: response.statusText };
        }

        throw new IOBPartnerSDKError(
          errorData.error || `HTTP ${response.status}: ${response.statusText}`,
          response.status,
          errorData
        );
      }

      if (options?.responseType === 'blob') {
        const arrayBuffer = await response.arrayBuffer();
        return { success: true, data: arrayBuffer as T };
      }

      const result = await response.json();
      return result;

    } catch (error) {
      clearTimeout(timeoutId);

      if (error instanceof IOBPartnerSDKError) {
        throw error;
      }

      if (error instanceof Error && error.name === 'AbortError') {
        throw new IOBPartnerSDKError('Request timeout');
      }

      throw new IOBPartnerSDKError(
        `Network error: ${error instanceof Error ? error.message : 'Unknown error'}`
      );
    }
  }

  private getContentTypeForFormat(format: string): string {
    switch (format) {
      case 'pdf':
        return 'application/pdf';
      case 'excel':
        return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
      case 'csv':
        return 'text/csv';
      default:
        return 'application/octet-stream';
    }
  }
}

// Export par défaut
export default IOBPartnerSDK;