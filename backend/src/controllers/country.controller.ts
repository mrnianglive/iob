import { Request, Response } from 'express';
import { CountryService } from '../services/country.service';
import { logger } from '../config/logger';

export class CountryController {
  private countryService: CountryService;

  constructor() {
    this.countryService = new CountryService();
  }

  /**
   * Get list of accessible countries
   */
  getCountries = async (req: Request, res: Response) => {
    try {
      if (!req.tenantContext) {
        return res.status(401).json({
          success: false,
          error: 'Authentication required',
        });
      }

      const countries = await this.countryService.getCountries(req.tenantContext);

      res.json({
        success: true,
        data: countries,
      });
    } catch (error: any) {
      logger.error('Get countries error:', error);
      res.status(500).json({
        success: false,
        error: error.message || 'Failed to get countries',
      });
    }
  };

  /**
   * Get country details by ID
   */
  getCountryById = async (req: Request, res: Response) => {
    try {
      if (!req.tenantContext) {
        return res.status(401).json({
          success: false,
          error: 'Authentication required',
        });
      }

      const countryId = parseInt(req.params.id);
      const country = await this.countryService.getCountryById(
        countryId,
        req.tenantContext
      );

      if (!country) {
        return res.status(404).json({
          success: false,
          error: 'Country not found',
        });
      }

      res.json({
        success: true,
        data: country,
      });
    } catch (error: any) {
      logger.error('Get country by ID error:', error);
      res.status(500).json({
        success: false,
        error: error.message || 'Failed to get country',
      });
    }
  };

  /**
   * Update country configuration
   */
  updateCountry = async (req: Request, res: Response) => {
    try {
      const countryId = parseInt(req.params.id);
      const updates = req.body;

      const country = await this.countryService.updateCountry(
        countryId,
        updates
      );

      res.json({
        success: true,
        data: country,
        message: 'Country updated successfully',
      });
    } catch (error: any) {
      logger.error('Update country error:', error);
      res.status(500).json({
        success: false,
        error: error.message || 'Failed to update country',
      });
    }
  };

  /**
   * Get agencies of a country
   */
  getCountryAgencies = async (req: Request, res: Response) => {
    try {
      if (!req.tenantContext) {
        return res.status(401).json({
          success: false,
          error: 'Authentication required',
        });
      }

      const countryId = parseInt(req.params.id);
      const agencies = await this.countryService.getCountryAgencies(
        countryId,
        req.tenantContext
      );

      res.json({
        success: true,
        data: agencies,
      });
    } catch (error: any) {
      logger.error('Get country agencies error:', error);
      res.status(500).json({
        success: false,
        error: error.message || 'Failed to get agencies',
      });
    }
  };

  /**
   * Get partners of a country
   */
  getCountryPartners = async (req: Request, res: Response) => {
    try {
      if (!req.tenantContext) {
        return res.status(401).json({
          success: false,
          error: 'Authentication required',
        });
      }

      const countryId = parseInt(req.params.id);
      const partners = await this.countryService.getCountryPartners(
        countryId,
        req.tenantContext
      );

      res.json({
        success: true,
        data: partners,
      });
    } catch (error: any) {
      logger.error('Get country partners error:', error);
      res.status(500).json({
        success: false,
        error: error.message || 'Failed to get partners',
      });
    }
  };

  /**
   * Get country statistics
   */
  getCountryStatistics = async (req: Request, res: Response) => {
    try {
      if (!req.tenantContext) {
        return res.status(401).json({
          success: false,
          error: 'Authentication required',
        });
      }

      const countryId = parseInt(req.params.id);
      const dateFrom = req.query.dateFrom as string;
      const dateTo = req.query.dateTo as string;

      const statistics = await this.countryService.getCountryStatistics(
        countryId,
        req.tenantContext,
        { dateFrom, dateTo }
      );

      res.json({
        success: true,
        data: statistics,
      });
    } catch (error: any) {
      logger.error('Get country statistics error:', error);
      res.status(500).json({
        success: false,
        error: error.message || 'Failed to get statistics',
      });
    }
  };

  /**
   * Get users of a country
   */
  getCountryUsers = async (req: Request, res: Response) => {
    try {
      const countryId = parseInt(req.params.id);
      const users = await this.countryService.getCountryUsers(countryId);

      res.json({
        success: true,
        data: users,
      });
    } catch (error: any) {
      logger.error('Get country users error:', error);
      res.status(500).json({
        success: false,
        error: error.message || 'Failed to get users',
      });
    }
  };
}